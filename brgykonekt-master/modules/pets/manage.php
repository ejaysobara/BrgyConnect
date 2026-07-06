<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([1, 2, 5]);

$message = "";
$error = "";

if (isset($_POST["update_vaccination"])) {
    $pet_id = (int)$_POST["pet_id"];
    $vaccine_name = mysqli_real_escape_string($conn, trim($_POST["vaccine_name"]));
    $vaccination_date = mysqli_real_escape_string($conn, $_POST["vaccination_date"]);
    $next_due_date = mysqli_real_escape_string($conn, $_POST["next_due_date"]);
    $veterinarian = mysqli_real_escape_string($conn, trim($_POST["veterinarian"]));
    $remarks = mysqli_real_escape_string($conn, trim($_POST["remarks"]));

    $inserted = mysqli_query($conn, "INSERT INTO vaccinations
                         (pet_id, vaccine_name, vaccination_date, next_due_date, veterinarian, remarks)
                         VALUES
                         ('$pet_id', '$vaccine_name', '$vaccination_date', '$next_due_date', '$veterinarian', '$remarks')");

    if ($inserted) {
        mysqli_query($conn, "UPDATE pets SET
                             vaccination_status = 'Vaccinated',
                             last_vaccination_date = '$vaccination_date',
                             next_booster_date = '$next_due_date',
                             veterinarian = '$veterinarian'
                             WHERE id = '$pet_id'");
        addAuditLog($conn, $_SESSION["user_id"], "update_pet_vaccination", "pet#$pet_id", $vaccine_name);
        $message = "Vaccination record updated successfully.";
    } else {
        $error = "Unable to update vaccination record: " . mysqli_error($conn);
    }
}

$pets = mysqli_query($conn, "SELECT pets.*, residents.first_name, residents.middle_name, residents.last_name
                             FROM pets
                             INNER JOIN residents ON pets.resident_id = residents.id
                             ORDER BY pets.created_at DESC");

include "../../includes/header.php";
renderHeader("Pet Vaccination Records", "Manage pet registrations, anti-rabies vaccination, booster schedules, and health records.", "pets");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>Pet</th>
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Vaccination</th>
                    <th>Next Booster</th>
                    <th>Update Vaccination</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pets && mysqli_num_rows($pets) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($pets)) { ?>
                        <tr>
                            <td><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></td>
                            <td><?php echo e($row["pet_name"]); ?></td>
                            <td><?php echo e($row["species"]); ?></td>
                            <td><?php echo e($row["breed"]); ?></td>
                            <td><span class="badge"><?php echo e($row["vaccination_status"]); ?></span></td>
                            <td><?php echo e($row["next_booster_date"] ?? ""); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="pet_id" value="<?php echo e($row["id"]); ?>">

                                    <label>Vaccine</label>
                                    <input type="text" name="vaccine_name" placeholder="Vaccine" required>
                                    <label>Vaccination Date</label>
                                    <input type="date" name="vaccination_date" required>
                                    <label>Next Due Date</label>
                                    <input type="date" name="next_due_date" required>
                                    <label>Veterinarian</label>
                                    <input type="text" name="veterinarian" placeholder="Veterinarian">
                                    <label>Remarks</label>
                                    <textarea name="remarks" placeholder="Remarks"></textarea>

                                    <button type="submit" name="update_vaccination">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="7">No pet records found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
