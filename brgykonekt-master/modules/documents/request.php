<?php
/*
 * Consolidated Documents hub for residents:
 *   1. Fill out barangay forms online (D-Forms) — or print a blank copy
 *   2. Request fee-based official documents from the barangay
 *   3. Track D-Form submissions and document requests
 */
include "../../includes/auth_check.php";
include "../../config/database.php";
require_once "../../includes/privacy.php";
require_once "../../includes/forms.php";
require_once "../../includes/dforms.php";

requireRoles([8]);
requirePrivacyConsent($conn);
ensureDFormTables($conn);

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

$flash = (string)($_SESSION["dform_flash"] ?? "");
unset($_SESSION["dform_flash"]);

$resident_result = mysqli_query($conn, "SELECT * FROM residents WHERE user_id = '$user_id'");
$resident = $resident_result ? mysqli_fetch_assoc($resident_result) : null;
$resident_id = $resident ? (int)$resident["id"] : 0;
$is_verified = $resident && $resident["status"] === "Verified";

if (isset($_POST["submit_request"])) {
    if (!$is_verified) {
        $error = "Your resident profile must be verified before requesting documents.";
    } else {
        $document_type_id = (int)$_POST["document_type_id"];
        $purpose = mysqli_real_escape_string($conn, trim($_POST["purpose"]));

        $sql = "INSERT INTO document_requests (resident_id, document_type_id, purpose, status)
                VALUES ('$resident_id', '$document_type_id', '$purpose', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            $message = "Document request submitted successfully.";
        } else {
            $error = "Unable to submit request: " . mysqli_error($conn);
        }
    }
}

$definitions = getDFormDefinitions();

// Active D-Forms grouped by category for the picker.
$grouped = [];
$forms_result = mysqli_query($conn, "SELECT * FROM forms WHERE status = 'Active' ORDER BY category, name");
if ($forms_result) {
    while ($row = mysqli_fetch_assoc($forms_result)) {
        if (isset($definitions[$row["form_key"]])) {
            $grouped[$row["category"]][] = $row;
        }
    }
}

// "Print blank" links: match each D-Form's template back to the blank
// printable registry, respecting its access level.
$blank_by_template = [];
foreach (getPrintableForms() as $printable_key => $printable_def) {
    if (staffLevel() >= $printable_def[2]) {
        $blank_by_template[$printable_def[1]] = $printable_key;
    }
}

// The resident's D-Form submissions.
$submissions = [];
if ($resident_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT form_submissions.*, forms.name AS form_name
                                   FROM form_submissions
                                   INNER JOIN forms ON forms.id = form_submissions.form_id
                                   WHERE form_submissions.resident_id = ?
                                   ORDER BY form_submissions.submitted_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $resident_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($result && ($row = mysqli_fetch_assoc($result))) {
        $submissions[] = $row;
    }
}

