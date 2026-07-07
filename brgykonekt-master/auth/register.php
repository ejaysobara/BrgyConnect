<?php
include "../includes/auth_check.php";
include "../config/database.php";

$message = "";
$error = "";
$allow_registration = getSystemSetting($conn, "allow_registration", "1") === "1";

if (isset($_POST["register"]) && $allow_registration) {
    $full_name = trim($_POST["full_name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role_id = 8;
    $status = "Active";

    $sql = "INSERT INTO users (role_id, full_name, username, email, password, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssss", $role_id, $full_name, $username, $email, $password, $status);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Registration successful. You can now login.";
    } else {
        $error = "Unable to create account. Please check if the username is already taken.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BrgyKonekt</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <section class="auth-hero">
            <div class="brand">
                <div class="brand-seal" aria-hidden="true"><span>BK</span></div>
                <div>
                    <h1>BrgyKonekt</h1>
                    <span class="tag">Resident Access</span>
                </div>
            </div>
            <div class="auth-copy">
                <h1>Create your resident account and start online barangay requests.</h1>
                <p>After registration, complete your profile and wait for barangay verification to unlock document requests, appointments, and complaints.</p>
            </div>
        </section>

        <main class="auth-card">
            <h2>Resident Registration</h2>
            <p>Use your real name so the barangay can verify your resident profile.</p>

            <?php if ($message !== "") { ?>
                <p class="message"><?php echo e($message); ?></p>
            <?php } ?>
            <?php if ($error !== "") { ?>
                <p class="error"><?php echo e($error); ?></p>
            <?php } ?>
            <?php if (!$allow_registration) { ?>
                <p class="error">New resident registration is currently disabled by the administrator.</p>
            <?php } else { ?>
                <form method="POST">
                    <label for="full_name">Full Name</label>
                    <input id="full_name" type="text" name="full_name" required autocomplete="name">

                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" required autocomplete="username">

                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" autocomplete="email">

                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">

                    <button type="submit" name="register">Register</button>
                </form>
            <?php } ?>

            <div class="quick-actions">
                <a class="button secondary" href="login.php">Already have an account?</a>
            </div>
        </main>
    </div>
</body>
</html>
