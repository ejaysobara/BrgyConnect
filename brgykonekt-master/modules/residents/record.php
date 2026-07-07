<?php
/*
 * Single-record preview (document request, appointment, or blotter case),
 * opened from the resident profile preview.
 */
include "../../includes/auth_check.php";
include "../../config/database.php";

requireStaffLevel(1);

$type = $_GET["type"] ?? "";
$id = (int)($_GET["id"] ?? 0);
$title = "Record Preview";
$fields = [];

if ($type === "document") {
    $result = mysqli_query($conn, "SELECT document_requests.*, document_types.document_name, document_types.fee
                                   FROM document_requests
                                   LEFT JOIN document_types ON document_requests.document_type_id = document_types.id
                                   WHERE document_requests.id = '$id'");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if ($row) {
        $title = "Document Request #" . $row["id"];
        $fields = [
            "Document" => $row["document_name"] ?? "Document",
            "Purpose" => $row["purpose"],
            "Fee" => "PHP " . number_format((float)($row["fee"] ?? 0), 2),
            "Status" => $row["status"],
            "Requested At" => $row["requested_at"]
        ];
    }
} elseif ($type === "appointment") {
    $result = mysqli_query($conn, "SELECT appointments.*, health_services.service_name
                                   FROM appointments
                                   LEFT JOIN health_services ON appointments.health_service_id = health_services.id
                                   WHERE appointments.id = '$id'");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if ($row) {
        $category = $row["service_category"] ?? "health";
        $service = ($category === "health" || $category === "")
            ? ($row["service_name"] ?? "Health Center")
            : (($category === "other" && !empty($row["other_service"])) ? "Other: " . $row["other_service"] : ucwords(str_replace("_", " ", $category)));
        $title = "Appointment #" . $row["id"];
        $fields = [
            "Service" => $service,
            "Date" => $row["appointment_date"],
            "Time" => $row["appointment_time"],
            "Queue Number" => $row["queue_number"],
            "Status" => $row["status"],
            "Booked At" => $row["created_at"]
        ];
    }
} elseif ($type === "blotter") {
    $result = mysqli_query($conn, "SELECT * FROM blotter_cases WHERE id = '$id'");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if ($row) {
        $title = "Blotter Case #" . $row["id"];
        $fields = [
            "Respondent" => $row["respondent_name"],
            "Incident Date" => $row["incident_date"],
            "Location" => $row["incident_location"],
            "Details" => $row["complaint_details"],
            "Mediation Schedule" => $row["mediation_schedule"],
            "Resolution" => $row["resolution"],
            "Status" => $row["status"],
            "Filed At" => $row["created_at"]
        ];
    }
}

if (empty($fields)) {
    http_response_code(404);
    exit("Record not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <link rel="stylesheet" href="<?php echo e(appPath("assets/css/style.css")); ?>">
    <style>
        body { background: #f5f7fb; padding: 18px; }
        table td, table th { font-size: 13px; }
    </style>
</head>
<body>
<section class="panel">
    <a href="javascript:history.back()" class="button secondary">&larr; Back</a>
    <h2><?php echo e($title); ?></h2>
    <div class="table-wrap">
        <table>
            <tbody>
                <?php foreach ($fields as $label => $value) { ?>
                    <tr>
                        <th style="width:180px;"><?php echo e($label); ?></th>
                        <td><?php echo nl2br(e((string)$value)); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if ($type === "blotter" && !empty($row["evidence_path"])) { ?>
        <p><a href="<?php echo e(appPath($row["evidence_path"])); ?>" target="_blank">View attached evidence</a></p>
    <?php } ?>
</section>
</body>
</html>
