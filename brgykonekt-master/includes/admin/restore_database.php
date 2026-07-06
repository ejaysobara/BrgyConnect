<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireRoles([1]);

$message = "";
$error = "";

if (isset($_POST["restore_database"])) {
    if (!isset($_FILES["sql_file"]) || $_FILES["sql_file"]["error"] !== UPLOAD_ERR_OK) {
        $error = "Please choose a valid SQL backup file.";
    } else {
        $extension = strtolower(pathinfo($_FILES["sql_file"]["name"], PATHINFO_EXTENSION));

        if ($extension !== "sql") {
            $error = "Only .sql backup files are allowed.";
        } else {
            $sql = file_get_contents($_FILES["sql_file"]["tmp_name"]);

            if ($sql === false || trim($sql) === "") {
                $error = "The selected SQL file is empty.";
            } elseif (mysqli_multi_query($conn, $sql)) {
                do {
                    if ($result = mysqli_store_result($conn)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_more_results($conn) && mysqli_next_result($conn));

                if (mysqli_errno($conn)) {
                    $error = "Restore stopped: " . mysqli_error($conn);
                } else {
                    addAuditLog($conn, $_SESSION["user_id"], "restore_database", $_FILES["sql_file"]["name"]);
                    $message = "Database restored successfully.";
                }
            } else {
                $error = "Unable to restore database: " . mysqli_error($conn);
            }
        }
    }
}

include "../includes/header.php";
renderHeader("Restore Database", "Upload a SQL backup to restore barangay system records.", "restore");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="sql_file">SQL Backup File</label>
        <input id="sql_file" type="file" name="sql_file" accept=".sql" required>

        <button type="submit" name="restore_database">Restore Database</button>
    </form>
</section>

<?php include "../includes/footer.php"; ?>
