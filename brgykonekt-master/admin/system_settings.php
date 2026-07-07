<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireRoles([1]);

$message = "";
$settings = [];
$setting_result = mysqli_query($conn, "SELECT * FROM system_settings ORDER BY setting_key ASC");
if ($setting_result) {
    while ($row = mysqli_fetch_assoc($setting_result)) {
        $settings[$row["setting_key"]] = $row["setting_value"];
    }
}

if (isset($_POST["save_settings"])) {
    $barangay_name = mysqli_real_escape_string($conn, trim($_POST["barangay_name"]));
    $contact_email = mysqli_real_escape_string($conn, trim($_POST["contact_email"]));
    $allow_registration = isset($_POST["allow_registration"]) ? "1" : "0";

    $queries = [
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('barangay_name', '$barangay_name') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('contact_email', '$contact_email') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('allow_registration', '$allow_registration') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    ];

    foreach ($queries as $query) {
        mysqli_query($conn, $query);
    }

    addAuditLog($conn, $_SESSION["user_id"], "update_system_settings", "system_settings");
    $settings["barangay_name"] = $barangay_name;
    $settings["contact_email"] = $contact_email;
    $settings["allow_registration"] = $allow_registration;
    $message = "System settings updated.";
}

include "../includes/header.php";
renderHeader("System Settings", "Configure barangay identity, contact information, and resident registration access.", "settings");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>

    <form method="POST">
        <label for="barangay_name">Barangay Name</label>
        <input id="barangay_name" type="text" name="barangay_name" value="<?php echo e($settings["barangay_name"] ?? "BrgyKonekt"); ?>">

        <label for="contact_email">Contact Email</label>
        <input id="contact_email" type="email" name="contact_email" value="<?php echo e($settings["contact_email"] ?? ""); ?>">

        <label>
            <input type="checkbox" name="allow_registration" value="1" <?php echo ($settings["allow_registration"] ?? "1") === "1" ? "checked" : ""; ?>>
            Allow new resident registration
        </label>

        <button type="submit" name="save_settings">Save Settings</button>
    </form>
</section>

<?php include "../includes/footer.php"; ?>
