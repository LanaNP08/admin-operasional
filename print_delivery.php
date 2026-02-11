<?php
session_start();
// print_delivery.php - Cetak Surat Jalan
require_once 'app/config/database.php';
require_once 'app/controllers/DeliveryController.php';

if (empty($_GET['code'])) { die("Kode batch tidak ditemukan."); }

$controller = new DeliveryController($conn);
$controller->print($_GET['code']);
?>