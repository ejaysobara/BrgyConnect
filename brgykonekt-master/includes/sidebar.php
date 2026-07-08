<?php
require_once __DIR__ . "/auth_check.php";

function navItem($key, $label, $path, $icon, $min_level = 0, $requires_health = false) {
    return [
        "key" => $key,
        "label" => $label,
        "path" => $path,
        "icon" => $icon,
        "min_level" => $min_level,
        "requires_health" => $requires_health
    ];
}

function navItemVisible($item) {
    if ($item["requires_health"]) {
        return canViewHealthRecords();
    }
    return staffLevel() >= $item["min_level"];
}

function renderNavbar($active = "") {
    $role_id = currentRoleId();
    $role_name = getRoleName($role_id);
    $is_resident = isResident();

    // Residents: minimal tabs. My Profile lives in the user menu (top right).
    $resident_items = [
        navItem("dashboard", "Home", "resident/dashboard.php", "HM"),
        navItem("community", "Community Feed", "modules/community/feed.php", "CF"),
        navItem("documents", "Documents", "modules/documents/request.php", "DC"),
        navItem("dforms", "D-Forms", "modules/forms/dforms.php", "DF"),
        navItem("appointments", "Appointments", "modules/appointments/book.php", "AP"),
        navItem("blotter", "File Complaint", "modules/blotter/file.php", "BL"),
    ];

    // Staff: items appear based on the access hierarchy
    // (1 Tanod, 2 Treasurer, 3 Secretary, 4 Chairman, 5 Captain, 6 Admin).
    // Announcements/activities are created from the Community Feed composer.
    $staff_items = [
        navItem("dashboard", "Dashboard", "admin/dashboard.php", "DB", 1),
        navItem("community", "Community Feed", "modules/community/feed.php", "CF", 1),
        navItem("blotter", "Blotter", "modules/blotter/manage.php", "BC", 1),
        navItem("payments", "Payments", "modules/payments/manage.php", "OR", 2),
        navItem("reports", "Reports", "modules/reports/dashboard.php", "RP", 2),
        navItem("residents", "Residents", "modules/residents/verify.php", "RV", 1),
        navItem("documents", "Documents", "modules/documents/manage.php", "DR", 3),
        navItem("dforms", "D-Forms", "modules/forms/dform_manage.php", "DF", 3),
        navItem("health", "Appointments", "modules/health/manage.php", "AP", 0, true),
    ];

    // Backup, Restore, and Audit Logs are reached from inside System Settings.
    $admin_items = [
        navItem("users", "Users", "admin/manage_users.php", "UA", 6),
        navItem("officials", "Officials", "admin/manage_officials.php", "BO", 6),
        navItem("settings", "System", "admin/system_settings.php", "ST", 6),
    ];

    $items = $is_resident ? $resident_items : $staff_items;
    $show_admin = !$is_resident && staffLevel() >= 6;
    ?>
    <header class="navbar">
        <div class="navbar-inner">
            <div class="brand">
                <div class="brand-seal" aria-hidden="true"><span>BK</span></div>
                <div>
                    <h1>BrgyKonekt</h1>
                    <span class="tag">Digital Barangay Services</span>
                </div>
            </div>

            <nav class="nav-links" aria-label="Primary navigation">
                <?php foreach ($items as $item) { ?>
                    <?php if (navItemVisible($item)) { ?>
                        <a class="<?php echo $active === $item["key"] ? "active" : ""; ?>" href="<?php echo e(appPath($item["path"])); ?>">
                            <span class="nav-icon"><?php echo e($item["icon"]); ?></span>
                            <span><?php echo e($item["label"]); ?></span>
                        </a>
                    <?php } ?>
                <?php } ?>

                <?php if ($show_admin) { ?>
                    <span class="nav-divider" aria-hidden="true"></span>
                    <?php foreach ($admin_items as $item) { ?>
                        <a class="<?php echo $active === $item["key"] ? "active" : ""; ?>" href="<?php echo e(appPath($item["path"])); ?>">
                            <span class="nav-icon"><?php echo e($item["icon"]); ?></span>
                            <span><?php echo e($item["label"]); ?></span>
                        </a>
                    <?php } ?>
                <?php } ?>
            </nav>

            <details class="user-menu user-dropdown">
                <summary>
                    <div class="avatar"><?php echo e(substr($_SESSION["full_name"] ?? "U", 0, 1)); ?></div>
                    <div class="who">
                        <strong><?php echo e($_SESSION["full_name"] ?? "User"); ?></strong>
                        <small><?php echo e($role_name); ?></small>
                    </div>
                    <span class="caret" aria-hidden="true">▾</span>
                </summary>
                <div class="user-dropdown-menu">
                    <?php if ($is_resident) { ?>
                        <a href="<?php echo e(appPath("modules/residents/profile.php")); ?>">My Profile</a>
                    <?php } ?>
                    <?php if (staffLevel() >= 6) { ?>
                        <a href="<?php echo e(appPath("admin/system_settings.php")); ?>">Settings</a>
                    <?php } ?>
                    <a href="<?php echo e(appPath("auth/change_password.php")); ?>">Change Password</a>
                    <a href="<?php echo e(appPath("auth/logout.php")); ?>">Logout</a>
                </div>
            </details>
        </div>
    </header>
    <script>
        document.addEventListener("click", function (event) {
            var dropdown = document.querySelector(".user-dropdown[open]");
            if (dropdown && !dropdown.contains(event.target)) {
                dropdown.removeAttribute("open");
            }
        });
    </script>
    <?php
}
?>
