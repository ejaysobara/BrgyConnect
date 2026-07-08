<?php
/*
 * Data Privacy Act of 2012 (Republic Act No. 10173) consent workflow.
 *
 * Every page that collects personal information calls
 * requirePrivacyConsent($conn) after login; unauthenticated registration is
 * gated inside auth/register.php using the session flag set by
 * auth/privacy_notice.php. Consent (yes/no, timestamp, notice version) is
 * stored on the resident record and every accept/decline is audit-logged
 * with IP address and user agent.
 */

require_once __DIR__ . "/auth_check.php";

// Bump this whenever the wording of the Data Privacy Notice changes.
const PRIVACY_NOTICE_VERSION = "1.0";

// Adds the consent columns to `residents` if the SQL migration has not been
// run yet (mirrors migrations/2026-07-08_dforms_and_privacy.sql).
function ensurePrivacyColumns($conn) {
    static $done = false;
    if ($done || !$conn) {
        return;
    }
    $done = true;

    $check = mysqli_query($conn, "SHOW COLUMNS FROM residents LIKE 'privacy_consent'");
    if ($check && mysqli_num_rows($check) === 0) {
        mysqli_query($conn, "ALTER TABLE residents ADD COLUMN privacy_consent TINYINT(1) NOT NULL DEFAULT 0");
        mysqli_query($conn, "ALTER TABLE residents ADD COLUMN privacy_consent_date DATETIME DEFAULT NULL");
        mysqli_query($conn, "ALTER TABLE residents ADD COLUMN privacy_version VARCHAR(20) DEFAULT ''");
    }
}

function privacyClientDetails() {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $agent = substr((string)($_SERVER["HTTP_USER_AGENT"] ?? "unknown"), 0, 250);
    return "IP: " . $ip . " | User Agent: " . $agent . " | Notice version: " . PRIVACY_NOTICE_VERSION;
}

// Resident row of the currently logged-in user, or null.
function privacyCurrentResident($conn) {
    if (!isset($_SESSION["user_id"])) {
        return null;
    }
    $stmt = mysqli_prepare($conn, "SELECT * FROM residents WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_assoc($result) : null;
}

// Writes consent to the resident record (flag + timestamp + notice version).
function recordResidentConsent($conn, $resident_id) {
    ensurePrivacyColumns($conn);
    $version = PRIVACY_NOTICE_VERSION;
    $stmt = mysqli_prepare($conn, "UPDATE residents SET privacy_consent = 1, privacy_consent_date = NOW(), privacy_version = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $version, $resident_id);
    mysqli_stmt_execute($stmt);
}

// True when the logged-in user has already consented, either on their
// resident record or earlier in this session (pre-registration consent).
// When only the session flag exists and a resident record does, the consent
// is persisted so it survives logout.
function hasPrivacyConsent($conn) {
    ensurePrivacyColumns($conn);
    $resident = privacyCurrentResident($conn);

    if ($resident && (int)($resident["privacy_consent"] ?? 0) === 1) {
        return true;
    }

    if (!empty($_SESSION["privacy_consent"])) {
        if ($resident) {
            recordResidentConsent($conn, (int)$resident["id"]);
        }
        return true;
    }

    return false;
}

// Gate for logged-in pages that collect personal information. Redirects to
// the Data Privacy Notice and returns the user here after they consent.
function requirePrivacyConsent($conn) {
    requireLogin();

    // Staff act on behalf of the office, not as data subjects.
    if (!isResident()) {
        return;
    }

    if (hasPrivacyConsent($conn)) {
        return;
    }

    $current = $_SERVER["REQUEST_URI"] ?? "";
    redirectTo("auth/privacy_notice.php?return=" . urlencode($current));
}
?>
