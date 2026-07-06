<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireRoles([1]);

$officials = mysqli_query($conn, "SELECT users.id, users.full_name, users.username, roles.role_name, users.status
                                  FROM users
                                  LEFT JOIN roles ON users.role_id = roles.id
                                  WHERE users.role_id IN (1,2,3,4,5,6,7)
                                  ORDER BY users.role_id ASC, users.full_name ASC");

include "../includes/header.php";
renderHeader("Barangay Officials", "View official staff accounts and assigned barangay system roles.", "officials");
?>

<section class="panel">
    <h3>Official Accounts</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($officials && mysqli_num_rows($officials) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($officials)) { ?>
                        <tr>
                            <td><?php echo e($row["full_name"]); ?></td>
                            <td><?php echo e($row["username"]); ?></td>
                            <td><?php echo e($row["role_name"]); ?></td>
                            <td><span class="badge"><?php echo e($row["status"]); ?></span></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="4">No official accounts found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
