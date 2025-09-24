<?php
include 'db.php';
startSession();
session_destroy();
echo "<script>window.location.href = 'index.php';</script>";
?>
