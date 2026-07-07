<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireLogin();

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

if (isset($_POST["change_password"])) {
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password !== $confirm_password) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters.";
    } else {
        $result = mysqli_query($conn, "SELECT password FROM users WHERE id = '$user_id'");
        $user = $result ? mysqli_fetch_assoc($result) : null;

        if ($user && password_verify($current_password, $user["password"])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'");
            addAuditLog($conn, $user_id, "change_password", "own_account");
            $message = "Password changed successfully.";
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

include "../includes/header.php";
renderHeader("Change Password", "Update your account password to keep barangay records protected.", "password");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <form method="POST">
        <label for="current_password">Current Password</label>
        <input id="current_password" type="password" name="current_password" required autocomplete="current-password">

        <label for="new_password">New Password</label>
        <input id="new_password" type="password" name="new_password" required autocomplete="new-password">

        <label for="confirm_password">Confirm New Password</label>
        <input id="confirm_password" type="password" name="confirm_password" required autocomplete="new-password">

        <button type="submit" name="change_password">Change Password</button>
    </form>
</section>

<?php include "../includes/footer.php"; ?>
