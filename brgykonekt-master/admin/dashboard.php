<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireLogin();

if (currentRoleId() === 8) {
    redirectTo("resident/dashboard.php");
}

$total_residents = getCountValue($conn, "SELECT COUNT(*) AS total FROM residents");
$pending_requests = getCountValue($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE status = 'Pending'");
$collection_today = getSumValue($conn, "SELECT SUM(amount) AS total FROM payments WHERE DATE(payment_date) = CURDATE()");
$appointments_today = getCountValue($conn, "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()");
$open_cases = getCountValue($conn, "SELECT COUNT(*) AS total FROM blotter_cases WHERE status NOT IN ('Closed', 'Settled')");
$announcements = getCountValue($conn, "SELECT COUNT(*) AS total FROM announcements");

$recent_requests = mysqli_query($conn, "SELECT document_requests.*, document_types.document_name, residents.first_name, residents.last_name
                                        FROM document_requests
                                        LEFT JOIN document_types ON document_requests.document_type_id = document_types.id
                                        LEFT JOIN residents ON document_requests.resident_id = residents.id
                                        ORDER BY document_requests.requested_at DESC
                                        LIMIT 6");

include "../includes/header.php";
renderHeader("Admin / Staff Dashboard", "Monitor barangay services, requests, collections, appointments, and case activity.", "dashboard");
?>

<section class="cards">
    <div class="card">
        <h3>Total Residents</h3>
        <p><?php echo e($total_residents); ?></p>
    </div>
    <div class="card">
        <h3>Pending Requests</h3>
        <p><?php echo e($pending_requests); ?></p>
    </div>
    <?php if (hasStaffLevel(2)) { // financial figures: Treasurer and above ?>
        <div class="card">
            <h3>Collected Today</h3>
            <p>PHP <?php echo e(number_format($collection_today, 2)); ?></p>
        </div>
    <?php } ?>
    <div class="card">
        <h3>Appointments Today</h3>
        <p><?php echo e($appointments_today); ?></p>
    </div>
    <div class="card">
        <h3>Open Cases</h3>
        <p><?php echo e($open_cases); ?></p>
    </div>
    <div class="card">
        <h3>Announcements</h3>
        <p><?php echo e($announcements); ?></p>
    </div>
</section>

<section class="panel">
    <h3>Service Shortcuts</h3>
    <div class="service-grid">
        <?php if (hasStaffLevel(1)) { ?>
            <a class="service-card" href="<?php echo e(appPath("modules/blotter/manage.php")); ?>"><span class="service-icon">BC</span><strong>Blotter & complaints</strong></a>
        <?php } ?>
        <?php if (hasStaffLevel(2)) { ?>
            <a class="service-card" href="<?php echo e(appPath("modules/payments/manage.php")); ?>"><span class="service-icon">OR</span><strong>Payments & receipts</strong></a>
        <?php } ?>
        <?php if (hasStaffLevel(3)) { ?>
            <a class="service-card" href="<?php echo e(appPath("modules/residents/verify.php")); ?>"><span class="service-icon">RV</span><strong>Verify residents</strong></a>
            <a class="service-card" href="<?php echo e(appPath("modules/documents/manage.php")); ?>"><span class="service-icon">DR</span><strong>Document workflow</strong></a>
        <?php } ?>
        <?php if (canViewHealthRecords()) { ?>
            <a class="service-card" href="<?php echo e(appPath("modules/health/manage.php")); ?>"><span class="service-icon">AP</span><strong>Appointments</strong></a>
        <?php } ?>
    </div>
</section>

<section class="panel">
    <h3>Recent Document Requests</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Document</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_requests && mysqli_num_rows($recent_requests) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($recent_requests)) { ?>
                        <tr>
                            <td><?php echo e(trim(($row["first_name"] ?? "") . " " . ($row["last_name"] ?? ""))); ?></td>
                            <td><?php echo e($row["document_name"] ?? "Document"); ?></td>
                            <td><?php echo e($row["purpose"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td><?php echo e($row["requested_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No document requests yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
