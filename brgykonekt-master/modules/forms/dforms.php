<?php
/*
 * D-Forms hub for residents: choose a barangay service to answer digitally,
 * and track submitted forms through the approval workflow. Approved forms can
 * be downloaded as the auto-filled official document (printable PDF).
 */
include "../../includes/auth_check.php";
include "../../config/database.php";
require_once "../../includes/privacy.php";
require_once "../../includes/dforms.php";

requireRoles([8]);
requirePrivacyConsent($conn);
ensureDFormTables($conn);

$user_id = (int)$_SESSION["user_id"];
$resident = privacyCurrentResident($conn);
$resident_id = $resident ? (int)$resident["id"] : 0;
$is_verified = $resident && $resident["status"] === "Verified";

$definitions = getDFormDefinitions();

$flash = (string)($_SESSION["dform_flash"] ?? "");
unset($_SESSION["dform_flash"]);

// Group active forms by category for the service picker.
$grouped = [];
$forms_result = mysqli_query($conn, "SELECT * FROM forms WHERE status = 'Active' ORDER BY category, name");
if ($forms_result) {
    while ($row = mysqli_fetch_assoc($forms_result)) {
        if (isset($definitions[$row["form_key"]])) {
            $grouped[$row["category"]][] = $row;
        }
    }
}

// The resident's own submissions.
$submissions = [];
if ($resident_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT form_submissions.*, forms.name AS form_name, forms.form_key
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

include "../../includes/header.php";
renderHeader("Digital Forms (D-Forms)", "Answer barangay forms online. Approved submissions are transferred onto the official printable form, ready to download and sign.", "dforms");
?>

<section class="panel">
    <h3>Available Services</h3>
    <?php if ($flash !== "") { ?>
        <p class="message"><?php echo e($flash); ?></p>
    <?php } ?>
    <?php if (!$is_verified) { ?>
        <p class="error">Your resident profile must be completed and verified by the barangay before you can submit digital forms.
            <a href="<?php echo e(appPath("modules/residents/profile.php")); ?>">Go to My Profile</a></p>
    <?php } ?>

    <?php if (empty($grouped)) { ?>
        <p class="empty-state">No digital forms are available yet.</p>
    <?php } else { ?>
        <?php foreach ($grouped as $category => $category_forms) { ?>
            <h4><?php echo e($category); ?></h4>
            <div class="table-wrap">
                <table>
                    <tbody>
                        <?php foreach ($category_forms as $form_row) { ?>
                            <tr>
                                <td><?php echo e($form_row["name"]); ?></td>
                                <td style="text-align:right;">
                                    <?php if ($is_verified) { ?>
                                        <a class="button secondary" href="<?php echo e(appPath("modules/forms/dform_fill.php?form=" . $form_row["form_key"])); ?>">Fill Out Online</a>
                                    <?php } else { ?>
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

<section class="panel">
    <h3>My Submitted D-Forms</h3>
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
</section>

<section class="panel">
    <h3>Blank Printable Forms</h3>
    <p>Prefer to fill out a form by hand? Print a blank copy below.</p>
    <?php include "form_list.php"; ?>
</section>

<?php include "../../includes/footer.php"; ?>
