<?php
session_start();

// Security Gate: Cek Login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

// Config Output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    // Load Database
    $dbPath = __DIR__ . '/app/config/database.php';
    if (!file_exists($dbPath)) throw new Exception("Database file not found.");
    require_once $dbPath;

    if (!isset($conn)) throw new Exception("Database connection failed.");

    // Load Logic
    $logicPath = __DIR__ . '/app/actions/api/dashboard.php';
    if (!file_exists($logicPath)) throw new Exception("Logic file not found.");
    require_once $logicPath;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>