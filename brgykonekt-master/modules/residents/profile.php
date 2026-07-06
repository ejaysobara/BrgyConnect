<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([8]);

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

$check = mysqli_query($conn, "SELECT * FROM residents WHERE user_id = '$user_id'");
$resident = $check ? mysqli_fetch_assoc($check) : null;

function profileSelected($resident, $field, $value) {
    return (($resident[$field] ?? "") === $value) ? "selected" : "";
}

if (isset($_POST["save_profile"])) {
    $first_name = mysqli_real_escape_string($conn, trim($_POST["first_name"]));
    $middle_name = mysqli_real_escape_string($conn, trim($_POST["middle_name"]));
    $last_name = mysqli_real_escape_string($conn, trim($_POST["last_name"]));
    $birthdate = mysqli_real_escape_string($conn, $_POST["birthdate"]);
    $gender = mysqli_real_escape_string($conn, $_POST["gender"]);
    $civil_status = mysqli_real_escape_string($conn, $_POST["civil_status"]);
    $address = mysqli_real_escape_string($conn, trim($_POST["address"]));
    $purok = mysqli_real_escape_string($conn, trim($_POST["purok"]));
    $contact_number = mysqli_real_escape_string($conn, trim($_POST["contact_number"]));
    $occupation = mysqli_real_escape_string($conn, trim($_POST["occupation"]));
    $family_information = mysqli_real_escape_string($conn, trim($_POST["family_information"]));
    $emergency_contact = mysqli_real_escape_string($conn, trim($_POST["emergency_contact"]));

    $valid_id_path = $resident["valid_id_path"] ?? "";
    $proof_path = $resident["proof_of_residency_path"] ?? "";
    $new_valid_id = saveUploadedFile("valid_id", "assets/uploads/residents", "valid-id");
    $new_proof = saveUploadedFile("proof_of_residency", "assets/uploads/residents", "proof");

    if ($new_valid_id !== "") {
        $valid_id_path = $new_valid_id;
    }
    if ($new_proof !== "") {
        $proof_path = $new_proof;
    }

    $valid_id_path = mysqli_real_escape_string($conn, $valid_id_path);
    $proof_path = mysqli_real_escape_string($conn, $proof_path);

    if ($resident) {
        $resident_id = (int)$resident["id"];
        $sql = "UPDATE residents SET
                first_name = '$first_name',
                middle_name = '$middle_name',
                last_name = '$last_name',
                birthdate = '$birthdate',
                gender = '$gender',
                civil_status = '$civil_status',
                address = '$address',
                purok = '$purok',
                contact_number = '$contact_number',
                occupation = '$occupation',
                family_information = '$family_information',
                emergency_contact = '$emergency_contact',
                valid_id_path = '$valid_id_path',
                proof_of_residency_path = '$proof_path',
                status = 'Pending'
                WHERE id = '$resident_id'";
    } else {
        $sql = "INSERT INTO residents
                (user_id, first_name, middle_name, last_name, birthdate, gender, civil_status, address, purok, contact_number, occupation, family_information, emergency_contact, valid_id_path, proof_of_residency_path, status)
                VALUES
                ('$user_id', '$first_name', '$middle_name', '$last_name', '$birthdate', '$gender', '$civil_status', '$address', '$purok', '$contact_number', '$occupation', '$family_information', '$emergency_contact', '$valid_id_path', '$proof_path', 'Pending')";
    }

    if (mysqli_query($conn, $sql)) {
        $resident_id = $resident ? (int)$resident["id"] : mysqli_insert_id($conn);
        $resident_code = "BRGY-" . str_pad((string)$resident_id, 6, "0", STR_PAD_LEFT);
        mysqli_query($conn, "UPDATE residents SET resident_code = '$resident_code' WHERE id = '$resident_id'");
        $message = "Profile saved successfully. Please wait for barangay verification.";
        $check = mysqli_query($conn, "SELECT * FROM residents WHERE user_id = '$user_id'");
        $resident = $check ? mysqli_fetch_assoc($check) : null;
    } else {
        $error = "Unable to save profile: " . mysqli_error($conn);
    }
}

