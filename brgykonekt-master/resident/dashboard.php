<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireRoles([8]);

$user_id = (int)$_SESSION["user_id"];
$resident_result = mysqli_query($conn, "SELECT * FROM residents WHERE user_id = '$user_id'");
$resident = $resident_result ? mysqli_fetch_assoc($resident_result) : null;
$resident_id = $resident ? (int)$resident["id"] : 0;

$my_requests = getCountValue($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE resident_id = '$resident_id'");
$my_appointments = getCountValue($conn, "SELECT COUNT(*) AS total FROM appointments WHERE resident_id = '$resident_id'");
$my_complaints = getCountValue($conn, "SELECT COUNT(*) AS total FROM blotter_cases WHERE resident_id = '$resident_id'");
$total_announcements = getCountValue($conn, "SELECT COUNT(*) AS total FROM announcements");

$latest_announcements = mysqli_query($conn, "SELECT title, category, created_at FROM announcements ORDER BY created_at DESC LIMIT 4");

include "../includes/header.php";
renderHeader("Resident Dashboard", "Access barangay services, requests, appointments, announcements, and community records.", "dashboard");
?>

<section class="cards">
    <div class="card">
        <h3>My Requests</h3>
        <p><?php echo e($my_requests); ?></p>
    </div>
    <div class="card">
        <h3>Appointments</h3>
        <p><?php echo e($my_appointments); ?></p>
    </div>
    <div class="card">
        <h3>Complaints</h3>
        <p><?php echo e($my_complaints); ?></p>
    </div>
    <div class="card">
        <h3>Announcements</h3>
        <p><?php echo e($total_announcements); ?></p>
    </div>
</section>

<?php if (!$resident || $resident["status"] !== "Verified") { ?>
    <div class="notice">
        <p class="error">Complete your resident profile and wait for barangay verification before requesting documents or filing complaints.</p>
    </div>
<?php } ?>

<section class="panel">
    <h3>Online Services</h3>
    <div class="service-grid">
        <a class="service-card" href="<?php echo e(appPath("modules/residents/profile.php")); ?>"><span class="service-icon">ID</span><strong>Resident profile</strong></a>
        <a class="service-card" href="<?php echo e(appPath("modules/documents/request.php")); ?>"><span class="service-icon">DC</span><strong>Request documents</strong></a>
        <a class="service-card" href="<?php echo e(appPath("modules/health/book.php")); ?>"><span class="service-icon">HC</span><strong>Book appointment</strong></a>
        <a class="service-card" href="<?php echo e(appPath("modules/blotter/file.php")); ?>"><span class="service-icon">BL</span><strong>File complaint</strong></a>
    </div>
</section>

<section class="panel">
    <h3>Latest Announcements</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($latest_announcements && mysqli_num_rows($latest_announcements) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($latest_announcements)) { ?>
                        <tr>
                            <td><?php echo e($row["title"]); ?></td>
                            <td><span class="badge"><?php echo e($row["category"]); ?></span></td>
                            <td><?php echo e($row["created_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="3">No announcements posted yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
