<?php
session_start();
session_destroy();
header("Location: admin_feedback.php");
exit;