$document_types = mysqli_query($conn, "SELECT * FROM document_types ORDER BY document_name ASC");
$my_requests = mysqli_query($conn, "SELECT document_requests.*, document_types.document_name, document_types.fee, payments.receipt_number, payments.payment_method, payments.payment_date
                                    FROM document_requests
                                    INNER JOIN document_types ON document_requests.document_type_id = document_types.id
                                    LEFT JOIN payments ON payments.document_request_id = document_requests.id
                                    WHERE document_requests.resident_id = '$resident_id'
                                    ORDER BY document_requests.requested_at DESC");

include "../../includes/header.php";
renderHeader("Documents", "Fill out barangay forms online, request official documents, and track everything in one place.", "documents");
?>

<section class="panel">
    <?php if ($flash !== "") { ?>
        <p class="message"><?php echo e($flash); ?></p>
    <?php } ?>
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <div class="quick-actions">
        <a class="button secondary" href="#online-forms">Fill Out a Form Online</a>
        <a class="button secondary" href="#document-requests">Request an Official Document</a>
        <a class="button secondary" href="#my-submissions">My Submissions &amp; Requests</a>
    </div>

    <?php if (!$is_verified) { ?>
        <p class="error">Complete and verify your resident profile to submit forms and document requests.
            <a href="<?php echo e(appPath("modules/residents/profile.php")); ?>">Go to My Profile</a></p>
    <?php } ?>
</section>

<section class="panel" id="online-forms">
    <h3>Fill Out Forms Online (D-Forms)</h3>
    <p>Answer the digital version of an official barangay form. Once approved, download the auto-filled document, print it, and sign it. You can also print a blank copy to fill out by hand.</p>

    <?php if (empty($grouped)) { ?>
        <p class="empty-state">No digital forms are available yet.</p>
    <?php } else { ?>
        <?php foreach ($grouped as $category => $category_forms) { ?>
            <h4><?php echo e($category); ?></h4>
            <div class="table-wrap">
                <table>
                    <tbody>
                        <?php foreach ($category_forms as $form_row) { ?>
                            <?php $blank_key = $blank_by_template[$definitions[$form_row["form_key"]]["template"]] ?? ""; ?>
                            <tr>
                                <td><?php echo e($form_row["name"]); ?></td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <?php if ($is_verified) { ?>
                                        <a class="button secondary" href="<?php echo e(appPath("modules/forms/dform_fill.php?form=" . $form_row["form_key"])); ?>">Fill Out Online</a>
                                    <?php } ?>
                                    <?php if ($blank_key !== "") { ?>
                                        <a class="button secondary" href="<?php echo e(appPath("modules/forms/print.php?form=" . $blank_key)); ?>" target="_blank">Print Blank</a>
                                    <?php } ?>
                                    <?php if (!$is_verified && $blank_key === "") { ?>
                                        <span class="badge">Verification required</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    <?php } ?>
</section>

<section class="panel" id="document-requests">
    <h3>Request Official Documents</h3>
    <p>Fee-based documents issued by the barangay. Track verification, payment, release, or rejection below.</p>

    <?php if (!$is_verified) { ?>
        <p class="error">Complete and verify your resident profile before creating document requests.</p>
    <?php } else { ?>
        <form method="POST">
            <label for="document_type_id">Document Type</label>
            <select id="document_type_id" name="document_type_id" required>
                <option value="">Select document</option>
                <?php if ($document_types) { ?>
                    <?php while ($doc = mysqli_fetch_assoc($document_types)) { ?>
                        <option value="<?php echo e($doc["id"]); ?>">
                            <?php echo e($doc["document_name"]); ?> - PHP <?php echo e(number_format((float)$doc["fee"], 2)); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>

            <label for="purpose">Purpose</label>
            <input id="purpose" type="text" name="purpose" required placeholder="Employment, scholarship, business permit, travel">

            <button type="submit" name="submit_request">Submit Request</button>
        </form>
    <?php } ?>
</section>

<section class="panel" id="my-submissions">
    <h3>My D-Form Submissions</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Form</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Official Document</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)) { ?>
                    <tr><td colspan="5">No digital form submissions yet.</td></tr>
                <?php } else { ?>
                    <?php foreach ($submissions as $submission) { ?>
                        <tr>
                            <td><?php echo e($submission["form_name"]); ?></td>
                            <td><?php echo e($submission["submitted_at"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower($submission["workflow_status"])); ?>"><?php echo e($submission["workflow_status"]); ?></span></td>
                            <td><?php echo e($submission["remarks"] !== "" ? $submission["remarks"] : "—"); ?></td>
                            <td>
                                <?php if ($submission["workflow_status"] === "Approved") { ?>
                                    <a class="button secondary" href="<?php echo e(appPath("modules/forms/dform_print.php?id=" . (int)$submission["id"])); ?>" target="_blank">Download / Print PDF</a>
                                <?php } else { ?>
                                    Available after approval
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <p><small>After downloading, print the document and sign it. The barangay processes your request upon presentation of the signed copy.</small></p>

    <h3>My Document Requests</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Purpose</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Date Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_requests && mysqli_num_rows($my_requests) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($my_requests)) { ?>
                        <tr>
                            <td><?php echo e($row["document_name"]); ?></td>
                            <td><?php echo e($row["purpose"]); ?></td>
                            <td>PHP <?php echo e(number_format((float)$row["fee"], 2)); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <?php if (!empty($row["receipt_number"])) { ?>
                                    <?php echo e($row["receipt_number"]); ?><br>
                                    <?php echo e($row["payment_method"]); ?><br>
                                    <?php echo e($row["payment_date"]); ?>
                                <?php } else { ?>
                                    No receipt yet
                                <?php } ?>
                            </td>
                            <td><?php echo e($row["requested_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="6">No document requests yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
