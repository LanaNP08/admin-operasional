<?php
// public_html/invoice.php

// 1. WAJIB: Start Session paling atas agar AJAX dikenali sebagai user login
session_start();

// 2. Load Konfigurasi & Controller
require_once 'app/config/database.php';
require_once 'app/controllers/InvoiceController.php';

// 3. Inisialisasi Controller
$controller = new InvoiceController($conn);

// 4. Jalankan Logic Utama (Termasuk AJAX & Tampilan)
$controller->handleRequest();
?>