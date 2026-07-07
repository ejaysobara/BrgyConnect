<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireLogin();

$message = "";
$error = "";
$can_post = isStaff(); // Tanod and above can post to the feed

// --- Staff actions: post / delete ---
if ($can_post && isset($_POST["post_announcement"])) {
    $title = mysqli_real_escape_string($conn, trim($_POST["title"]));
    $category = mysqli_real_escape_string($conn, $_POST["category"]);
    $content = mysqli_real_escape_string($conn, trim($_POST["content"]));
    $posted_by = (int)$_SESSION["user_id"];

    if (mysqli_query($conn, "INSERT INTO announcements (title, category, content, posted_by)
                             VALUES ('$title', '$category', '$content', '$posted_by')")) {
        addAuditLog($conn, $_SESSION["user_id"], "post_announcement", $title);
        $message = "Announcement posted to the community feed.";
    } else {
        $error = "Unable to post announcement: " . mysqli_error($conn);
    }
}

if ($can_post && isset($_POST["post_activity"])) {
    $title = mysqli_real_escape_string($conn, trim($_POST["title"]));
    $description = mysqli_real_escape_string($conn, trim($_POST["description"]));
    $activity_date = mysqli_real_escape_string($conn, $_POST["activity_date"]);
    $venue = mysqli_real_escape_string($conn, trim($_POST["venue"]));
    $organizer = mysqli_real_escape_string($conn, trim($_POST["organizer"]));
    $participants = mysqli_real_escape_string($conn, trim($_POST["participants"]));
    $budget = (float)$_POST["budget"];
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    if (mysqli_query($conn, "INSERT INTO activities
                             (title, description, activity_date, venue, organizer, participants, budget, status)
                             VALUES ('$title', '$description', '$activity_date', '$venue', '$organizer', '$participants', '$budget', '$status')")) {
        addAuditLog($conn, $_SESSION["user_id"], "add_activity", $title);
        $message = "Activity posted to the community feed.";
    } else {
        $error = "Unable to post activity: " . mysqli_error($conn);
    }
}

if ($can_post && isset($_GET["delete_announcement"])) {
    $id = (int)$_GET["delete_announcement"];
    mysqli_query($conn, "DELETE FROM announcements WHERE id = '$id'");
    addAuditLog($conn, $_SESSION["user_id"], "delete_announcement", "announcement#$id");
    redirectTo("modules/community/feed.php");
}

if ($can_post && isset($_GET["delete_activity"])) {
    $id = (int)$_GET["delete_activity"];
    mysqli_query($conn, "DELETE FROM activities WHERE id = '$id'");
    addAuditLog($conn, $_SESSION["user_id"], "delete_activity", "activity#$id");
    redirectTo("modules/community/feed.php");
}

// --- Build the merged feed ---
$filter = $_GET["filter"] ?? "all";
$feed_items = [];

if ($filter === "all" || $filter === "announcements") {
    $announcements = mysqli_query($conn, "SELECT announcements.*, users.full_name
                                          FROM announcements
                                          LEFT JOIN users ON announcements.posted_by = users.id
                                          ORDER BY announcements.created_at DESC");
    if ($announcements) {
        while ($row = mysqli_fetch_assoc($announcements)) {
            $feed_items[] = ["type" => "announcement", "sort_date" => $row["created_at"], "data" => $row];
        }
    }
}

if ($filter === "all" || $filter === "events") {
    $activities = mysqli_query($conn, "SELECT * FROM activities ORDER BY activity_date DESC");
    if ($activities) {
        while ($row = mysqli_fetch_assoc($activities)) {
            $feed_items[] = ["type" => "event", "sort_date" => $row["activity_date"], "data" => $row];
        }
    }
}

usort($feed_items, function ($a, $b) {
    return strcmp($b["sort_date"], $a["sort_date"]);
});

include "../../includes/header.php";
renderHeader("Community Feed", "Official announcements and upcoming barangay activities and events, all in one place.", "community");
?>

<?php if ($can_post) { ?>
<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <h3>Create a Post</h3>
    <div class="quick-actions">
        <button type="button" id="tab-announcement" class="button" onclick="switchComposer('announcement')">Announcement</button>
        <button type="button" id="tab-activity" class="button secondary" onclick="switchComposer('activity')">Activity / Event</button>
    </div>

    <form method="POST" id="composer-announcement">
        <label for="a_title">Title</label>
        <input id="a_title" type="text" name="title" required>

        <label for="a_category">Category</label>
        <select id="a_category" name="category" required>
            <?php foreach (["Emergency", "Typhoon", "Health", "Meeting", "Sports", "Fiesta", "Projects", "Road Closure", "Water Interruption"] as $cat) { ?>
                <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
            <?php } ?>
        </select>

        <label for="a_content">Content</label>
        <textarea id="a_content" name="content" required></textarea>

        <button type="submit" name="post_announcement">Post Announcement</button>
    </form>

    <form method="POST" id="composer-activity" style="display:none;">
        <label for="t_title">Activity Title</label>
        <input id="t_title" type="text" name="title" required>

        <label for="t_description">Description</label>
        <textarea id="t_description" name="description" required></textarea>

        <div class="grid-2">
            <div>
                <label for="t_date">Date</label>
                <input id="t_date" type="date" name="activity_date" required>
            </div>
            <div>
                <label for="t_venue">Venue</label>
                <input id="t_venue" type="text" name="venue" required>
            </div>
            <div>
                <label for="t_organizer">Organizer</label>
                <input id="t_organizer" type="text" name="organizer" required>
            </div>
            <div>
                <label for="t_budget">Budget</label>
                <input id="t_budget" type="number" step="0.01" name="budget" value="0">
            </div>
        </div>

        <label for="t_participants">Participants</label>
        <textarea id="t_participants" name="participants" placeholder="Target groups, invited residents, volunteers"></textarea>

        <label for="t_status">Status</label>
        <select id="t_status" name="status">
            <option value="Upcoming">Upcoming</option>
            <option value="Ongoing">Ongoing</option>
            <option value="Completed">Completed</option>
        </select>

        <button type="submit" name="post_activity">Post Activity</button>
    </form>

    <script>
        function switchComposer(kind) {
            var isAnnouncement = kind === "announcement";
            document.getElementById("composer-announcement").style.display = isAnnouncement ? "" : "none";
            document.getElementById("composer-activity").style.display = isAnnouncement ? "none" : "";
            document.getElementById("tab-announcement").className = isAnnouncement ? "button" : "button secondary";
            document.getElementById("tab-activity").className = isAnnouncement ? "button secondary" : "button";
        }
    </script>
</section>
<?php } ?>

<section class="panel">
    <div class="quick-actions">
        <a class="button <?php echo $filter === "all" ? "" : "secondary"; ?>" href="?filter=all">All</a>
        <a class="button <?php echo $filter === "announcements" ? "" : "secondary"; ?>" href="?filter=announcements">Announcements</a>
        <a class="button <?php echo $filter === "events" ? "" : "secondary"; ?>" href="?filter=events">Activities / Events</a>
    </div>
</section>

<?php if (count($feed_items) > 0) { ?>
    <?php foreach ($feed_items as $item) { ?>
        <?php $row = $item["data"]; ?>
        <?php if ($item["type"] === "announcement") { ?>
            <article class="panel">
                <span class="badge">Announcement<?php echo !empty($row["category"]) ? " · " . e($row["category"]) : ""; ?></span>
                <h3><?php echo e($row["title"]); ?></h3>
                <p><strong>Posted by:</strong> <?php echo e($row["full_name"] ?? "Barangay Office"); ?> | <?php echo e($row["created_at"]); ?></p>
                <p><?php echo nl2br(e($row["content"])); ?></p>
                <?php if ($can_post) { ?>
                    <a class="button secondary" href="?delete_announcement=<?php echo e($row["id"]); ?>" onclick="return confirm('Delete this announcement?');">Delete</a>
                <?php } ?>
            </article>
        <?php } else { ?>
            <article class="panel">
                <span class="badge">Activity / Event</span>
                <span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span>
                <h3><?php echo e($row["title"]); ?></h3>
                <p><strong>Date:</strong> <?php echo e($row["activity_date"]); ?></p>
                <p><strong>Venue:</strong> <?php echo e($row["venue"]); ?></p>
                <p><strong>Organizer:</strong> <?php echo e($row["organizer"]); ?></p>
                <?php if (!empty($row["participants"])) { ?>
                    <p><strong>Participants:</strong> <?php echo e($row["participants"]); ?></p>
                <?php } ?>
                <?php if ($can_post && !empty($row["budget"]) && (float)$row["budget"] > 0) { ?>
                    <p><strong>Budget:</strong> PHP <?php echo e(number_format((float)$row["budget"], 2)); ?></p>
                <?php } ?>
                <p><?php echo nl2br(e($row["description"])); ?></p>
                <?php if ($can_post) { ?>
                    <a class="button secondary" href="?delete_activity=<?php echo e($row["id"]); ?>" onclick="return confirm('Delete this activity?');">Delete</a>
                <?php } ?>
            </article>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <section class="panel">
        <p class="empty-state">Nothing on the community feed yet.</p>
    </section>
<?php } ?>

<?php include "../../includes/footer.php"; ?>
