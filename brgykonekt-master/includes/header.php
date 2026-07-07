<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/sidebar.php";

function renderHeader($page_title, $page_description = "", $active = "") {
    $asset_path = appPath("assets/css/style.css");
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
    <div class="app-shell">
        <?php renderNavbar($active); ?>

        <main class="main-content">
            <header class="topbar">
                <div>
                    <h2><?php echo e($page_title); ?></h2>
                    <?php if ($page_description !== "") { ?>
                        <p><?php echo e($page_description); ?></p>
                    <?php } ?>
                </div>
            </header>
    <?php
}
?>
