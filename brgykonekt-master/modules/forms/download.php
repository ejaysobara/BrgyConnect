<?php
// Blank forms are now rendered as print-ready templates; see print.php.
include "../../includes/auth_check.php";
$key = urlencode($_GET["form"] ?? "");
redirectTo("modules/forms/print.php?form=" . $key);
