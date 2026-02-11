<?php
session_start();
// logout.php - Entry Point Logout
require_once 'app/config/database.php';
require_once 'app/controllers/AuthController.php';

$auth = new AuthController($conn);
$auth->logout();
?>