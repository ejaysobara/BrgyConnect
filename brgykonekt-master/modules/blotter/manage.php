<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([1, 2, 6]);

$message = "";
$statuses = ["Pending", "Under Investigation", "For Mediation", "Settled", "Court Action", "Closed"];

if (isset($_POST["update_case"])) {
    $case_id = (int)$_POST["case_id"];
    $status = mysqli_real_escape_string($conn, $_POST["status"]);
    $mediation_schedule_value = str_replace("T", " ", $_POST["mediation_schedule"]);
    $mediation_schedule = mysqli_real_escape_string($conn, $mediation_schedule_value);
    $resolution = mysqli_real_escape_string($conn, trim($_POST["resolution"]));
    $assigned_to = (int)$_SESSION["user_id"];

    if (in_array($status, $statuses)) {
        mysqli_query($conn, "UPDATE blotter_cases
                             SET status = '$status',
                                 mediation_schedule = " . ($mediation_schedule === "" ? "NULL" : "'$mediation_schedule'") . ",
                                 resolution = '$resolution',
                                 assigned_to = '$assigned_to'
                             WHERE id = '$case_id'");
        addAuditLog($conn, $_SESSION["user_id"], "update_blotter_case", "case#$case_id", $status);
        $message = "Blotter case updated.";
    }
}

$cases = mysqli_query($conn, "SELECT blotter_cases.*, residents.first_name, residents.middle_name, residents.last_name
                              FROM blotter_cases
                              INNER JOIN residents ON blotter_cases.resident_id = residents.id
                              ORDER BY blotter_cases.created_at DESC");

include "../../includes/header.php";
renderHeader("Blotter Cases", "Review complaints, assign mediation, capture resolution, and track incident reports.", "blotter");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Complainant</th>
                    <th>Respondent</th>
                    <th>Incident</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Evidence</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($cases && mysqli_num_rows($cases) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($cases)) { ?>
                        <?php
                        $mediation_value = "";
                        if (!empty($row["mediation_schedule"])) {
                            $mediation_value = date("Y-m-d\TH:i", strtotime($row["mediation_schedule"]));
                        }
                        ?>
                        <tr>
                            <td><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></td>
                            <td><?php echo e($row["respondent_name"]); ?></td>
                            <td>
                                <?php echo e($row["incident_date"]); ?><br>
                                <?php echo e($row["incident_location"]); ?>
                            </td>
                            <td><?php echo e($row["complaint_details"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <?php if (!empty($row["evidence_path"])) { ?>
                                    <a href="<?php echo e(appPath($row["evidence_path"])); ?>" target="_blank">View evidence</a>
                                <?php } else { ?>
                                    No upload
                                <?php } ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="case_id" value="<?php echo e($row["id"]); ?>">
                                    <label>Status</label>
                                    <select name="status">
                                        <?php foreach ($statuses as $status) { ?>
                                            <option value="<?php echo e($status); ?>" <?php echo $row["status"] === $status ? "selected" : ""; ?>><?php echo e($status); ?></option>
                                        <?php } ?>
                                    </select>
                                    <label>Mediation Schedule</label>
                                    <input type="datetime-local" name="mediation_schedule" value="<?php echo e($mediation_value); ?>">
                                    <label>Resolution</label>
                                    <textarea name="resolution"><?php echo e($row["resolution"] ?? ""); ?></textarea>
                                    <button type="submit" name="update_case">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="7">No blotter cases found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
