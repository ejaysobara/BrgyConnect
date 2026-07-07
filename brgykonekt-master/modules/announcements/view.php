<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireLogin();

$announcements = mysqli_query($conn, "SELECT announcements.*, users.full_name
                                      FROM announcements
                                      LEFT JOIN users ON announcements.posted_by = users.id
                                      ORDER BY announcements.created_at DESC");

include "../../includes/header.php";
renderHeader("Barangay Announcements", "Read official notices and community updates from your barangay.", "announcements");
?>

<?php if ($announcements && mysqli_num_rows($announcements) > 0) { ?>
    <?php while ($row = mysqli_fetch_assoc($announcements)) { ?>
        <article class="panel">
            <span class="badge"><?php echo e($row["category"]); ?></span>
            <h3><?php echo e($row["title"]); ?></h3>
            <p><strong>Posted by:</strong> <?php echo e($row["full_name"] ?? "Barangay Office"); ?> | <?php echo e($row["created_at"]); ?></p>
            <p><?php echo nl2br(e($row["content"])); ?></p>
        </article>
    <?php } ?>
<?php } else { ?>
    <section class="panel">
        <p class="empty-state">No announcements posted yet.</p>
    </section>
<?php } ?>

<?php include "../../includes/footer.php"; ?>
