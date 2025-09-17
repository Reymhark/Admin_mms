<?php
// logout.php
session_start();

// Unset all of the session variables
$_SESSION = [];

// Destroy the session.
session_destroy();

// Redirect to login page (change 'login.php' if your login page is named differently)
header("Location: login.php");
exit();
?>