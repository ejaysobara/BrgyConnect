<?php
include "../../includes/auth_check.php";
include "../../config/database.php";
require_once "../../includes/privacy.php";

requireRoles([8]);
requirePrivacyConsent($conn);

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

$resident_result = mysqli_query($conn, "SELECT * FROM residents WHERE user_id = '$user_id'");
$resident = $resident_result ? mysqli_fetch_assoc($resident_result) : null;
$resident_id = $resident ? (int)$resident["id"] : 0;

// Appointment service categories (mirrors the printable forms available).
$service_categories = [
    "health" => "Health Center Services",
    "pet_registration" => "Pet Registration",
    "complaint" => "Complaint Filing Assistance",
    "blotter" => "Blotter Report Assistance",
    "business_permit" => "Application for Business Permit",
    "special_permit" => "Request for Special Permit",
    "other" => "Other Services (please specify)"
];

if (isset($_POST["book_appointment"])) {
    if (!$resident || $resident["status"] !== "Verified") {
        $error = "Your resident profile must be verified before booking an appointment.";
    } else {
        $service_category = $_POST["service_category"] ?? "";
        if (!isset($service_categories[$service_category])) {
            $service_category = "other";
        }
        $other_service = trim($_POST["other_service"] ?? "");
        $health_service_id = (int)($_POST["health_service_id"] ?? 0);
        $appointment_date = mysqli_real_escape_string($conn, $_POST["appointment_date"]);
        $appointment_time = mysqli_real_escape_string($conn, $_POST["appointment_time"]);

        // Only health bookings reference a health_services row; validate the
        // chosen service actually exists so the FK cannot fail.
        $valid_health_service = false;
        if ($service_category === "health" && $health_service_id > 0) {
            $service_check = mysqli_prepare($conn, "SELECT id FROM health_services WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($service_check, "i", $health_service_id);
            mysqli_stmt_execute($service_check);
            $service_check_result = mysqli_stmt_get_result($service_check);
            $valid_health_service = $service_check_result && mysqli_num_rows($service_check_result) === 1;
        }

        if ($service_category === "other" && $other_service === "") {
            $error = "Please describe the service you want to book under Other Services.";
        } elseif ($service_category === "health" && !$valid_health_service) {
            $error = "Please choose a health service.";
        } else {
            if ($service_category !== "other") {
                $other_service = "";
            }

            // Non-health appointments store NULL (not 0) so the foreign key
            // on health_service_id is satisfied. Make the column nullable if
            // this database still has it as NOT NULL.
            $column_check = mysqli_query($conn, "SHOW COLUMNS FROM appointments LIKE 'health_service_id'");
            $column = $column_check ? mysqli_fetch_assoc($column_check) : null;
            if ($column && strtoupper($column["Null"]) === "NO") {
                mysqli_query($conn, "ALTER TABLE appointments MODIFY health_service_id INT NULL DEFAULT NULL");
            }

            $health_service_sql = $service_category === "health" ? "'" . $health_service_id . "'" : "NULL";
            $service_category_safe = mysqli_real_escape_string($conn, $service_category);
            $other_service_safe = mysqli_real_escape_string($conn, $other_service);
            $queue_number = getCountValue($conn, "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = '$appointment_date'") + 1;

            $sql = "INSERT INTO appointments (resident_id, health_service_id, service_category, other_service, appointment_date, appointment_time, queue_number, status)
                    VALUES ('$resident_id', $health_service_sql, '$service_category_safe', '$other_service_safe', '$appointment_date', '$appointment_time', '$queue_number', 'Pending')";

            if (mysqli_query($conn, $sql)) {
                $message = "Appointment booked successfully. Your queue number is $queue_number.";
            } else {
                $error = "Unable to book appointment: " . mysqli_error($conn);
            }
        }
    }
}

$services = mysqli_query($conn, "SELECT * FROM health_services ORDER BY service_name ASC");
$my_appointments = mysqli_query($conn, "SELECT appointments.*, health_services.service_name
                                        FROM appointments
                                        LEFT JOIN health_services ON appointments.health_service_id = health_services.id
                                        WHERE appointments.resident_id = '$resident_id'
                                        ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC");

function appointmentServiceLabel($row, $service_categories) {
    $category = $row["service_category"] ?? "health";
    if ($category === "health" || $category === "") {
        return $row["service_name"] ?? "Health Center";
    }
    if ($category === "other" && !empty($row["other_service"])) {
        return "Other: " . $row["other_service"];
    }
    return $service_categories[$category] ?? ucfirst($category);
}

include "../../includes/header.php";
renderHeader("Appointments", "Book barangay services and print blank copies of official forms for physical filing.", "appointments");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <?php if (!$resident || $resident["status"] !== "Verified") { ?>
        <p class="error">Complete and verify your resident profile before booking appointments.</p>
    <?php } else { ?>
        <h3>Book an Appointment</h3>
        <form method="POST">
            <label for="service_category">Type of Service</label>
            <select id="service_category" name="service_category" required onchange="
                document.getElementById('health-fields').style.display = this.value === 'health' ? '' : 'none';
                document.getElementById('other-fields').style.display = this.value === 'other' ? '' : 'none';">
                <?php foreach ($service_categories as $value => $label) { ?>
                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                <?php } ?>
            </select>

            <div id="health-fields">
                <label for="health_service_id">Health Service</label>
                <select id="health_service_id" name="health_service_id">
                    <option value="">Select service</option>
                    <?php if ($services) { ?>
                        <?php while ($service = mysqli_fetch_assoc($services)) { ?>
                            <option value="<?php echo e($service["id"]); ?>"><?php echo e($service["service_name"]); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>

            <div id="other-fields" style="display:none;">
                <label for="other_service">What service would you like to book?</label>
                <input id="other_service" type="text" name="other_service" placeholder="Describe the service you need">
            </div>

            <div class="grid-2">
                <div>
                    <label for="appointment_date">Appointment Date</label>
                    <input id="appointment_date" type="date" name="appointment_date" required>
                </div>
                <div>
                    <label for="appointment_time">Appointment Time</label>
                    <input id="appointment_time" type="time" name="appointment_time" required>
                </div>
            </div>

            <button type="submit" name="book_appointment">Book Appointment</button>
        </form>
    <?php } ?>
    <p><small>Prefer to fill out a form online or print a blank copy? Head to
        <a href="<?php echo e(appPath("modules/documents/request.php")); ?>">Documents</a>.</small></p>
</section>

<section class="panel">
    <h3>My Appointments</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Queue No.</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_appointments && mysqli_num_rows($my_appointments) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($my_appointments)) { ?>
                        <tr>
                            <td><?php echo e(appointmentServiceLabel($row, $service_categories)); ?></td>
                            <td><?php echo e($row["appointment_date"]); ?></td>
                            <td><?php echo e($row["appointment_time"]); ?></td>
                            <td><?php echo e($row["queue_number"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No appointments yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
