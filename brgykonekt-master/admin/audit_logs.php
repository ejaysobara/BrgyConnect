<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireRoles([1]);

$logs = mysqli_query($conn, "SELECT audit_logs.*, users.full_name
                             FROM audit_logs
                             LEFT JOIN users ON audit_logs.user_id = users.id
                             ORDER BY audit_logs.created_at DESC
                             LIMIT 100");

include "../includes/header.php";
renderHeader("Audit Logs", "Review recent system actions, administrative updates, and account activity.", "audit");
?>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Details</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs && mysqli_num_rows($logs) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($logs)) { ?>
                        <tr>
                            <td><?php echo e($row["full_name"] ?? "System"); ?></td>
                            <td><?php echo e($row["action"]); ?></td>
                            <td><?php echo e($row["target"]); ?></td>
                            <td><?php echo e($row["details"] ?? ""); ?></td>
                            <td><?php echo e($row["created_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No audit logs found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
