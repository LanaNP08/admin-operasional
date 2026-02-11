<?php
session_start();
// users.php - Manajemen User (Super Admin)
require_once 'app/config/database.php';
require_once 'app/controllers/UserController.php';

$controller = new UserController($conn);
$controller->index();
?>