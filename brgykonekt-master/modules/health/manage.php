<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireHealthAccess(); // Health staff, Captain, and administrator only

$message = "";
$statuses = ["Pending", "Confirmed", "Completed", "Cancelled", "Rescheduled", "No Show"];

if (isset($_POST["update_status"])) {
    $appointment_id = (int)$_POST["appointment_id"];
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    if (in_array($status, $statuses)) {
        mysqli_query($conn, "UPDATE appointments SET status = '$status' WHERE id = '$appointment_id'");
        addAuditLog($conn, $_SESSION["user_id"], "update_appointment_status", "appointment#$appointment_id", $status);
        $message = "Appointment status updated.";
    }
}

// Optional resident health info lookup (visible only under requireHealthAccess above).
$health_lookup = null;
$health_lookup_name = "";
$lookup_code = trim($_GET["resident_code"] ?? "");

if ($lookup_code !== "") {
    $lookup_code_safe = mysqli_real_escape_string($conn, $lookup_code);
    $lookup_result = mysqli_query($conn, "SELECT residents.id, residents.first_name, residents.last_name, resident_health_info.*
                                          FROM residents
                                          LEFT JOIN resident_health_info ON resident_health_info.resident_id = residents.id
                                          WHERE residents.resident_code = '$lookup_code_safe'");
    $health_lookup = $lookup_result ? mysqli_fetch_assoc($lookup_result) : null;
    if ($health_lookup) {
        $health_lookup_name = trim($health_lookup["first_name"] . " " . $health_lookup["last_name"]);
        addAuditLog($conn, $_SESSION["user_id"], "view_health_info", "resident#" . $health_lookup["id"], $lookup_code);
    }
}

$appointments = mysqli_query($conn, "SELECT appointments.*, health_services.service_name,
                                            residents.first_name, residents.middle_name, residents.last_name
                                     FROM appointments
                                     LEFT JOIN health_services ON appointments.health_service_id = health_services.id
                                     INNER JOIN residents ON appointments.resident_id = residents.id
                                     ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC");

function staffAppointmentServiceLabel($row) {
    $category = $row["service_category"] ?? "health";
    if ($category === "health" || $category === "") {
        return $row["service_name"] ?? "Health Center";
    }
    if ($category === "other" && !empty($row["other_service"])) {
        return "Other: " . $row["other_service"];
    }
    return ucwords(str_replace("_", " ", $category));
}

include "../../includes/header.php";
renderHeader("Health Appointments", "Confirm, complete, cancel, reschedule, or mark no-show appointments.", "health");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Queue</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments && mysqli_num_rows($appointments) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($appointments)) { ?>
                        <tr>
                            <td><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></td>
                            <td><?php echo e(staffAppointmentServiceLabel($row)); ?></td>
                            <td><?php echo e($row["appointment_date"]); ?></td>
                            <td><?php echo e($row["appointment_time"]); ?></td>
                            <td><?php echo e($row["queue_number"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <form class="inline-form" method="POST">
                                    <input type="hidden" name="appointment_id" value="<?php echo e($row["id"]); ?>">
                                    <select name="status" aria-label="Appointment status">
                                        <?php foreach ($statuses as $status) { ?>
                                            <option value="<?php echo e($status); ?>" <?php echo $row["status"] === $status ? "selected" : ""; ?>><?php echo e($status); ?></option>
                                        <?php } ?>
                                    </select>
                                    <button type="submit" name="update_status">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="7">No appointments found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h3>Resident Health Information Lookup</h3>
    <p>Confidential — accessible to authorized health staff only. Search by resident code (e.g. BRGY-000001).</p>
    <form method="GET">
        <label for="resident_code">Resident Code</label>
        <input id="resident_code" type="text" name="resident_code" value="<?php echo e($lookup_code); ?>" placeholder="BRGY-000000">
        <button type="submit">Look Up</button>
    </form>

    <?php if ($lookup_code !== "" && !$health_lookup) { ?>
        <p class="error">No resident found with that code.</p>
    <?php } elseif ($health_lookup) { ?>
        <h3><?php echo e($health_lookup_name); ?></h3>
        <?php if (empty($health_lookup["resident_id"])) { ?>
            <p class="empty-state">This resident has not filled out their health information yet.</p>
        <?php } else { ?>
            <div class="table-wrap">
                <table>
                    <tbody>
                        <tr><th>Blood Type</th><td><?php echo e($health_lookup["blood_type"] ?? ""); ?></td></tr>
                        <tr><th>PhilHealth Number</th><td><?php echo e($health_lookup["philhealth_number"] ?? ""); ?></td></tr>
                        <tr><th>Allergies</th><td><?php echo nl2br(e($health_lookup["allergies"] ?? "")); ?></td></tr>
                        <tr><th>Medical Conditions</th><td><?php echo nl2br(e($health_lookup["medical_conditions"] ?? "")); ?></td></tr>
                        <tr><th>Medications</th><td><?php echo nl2br(e($health_lookup["medications"] ?? "")); ?></td></tr>
                        <tr><th>Disabilities</th><td><?php echo nl2br(e($health_lookup["disabilities"] ?? "")); ?></td></tr>
                        <tr><th>Notes</th><td><?php echo nl2br(e($health_lookup["notes"] ?? "")); ?></td></tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    <?php } ?>
</section>

<?php include "../../includes/footer.php"; ?>
