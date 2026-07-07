<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireLogin();

$activities = mysqli_query($conn, "SELECT * FROM activities ORDER BY activity_date DESC");

include "../../includes/header.php";
renderHeader("Barangay Activities", "View upcoming, ongoing, and completed barangay programs.", "activities");
?>

<?php if ($activities && mysqli_num_rows($activities) > 0) { ?>
    <?php while ($row = mysqli_fetch_assoc($activities)) { ?>
        <article class="panel">
            <span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span>
            <h3><?php echo e($row["title"]); ?></h3>
            <p><strong>Date:</strong> <?php echo e($row["activity_date"]); ?></p>
            <p><strong>Venue:</strong> <?php echo e($row["venue"]); ?></p>
            <p><strong>Organizer:</strong> <?php echo e($row["organizer"]); ?></p>
            <?php if (!empty($row["participants"])) { ?>
                <p><strong>Participants:</strong> <?php echo e($row["participants"]); ?></p>
            <?php } ?>
            <p><?php echo nl2br(e($row["description"])); ?></p>
        </article>
    <?php } ?>
<?php } else { ?>
    <section class="panel">
        <p class="empty-state">No activities posted yet.</p>
    </section>
<?php } ?>

<?php include "../../includes/footer.php"; ?>
