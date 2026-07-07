<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([1, 3]);

$message = "";
$search = trim($_GET["search"] ?? "");

if (isset($_GET["verify"])) {
    $resident_id = (int)$_GET["verify"];
    mysqli_query($conn, "UPDATE residents SET status = 'Verified' WHERE id = '$resident_id'");
    $message = "Resident marked as verified.";
    addAuditLog($conn, $_SESSION["user_id"], "verify_resident", "resident#$resident_id");
}

if (isset($_GET["reject"])) {
    $resident_id = (int)$_GET["reject"];
    mysqli_query($conn, "UPDATE residents SET status = 'Rejected' WHERE id = '$resident_id'");
    $message = "Resident marked as rejected.";
    addAuditLog($conn, $_SESSION["user_id"], "reject_resident", "resident#$resident_id");
}

$query = "SELECT residents.*, users.username
          FROM residents
          LEFT JOIN users ON residents.user_id = users.id
          WHERE 1=1";

if ($search !== "") {
    $search_value = mysqli_real_escape_string($conn, $search);
    $query .= " AND (residents.first_name LIKE '%$search_value%'
                OR residents.middle_name LIKE '%$search_value%'
                OR residents.last_name LIKE '%$search_value%'
                OR residents.purok LIKE '%$search_value%'
                OR users.username LIKE '%$search_value%')";
}

$query .= " ORDER BY residents.id DESC";
$residents = mysqli_query($conn, $query);

include "../../includes/header.php";
renderHeader("Resident Verification", "Search, review, verify, or reject resident profiles and uploaded requirements.", "residents");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>

    <form class="inline-form" method="GET">
        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search resident, username, or purok">
        <button type="submit">Search</button>
        <a class="button secondary" href="verify.php">Reset</a>
    </form>
</section>

<section class="panel">
    <h3>Resident Records</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Address</th>
                    <th>Purok</th>
                    <th>Contact</th>
                    <th>Uploads</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($residents && mysqli_num_rows($residents) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($residents)) { ?>
                        <tr>
                            <td><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></td>
                            <td><?php echo e($row["username"]); ?></td>
                            <td><?php echo e($row["address"]); ?></td>
                            <td><?php echo e($row["purok"]); ?></td>
                            <td><?php echo e($row["contact_number"]); ?></td>
                            <td>
                                <?php if (!empty($row["valid_id_path"])) { ?>
                                    <a href="<?php echo e(appPath($row["valid_id_path"])); ?>" target="_blank">Valid ID</a><br>
                                <?php } ?>
                                <?php if (!empty($row["proof_of_residency_path"])) { ?>
                                    <a href="<?php echo e(appPath($row["proof_of_residency_path"])); ?>" target="_blank">Proof</a>
                                <?php } ?>
                            </td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <div class="quick-actions">
                                    <a class="button" href="verify.php?verify=<?php echo e($row["id"]); ?>">Verify</a>
                                    <a class="button secondary" href="verify.php?reject=<?php echo e($row["id"]); ?>">Reject</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="8">No resident records found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