include "../../includes/header.php";
renderHeader("My Resident Profile", "Maintain your barangay identity, household details, uploads, and verification status.", "profile");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <div class="quick-actions">
        <span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $resident["status"] ?? "no-profile"))); ?>">
            Status: <?php echo e($resident["status"] ?? "No profile yet"); ?>
        </span>
        <?php if (!empty($resident["resident_code"])) { ?>
            <span class="badge">Resident Code: <?php echo e($resident["resident_code"]); ?></span>
        <?php } ?>
    </div>
</section>

<section class="panel">
    <h3>Resident Information</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="grid-2">
            <div>
                <label for="first_name">First Name</label>
                <input id="first_name" type="text" name="first_name" required value="<?php echo e($resident["first_name"] ?? ""); ?>">
            </div>
            <div>
                <label for="middle_name">Middle Name</label>
                <input id="middle_name" type="text" name="middle_name" value="<?php echo e($resident["middle_name"] ?? ""); ?>">
            </div>
            <div>
                <label for="last_name">Last Name</label>
                <input id="last_name" type="text" name="last_name" required value="<?php echo e($resident["last_name"] ?? ""); ?>">
            </div>
            <div>
                <label for="birthdate">Birthdate</label>
                <input id="birthdate" type="date" name="birthdate" value="<?php echo e($resident["birthdate"] ?? ""); ?>">
            </div>
            <div>
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="Male" <?php echo profileSelected($resident ?? [], "gender", "Male"); ?>>Male</option>
                    <option value="Female" <?php echo profileSelected($resident ?? [], "gender", "Female"); ?>>Female</option>
                </select>
            </div>
            <div>
                <label for="civil_status">Civil Status</label>
                <select id="civil_status" name="civil_status">
                    <option value="Single" <?php echo profileSelected($resident ?? [], "civil_status", "Single"); ?>>Single</option>
                    <option value="Married" <?php echo profileSelected($resident ?? [], "civil_status", "Married"); ?>>Married</option>
                    <option value="Widowed" <?php echo profileSelected($resident ?? [], "civil_status", "Widowed"); ?>>Widowed</option>
                    <option value="Annulled" <?php echo profileSelected($resident ?? [], "civil_status", "Annulled"); ?>>Annulled</option>
                </select>
            </div>
            <div>
                <label for="purok">Purok Assignment</label>
                <input id="purok" type="text" name="purok" value="<?php echo e($resident["purok"] ?? ""); ?>">
            </div>
            <div>
                <label for="contact_number">Contact Number</label>
                <input id="contact_number" type="text" name="contact_number" value="<?php echo e($resident["contact_number"] ?? ""); ?>">
            </div>
            <div>
                <label for="occupation">Occupation / Employment</label>
                <input id="occupation" type="text" name="occupation" value="<?php echo e($resident["occupation"] ?? ""); ?>">
            </div>
            <div>
                <label for="emergency_contact">Emergency Contact</label>
                <input id="emergency_contact" type="text" name="emergency_contact" value="<?php echo e($resident["emergency_contact"] ?? ""); ?>">
            </div>
        </div>

        <label for="address">Complete Address</label>
        <textarea id="address" name="address" required><?php echo e($resident["address"] ?? ""); ?></textarea>

        <label for="family_information">Family Information</label>
        <textarea id="family_information" name="family_information" placeholder="Household members, relationship, and notes"><?php echo e($resident["family_information"] ?? ""); ?></textarea>

        <div class="grid-2">
            <div>
                <label for="valid_id">Upload Valid ID</label>
                <input id="valid_id" type="file" name="valid_id" accept=".jpg,.jpeg,.png,.pdf">
                <?php if (!empty($resident["valid_id_path"])) { ?>
                    <a href="<?php echo e(appPath($resident["valid_id_path"])); ?>" target="_blank">View uploaded ID</a>
                <?php } ?>
            </div>
            <div>
                <label for="proof_of_residency">Upload Proof of Residency</label>
                <input id="proof_of_residency" type="file" name="proof_of_residency" accept=".jpg,.jpeg,.png,.pdf">
                <?php if (!empty($resident["proof_of_residency_path"])) { ?>
                    <a href="<?php echo e(appPath($resident["proof_of_residency_path"])); ?>" target="_blank">View proof of residency</a>
                <?php } ?>
            </div>
        </div>

        <button type="submit" name="save_profile">Save Profile</button>
    </form>
</section>

<?php include "../../includes/footer.php"; ?>
