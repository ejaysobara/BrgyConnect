<?php
require_once "includes/auth_check.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

if (currentRoleId() === 8) {
    header("Location: resident/dashboard.php");
    exit();
}

header("Location: admin/dashboard.php");
exit();
?>
