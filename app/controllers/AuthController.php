<?php
// app/controllers/AuthController.php

class AuthController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function handleLoginRequest() {
        // 1. Pastikan Session Nyala
        if (session_status() == PHP_SESSION_NONE) { session_start(); }

        // 2. Generate Tiket Rahasia (CSRF Token) jika belum ada
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Cek jika sudah login, lempar ke dashboard
        if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
            header("Location: index.php");
            exit;
        }

        $error = "";

        // Jika ada POST login
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            
            // [SECURITY 1] Cek Tiket Rahasia (CSRF)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = "Sesi kadaluarsa atau tidak valid. Silahkan refresh halaman.";
            } else {
                $username = mysqli_real_escape_string($this->conn, $_POST['username']);
                $password = $_POST['password'];
    
                // Query User
                $q = mysqli_query($this->conn, "SELECT * FROM users WHERE username = '$username'");
                
                if ($q && mysqli_num_rows($q) === 1) {
                    $user = mysqli_fetch_assoc($q);
                    
                    // [SECURITY 2] Validasi Password (STRICT MODE)
                    // Hapus logika "OR" yang lama. Hanya terima hash.
                    if (password_verify($password, $user['password'])) {
                        
                        // Regenerate ID session biar session lama gak bisa dibajak
                        session_regenerate_id(true);

                        // SET SESSION
                        $_SESSION['is_logged_in'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role']; 
    
                        header("Location: index.php");
                        exit;
                    } else {
                        $error = "Password salah!";
                    }
                } else {
                    $error = "Username tidak ditemukan!";
                }
            }
        }

        // Tampilkan View Login
        require 'views/pages/login.php';
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Hapus semua data session
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>