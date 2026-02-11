<?php
session_start();
// settlemen.php - Entry Point
require_once 'app/config/database.php';
require_once 'app/controllers/SettlementController.php';

$controller = new SettlementController($conn);
$controller->index();
?>