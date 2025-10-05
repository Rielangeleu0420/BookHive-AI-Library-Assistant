<?php
require_once 'functions.php';

// Log the logout action
if (isLoggedIn()) {
    logAudit($_SESSION['user_id'], 'logout', $_SESSION['user_id'], 'User logged out.');
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: login.php");
exit;
?>