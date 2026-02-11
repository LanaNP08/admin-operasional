<?php
session_start();
// receive.php - Halaman Penerimaan (Public/Kurir)
require_once 'app/config/database.php';
require_once 'app/controllers/DeliveryController.php';

$code = $_GET['code'] ?? '';
$controller = new DeliveryController($conn);
$controller->receive($code);
?>