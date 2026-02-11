<?php
// admin-ops/ajax_notif.php

// PENTING: Session wajib dimulai agar tahu siapa user yang login
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    // 1. Cek & Load Database
    $dbPath = __DIR__ . '/app/config/database.php';
    if (!file_exists($dbPath)) {
        throw new Exception("File Database tidak ditemukan di: $dbPath");
    }
    require_once $dbPath;

    // 2. Cek & Load Logic Notif
    $logicPath = __DIR__ . '/app/actions/api/notif.php';
    if (!file_exists($logicPath)) {
        throw new Exception("File Logic Notif tidak ditemukan di: $logicPath");
    }
    require_once $logicPath;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>