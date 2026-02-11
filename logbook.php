<?php
// logbook.php - Entry Point Logbook

// FIX: WAJIB ADA SESSION START DI SINI
session_start();

require_once 'app/config/database.php';
require_once 'app/controllers/LogbookController.php';

$controller = new LogbookController($conn);
$controller->index();
?>