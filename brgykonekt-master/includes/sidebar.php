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

function renderSidebar($active = "") {
    $role_id = currentRoleId();
    $role_name = getRoleName($role_id);
    $is_resident = $role_id === 8;

    $resident_items = [
        navItem("dashboard", "Home", "resident/dashboard.php", "HM", [8]),
        navItem("profile", "My Profile", "modules/residents/profile.php", "ID", [8]),
        navItem("documents", "Request Documents", "modules/documents/request.php", "DC", [8]),
        navItem("health", "Health Appointment", "modules/health/book.php", "HC", [8]),
        navItem("blotter", "File Complaint", "modules/blotter/file.php", "BL", [8]),
        navItem("announcements", "Announcements", "modules/announcements/view.php", "AN", [8]),
        navItem("activities", "Activities", "modules/activities/view.php", "EV", [8]),
        navItem("pets", "Register Pets", "modules/pets/register.php", "PT", [8]),
        navItem("inventory", "Borrow Equipment", "modules/inventory/borrow.php", "EQ", [8]),
    ];

    $staff_items = [
        navItem("dashboard", "Dashboard", "admin/dashboard.php", "DB", [1, 2, 3, 4, 5, 6, 7]),
        navItem("residents", "Resident Verification", "modules/residents/verify.php", "RV", [1, 3]),
        navItem("documents", "Document Requests", "modules/documents/manage.php", "DR", [1, 2, 3]),
        navItem("payments", "Payments & OR", "modules/payments/manage.php", "OR", [1, 4]),
        navItem("health", "Health Center", "modules/health/manage.php", "HC", [1, 2, 5]),
        navItem("blotter", "Blotter Cases", "modules/blotter/manage.php", "BC", [1, 2, 6]),
        navItem("announcements", "Announcements", "modules/announcements/manage.php", "AN", [1, 2, 7]),
        navItem("activities", "Activities", "modules/activities/manage.php", "AC", [1, 2, 7]),
        navItem("inventory", "Inventory", "modules/inventory/manage.php", "IN", [1, 2, 3]),
        navItem("pets", "Pet Records", "modules/pets/manage.php", "PT", [1, 2, 5]),
        navItem("reports", "Reports", "modules/reports/dashboard.php", "RP", [1, 2, 3, 4]),
    ];

    $admin_items = [
        navItem("users", "User Accounts", "admin/manage_users.php", "UA", [1]),
        navItem("officials", "Barangay Officials", "admin/manage_officials.php", "BO", [1]),
        navItem("backup", "Backup Database", "admin/backup_database.php", "BK", [1]),
        navItem("restore", "Restore Database", "admin/restore_database.php", "RS", [1]),
        navItem("settings", "System Settings", "admin/system_settings.php", "ST", [1]),
        navItem("audit", "Audit Logs", "admin/audit_logs.php", "AL", [1]),
    ];

    $items = $is_resident ? $resident_items : $staff_items;
    ?>
    <aside class="sidebar" aria-label="Primary navigation">
        <div class="brand">
            <div class="brand-mark">BK</div>
            <div>
                <h1>BrgyKonekt</h1>
                <span>Digital Barangay Services</span>
            </div>
        </div>

        <div class="user-strip">
            <div class="avatar"><?php echo e(substr($_SESSION["full_name"] ?? "U", 0, 1)); ?></div>
            <div>
                <strong><?php echo e($_SESSION["full_name"] ?? "User"); ?></strong>
                <small><?php echo e($role_name); ?></small>
            </div>
        </div>

        <nav class="nav-links">
            <?php foreach ($items as $item) { ?>
                <?php if (canAccess($item["roles"])) { ?>
                    <a class="<?php echo $active === $item["key"] ? "active" : ""; ?>" href="<?php echo e(appPath($item["path"])); ?>">
                        <span class="nav-icon"><?php echo e($item["icon"]); ?></span>
                        <span><?php echo e($item["label"]); ?></span>
                    </a>
                <?php } ?>
            <?php } ?>

            <?php if (!$is_resident && canAccess([1])) { ?>
                <div class="nav-section">Administration</div>
                <?php foreach ($admin_items as $item) { ?>
                    <a class="<?php echo $active === $item["key"] ? "active" : ""; ?>" href="<?php echo e(appPath($item["path"])); ?>">
                        <span class="nav-icon"><?php echo e($item["icon"]); ?></span>
                        <span><?php echo e($item["label"]); ?></span>
                    </a>
                <?php } ?>
            <?php } ?>

            <div class="nav-section">Account</div>
            <a class="<?php echo $active === "password" ? "active" : ""; ?>" href="<?php echo e(appPath("auth/change_password.php")); ?>">
                <span class="nav-icon">PW</span>
                <span>Change Password</span>
            </a>
            <a href="<?php echo e(appPath("auth/logout.php")); ?>">
                <span class="nav-icon">LO</span>
                <span>Logout</span>
            </a>
        </nav>
    </aside>
    <?php
}
?>
