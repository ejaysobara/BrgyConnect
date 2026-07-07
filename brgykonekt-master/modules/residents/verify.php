<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireStaffLevel(1); // all staff may view the resident list

/*
 * Action permissions:
 *   Verify / Reject — Barangay Captain and Secretary (admin retains oversight)
 *   Revoke verified status — Barangay Captain only (and admin)
 */
$can_moderate = in_array(currentRoleId(), [1, 2, 3]);
$can_revoke = in_array(currentRoleId(), [1, 2]);

$message = "";
$search = trim($_GET["search"] ?? "");

if (isset($_GET["verify"]) && $can_moderate) {
    $resident_id = (int)$_GET["verify"];
    mysqli_query($conn, "UPDATE residents SET status = 'Verified' WHERE id = '$resident_id'");
    $message = "Resident marked as verified.";
    addAuditLog($conn, $_SESSION["user_id"], "verify_resident", "resident#$resident_id");
}

if (isset($_GET["reject"]) && $can_moderate) {
    $resident_id = (int)$_GET["reject"];
    mysqli_query($conn, "UPDATE residents SET status = 'Rejected' WHERE id = '$resident_id'");
    $message = "Resident marked as rejected.";
    addAuditLog($conn, $_SESSION["user_id"], "reject_resident", "resident#$resident_id");
}

if (isset($_GET["revoke"]) && $can_revoke) {
    $resident_id = (int)$_GET["revoke"];
    mysqli_query($conn, "UPDATE residents SET status = 'Pending' WHERE id = '$resident_id' AND status = 'Verified'");
    $message = "Resident's verified status has been revoked.";
    addAuditLog($conn, $_SESSION["user_id"], "revoke_resident_verification", "resident#$resident_id");
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
renderHeader("Resident Records", "Search and review resident profiles. Click a resident's name to preview their full profile.", "residents");
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
                    <th>Resident Code</th>
                    <th>Purok</th>
                    <th>Contact</th>
                    <th>Uploads</th>
                    <th>Status</th>
                    <?php if ($can_moderate) { ?>
                        <th>Action</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($residents && mysqli_num_rows($residents) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($residents)) { ?>
                        <tr>
                            <td>
                                <a href="#" onclick="openResidentPreview(<?php echo (int)$row["id"]; ?>); return false;">
                                    <strong><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></strong>
                                </a>
                            </td>
                            <td><?php echo e($row["resident_code"] ?: "—"); ?></td>
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
                            <?php if ($can_moderate) { ?>
                                <td>
                                    <div class="quick-actions">
                                        <?php if ($row["status"] === "Verified") { ?>
                                            <?php if ($can_revoke) { ?>
                                                <a class="button secondary" href="verify.php?revoke=<?php echo e($row["id"]); ?>" onclick="return confirm('Revoke this resident\'s verified status?');">Revoke</a>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <a class="button" href="verify.php?verify=<?php echo e($row["id"]); ?>">Verify</a>
                                            <a class="button secondary" href="verify.php?reject=<?php echo e($row["id"]); ?>">Reject</a>
                                        <?php } ?>
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="<?php echo $can_moderate ? 7 : 6; ?>">No resident records found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Resident profile preview pop-up -->
<div id="resident-preview-overlay" class="modal-overlay" onclick="if (event.target === this) closeResidentPreview();">
    <div class="modal-box">
        <button type="button" class="modal-close" onclick="closeResidentPreview()">&times;</button>
        <iframe id="resident-preview-frame" title="Resident profile preview"></iframe>
    </div>
</div>

<script>
    function openResidentPreview(id) {
        document.getElementById("resident-preview-frame").src = "preview.php?id=" + id;
        document.getElementById("resident-preview-overlay").style.display = "flex";
        document.body.style.overflow = "hidden";
    }
    function closeResidentPreview() {
        document.getElementById("resident-preview-overlay").style.display = "none";
        document.getElementById("resident-preview-frame").src = "";
        document.body.style.overflow = "";
    }
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") closeResidentPreview();
    });
</script>

<?php include "../../includes/footer.php"; ?>
