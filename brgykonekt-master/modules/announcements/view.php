<?php
// Announcements are now part of the merged Community Feed.
include "../../includes/auth_check.php";
redirectTo("modules/community/feed.php?filter=announcements");
