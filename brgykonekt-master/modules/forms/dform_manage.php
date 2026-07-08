<?php
/*
 * Staff workflow for D-Form submissions (Secretary and above).
 * Pending → Approved / Rejected, with remarks and audit logging.
 * Approving releases the auto-filled official document to the resident.
 */
include "../../includes/auth_check.php";
include "../../config/database.php";
require_once "../../includes/dforms.php";

requireStaffLevel(3);
ensureDFormTables($conn);

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

if (isset($_POST["review_submission"])) {
    $submission_id = (int)$_POST["submission_id"];
    $decision = $_POST["decision"] === "Approved" ? "Approved" : "Rejected";
    $remarks = substr(trim((string)$_POST["remarks"]), 0, 255);

    $stmt = mysqli_prepare($conn, "UPDATE form_submissions SET workflow_status = ?, remarks = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ? AND workflow_status = 'Pending'");
    mysqli_stmt_bind_param($stmt, "ssii", $decision, $remarks, $user_id, $submission_id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        addAuditLog($conn, $user_id, "D-Form " . $decision, "Submission #" . $submission_id, $remarks);
        $message = "Submission #" . $submission_id . " marked as " . $decision . ".";
    } else {
        $error = "Unable to update the submission (it may have been reviewed already).";
    }
}

$status_filter = in_array($_GET["status"] ?? "", ["Pending", "Approved", "Rejected"], true) ? $_GET["status"] : "Pending";

$stmt = mysqli_prepare($conn, "SELECT form_submissions.*, forms.name AS form_name, forms.form_key,
                                      residents.first_name, residents.last_name, residents.resident_code,
                                      reviewers.full_name AS reviewer_name
                               FROM form_submissions
                               INNER JOIN forms ON forms.id = form_submissions.form_id
                               INNER JOIN residents ON residents.id = form_submissions.resident_id
                               LEFT JOIN users AS reviewers ON reviewers.id = form_submissions.reviewed_by
                               WHERE form_submissions.workflow_status = ?
                               ORDER BY form_submissions.submitted_at ASC");
mysqli_stmt_bind_param($stmt, "s", $status_filter);
mysqli_stmt_execute($stmt);
$submissions_result = mysqli_stmt_get_result($stmt);

$definitions = getDFormDefinitions();

include "../../includes/header.php";
renderHeader("D-Form Submissions", "Review digital form submissions, then approve to release the auto-filled official document.", "dforms");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <div class="quick-actions">
        <?php foreach (["Pending", "Approved", "Rejected"] as $tab) { ?>
            <a class="button <?php echo $status_filter === $tab ? "" : "secondary"; ?>" href="?status=<?php echo e($tab); ?>"><?php echo e($tab); ?></a>
        <?php } ?>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Form</th>
                    <th>Resident</th>
                    <th>Submitted Answers</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$submissions_result || mysqli_num_rows($submissions_result) === 0) { ?>
                    <tr><td colspan="6">No <?php echo e(strtolower($status_filter)); ?> submissions.</td></tr>
                <?php } else { ?>
                    <?php while ($row = mysqli_fetch_assoc($submissions_result)) { ?>
                        <?php
                        $data = json_decode($row["submission_data"], true) ?: [];
                        $fields = $definitions[$row["form_key"]]["fields"] ?? [];
                        ?>
                        <tr>
                            <td><?php echo (int)$row["id"]; ?></td>
                            <td><?php echo e($row["form_name"]); ?></td>
                            <td>
                                <?php echo e($row["first_name"] . " " . $row["last_name"]); ?><br>
                                <small><?php echo e($row["resident_code"]); ?></small>
                            </td>
                            <td>
                                <details>
                                    <summary>View answers</summary>
                                    <?php foreach ($data as $answer_key => $answer_value) { ?>
                                        <div><small><strong><?php echo e($fields[$answer_key]["label"] ?? $answer_key); ?>:</strong> <?php echo e($answer_value); ?></small></div>
                                    <?php } ?>
                                </details>
                                <a href="<?php echo e(appPath("modules/forms/dform_print.php?id=" . (int)$row["id"] . "&preview=1")); ?>" target="_blank"><small>Preview official document</small></a>
                            </td>
                            <td><?php echo e($row["submitted_at"]); ?></td>
                            <td>
                                <?php if ($row["workflow_status"] === "Pending") { ?>
                                    <form method="POST">
                                        <input type="hidden" name="submission_id" value="<?php echo (int)$row["id"]; ?>">
                                        <input type="text" name="remarks" placeholder="Remarks (optional)">
                                        <div class="quick-actions">
                                            <button type="submit" name="review_submission" value="1" onclick="this.form.decision.value='Approved';">Approve</button>
                                            <button class="secondary" type="submit" name="review_submission" value="1" onclick="this.form.decision.value='Rejected';">Reject</button>
                                        </div>
                                        <input type="hidden" name="decision" value="Approved">
                                    </form>
                                <?php } else { ?>
                                    <span class="status-pill status-<?php echo e(strtolower($row["workflow_status"])); ?>"><?php echo e($row["workflow_status"]); ?></span><br>
                                    <small>By <?php echo e($row["reviewer_name"] ?? "—"); ?> on <?php echo e($row["reviewed_at"]); ?></small>
                                    <?php if ($row["remarks"] !== "") { ?>
                                        <br><small>Remarks: <?php echo e($row["remarks"]); ?></small>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
