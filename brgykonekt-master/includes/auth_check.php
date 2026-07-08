<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function appBasePath() {
    $script_path = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $project_folder = basename(dirname(__DIR__));
    $needle = "/" . $project_folder;
    $position = strpos($script_path, $needle);

    if ($position !== false) {
        return rtrim(substr($script_path, 0, $position + strlen($needle)), "/") . "/";
    }

    return "/";
}

function redirectTo($path) {
    header("Location: " . appPath($path));
    exit();
}

function appPath($path) {
    return appBasePath() . ltrim($path, "/");
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        redirectTo("auth/login.php");
    }

    // End active sessions of accounts that have been revoked or deactivated
    // (e.g. resident access revoked by the Barangay Captain). Records are
    // kept; only portal access is blocked.
    $conn = $GLOBALS["conn"] ?? null;
    if ($conn) {
        $stmt = mysqli_prepare($conn, "SELECT status FROM users WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;

        if (!$row || $row["status"] !== "Active") {
            session_unset();
            session_destroy();
            redirectTo("auth/login.php?notice=access_revoked");
        }
    }
}

function requireRoles($allowed_roles) {
    requireLogin();

    if (!in_array($_SESSION["role_id"], $allowed_roles)) {
        redirectTo("auth/unauthorized.php");
    }
}

function currentRoleId() {
    return isset($_SESSION["role_id"]) ? (int)$_SESSION["role_id"] : 0;
}

function canAccess($allowed_roles) {
    return in_array(currentRoleId(), $allowed_roles);
}

function getRoleList() {
    return [
        1 => "Senior Administrator",
        2 => "Barangay Captain",
        3 => "Barangay Secretary",
        4 => "Barangay Treasurer",
        5 => "Barangay Health Staff",
        6 => "Barangay Tanod",
        7 => "Barangay Chairman",
        8 => "Resident / Constituent"
    ];
}

/*
 * Staff access hierarchy (higher level inherits everything below it):
 *   0 Resident, 1 Tanod / Health Staff, 2 Treasurer, 3 Secretary,
 *   4 Chairman, 5 Captain, 6 Senior Administrator
 */
function staffLevel($role_id = null) {
    $role_id = $role_id === null ? currentRoleId() : (int)$role_id;
    $levels = [
        1 => 6, // Senior Administrator
        2 => 5, // Captain
        7 => 4, // Chairman
        3 => 3, // Secretary
        4 => 2, // Treasurer
        6 => 1, // Tanod
        5 => 1, // Health Staff (plus separate health-record access flag)
        8 => 0  // Resident
    ];
    return $levels[$role_id] ?? 0;
}

function hasStaffLevel($minimum_level) {
    return staffLevel() >= $minimum_level;
}

function requireStaffLevel($minimum_level) {
    requireLogin();

    if (!hasStaffLevel($minimum_level)) {
        redirectTo("auth/unauthorized.php");
    }
}

function isResident() {
    return currentRoleId() === 8;
}

function isStaff() {
    return staffLevel() >= 1;
}

// Health records are private to the resident who owns them and authorized
// health staff. Captain/administrator retain oversight access.
function canViewHealthRecords() {
    return currentRoleId() === 5 || staffLevel() >= 5;
}

function requireHealthAccess() {
    requireLogin();

    if (!canViewHealthRecords()) {
        redirectTo("auth/unauthorized.php");
    }
}

function getRoleName($role_id) {
    $roles = getRoleList();
    return $roles[(int)$role_id] ?? "Unknown";
}

function addAuditLog($conn, $user_id, $action, $target, $details = "") {
    if (!$conn) {
        return;
    }

    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'audit_logs'");
    if (!$table_check || mysqli_num_rows($table_check) === 0) {
        return;
    }

    $user_id = (int)$user_id;
    $action = mysqli_real_escape_string($conn, $action);
    $target = mysqli_real_escape_string($conn, $target);
    $details = mysqli_real_escape_string($conn, $details);

    mysqli_query($conn, "INSERT INTO audit_logs (user_id, action, target, details) VALUES ('$user_id', '$action', '$target', '$details')");
}

function getSystemSetting($conn, $key, $default = "") {
    if (!$conn) {
        return $default;
    }

    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'system_settings'");
    if (!$table_check || mysqli_num_rows($table_check) === 0) {
        return $default;
    }

    $key = mysqli_real_escape_string($conn, $key);
    $result = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = '$key' LIMIT 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row["setting_value"];
    }

    return $default;
}

function getCountValue($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)($row["total"] ?? 0);
}

function getSumValue($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (float)($row["total"] ?? 0);
}

function saveUploadedFile($field_name, $relative_directory, $prefix = "upload") {
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]["error"] !== UPLOAD_ERR_OK) {
        return "";
    }

    $allowed_extensions = ["jpg", "jpeg", "png", "pdf"];
    $original_name = $_FILES[$field_name]["name"];
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions)) {
        return "";
    }

    $root_path = dirname(__DIR__);
    $target_directory = $root_path . "/" . trim($relative_directory, "/");

    if (!is_dir($target_directory)) {
        mkdir($target_directory, 0775, true);
    }

    $filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $prefix) . "-" . time() . "-" . random_int(1000, 9999) . "." . $extension;
    $target_path = $target_directory . "/" . $filename;

    if (move_uploaded_file($_FILES[$field_name]["tmp_name"], $target_path)) {
        return trim($relative_directory, "/") . "/" . $filename;
    }

    return "";
}
?>
