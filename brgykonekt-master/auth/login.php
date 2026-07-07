<?php
include "../includes/auth_check.php";
include "../config/database.php";

$message = "";

if (isset($_SESSION["user_id"])) {
    if (currentRoleId() === 8) {
        header("Location: ../resident/dashboard.php");
    } else {
        header("Location: ../admin/dashboard.php");
    }
    exit();
}

if (isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username = ? AND status = 'Active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role_id"] = $user["role_id"];

            if ((int)$user["role_id"] === 8) {
                header("Location: ../resident/dashboard.php");
            } else {
                header("Location: ../admin/dashboard.php");
            }
            exit();
        }
    }

    $message = "Invalid username or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BrgyKonekt</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <section class="auth-hero">
            <div class="brand">
                <div class="brand-seal" aria-hidden="true"><span>BK</span></div>
                <div>
                    <h1>BrgyKonekt</h1>
                    <span class="tag">Digital Barangay Services</span>
                </div>
            </div>
            <div class="auth-copy">
                <h1>One barangay portal for residents, staff, reports, and service requests.</h1>
                <p>Fast access, verified records, and role-based workflows for every barangay office &mdash; in one place.</p>
                <div class="mini-services">
                    <span>Documents</span>
                    <span>Health</span>
                    <span>Payments</span>
                    <span>Complaints</span>
                </div>
            </div>
        </section>

        <main class="auth-card">
            <h2>Welcome back</h2>
            <p>Sign in with your barangay account.</p>

            <?php if ($message !== "") { ?>
                <p class="error"><?php echo e($message); ?></p>
            <?php } ?>

            <form method="POST">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" required autocomplete="username">

                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">

                <button type="submit" name="login">Login</button>
            </form>

            <div class="quick-actions">
                <a class="button secondary" href="register.php">Create resident account</a>
            </div>
        </main>
    </div>
</body>
</html>
