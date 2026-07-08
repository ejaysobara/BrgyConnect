<?php
/*
 * Auto-fills the official barangay form with a resident's approved D-Form
 * submission and renders it as a print-ready page (downloadable as PDF via
 * the browser print dialog), preserving the physical layout of the paper
 * form. The resident signs the printed copy.
 *
 * Access: the owning resident (approved submissions only) and staff
 * (Secretary and above, any status — used to preview before approval).
 */
include "../../includes/auth_check.php";
include "../../config/database.php";
require_once "../../includes/privacy.php";
require_once "../../includes/forms.php";
require_once "../../includes/dforms.php";

requireLogin();
ensureDFormTables($conn);

$submission_id = (int)($_GET["id"] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT form_submissions.*, forms.name AS form_name, forms.form_key, forms.template,
                                      residents.user_id AS owner_user_id, residents.resident_code
                               FROM form_submissions
                               INNER JOIN forms ON forms.id = form_submissions.form_id
                               INNER JOIN residents ON residents.id = form_submissions.resident_id
                               WHERE form_submissions.id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $submission_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$submission = $result ? mysqli_fetch_assoc($result) : null;

if (!$submission) {
    http_response_code(404);
    exit("Submission not found.");
}

$is_owner = (int)$submission["owner_user_id"] === (int)($_SESSION["user_id"] ?? 0);
$is_reviewer = hasStaffLevel(3);

if (!$is_reviewer && (!$is_owner || $submission["workflow_status"] !== "Approved")) {
    redirectTo("auth/unauthorized.php");
}

$template_path = printableFormTemplate($submission["template"]);
if (!is_file($template_path)) {
    http_response_code(404);
    exit("This form template is missing.");
}

// Submitted answers + values computed at print time (issuance date, codes).
$dform = json_decode($submission["submission_data"], true) ?: [];
$dform["resident_code"] = $dform["resident_code"] ?? $submission["resident_code"];
$dform["date_today"] = date("F j, Y");
$dform["issue_day"] = date("jS");
$dform["issue_month"] = date("F");
$dform["issue_year"] = date("y");
$dform["control_number"] = "DF-" . date("Y") . "-" . str_pad((string)$submission["id"], 5, "0", STR_PAD_LEFT);

$brgy = getBarangayIdentity($conn);
$brgy_officials = getBarangayOfficials($conn);
$is_draft = $submission["workflow_status"] !== "Approved";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($submission["form_name"]); ?> - <?php echo e($brgy["name"]); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", serif; color: #111; margin: 0; background: #f0f0f0; }
        .sheet { position: relative; background: #fff; width: 8.5in; min-height: 11in; margin: 20px auto; padding: 0.8in 0.9in; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
        .form-header { display: flex; align-items: center; gap: 16px; justify-content: center; text-align: center; margin-bottom: 8px; }
        .form-header img { width: 80px; height: 80px; object-fit: contain; }
        .form-header .logo-placeholder { width: 80px; height: 80px; border: 1px dashed #999; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999; }
        .form-header h4 { margin: 2px 0; font-weight: normal; font-size: 13px; }
        .form-header h2 { margin: 2px 0; font-size: 16px; text-transform: uppercase; }
        .form-title { text-align: center; font-size: 17px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 18px 0 14px; }
        .section-title { text-align: center; font-weight: bold; margin: 16px 0 8px; }
        .rule { border-top: 2px solid #111; margin: 10px 0 16px; }
        p, td, th, label { font-size: 13px; }
        .line { display: inline-block; border-bottom: 1px solid #111; min-width: 180px; }
        .line.short { min-width: 90px; }
        .line.long { min-width: 320px; }
        .line.full { display: block; width: 100%; height: 18px; }
        .row { margin: 10px 0; }
        .writing-lines .line.full { margin-bottom: 12px; }
        .two-col { display: flex; gap: 30px; }
        .two-col > div { flex: 1; }
        .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid #111; margin-right: 6px; vertical-align: middle; }
        .sig-block { margin-top: 48px; display: flex; justify-content: space-between; gap: 40px; }
        .sig { text-align: center; flex: 1; }
        .sig .line { width: 100%; }
        .sig small { display: block; margin-top: 4px; }
        .toolbar { text-align: center; padding: 14px; }
        .toolbar button { padding: 10px 26px; font-size: 15px; cursor: pointer; }
        .control-no { text-align: right; font-size: 11px; color: #333; margin-bottom: 4px; }
        .draft-mark { position: absolute; top: 40%; left: 0; right: 0; text-align: center; font-size: 90px; color: rgba(200, 30, 30, 0.12); transform: rotate(-25deg); pointer-events: none; letter-spacing: 12px; }
        @media print {
            body { background: #fff; }
            .sheet { box-shadow: none; margin: 0; width: auto; min-height: auto; padding: 0.2in 0.3in; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Download / Print PDF</button>
        <?php if ($is_draft) { ?>
            <span style="color:#8a1f2d; font-size:13px;">Preview only — this submission is <?php echo e($submission["workflow_status"]); ?>.</span>
        <?php } ?>
    </div>
    <div class="sheet">
        <?php if ($is_draft) { ?>
            <div class="draft-mark">DRAFT</div>
        <?php } ?>
        <div class="control-no">Control No.: <?php echo e($dform["control_number"]); ?></div>
        <div class="form-header">
            <?php if ($brgy["logo"] !== "" && is_file(dirname(__DIR__, 2) . "/" . $brgy["logo"])) { ?>
                <img src="<?php echo e(appPath($brgy["logo"])); ?>" alt="Barangay logo">
            <?php } else { ?>
                <div class="logo-placeholder">Barangay<br>Logo</div>
            <?php } ?>
            <div>
                <h4>Republic of the Philippines</h4>
                <h4><?php echo e($brgy["address"]); ?></h4>
                <h2><?php echo e($brgy["name"]); ?></h2>
                <h4>Office of the Punong Barangay</h4>
            </div>
        </div>
        <div class="rule"></div>

        <?php include $template_path; ?>

        <p style="margin-top: 36px; font-size: 10px; color: #555; text-align: right;">
            Date printed: <?php echo e(date("F j, Y g:i A")); ?>
        </p>
    </div>
</body>
</html>
