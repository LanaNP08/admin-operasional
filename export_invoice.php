<?php
// public_html/export_invoice.php

// 1. Start Session
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 2. SECURITY CHECK (SATPAM)
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 3. Load Dependencies
require_once 'app/config/database.php';
require_once 'app/controllers/ExportController.php';

// 4. Eksekusi Export
$controller = new ExportController($conn);
$controller->invoice();
?>