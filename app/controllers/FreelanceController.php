<?php
// app/controllers/FreelanceController.php

class FreelanceController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        // Load Layout untuk cek login
        require_once 'app/controllers/LayoutController.php';
        // session_start() diasumsikan sudah dipanggil di file utama (freelance.php)
    }

    public function index() {
        // === [SECURITY CHECK: TAMU] ===
        $isTamu = (isset($_SESSION['role']) && $_SESSION['role'] === 'tamu');

        // Jika Tamu mencoba POST (Tambah/Edit) atau GET Hapus -> TOLAK
        if (($isTamu && $_SERVER['REQUEST_METHOD'] === 'POST') || ($isTamu && isset($_GET['hapus_id']))) {
            echo "<script>alert('AKSES DITOLAK: Akun Tamu tidak diizinkan mengubah data freelance.'); window.location='freelance.php';</script>";
            exit;
        }
        // ==============================

        $notifPesan = "";
        $notifTipe = "";

        // 1. HANDLE POST (CREATE & UPDATE)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // A. TAMBAH DATA
            if (isset($_POST['btn_tambah'])) {
                $nama = mysqli_real_escape_string($this->conn, $_POST['nama']);
                $nik = mysqli_real_escape_string($this->conn, $_POST['nik']);
                $posisi = mysqli_real_escape_string($this->conn, $_POST['posisi']);
                $tgl_gabung = $_POST['tgl_gabung'];
                $bank = mysqli_real_escape_string($this->conn, $_POST['bank']);
                $norek = mysqli_real_escape_string($this->conn, $_POST['norek']);
                $an_rek = mysqli_real_escape_string($this->conn, $_POST['an_rek']);

                $q = "INSERT INTO data_helper (nama_lengkap, nik, cabang_posisi, tgl_bergabung, nama_bank, no_rekening, nama_rekening) 
                      VALUES ('$nama', '$nik', '$posisi', '$tgl_gabung', '$bank', '$norek', '$an_rek')";
                
                if (mysqli_query($this->conn, $q)) {
                    $notifPesan = "Data Freelance berhasil ditambahkan!";
                    $notifTipe = "success";
                } else {
                    $notifPesan = "Gagal menambah data: " . mysqli_error($this->conn);
                    $notifTipe = "error";
                }
            }

            // B. UPDATE DATA
            if (isset($_POST['btn_update'])) {
                $id = $_POST['id_edit'];
                $nama = mysqli_real_escape_string($this->conn, $_POST['nama_edit']);
                $nik = mysqli_real_escape_string($this->conn, $_POST['nik_edit']);
                $posisi = mysqli_real_escape_string($this->conn, $_POST['posisi_edit']);
                $tgl_gabung = $_POST['tgl_gabung_edit'];
                $bank = mysqli_real_escape_string($this->conn, $_POST['bank_edit']);
                $norek = mysqli_real_escape_string($this->conn, $_POST['norek_edit']);
                $an_rek = mysqli_real_escape_string($this->conn, $_POST['an_rek_edit']);

                $q = "UPDATE data_helper SET 
                        nama_lengkap='$nama', nik='$nik', cabang_posisi='$posisi', 
                        tgl_bergabung='$tgl_gabung', nama_bank='$bank', 
                        no_rekening='$norek', nama_rekening='$an_rek' 
                      WHERE id='$id'";
                
                if (mysqli_query($this->conn, $q)) {
                    $notifPesan = "Data Freelance berhasil diperbarui!";
                    $notifTipe = "success";
                } else {
                    $notifPesan = "Gagal update data: " . mysqli_error($this->conn);
                    $notifTipe = "error";
                }
            }
        }

        // 2. HANDLE DELETE (GET)
        if (isset($_GET['hapus_id'])) {
            $id = $_GET['hapus_id'];
            if (mysqli_query($this->conn, "DELETE FROM data_helper WHERE id='$id'")) {
                // Redirect agar URL bersih
                echo "<script>alert('Data berhasil dihapus'); window.location.href='freelance.php';</script>";
                exit;
            } else {
                $notifPesan = "Gagal menghapus: " . mysqli_error($this->conn);
                $notifTipe = "error";
            }
        }

        // 3. GET DATA (READ)
        $result = mysqli_query($this->conn, "SELECT * FROM data_helper ORDER BY nama_lengkap ASC");
        $dataFreelance = [];
        while($row = mysqli_fetch_assoc($result)) {
            $dataFreelance[] = $row;
        }

        // 4. LOAD VIEW
        $layout = new LayoutController($this->conn);

        include 'views/partials/header.php';
        include 'views/pages/freelance.php';
        include 'views/partials/footer.php';
    }
}
?>