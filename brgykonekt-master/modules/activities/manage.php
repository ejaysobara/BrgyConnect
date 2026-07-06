<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([1, 2, 7]);

$message = "";
$error = "";

if (isset($_POST["add_activity"])) {
    $title = mysqli_real_escape_string($conn, trim($_POST["title"]));
    $description = mysqli_real_escape_string($conn, trim($_POST["description"]));
    $activity_date = mysqli_real_escape_string($conn, $_POST["activity_date"]);
    $venue = mysqli_real_escape_string($conn, trim($_POST["venue"]));
    $organizer = mysqli_real_escape_string($conn, trim($_POST["organizer"]));
    $participants = mysqli_real_escape_string($conn, trim($_POST["participants"]));
    $budget = (float)$_POST["budget"];
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    $sql = "INSERT INTO activities
            (title, description, activity_date, venue, organizer, participants, budget, status)
            VALUES
            ('$title', '$description', '$activity_date', '$venue', '$organizer', '$participants', '$budget', '$status')";

    if (mysqli_query($conn, $sql)) {
        addAuditLog($conn, $_SESSION["user_id"], "add_activity", $title);
        $message = "Activity added successfully.";
    } else {
        $error = "Unable to add activity: " . mysqli_error($conn);
    }
}

if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    mysqli_query($conn, "DELETE FROM activities WHERE id = '$id'");
    addAuditLog($conn, $_SESSION["user_id"], "delete_activity", "activity#$id");
    header("Location: manage.php");
    exit();
}

$activities = mysqli_query($conn, "SELECT * FROM activities ORDER BY activity_date DESC");

include "../../includes/header.php";
renderHeader("Barangay Activities", "Create and manage community programs, venues, organizers, participants, budgets, and status.", "activities");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <form method="POST">
        <label for="title">Activity Title</label>
        <input id="title" type="text" name="title" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <div class="grid-2">
            <div>
                <label for="activity_date">Date</label>
                <input id="activity_date" type="date" name="activity_date" required>
            </div>
            <div>
                <label for="venue">Venue</label>
                <input id="venue" type="text" name="venue" required>
            </div>
            <div>
                <label for="organizer">Organizer</label>
                <input id="organizer" type="text" name="organizer" required>
            </div>
            <div>
                <label for="budget">Budget</label>
                <input id="budget" type="number" step="0.01" name="budget" value="0">
            </div>
        </div>

        <label for="participants">Participants</label>
        <textarea id="participants" name="participants" placeholder="Target groups, invited residents, SK members, volunteers"></textarea>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="Upcoming">Upcoming</option>
            <option value="Ongoing">Ongoing</option>
            <option value="Completed">Completed</option>
        </select>

        <button type="submit" name="add_activity">Add Activity</button>
    </form>
</section>

<section class="panel">
    <h3>Activity List</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Organizer</th>
                    <th>Participants</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($activities && mysqli_num_rows($activities) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($activities)) { ?>
                        <tr>
                            <td><?php echo e($row["title"]); ?></td>
                            <td><?php echo e($row["activity_date"]); ?></td>
                            <td><?php echo e($row["venue"]); ?></td>
                            <td><?php echo e($row["organizer"]); ?></td>
                            <td><?php echo e($row["participants"] ?? ""); ?></td>
                            <td>PHP <?php echo e(number_format((float)$row["budget"], 2)); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td><a class="button secondary" href="manage.php?delete=<?php echo e($row["id"]); ?>">Delete</a></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="8">No activities found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
