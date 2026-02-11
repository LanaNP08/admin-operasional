<?php

session_start();
require_once 'app/config/database.php';
require_once 'app/controllers/ExportController.php';

$controller = new ExportController($conn);
$controller->monitoring();
?>