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
    $barangay_address = mysqli_real_escape_string($conn, trim($_POST["barangay_address"]));
    $contact_email = mysqli_real_escape_string($conn, trim($_POST["contact_email"]));
    $allow_registration = isset($_POST["allow_registration"]) ? "1" : "0";

    // Barangay logo printed on all forms and certificates.
    $logo_path = $settings["barangay_logo"] ?? "";
    $new_logo = saveUploadedFile("barangay_logo", "assets/uploads/system", "logo");
    if ($new_logo !== "") {
        $logo_path = $new_logo;
    }
    $logo_path_safe = mysqli_real_escape_string($conn, $logo_path);

    $queries = [
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('barangay_name', '$barangay_name') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('barangay_address', '$barangay_address') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('contact_email', '$contact_email') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('allow_registration', '$allow_registration') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        "INSERT INTO system_settings (setting_key, setting_value) VALUES ('barangay_logo', '$logo_path_safe') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    ];

    foreach ($queries as $query) {
        mysqli_query($conn, $query);
    }

    addAuditLog($conn, $_SESSION["user_id"], "update_system_settings", "system_settings");
    $settings["barangay_name"] = $barangay_name;
    $settings["barangay_address"] = $barangay_address;
    $settings["contact_email"] = $contact_email;
    $settings["allow_registration"] = $allow_registration;
    $settings["barangay_logo"] = $logo_path;
    $message = "System settings updated.";
}

include "../includes/header.php";
renderHeader("System Settings", "Configure barangay identity, printed-form branding, registration access, and system tools.", "settings");
?>

<section class="panel">
    <h3>System Tools</h3>
    <div class="quick-actions">
        <a class="button secondary" href="<?php echo e(appPath("admin/backup_database.php")); ?>">Database Backup</a>
        <a class="button secondary" href="<?php echo e(appPath("admin/restore_database.php")); ?>">Database Restore</a>
        <a class="button secondary" href="<?php echo e(appPath("admin/audit_logs.php")); ?>">Audit Logs</a>
    </div>
</section>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>

    <h3>Barangay Identity</h3>
    <form method="POST" enctype="multipart/form-data">
        <label for="barangay_name">Barangay Name</label>
        <input id="barangay_name" type="text" name="barangay_name" value="<?php echo e($settings["barangay_name"] ?? "BrgyKonekt"); ?>">

        <label for="barangay_address">Barangay Address (Municipality / City, Province)</label>
        <input id="barangay_address" type="text" name="barangay_address" value="<?php echo e($settings["barangay_address"] ?? ""); ?>" placeholder="e.g. Municipality of ______, Province of ______">

        <label for="contact_email">Contact Email</label>
        <input id="contact_email" type="email" name="contact_email" value="<?php echo e($settings["contact_email"] ?? ""); ?>">

        <label for="barangay_logo">Barangay Logo (printed on all forms and certificates)</label>
        <input id="barangay_logo" type="file" name="barangay_logo" accept=".jpg,.jpeg,.png">
        <?php if (!empty($settings["barangay_logo"])) { ?>
            <p><img src="<?php echo e(appPath($settings["barangay_logo"])); ?>" alt="Current barangay logo" style="width:80px;height:80px;object-fit:contain;"> Current logo</p>
        <?php } ?>

        <label>
            <input type="checkbox" name="allow_registration" value="1" <?php echo ($settings["allow_registration"] ?? "1") === "1" ? "checked" : ""; ?>>
            Allow new resident registration
        </label>

        <button type="submit" name="save_settings">Save Settings</button>
    </form>
</section>

<?php include "../includes/footer.php"; ?>
