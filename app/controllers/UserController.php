<?php
// app/controllers/UserController.php

class UserController {
    private $conn;
    private $currentUserRole;
    private $currentUserId;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        
        // 1. Cek Login & Ambil Session
        require_once 'app/controllers/LayoutController.php';
        $layout = new LayoutController($this->conn);
        
        $this->currentUserId = $_SESSION['user_id'];
        $this->currentUserRole = $_SESSION['role'];

        // 2. CEK ROLE: WAJIB SUPER ADMIN
        if ($this->currentUserRole !== 'super_admin') {
            echo "<script>alert('Akses Ditolak! Hanya Super Admin yang boleh masuk.'); window.location='index.php';</script>";
            exit;
        }
    }

    public function index() {
        $pesan = "";
        $tipe = "";

        // 3. HANDLE POST REQUEST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // A. TAMBAH USER
            if (isset($_POST['btn_add'])) {
                $username = mysqli_real_escape_string($this->conn, $_POST['username']);
                $password = $_POST['password']; // Password plain sebelum di-hash
                $role     = $_POST['role'];

                // Cek username kembar
                $cek = mysqli_query($this->conn, "SELECT id FROM users WHERE username = '$username'");
                if (mysqli_num_rows($cek) > 0) {
                    $pesan = "Username '$username' sudah terpakai!";
                    $tipe = "error";
                } else {
                    // Hash Password
                    $passHash = password_hash($password, PASSWORD_DEFAULT);
                    
                    $q = "INSERT INTO users (username, password, role) VALUES ('$username', '$passHash', '$role')";
                    if (mysqli_query($this->conn, $q)) {
                        $pesan = "User baru berhasil ditambahkan!";
                        $tipe = "success";
                    } else {
                        $pesan = "Gagal: " . mysqli_error($this->conn);
                        $tipe = "error";
                    }
                }
            }

            // B. HAPUS USER
            if (isset($_POST['btn_delete'])) {
                $id_hapus = $_POST['id_user'];
                
                // Cek hapus diri sendiri
                if ($id_hapus == $this->currentUserId) {
                    $pesan = "Anda tidak bisa menghapus akun sendiri!";
                    $tipe = "error";
                } else {
                    if (mysqli_query($this->conn, "DELETE FROM users WHERE id='$id_hapus'")) {
                        $pesan = "User berhasil dihapus.";
                        $tipe = "success";
                    } else {
                        $pesan = "Gagal menghapus user.";
                        $tipe = "error";
                    }
                }
            }
        }

        // 4. GET DATA USERS
        $query = mysqli_query($this->conn, "SELECT * FROM users ORDER BY created_at DESC");
        $users = [];
        while($row = mysqli_fetch_assoc($query)) {
            $users[] = $row;
        }

        // 5. LOAD VIEW
        // Setup layout variable for header
        require_once 'app/controllers/LayoutController.php';
        $layout = new LayoutController($this->conn);
        
        // Pass variable ke view
        $currentUserId = $this->currentUserId; // Untuk cek di tabel (tombol hapus hidden)

        include 'views/partials/header.php';
        include 'views/pages/users.php';
        include 'views/partials/footer.php';
    }
}
?>