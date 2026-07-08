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

if (isset($_POST["file_complaint"])) {
    if (!$resident || $resident["status"] !== "Verified") {
        $error = "Your resident profile must be verified before filing a complaint.";
    } else {
        $respondent_name = mysqli_real_escape_string($conn, trim($_POST["respondent_name"]));
        $incident_date = mysqli_real_escape_string($conn, $_POST["incident_date"]);
        $incident_location = mysqli_real_escape_string($conn, trim($_POST["incident_location"]));
        $complaint_details = mysqli_real_escape_string($conn, trim($_POST["complaint_details"]));
        $evidence_path = mysqli_real_escape_string($conn, saveUploadedFile("evidence", "assets/uploads/blotter", "evidence"));

        $sql = "INSERT INTO blotter_cases
                (resident_id, respondent_name, incident_date, incident_location, complaint_details, evidence_path, status)
                VALUES
                ('$resident_id', '$respondent_name', '$incident_date', '$incident_location', '$complaint_details', '$evidence_path', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            $message = "Complaint filed successfully.";
        } else {
            $error = "Unable to file complaint: " . mysqli_error($conn);
        }
    }
}

$my_cases = false;
if ($resident_id > 0) {
    $my_cases = mysqli_query($conn, "SELECT * FROM blotter_cases
                                     WHERE resident_id = '$resident_id'
                                     ORDER BY created_at DESC");
}

include "../../includes/header.php";
renderHeader("File Blotter / Complaint", "Submit incident reports, upload evidence, and track mediation or resolution status.", "blotter");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <?php if (!$resident || $resident["status"] !== "Verified") { ?>
        <p class="error">Complete and verify your resident profile before filing a complaint.</p>
    <?php } else { ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="respondent_name">Respondent Name</label>
            <input id="respondent_name" type="text" name="respondent_name" required>

            <div class="grid-2">
                <div>
                    <label for="incident_date">Incident Date and Time</label>
                    <input id="incident_date" type="datetime-local" name="incident_date" required>
                </div>
                <div>
                    <label for="incident_location">Incident Location</label>
                    <input id="incident_location" type="text" name="incident_location" required>
                </div>
            </div>

            <label for="complaint_details">Complaint Details</label>
            <textarea id="complaint_details" name="complaint_details" required></textarea>

            <label for="evidence">Upload Evidence</label>
            <input id="evidence" type="file" name="evidence" accept=".jpg,.jpeg,.png,.pdf">

            <button type="submit" name="file_complaint">Submit Complaint</button>
        </form>
    <?php } ?>
</section>

<section class="panel">
    <h3>My Complaints</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Respondent</th>
                    <th>Incident Date</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Mediation</th>
                    <th>Date Filed</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_cases && mysqli_num_rows($my_cases) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($my_cases)) { ?>
                        <tr>
                            <td><?php echo e($row["respondent_name"]); ?></td>
                            <td><?php echo e($row["incident_date"]); ?></td>
                            <td><?php echo e($row["incident_location"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td><?php echo e($row["mediation_schedule"] ?? "Not scheduled"); ?></td>
                            <td><?php echo e($row["created_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="6">No complaints filed yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
