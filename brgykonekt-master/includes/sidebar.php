<?php
require_once __DIR__ . "/auth_check.php";

function navItem($key, $label, $path, $icon, $roles) {
    return [
        "key" => $key,
        "label" => $label,
        "path" => $path,
        "icon" => $icon,
        "roles" => $roles
    ];
}

function renderNavbar($active = "") {
    $role_id = currentRoleId();
    $role_name = getRoleName($role_id);
    $is_resident = $role_id === 8;

    $resident_items = [
        navItem("dashboard", "Home", "resident/dashboard.php", "HM", [8]),
        navItem("profile", "My Profile", "modules/residents/profile.php", "ID", [8]),
        navItem("documents", "Documents", "modules/documents/request.php", "DC", [8]),
        navItem("health", "Health", "modules/health/book.php", "HC", [8]),
        navItem("blotter", "File Complaint", "modules/blotter/file.php", "BL", [8]),
        navItem("announcements", "Announcements", "modules/announcements/view.php", "AN", [8]),
        navItem("activities", "Activities", "modules/activities/view.php", "EV", [8]),
    ];

    $staff_items = [
        navItem("dashboard", "Dashboard", "admin/dashboard.php", "DB", [1, 2, 3, 4, 5, 6, 7]),
        navItem("residents", "Residents", "modules/residents/verify.php", "RV", [1, 3]),
        navItem("documents", "Documents", "modules/documents/manage.php", "DR", [1, 2, 3]),
        navItem("payments", "Payments", "modules/payments/manage.php", "OR", [1, 4]),
        navItem("health", "Health Center", "modules/health/manage.php", "HC", [1, 2, 5]),
        navItem("blotter", "Blotter", "modules/blotter/manage.php", "BC", [1, 2, 6]),
        navItem("announcements", "Announcements", "modules/announcements/manage.php", "AN", [1, 2, 7]),
        navItem("activities", "Activities", "modules/activities/manage.php", "AC", [1, 2, 7]),
        navItem("reports", "Reports", "modules/reports/dashboard.php", "RP", [1, 2, 3, 4]),
    ];

    $admin_items = [
        navItem("users", "Users", "admin/manage_users.php", "UA", [1]),
        navItem("officials", "Officials", "admin/manage_officials.php", "BO", [1]),
        navItem("backup", "Backup", "admin/backup_database.php", "BK", [1]),
        navItem("restore", "Restore", "admin/restore_database.php", "RS", [1]),
        navItem("settings", "Settings", "admin/system_settings.php", "ST", [1]),
        navItem("audit", "Audit Logs", "admin/audit_logs.php", "AL", [1]),
    ];

    $items = $is_resident ? $resident_items : $staff_items;
    $show_admin = !$is_resident && canAccess([1]);
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
                    <?php if (canAccess($item["roles"])) { ?>
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

            <div class="user-menu">
                <div class="avatar"><?php echo e(substr($_SESSION["full_name"] ?? "U", 0, 1)); ?></div>
                <div class="who">
                    <strong><?php echo e($_SESSION["full_name"] ?? "User"); ?></strong>
                    <small><?php echo e($role_name); ?></small>
                </div>
                <a href="<?php echo e(appPath("auth/change_password.php")); ?>" title="Change Password" class="<?php echo $active === "password" ? "active" : ""; ?>">PW</a>
                <a href="<?php echo e(appPath("auth/logout.php")); ?>" title="Logout">LO</a>
            </div>
        </div>
    </header>
    <?php
}
?>
