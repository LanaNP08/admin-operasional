<?php
session_start();
// freelance.php - Halaman Master Data Freelance
require_once 'app/config/database.php';
require_once 'app/controllers/FreelanceController.php';

$controller = new FreelanceController($conn);
$controller->index();
?>