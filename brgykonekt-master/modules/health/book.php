<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([8]);

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

$resident_result = mysqli_query($conn, "SELECT * FROM residents WHERE user_id = '$user_id'");
$resident = $resident_result ? mysqli_fetch_assoc($resident_result) : null;
$resident_id = $resident ? (int)$resident["id"] : 0;

if (isset($_POST["book_appointment"])) {
    if (!$resident || $resident["status"] !== "Verified") {
        $error = "Your resident profile must be verified before booking an appointment.";
    } else {
        $health_service_id = (int)$_POST["health_service_id"];
        $appointment_date = mysqli_real_escape_string($conn, $_POST["appointment_date"]);
        $appointment_time = mysqli_real_escape_string($conn, $_POST["appointment_time"]);
        $queue_number = getCountValue($conn, "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = '$appointment_date'") + 1;

        $sql = "INSERT INTO appointments (resident_id, health_service_id, appointment_date, appointment_time, queue_number, status)
                VALUES ('$resident_id', '$health_service_id', '$appointment_date', '$appointment_time', '$queue_number', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            $message = "Appointment booked successfully. Your queue number is $queue_number.";
        } else {
            $error = "Unable to book appointment: " . mysqli_error($conn);
        }
    }
}

$services = mysqli_query($conn, "SELECT * FROM health_services ORDER BY service_name ASC");
$my_appointments = mysqli_query($conn, "SELECT appointments.*, health_services.service_name
                                        FROM appointments
                                        INNER JOIN health_services ON appointments.health_service_id = health_services.id
                                        WHERE appointments.resident_id = '$resident_id'
                                        ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC");

include "../../includes/header.php";
renderHeader("Health Center Appointment", "Book checkups, vaccination, prenatal, postnatal, senior, PWD, and medical mission services.", "health");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <?php if (!$resident || $resident["status"] !== "Verified") { ?>
        <p class="error">Complete and verify your resident profile before booking health center appointments.</p>
    <?php } else { ?>
        <form method="POST">
            <label for="health_service_id">Health Service</label>
            <select id="health_service_id" name="health_service_id" required>
                <option value="">Select service</option>
                <?php if ($services) { ?>
                    <?php while ($service = mysqli_fetch_assoc($services)) { ?>
                        <option value="<?php echo e($service["id"]); ?>"><?php echo e($service["service_name"]); ?></option>
                    <?php } ?>
                <?php } ?>
            </select>

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
                            <td><?php echo e($row["service_name"]); ?></td>
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
