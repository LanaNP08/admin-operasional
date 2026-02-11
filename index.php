<?php

ini_set('session.gc_maxlifetime', 36000); // 3600 detik = 1 Jam
session_set_cookie_params(36000);
// index.php - Entry Point
require_once 'app/config/database.php';
require_once 'app/controllers/DashboardController.php';


$controller = new DashboardController($conn);
$controller->index();
?>