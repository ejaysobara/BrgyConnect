<?php
/*
 * Digital version of an official barangay paper form. Fields are generated
 * from the registry in includes/dforms.php, pre-filled from the resident's
 * verified profile, validated server-side, and stored with a prepared
 * statement as one form_submissions row awaiting workflow approval.
 */
include "../../includes/auth_check.php";
include "../../config/database.php";
require_once "../../includes/privacy.php";
require_once "../../includes/dforms.php";

requireRoles([8]);
requirePrivacyConsent($conn);
ensureDFormTables($conn);

$definitions = getDFormDefinitions();
$form_key = (string)($_GET["form"] ?? $_POST["form_key"] ?? "");

if (!isset($definitions[$form_key])) {
    redirectTo("modules/forms/dforms.php");
}

$definition = $definitions[$form_key];
$form_record = getDFormRecord($conn, $form_key);

if (!$form_record || $form_record["status"] !== "Active") {
    redirectTo("modules/forms/dforms.php");
}

$user_id = (int)$_SESSION["user_id"];
$resident = privacyCurrentResident($conn);
$message = "";
$errors = [];
$values = [];

// Pre-fill from the resident profile; POST values win on re-display.
foreach ($definition["fields"] as $field_key => $field) {
    $values[$field_key] = isset($field["prefill"]) ? dformPrefillValue($resident, $field["prefill"]) : "";
}

if (isset($_POST["submit_dform"])) {
    if (!$resident || $resident["status"] !== "Verified") {
        $errors[] = "Your resident profile must be verified before submitting digital forms.";
    } else {
        list($values, $errors) = validateDFormInput($definition, $_POST);

        if (empty($errors)) {
            $resident_id = (int)$resident["id"];
            $form_id = (int)$form_record["id"];
            $payload = json_encode($values, JSON_UNESCAPED_UNICODE);

            $stmt = mysqli_prepare($conn, "INSERT INTO form_submissions (form_id, resident_id, submission_data, workflow_status) VALUES (?, ?, ?, 'Pending')");
            mysqli_stmt_bind_param($stmt, "iis", $form_id, $resident_id, $payload);

            if (mysqli_stmt_execute($stmt)) {
                addAuditLog($conn, $user_id, "D-Form Submitted", $definition["name"], "Submission #" . mysqli_insert_id($conn));
                $_SESSION["dform_flash"] = $definition["name"] . " submitted successfully. You will be able to download the official document once it is approved.";
                redirectTo("modules/forms/dforms.php");
            } else {
                $errors[] = "Unable to submit the form. Please try again.";
            }
        } else {
            // Keep whatever the resident typed for re-display.
            foreach ($definition["fields"] as $field_key => $field) {
                $values[$field_key] = trim((string)($_POST[$field_key] ?? ""));
            }
        }
    }
}

include "../../includes/header.php";
renderHeader($definition["name"] . " (D-Form)", "Digital version of the official barangay form. Your answers are transferred onto the printable official layout after approval.", "dforms");
?>

<section class="panel">
    <div class="quick-actions">
        <a class="button secondary" href="<?php echo e(appPath("modules/forms/dforms.php")); ?>">&larr; Back to D-Forms</a>
        <span class="badge"><?php echo e($definition["category"]); ?></span>
    </div>

    <?php foreach ($errors as $field_error) { ?>
        <p class="error"><?php echo e($field_error); ?></p>
    <?php } ?>

    <?php if (!$resident || $resident["status"] !== "Verified") { ?>
        <p class="error">Complete and verify your resident profile before submitting this form.</p>
    <?php } else { ?>
        <form method="POST">
            <input type="hidden" name="form_key" value="<?php echo e($form_key); ?>">
            <div class="grid-2">
                <?php foreach ($definition["fields"] as $field_key => $field) { ?>
                    <?php
                    $input_id = "df_" . $field_key;
                    $required = !empty($field["required"]);
                    $is_wide = in_array($field["type"], ["textarea"], true);
                    ?>
                    <div <?php echo $is_wide ? 'style="grid-column: 1 / -1;"' : ""; ?>>
                        <label for="<?php echo e($input_id); ?>">
                            <?php echo e($field["label"]); ?><?php echo $required ? " *" : ""; ?>
                        </label>
                        <?php if ($field["type"] === "textarea") { ?>
                            <textarea id="<?php echo e($input_id); ?>" name="<?php echo e($field_key); ?>" <?php echo $required ? "required" : ""; ?>><?php echo e($values[$field_key]); ?></textarea>
                        <?php } elseif ($field["type"] === "select") { ?>
                            <select id="<?php echo e($input_id); ?>" name="<?php echo e($field_key); ?>" <?php echo $required ? "required" : ""; ?>>
                                <option value="">Select...</option>
                                <?php foreach ($field["options"] as $option) { ?>
                                    <option value="<?php echo e($option); ?>" <?php echo $values[$field_key] === $option ? "selected" : ""; ?>><?php echo e($option); ?></option>
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <input id="<?php echo e($input_id); ?>"
                                   type="<?php echo e(in_array($field["type"], ["date", "time", "email", "number", "tel"], true) ? $field["type"] : "text"); ?>"
                                   name="<?php echo e($field_key); ?>"
                                   value="<?php echo e($values[$field_key]); ?>"
                                   <?php echo $field["type"] === "number" ? 'min="0"' : ""; ?>
                                   <?php echo $required ? "required" : ""; ?>>
                        <?php } ?>
                        <?php if (!empty($field["help"])) { ?>
                            <small><?php echo e($field["help"]); ?></small>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <p><small>By submitting, you confirm that the information above is true and correct. Your data is processed under RA 10173 (Data Privacy Act of 2012).</small></p>
            <button type="submit" name="submit_dform">Submit D-Form</button>
        </form>
    <?php } ?>
</section>

<?php include "../../includes/footer.php"; ?>
