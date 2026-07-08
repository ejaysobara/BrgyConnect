<?php
/*
 * Data Privacy Notice — Republic Act No. 10173 (Data Privacy Act of 2012).
 *
 * Shown before any personal information is collected:
 *   - context=register  → before the resident registration form (anonymous)
 *   - ?return=...       → before a logged-in resident page that collects
 *                         personal data (D-Forms, document requests,
 *                         appointments, blotter)
 *
 * Accept  → consent recorded (session + resident record) and audit-logged.
 * Decline → session destroyed, redirected to the landing page with
 *           "Registration cancelled because consent was not provided."
 */
include "../includes/auth_check.php";
include "../config/database.php";
require_once "../includes/privacy.php";

$context = ($_GET["context"] ?? "") === "register" ? "register" : "portal";

// Only allow same-site relative return targets.
$return = (string)($_POST["return"] ?? $_GET["return"] ?? "");
if ($return === "" || $return[0] !== "/" || strpos($return, "//") === 0 || strpos($return, "\\") !== false) {
    $return = "";
}

if ($context === "portal" && !isset($_SESSION["user_id"])) {
    redirectTo("auth/login.php");
}

$error = "";

if (isset($_POST["privacy_decision"])) {
    $user_id = (int)($_SESSION["user_id"] ?? 0);

    if ($_POST["privacy_decision"] === "accept") {
        if (empty($_POST["read_notice"]) || empty($_POST["give_consent"])) {
            $error = "Please tick both boxes to confirm that you have read the notice and voluntarily give your consent.";
        } else {
            $_SESSION["privacy_consent"] = 1;
            $_SESSION["privacy_consent_date"] = date("Y-m-d H:i:s");
            $_SESSION["privacy_consent_version"] = PRIVACY_NOTICE_VERSION;

            addAuditLog($conn, $user_id, "Privacy Consent Accepted", "Data Privacy Notice v" . PRIVACY_NOTICE_VERSION, privacyClientDetails());

            $resident = privacyCurrentResident($conn);
            if ($resident) {
                recordResidentConsent($conn, (int)$resident["id"]);
            }

            if ($context === "register") {
                redirectTo("auth/register.php");
            }
            if ($return !== "") {
                header("Location: " . $return);
                exit();
            }
            redirectTo("resident/dashboard.php");
        }
    } else {
        addAuditLog($conn, $user_id, "Privacy Consent Declined", "Data Privacy Notice v" . PRIVACY_NOTICE_VERSION, privacyClientDetails());

        session_unset();
        session_destroy();
        redirectTo("auth/login.php?notice=privacy_declined");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy Notice - BrgyKonekt</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .privacy-shell { max-width: 860px; margin: 32px auto; padding: 0 16px; }
        .privacy-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 42, 90, 0.12); overflow: hidden; }
        .privacy-head { background: linear-gradient(135deg, #123a7a, #1d5bbf); color: #fff; padding: 28px 32px; text-align: center; }
        .privacy-head h1 { margin: 0 0 4px; font-size: 26px; letter-spacing: 1px; }
        .privacy-head h2 { margin: 0; font-size: 18px; font-weight: 600; }
        .privacy-head p { margin: 10px 0 0; opacity: .9; font-size: 14px; }
        .privacy-body { padding: 8px 32px 16px; max-height: 52vh; overflow-y: auto; font-size: 15.5px; line-height: 1.7; color: #1c2b45; }
        .privacy-body h3 { color: #123a7a; margin: 22px 0 8px; font-size: 16px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #e3ebf8; padding-bottom: 6px; }
        .privacy-body ul { margin: 6px 0; padding-left: 22px; }
        .privacy-body li { margin: 4px 0; }
        .privacy-consent { padding: 20px 32px; border-top: 1px solid #e3ebf8; background: #f6f9ff; }
        .privacy-consent label { display: flex; gap: 10px; align-items: flex-start; margin: 10px 0; font-size: 15px; line-height: 1.5; cursor: pointer; }
        .privacy-consent input[type="checkbox"] { margin-top: 4px; width: 18px; height: 18px; flex: none; }
        .privacy-actions { display: flex; gap: 12px; padding: 18px 32px 26px; background: #f6f9ff; flex-wrap: wrap; }
        .privacy-actions button { flex: 1; min-width: 200px; padding: 14px 20px; font-size: 16px; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; }
        .btn-continue { background: #1d5bbf; color: #fff; }
        .btn-continue:hover { background: #123a7a; }
        .btn-decline { background: #fff; color: #8a1f2d; border: 1px solid #d9a0a8 !important; }
        .btn-decline:hover { background: #fbeef0; }
        .privacy-error { margin: 16px 32px 0; padding: 12px 16px; background: #fbeef0; color: #8a1f2d; border-radius: 8px; font-size: 14px; }
        @media (max-width: 640px) { .privacy-head, .privacy-body, .privacy-consent, .privacy-actions { padding-left: 18px; padding-right: 18px; } }
    </style>
</head>
<body class="auth-page">
    <div class="privacy-shell">
        <div class="privacy-card">
            <div class="privacy-head">
                <h1>BARANGAY CONNECT</h1>
                <h2>Data Privacy Notice</h2>
                <p>Republic Act No. 10173 &mdash; Data Privacy Act of 2012</p>
            </div>

            <?php if ($error !== "") { ?>
                <p class="privacy-error"><?php echo e($error); ?></p>
            <?php } ?>

            <div class="privacy-body">
                <p style="margin-top:18px;">
                    Barangay Connect collects and processes your personal information solely for the
                    purpose of providing official barangay services.
                </p>

                <h3>Information We Collect</h3>
                <ul>
                    <li>Full Name</li>
                    <li>Address</li>
                    <li>Contact Number</li>
                    <li>Email Address</li>
                    <li>Date of Birth</li>
                    <li>Civil Status</li>
                    <li>Household Information</li>
                    <li>Requested Documents</li>
                    <li>Appointment Details</li>
                </ul>

                <h3>Purpose of Collection</h3>
                <ul>
                    <li>Resident Registration</li>
                    <li>Barangay Clearance</li>
                    <li>Certificate Requests</li>
                    <li>Appointments</li>
                    <li>Blotter Reports</li>
                    <li>Health Programs</li>
                    <li>Disaster Response</li>
                    <li>Community Services</li>
                </ul>

                <h3>Data Protection</h3>
                <p>
                    Your information is securely stored and processed in accordance with Republic Act
                    No. 10173 (Data Privacy Act of 2012). Your information will only be accessed by
                    authorized barangay personnel. It will never be disclosed without legal authority
                    or your consent unless required by law.
                </p>

                <h3>Your Rights</h3>
                <p>Under the Data Privacy Act of 2012, you have the right to:</p>
                <ul>
                    <li>Access your personal information</li>
                    <li>Correct inaccurate information</li>
                    <li>Request deletion when legally applicable</li>
                    <li>Withdraw consent where applicable</li>
                </ul>
            </div>

            <form method="POST">
                <input type="hidden" name="return" value="<?php echo e($return); ?>">
                <div class="privacy-consent">
                    <label>
                        <input type="checkbox" name="read_notice" value="1">
                        <span>I have read and understood the Data Privacy Notice.</span>
                    </label>
                    <label>
                        <input type="checkbox" name="give_consent" value="1">
                        <span>I voluntarily consent to the collection, storage, and processing of my
                        personal information for official barangay purposes.</span>
                    </label>
                </div>
                <div class="privacy-actions">
                    <button class="btn-continue" type="submit" name="privacy_decision" value="accept">
                        <?php echo $context === "register" ? "Continue Registration" : "I Agree — Continue"; ?>
                    </button>
                    <button class="btn-decline" type="submit" name="privacy_decision" value="decline" formnovalidate>Decline</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
