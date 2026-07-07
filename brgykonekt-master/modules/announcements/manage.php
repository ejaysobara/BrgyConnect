<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([1, 2, 7]);

$message = "";
$error = "";

if (isset($_POST["post_announcement"])) {
    $title = mysqli_real_escape_string($conn, trim($_POST["title"]));
    $category = mysqli_real_escape_string($conn, $_POST["category"]);
    $content = mysqli_real_escape_string($conn, trim($_POST["content"]));
    $posted_by = (int)$_SESSION["user_id"];

    $sql = "INSERT INTO announcements (title, category, content, posted_by)
            VALUES ('$title', '$category', '$content', '$posted_by')";

    if (mysqli_query($conn, $sql)) {
        addAuditLog($conn, $_SESSION["user_id"], "post_announcement", $title);
        $message = "Announcement posted successfully.";
    } else {
        $error = "Unable to post announcement: " . mysqli_error($conn);
    }
}

if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    mysqli_query($conn, "DELETE FROM announcements WHERE id = '$id'");
    addAuditLog($conn, $_SESSION["user_id"], "delete_announcement", "announcement#$id");
    header("Location: manage.php");
    exit();
}

$announcements = mysqli_query($conn, "SELECT announcements.*, users.full_name
                                      FROM announcements
                                      LEFT JOIN users ON announcements.posted_by = users.id
                                      ORDER BY announcements.created_at DESC");

include "../../includes/header.php";
renderHeader("Announcements", "Create official barangay notices for emergencies, health, meetings, sports, projects, and service interruptions.", "announcements");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <form method="POST">
        <label for="title">Title</label>
        <input id="title" type="text" name="title" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="Emergency">Emergency</option>
            <option value="Typhoon">Typhoon</option>
            <option value="Health">Health</option>
            <option value="Meeting">Meeting</option>
            <option value="Sports">Sports</option>
            <option value="Fiesta">Fiesta</option>
            <option value="Projects">Projects</option>
            <option value="Road Closure">Road Closure</option>
            <option value="Water Interruption">Water Interruption</option>
        </select>

        <label for="content">Content</label>
        <textarea id="content" name="content" required></textarea>

        <button type="submit" name="post_announcement">Post Announcement</button>
    </form>
</section>

<section class="panel">
    <h3>Posted Announcements</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Posted By</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($announcements && mysqli_num_rows($announcements) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($announcements)) { ?>
                        <tr>
                            <td><?php echo e($row["title"]); ?></td>
                            <td><span class="badge"><?php echo e($row["category"]); ?></span></td>
                            <td><?php echo e($row["full_name"] ?? "System"); ?></td>
                            <td><?php echo e($row["created_at"]); ?></td>
                            <td><a class="button secondary" href="manage.php?delete=<?php echo e($row["id"]); ?>">Delete</a></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No announcements posted yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
