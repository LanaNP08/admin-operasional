<?php
// app/config/database.php

// 1. SETTING WAKTU INDONESIA (WIB)
// Ini mengunci jam server agar selalu ikut waktu Jakarta, apapun settingan hostingnya.
date_default_timezone_set('Asia/Jakarta');

// 2. DETEKSI LINGKUNGAN (Localhost vs Live)
// Kita cek apakah domain yang diakses mengandung kata "localhost" atau IP "127.0.0.1"
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = (strpos($host, 'localhost') !== false) || (strpos($host, '127.0.0.1') !== false);

if ($isLocal) {
    // === SETTINGAN LAPTOP (LOCALHOST) ===
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');         // User default XAMPP
    define('DB_PASS', '');             // Password default XAMPP (biasanya kosong)
    define('DB_NAME', 'db_admin_ops'); // Pastikan nama database di Laptop sesuai ini
    
    // Auto-detect URL Localhost (Biar fleksibel kalau ganti nama folder)
    // Hasilnya misal: http://localhost/admin_ops
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    // Mengambil path folder project secara dinamis
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    define('BASE_URL', $protocol . "://" . $host . $path);
    
    // Mode Debug: Nyalakan Error biar ketahuan kalau ada salah koding
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

} else {
    // === SETTINGAN SERVER (LIVE / HOSTINGER) ===
    define('DB_HOST', 'localhost'); 
    define('DB_USER', 'u164923144_admin_ops'); 
    define('DB_PASS', 'Jak4rta2025!!');      
    define('DB_NAME', 'u164923144_admin_ops'); 
    
    // URL Domain Resmi (Wajib HTTPS)
    define('BASE_URL', 'https://anindyamarine.site'); 
    
    // Mode Aman: Matikan Error biar data rahasia gak muncul di layar kalau error
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 3. EKSEKUSI KONEKSI
try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (Exception $e) {
    $conn = false;
}

// 4. PENANGANAN JIKA KONEKSI GAGAL
if (!$conn) {
    if ($isLocal) {
        // Kalau di laptop, kasih tau errornya apa biar bisa dibenerin
        die("<h3>Koneksi Database Gagal (Localhost)</h3><p>Error: " . mysqli_connect_error() . "</p><p>Cek user, password, atau nama database di file app/config/database.php</p>");
    } else {
        // Kalau di web live, kasih pesan maintenance yang sopan
        header('HTTP/1.1 503 Service Unavailable');
        die("
            <div style='text-align:center; padding:50px; font-family:sans-serif;'>
                <h1>System Maintenance</h1>
                <p>Mohon maaf, sistem sedang dalam pemeliharaan rutin.</p>
                <p>Silahkan coba akses kembali dalam beberapa saat.</p>
            </div>
        ");
    }
}
?>