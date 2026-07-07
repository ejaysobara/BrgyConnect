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
    $photo_path = $resident["photo_path"] ?? "";
    $new_valid_id = saveUploadedFile("valid_id", "assets/uploads/residents", "valid-id");
    $new_proof = saveUploadedFile("proof_of_residency", "assets/uploads/residents", "proof");
    $new_photo = saveUploadedFile("profile_photo", "assets/uploads/residents", "photo");

    if ($new_valid_id !== "") {
        $valid_id_path = $new_valid_id;
    }
    if ($new_proof !== "") {
        $proof_path = $new_proof;
    }
    if ($new_photo !== "") {
        $photo_path = $new_photo;
    }

    $valid_id_path = mysqli_real_escape_string($conn, $valid_id_path);
    $proof_path = mysqli_real_escape_string($conn, $proof_path);
    $photo_path = mysqli_real_escape_string($conn, $photo_path);

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
                photo_path = '$photo_path',
                status = 'Pending'
                WHERE id = '$resident_id'";
    } else {
        $sql = "INSERT INTO residents
                (user_id, first_name, middle_name, last_name, birthdate, gender, civil_status, address, purok, contact_number, occupation, family_information, emergency_contact, valid_id_path, proof_of_residency_path, photo_path, status)
                VALUES
                ('$user_id', '$first_name', '$middle_name', '$last_name', '$birthdate', '$gender', '$civil_status', '$address', '$purok', '$contact_number', '$occupation', '$family_information', '$emergency_contact', '$valid_id_path', '$proof_path', '$photo_path', 'Pending')";
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

// Bio / Health Information — private to the resident and authorized health staff.
$health_table = mysqli_query($conn, "SHOW TABLES LIKE 'resident_health_info'");
$health_table_exists = $health_table && mysqli_num_rows($health_table) > 0;
$health_info = null;
$resident_id_for_health = $resident ? (int)$resident["id"] : 0;

if ($health_table_exists && $resident_id_for_health > 0) {
    $health_result = mysqli_query($conn, "SELECT * FROM resident_health_info WHERE resident_id = '$resident_id_for_health'");
    $health_info = $health_result ? mysqli_fetch_assoc($health_result) : null;
}

if (isset($_POST["save_health_info"])) {
    if (!$health_table_exists) {
        $error = "Health information storage is not set up yet. Please run the database migration.";
    } elseif ($resident_id_for_health === 0) {
        $error = "Save your resident information first before adding health information.";
    } else {
        $blood_type = mysqli_real_escape_string($conn, trim($_POST["blood_type"]));
        $allergies = mysqli_real_escape_string($conn, trim($_POST["allergies"]));
        $medical_conditions = mysqli_real_escape_string($conn, trim($_POST["medical_conditions"]));
        $medications = mysqli_real_escape_string($conn, trim($_POST["medications"]));
        $disabilities = mysqli_real_escape_string($conn, trim($_POST["disabilities"]));
        $philhealth_number = mysqli_real_escape_string($conn, trim($_POST["philhealth_number"]));
        $health_notes = mysqli_real_escape_string($conn, trim($_POST["health_notes"]));

        if ($health_info) {
            $sql = "UPDATE resident_health_info SET
                    blood_type = '$blood_type',
                    allergies = '$allergies',
                    medical_conditions = '$medical_conditions',
                    medications = '$medications',
                    disabilities = '$disabilities',
                    philhealth_number = '$philhealth_number',
                    notes = '$health_notes'
                    WHERE resident_id = '$resident_id_for_health'";
        } else {
            $sql = "INSERT INTO resident_health_info
                    (resident_id, blood_type, allergies, medical_conditions, medications, disabilities, philhealth_number, notes)
                    VALUES
                    ('$resident_id_for_health', '$blood_type', '$allergies', '$medical_conditions', '$medications', '$disabilities', '$philhealth_number', '$health_notes')";
        }

        if (mysqli_query($conn, $sql)) {
            $message = "Health information saved. This is visible only to you and authorized health staff.";
            $health_result = mysqli_query($conn, "SELECT * FROM resident_health_info WHERE resident_id = '$resident_id_for_health'");
            $health_info = $health_result ? mysqli_fetch_assoc($health_result) : null;
        } else {
            $error = "Unable to save health information: " . mysqli_error($conn);
        }
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
                <label for="profile_photo">Profile Picture</label>
                <input id="profile_photo" type="file" name="profile_photo" accept=".jpg,.jpeg,.png">
                <?php if (!empty($resident["photo_path"])) { ?>
                    <p><img src="<?php echo e(appPath($resident["photo_path"])); ?>" alt="Profile photo" style="width:90px;height:90px;object-fit:cover;border-radius:10px;"></p>
                <?php } ?>
            </div>
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

<section class="panel">
    <h3>Bio / Health Information</h3>
    <p><strong>Private:</strong> this section is visible only to you and authorized barangay health staff. It is used when you book health appointments.</p>

    <?php if (!$resident) { ?>
        <p class="error">Save your resident information above first, then fill out your health information.</p>
    <?php } else { ?>
        <form method="POST">
            <div class="grid-2">
                <div>
                    <label for="blood_type">Blood Type</label>
                    <select id="blood_type" name="blood_type">
                        <?php foreach (["Unknown", "A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"] as $type) { ?>
                            <option value="<?php echo e($type); ?>" <?php echo (($health_info["blood_type"] ?? "Unknown") === $type) ? "selected" : ""; ?>><?php echo e($type); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="philhealth_number">PhilHealth Number</label>
                    <input id="philhealth_number" type="text" name="philhealth_number" value="<?php echo e($health_info["philhealth_number"] ?? ""); ?>">
                </div>
            </div>

            <label for="allergies">Allergies</label>
            <textarea id="allergies" name="allergies" placeholder="Food, medicine, or other allergies"><?php echo e($health_info["allergies"] ?? ""); ?></textarea>

            <label for="medical_conditions">Existing Medical Conditions</label>
            <textarea id="medical_conditions" name="medical_conditions" placeholder="Hypertension, diabetes, asthma, etc."><?php echo e($health_info["medical_conditions"] ?? ""); ?></textarea>

            <label for="medications">Current Medications</label>
            <textarea id="medications" name="medications"><?php echo e($health_info["medications"] ?? ""); ?></textarea>

            <label for="disabilities">Disabilities / PWD Details</label>
            <textarea id="disabilities" name="disabilities"><?php echo e($health_info["disabilities"] ?? ""); ?></textarea>

            <label for="health_notes">Other Health Notes</label>
            <textarea id="health_notes" name="health_notes"><?php echo e($health_info["notes"] ?? ""); ?></textarea>

            <button type="submit" name="save_health_info">Save Health Information</button>
        </form>
    <?php } ?>
</section>

<?php include "../../includes/footer.php"; ?>
