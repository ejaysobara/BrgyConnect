<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireRoles([1]);

if (isset($_POST["download_backup"])) {
    // 1. Prevent PHP from timing out on large databases
    set_time_limit(0); 

    $filename = "BrgyKonekt-backup-" . date("Ymd-His") . ".sql";

    // 2. Clear any accidental whitespace buffered by included files
    if (ob_get_length()) ob_clean();

    header("Content-Type: application/sql");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "-- BrgyKonekt database backup\n";
    echo "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    $tables = mysqli_query($conn, "SHOW TABLES");
    if ($tables) {
        while ($table_row = mysqli_fetch_array($tables)) {
            $table = $table_row[0];
            $escaped_table = str_replace("`", "``", $table);

            echo "DROP TABLE IF EXISTS `$escaped_table`;\n";

            $create_result = mysqli_query($conn, "SHOW CREATE TABLE `$escaped_table`");
            if ($create_result) {
                $create_row = mysqli_fetch_assoc($create_result);
                echo $create_row["Create Table"] . ";\n\n";
            }

            $rows = mysqli_query($conn, "SELECT * FROM `$escaped_table`");
            if ($rows && mysqli_num_rows($rows) > 0) {
                while ($row = mysqli_fetch_assoc($rows)) {
                    $columns = array_map(function ($column) {
                        return "`" . str_replace("`", "``", $column) . "`";
                    }, array_keys($row));

                    $values = array_map(function ($value) use ($conn) {
                        if ($value === null) {
                            return "NULL";
                        }
                        return "'" . mysqli_real_escape_string($conn, $value) . "'";
                    }, array_values($row));

                    echo "INSERT INTO `$escaped_table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
                }
            }
            echo "\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Add audit log, catching errors silently if any output is generated
    if (function_exists('addAuditLog')) {
        addAuditLog($conn, $_SESSION["user_id"], "backup_database", $filename);
    }
    exit();
}

include "../includes/header.php";
renderHeader("Backup Database", "Download a SQL backup of barangay records, users, services, and transaction history.", "backup");
?>

<section class="panel">
    <form method="POST">
        <p>Generate a database backup file for safekeeping.</p>
        <button type="submit" name="download_backup">Download Backup</button>
    </form>
</section>

<?php include "../includes/footer.php"; ?>
