<?php
// admin-ops/login.php
session_start();
// 1. Load Konfigurasi Database
require_once 'app/config/database.php';

// 2. Load Auth Controller
require_once 'app/controllers/AuthController.php';

// 3. Jalankan Logic Login
$auth = new AuthController($conn);
$auth->handleLoginRequest();
?>