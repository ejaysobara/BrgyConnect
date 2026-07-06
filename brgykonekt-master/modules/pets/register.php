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

if (isset($_POST["register_pet"])) {
    if (!$resident || $resident["status"] !== "Verified") {
        $error = "Your resident profile must be verified before registering a pet.";
    } else {
        $pet_name = mysqli_real_escape_string($conn, trim($_POST["pet_name"]));
        $species = mysqli_real_escape_string($conn, trim($_POST["species"]));
        $breed = mysqli_real_escape_string($conn, trim($_POST["breed"]));
        $age = ($_POST["age"] === "") ? "NULL" : (int)$_POST["age"];
        $color = mysqli_real_escape_string($conn, trim($_POST["color"]));
        $vaccination_status = mysqli_real_escape_string($conn, $_POST["vaccination_status"]);
        $last_vaccination_date = $_POST["last_vaccination_date"] === "" ? "NULL" : "'" . mysqli_real_escape_string($conn, $_POST["last_vaccination_date"]) . "'";
        $next_booster_date = $_POST["next_booster_date"] === "" ? "NULL" : "'" . mysqli_real_escape_string($conn, $_POST["next_booster_date"]) . "'";
        $veterinarian = mysqli_real_escape_string($conn, trim($_POST["veterinarian"]));

        $sql = "INSERT INTO pets
                (resident_id, pet_name, species, breed, age, color, vaccination_status, last_vaccination_date, next_booster_date, veterinarian)
                VALUES
                ('$resident_id', '$pet_name', '$species', '$breed', $age, '$color', '$vaccination_status', $last_vaccination_date, $next_booster_date, '$veterinarian')";

        if (mysqli_query($conn, $sql)) {
            $message = "Pet registered successfully.";
        } else {
            $error = "Unable to register pet: " . mysqli_error($conn);
        }
    }
}

$my_pets = false;
if ($resident_id > 0) {
    $my_pets = mysqli_query($conn, "SELECT * FROM pets WHERE resident_id = '$resident_id' ORDER BY created_at DESC");
}

include "../../includes/header.php";
renderHeader("Pet Registration", "Register pets and track anti-rabies vaccination and booster records.", "pets");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <?php if (!$resident || $resident["status"] !== "Verified") { ?>
        <p class="error">Complete and verify your resident profile before registering pets.</p>
    <?php } else { ?>
        <form method="POST">
            <div class="grid-2">
                <div>
                    <label for="pet_name">Pet Name</label>
                    <input id="pet_name" type="text" name="pet_name" required>
                </div>
                <div>
                    <label for="species">Species</label>
                    <input id="species" type="text" name="species" placeholder="Dog, cat, etc." required>
                </div>
                <div>
                    <label for="breed">Breed</label>
                    <input id="breed" type="text" name="breed">
                </div>
                <div>
                    <label for="age">Age</label>
                    <input id="age" type="number" name="age" min="0">
                </div>
                <div>
                    <label for="color">Color</label>
                    <input id="color" type="text" name="color">
                </div>
                <div>
                    <label for="vaccination_status">Vaccination Status</label>
                    <select id="vaccination_status" name="vaccination_status">
                        <option value="Not Vaccinated">Not Vaccinated</option>
                        <option value="Vaccinated">Vaccinated</option>
                    </select>
                </div>
                <div>
                    <label for="last_vaccination_date">Last Vaccination Date</label>
                    <input id="last_vaccination_date" type="date" name="last_vaccination_date">
                </div>
                <div>
                    <label for="next_booster_date">Next Booster Date</label>
                    <input id="next_booster_date" type="date" name="next_booster_date">
                </div>
            </div>

            <label for="veterinarian">Veterinarian</label>
            <input id="veterinarian" type="text" name="veterinarian">

            <button type="submit" name="register_pet">Register Pet</button>
        </form>
    <?php } ?>
</section>

<section class="panel">
    <h3>My Registered Pets</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Vaccination</th>
                    <th>Next Booster</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_pets && mysqli_num_rows($my_pets) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($my_pets)) { ?>
                        <tr>
                            <td><?php echo e($row["pet_name"]); ?></td>
                            <td><?php echo e($row["species"]); ?></td>
                            <td><?php echo e($row["breed"]); ?></td>
                            <td><span class="badge"><?php echo e($row["vaccination_status"]); ?></span></td>
                            <td><?php echo e($row["next_booster_date"] ?? ""); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No pets registered yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
