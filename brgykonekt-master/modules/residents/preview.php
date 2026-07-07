<?php
/*
 * Resident profile preview — rendered inside the pop-up on the Residents tab.
 * All staff can view; the health information section is restricted to
 * authorized health staff (and Captain/administrator oversight).
 */
include "../../includes/auth_check.php";
include "../../config/database.php";

requireStaffLevel(1);

$resident_id = (int)($_GET["id"] ?? 0);

$result = mysqli_query($conn, "SELECT residents.*, users.username, users.email
                               FROM residents
                               LEFT JOIN users ON residents.user_id = users.id
                               WHERE residents.id = '$resident_id'");
$resident = $result ? mysqli_fetch_assoc($result) : null;

if (!$resident) {
    http_response_code(404);
    exit("Resident not found.");
}

$full_name = trim($resident["first_name"] . " " . $resident["middle_name"] . " " . $resident["last_name"]);

$health_info = null;
if (canViewHealthRecords()) {
    $health_result = mysqli_query($conn, "SELECT * FROM resident_health_info WHERE resident_id = '$resident_id'");
    $health_info = $health_result ? mysqli_fetch_assoc($health_result) : null;
    addAuditLog($conn, $_SESSION["user_id"], "view_health_info", "resident#$resident_id", "profile preview");
}

$documents = mysqli_query($conn, "SELECT document_requests.*, document_types.document_name
                                  FROM document_requests
                                  LEFT JOIN document_types ON document_requests.document_type_id = document_types.id
                                  WHERE document_requests.resident_id = '$resident_id'
                                  ORDER BY document_requests.requested_at DESC");

$appointments = mysqli_query($conn, "SELECT appointments.*, health_services.service_name
                                     FROM appointments
                                     LEFT JOIN health_services ON appointments.health_service_id = health_services.id
                                     WHERE appointments.resident_id = '$resident_id'
                                     ORDER BY appointments.appointment_date DESC");

$complaints = mysqli_query($conn, "SELECT * FROM blotter_cases WHERE resident_id = '$resident_id' ORDER BY created_at DESC");
$pets = mysqli_query($conn, "SELECT * FROM pets WHERE resident_id = '$resident_id' ORDER BY pet_name ASC");

function previewAppointmentLabel($row) {
    $category = $row["service_category"] ?? "health";
    if ($category === "health" || $category === "") {
        return $row["service_name"] ?? "Health Center";
    }
    if ($category === "other" && !empty($row["other_service"])) {
        return "Other: " . $row["other_service"];
    }
    return ucwords(str_replace("_", " ", $category));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($full_name); ?> - Resident Preview</title>
    <link rel="stylesheet" href="<?php echo e(appPath("assets/css/style.css")); ?>">
    <style>
        body { background: #f5f7fb; padding: 18px; }
        .preview-head { display: flex; gap: 16px; align-items: center; margin-bottom: 14px; }
        .preview-photo { width: 96px; height: 96px; border-radius: 12px; object-fit: cover; background: #dbe3ef; }
        .preview-photo.empty { display: grid; place-items: center; color: #6b7280; font-size: 12px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 6px 18px; }
        .info-grid p { margin: 4px 0; font-size: 13px; }
        .panel { margin-bottom: 14px; }
        table td, table th { font-size: 13px; }
    </style>
</head>
<body>

<section class="panel">
    <div class="preview-head">
        <?php if (!empty($resident["photo_path"])) { ?>
            <img class="preview-photo" src="<?php echo e(appPath($resident["photo_path"])); ?>" alt="Resident photo">
        <?php } else { ?>
            <div class="preview-photo empty">No photo</div>
        <?php } ?>
        <div>
            <h2 style="margin:0;"><?php echo e($full_name); ?></h2>
            <p style="margin:4px 0;">
                <span class="badge">Resident Code: <?php echo e($resident["resident_code"] ?: "Not assigned"); ?></span>
                <span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $resident["status"]))); ?>"><?php echo e($resident["status"]); ?></span>
            </p>
            <?php if (!empty($resident["photo_path"])) { ?>
                <a class="button secondary" href="<?php echo e(appPath($resident["photo_path"])); ?>" download>Download Photo</a>
            <?php } ?>
        </div>
    </div>

    <div class="info-grid">
        <p><strong>Username:</strong> <?php echo e($resident["username"] ?? ""); ?></p>
        <p><strong>Email:</strong> <?php echo e($resident["email"] ?? ""); ?></p>
        <p><strong>Birthdate:</strong> <?php echo e($resident["birthdate"]); ?></p>
        <p><strong>Gender:</strong> <?php echo e($resident["gender"]); ?></p>
        <p><strong>Civil Status:</strong> <?php echo e($resident["civil_status"]); ?></p>
        <p><strong>Purok:</strong> <?php echo e($resident["purok"]); ?></p>
        <p><strong>Contact:</strong> <?php echo e($resident["contact_number"]); ?></p>
        <p><strong>Occupation:</strong> <?php echo e($resident["occupation"]); ?></p>
        <p><strong>Emergency Contact:</strong> <?php echo e($resident["emergency_contact"]); ?></p>
        <p><strong>Address:</strong> <?php echo e($resident["address"]); ?></p>
    </div>
    <?php if (!empty($resident["family_information"])) { ?>
        <p style="font-size:13px;"><strong>Family Information:</strong> <?php echo nl2br(e($resident["family_information"])); ?></p>
    <?php } ?>
    <p style="font-size:13px;">
        <?php if (!empty($resident["valid_id_path"])) { ?>
            <a href="<?php echo e(appPath($resident["valid_id_path"])); ?>" target="_blank">View Valid ID</a> &nbsp;
        <?php } ?>
        <?php if (!empty($resident["proof_of_residency_path"])) { ?>
            <a href="<?php echo e(appPath($resident["proof_of_residency_path"])); ?>" target="_blank">View Proof of Residency</a>
        <?php } ?>
    </p>
</section>

<?php if (canViewHealthRecords()) { ?>
<section class="panel">
    <h3>Health Information <span class="badge">Confidential</span></h3>
    <?php if (!$health_info) { ?>
        <p class="empty-state">This resident has not filled out their health information yet.</p>
    <?php } else { ?>
        <div class="info-grid">
            <p><strong>Blood Type:</strong> <?php echo e($health_info["blood_type"]); ?></p>
            <p><strong>PhilHealth No.:</strong> <?php echo e($health_info["philhealth_number"]); ?></p>
            <p><strong>Allergies:</strong> <?php echo e($health_info["allergies"]); ?></p>
            <p><strong>Conditions:</strong> <?php echo e($health_info["medical_conditions"]); ?></p>
            <p><strong>Medications:</strong> <?php echo e($health_info["medications"]); ?></p>
            <p><strong>Disabilities:</strong> <?php echo e($health_info["disabilities"]); ?></p>
            <p><strong>Notes:</strong> <?php echo e($health_info["notes"]); ?></p>
        </div>
    <?php } ?>
</section>
<?php } ?>

<section class="panel">
    <h3>Document Requests</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Document</th><th>Purpose</th><th>Status</th><th>Requested</th></tr></thead>
            <tbody>
                <?php if ($documents && mysqli_num_rows($documents) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($documents)) { ?>
                        <tr>
                            <td><a href="record.php?type=document&id=<?php echo e($row["id"]); ?>"><?php echo e($row["document_name"] ?? "Document"); ?></a></td>
                            <td><?php echo e($row["purpose"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td><?php echo e($row["requested_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="4">No document requests.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h3>Appointments</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
            <tbody>
                <?php if ($appointments && mysqli_num_rows($appointments) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($appointments)) { ?>
                        <tr>
                            <td><a href="record.php?type=appointment&id=<?php echo e($row["id"]); ?>"><?php echo e(previewAppointmentLabel($row)); ?></a></td>
                            <td><?php echo e($row["appointment_date"]); ?></td>
                            <td><?php echo e($row["appointment_time"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="4">No appointments.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h3>Complaints Filed</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Respondent</th><th>Incident Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php if ($complaints && mysqli_num_rows($complaints) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($complaints)) { ?>
                        <tr>
                            <td><a href="record.php?type=blotter&id=<?php echo e($row["id"]); ?>"><?php echo e($row["respondent_name"]); ?></a></td>
                            <td><?php echo e($row["incident_date"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="3">No complaints filed.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h3>Registered Pets</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Species</th><th>Breed</th><th>Vaccination</th><th>Last Vaccinated</th></tr></thead>
            <tbody>
                <?php if ($pets && mysqli_num_rows($pets) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($pets)) { ?>
                        <tr>
                            <td><?php echo e($row["pet_name"]); ?></td>
                            <td><?php echo e($row["species"]); ?></td>
                            <td><?php echo e($row["breed"]); ?></td>
                            <td><?php echo e($row["vaccination_status"]); ?></td>
                            <td><?php echo e($row["last_vaccination_date"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No registered pets.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

</body>
</html>
