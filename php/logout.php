<?php
session_start();
session_destroy();
header("Location: ../index.php"); // Adjusted the path to the root index.php
exit();
?>
