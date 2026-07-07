<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireStaffLevel(2); // Treasurer and above: barangay reports

function fetchReportRows($result) {
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

$total_residents = getCountValue($conn, "SELECT COUNT(*) AS total FROM residents");
$verified_residents = getCountValue($conn, "SELECT COUNT(*) AS total FROM residents WHERE status = 'Verified'");
$pending_documents = getCountValue($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE status = 'Pending'");
$released_documents = getCountValue($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE status = 'Released'");
$appointments_today = getCountValue($conn, "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()");
$open_cases = getCountValue($conn, "SELECT COUNT(*) AS total FROM blotter_cases WHERE status NOT IN ('Closed', 'Settled')");
$total_activities = getCountValue($conn, "SELECT COUNT(*) AS total FROM activities");
$collection_today = getSumValue($conn, "SELECT SUM(amount) AS total FROM payments WHERE DATE(payment_date) = CURDATE()");
$collection_month = getSumValue($conn, "SELECT SUM(amount) AS total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())");

$documents_by_type = fetchReportRows(mysqli_query($conn, "SELECT document_types.document_name AS label, COUNT(document_requests.id) AS total
                                                          FROM document_types
                                                          LEFT JOIN document_requests ON document_types.id = document_requests.document_type_id
                                                          GROUP BY document_types.id, document_types.document_name
                                                          ORDER BY total DESC"));
$residents_by_purok = fetchReportRows(mysqli_query($conn, "SELECT COALESCE(NULLIF(purok, ''), 'Unassigned') AS label, COUNT(*) AS total
                                                           FROM residents
                                                           GROUP BY COALESCE(NULLIF(purok, ''), 'Unassigned')
                                                           ORDER BY total DESC"));
$residents_by_gender = fetchReportRows(mysqli_query($conn, "SELECT COALESCE(NULLIF(gender, ''), 'Unspecified') AS label, COUNT(*) AS total
                                                            FROM residents
                                                            GROUP BY COALESCE(NULLIF(gender, ''), 'Unspecified')"));
$appointments_by_service = fetchReportRows(mysqli_query($conn, "SELECT health_services.service_name AS label, COUNT(appointments.id) AS total
                                                                FROM health_services
                                                                LEFT JOIN appointments ON health_services.id = appointments.health_service_id
                                                                GROUP BY health_services.id, health_services.service_name
                                                                ORDER BY total DESC"));
$complaints_by_status = fetchReportRows(mysqli_query($conn, "SELECT status AS label, COUNT(*) AS total
                                                             FROM blotter_cases
                                                             GROUP BY status
                                                             ORDER BY total DESC"));
$monthly_collections = fetchReportRows(mysqli_query($conn, "SELECT DATE_FORMAT(payment_date, '%Y-%m') AS label, SUM(amount) AS total
                                                            FROM payments
                                                            GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                                                            ORDER BY label DESC
                                                            LIMIT 6"));

include "../../includes/header.php";
renderHeader("Reports and Analytics", "Track resident, document, health, financial, blotter, appointment, and activity reports.", "reports");
?>

<section class="cards">
    <div class="card"><h3>Total Residents</h3><p><?php echo e($total_residents); ?></p></div>
    <div class="card"><h3>Verified Residents</h3><p><?php echo e($verified_residents); ?></p></div>
    <div class="card"><h3>Pending Documents</h3><p><?php echo e($pending_documents); ?></p></div>
    <div class="card"><h3>Released Documents</h3><p><?php echo e($released_documents); ?></p></div>
    <div class="card"><h3>Appointments Today</h3><p><?php echo e($appointments_today); ?></p></div>
    <div class="card"><h3>Open Cases</h3><p><?php echo e($open_cases); ?></p></div>
    <div class="card"><h3>Collection Today</h3><p>PHP <?php echo e(number_format($collection_today, 2)); ?></p></div>
    <div class="card"><h3>Collection This Month</h3><p>PHP <?php echo e(number_format($collection_month, 2)); ?></p></div>
    <div class="card"><h3>Activities</h3><p><?php echo e($total_activities); ?></p></div>
</section>

<section class="grid-2">
    <?php
    $report_sets = [
        "Population by Purok" => $residents_by_purok,
        "Population by Gender" => $residents_by_gender,
        "Documents Issued by Type" => $documents_by_type,
        "Health Services" => $appointments_by_service,
        "Complaint Statistics" => $complaints_by_status,
        "Monthly Collections" => $monthly_collections
    ];
    ?>

    <?php foreach ($report_sets as $title => $rows) { ?>
        <div class="panel">
            <h3><?php echo e($title); ?></h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0) { ?>
                            <?php foreach ($rows as $row) { ?>
                                <tr>
                                    <td><?php echo e($row["label"]); ?></td>
                                    <td><?php echo is_numeric($row["total"]) && strpos((string)$row["total"], ".") !== false ? e(number_format((float)$row["total"], 2)) : e($row["total"]); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="2">No report data yet.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>
</section>

<?php include "../../includes/footer.php"; ?>
