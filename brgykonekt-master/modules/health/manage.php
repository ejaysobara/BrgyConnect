<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([1, 2, 5]);

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

$appointments = mysqli_query($conn, "SELECT appointments.*, health_services.service_name,
                                            residents.first_name, residents.middle_name, residents.last_name
                                     FROM appointments
                                     INNER JOIN health_services ON appointments.health_service_id = health_services.id
                                     INNER JOIN residents ON appointments.resident_id = residents.id
                                     ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC");

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
                            <td><?php echo e($row["service_name"]); ?></td>
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

<?php include "../../includes/footer.php"; ?>
