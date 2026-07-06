<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/sidebar.php";

function renderHeader($page_title, $page_description = "", $active = "") {
    $asset_path = appPath("assets/css/style.css");
    $role_name = getRoleName(currentRoleId());
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo e($page_title); ?> - BrgyKonekt</title>
        <link rel="stylesheet" href="<?php echo e($asset_path); ?>">
    </head>
    <body>
    <div class="dashboard">
        <?php renderSidebar($active); ?>

        <main class="main-content">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Barangay digital service portal</span>
                    <h2><?php echo e($page_title); ?></h2>
                    <?php if ($page_description !== "") { ?>
                        <p><?php echo e($page_description); ?></p>
                    <?php } ?>
                </div>

                <div class="topbar-meta">
                    <span><?php echo e($role_name); ?></span>
                    <strong><?php echo e($_SESSION["full_name"] ?? "User"); ?></strong>
                </div>
            </header>
    <?php
}
?>
