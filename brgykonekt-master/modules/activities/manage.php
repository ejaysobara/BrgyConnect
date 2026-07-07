<?php
// Activity management is now consolidated into the Community Feed composer.
include "../../includes/auth_check.php";
redirectTo("modules/community/feed.php?filter=events");
