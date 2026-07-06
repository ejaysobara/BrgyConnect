<?php
include "../includes/auth_check.php";

$dashboard_link = isset($_SESSION["role_id"]) && currentRoleId() === 8 ? "../resident/dashboard.php" : "../admin/dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - BrgyKonekt</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card panel">
        <h2>Access Denied</h2>
        <p>Your account role does not have permission to open that page.</p>
        <div class="quick-actions">
            <a class="button" href="<?php echo e($dashboard_link); ?>">Back to dashboard</a>
            <a class="button secondary" href="logout.php">Logout</a>
        </div>
    </main>
</body>
</html>
