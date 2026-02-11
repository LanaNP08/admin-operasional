<?php
session_start();
// monitoring.php - Entry Point Monitoring
require_once 'app/config/database.php';
require_once 'app/controllers/MonitoringController.php';

$controller = new MonitoringController($conn);
$controller->index();
?>