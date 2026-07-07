<?php
/*
 * Renders a blank form/certificate as a print-ready page.
 * Residents and staff can print it or save it as PDF from the browser dialog.
 * The barangay header (name, address, logo) comes from System Settings.
 */
include "../../includes/auth_check.php";
include "../../config/database.php";
require_once "../../includes/forms.php";

requireLogin();

$forms = getPrintableForms();
$key = $_GET["form"] ?? "";

if (!isset($forms[$key]) || staffLevel() < $forms[$key][2]) {
    redirectTo("auth/unauthorized.php");
}

$form = $forms[$key];
$template_path = printableFormTemplate($form[1]);

if (!is_file($template_path)) {
    http_response_code(404);
    exit("This form template is missing.");
}

$brgy = getBarangayIdentity($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($form[0]); ?> - <?php echo e($brgy["name"]); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", serif; color: #111; margin: 0; background: #f0f0f0; }
        .sheet { background: #fff; width: 8.5in; min-height: 11in; margin: 20px auto; padding: 0.8in 0.9in; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
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
        @media print {
            body { background: #fff; }
            .sheet { box-shadow: none; margin: 0; width: auto; min-height: auto; padding: 0.2in 0.3in; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>
    <div class="sheet">
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
    </div>
</body>
</html>
