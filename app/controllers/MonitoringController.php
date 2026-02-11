<?php
// app/controllers/MonitoringController.php

class MonitoringController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        // Load Layout untuk cek Auth
        require_once 'app/controllers/LayoutController.php';
        // session_start() diasumsikan sudah dipanggil di file utama (monitoring.php)
    }

    private function hitungSisaHari($tglDb) {
        if (!$tglDb || $tglDb == '0000-00-00') return 9999; 
        $dateNext = new DateTime($tglDb);
        $today = new DateTime();
        $today->setTime(0,0); $dateNext->setTime(0,0);
        $diff = $today->diff($dateNext);
        if ($dateNext < $today) return -$diff->days; 
        return $diff->days; 
    }

    public function index() {
        // === [SECURITY CHECK: TAMU] ===
        $isTamu = (isset($_SESSION['role']) && $_SESSION['role'] === 'tamu');

        // Jika Tamu mencoba mengirim data (POST) -> TOLAK
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isTamu) {
            echo "<script>alert('AKSES DITOLAK: Akun Tamu hanya diperbolehkan melihat data.'); window.location='monitoring.php';</script>";
            exit;
        }
        // ==============================

        $notifPesan = "";
        $notifTipe = "";

        // 1. HANDLE POST REQUEST (CRUD)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // TAMBAH
            if (isset($_POST['btn_tambah'])) {
                $kode_sn   = mysqli_real_escape_string($this->conn, $_POST['kode_sn']);
                $nama_alat = mysqli_real_escape_string($this->conn, $_POST['nama_alat']);
                $merk      = mysqli_real_escape_string($this->conn, $_POST['merk_type']);
                $tgl_last  = $_POST['tgl_terakhir'];
                $tgl_next  = $_POST['tgl_kalibrasi'];
                $kondisi   = $_POST['kondisi']; 
                $lokasi    = mysqli_real_escape_string($this->conn, $_POST['lokasi']);

                $cek = mysqli_query($this->conn, "SELECT kode_sn FROM alat_kalibrasi WHERE kode_sn = '$kode_sn'");
                if (mysqli_num_rows($cek) > 0) {
                    $notifPesan = "Gagal! Kode $kode_sn sudah ada.";
                    $notifTipe = "error";
                } else {
                    $q = "INSERT INTO alat_kalibrasi (kode_sn, nama_alat, merk_type, tgl_terakhir, tgl_kalibrasi, status_alat, lokasi) 
                          VALUES ('$kode_sn', '$nama_alat', '$merk', '$tgl_last', '$tgl_next', '$kondisi', '$lokasi')";
                    if (mysqli_query($this->conn, $q)) {
                        $notifPesan = "Data berhasil ditambahkan!";
                        $notifTipe = "success";
                    } else {
                        $notifPesan = "Error: " . mysqli_error($this->conn);
                        $notifTipe = "error";
                    }
                }
            }

            // UPDATE
            if (isset($_POST['btn_simpan_edit'])) {
                $id_alat   = $_POST['id_edit'];
                $kode_sn   = mysqli_real_escape_string($this->conn, $_POST['kode_edit']);
                $nama_alat = mysqli_real_escape_string($this->conn, $_POST['nama_edit']);
                $merk      = mysqli_real_escape_string($this->conn, $_POST['merk_edit']);
                $tgl_last  = $_POST['tgl_last_edit'];
                $tgl_next  = $_POST['tgl_next_edit'];
                $kondisi   = $_POST['kondisi_edit'];
                $lokasi    = mysqli_real_escape_string($this->conn, $_POST['lokasi_edit']);

                $q = "UPDATE alat_kalibrasi SET kode_sn = '$kode_sn', nama_alat = '$nama_alat', merk_type = '$merk', 
                      tgl_terakhir = '$tgl_last', tgl_kalibrasi = '$tgl_next', status_alat = '$kondisi', lokasi = '$lokasi' 
                      WHERE id = '$id_alat'";

                if (mysqli_query($this->conn, $q)) {
                    $notifPesan = "Data berhasil diupdate!";
                    $notifTipe = "success";
                } else {
                    $notifPesan = "Gagal update: " . mysqli_error($this->conn);
                    $notifTipe = "error";
                }
            }

            // HAPUS
            if (isset($_POST['btn_hapus'])) {
                $id_hapus = $_POST['id_hapus'];
                if (mysqli_query($this->conn, "DELETE FROM alat_kalibrasi WHERE id = '$id_hapus'")) {
                    $notifPesan = "Data berhasil dihapus.";
                    $notifTipe = "success";
                }
            }
        }

        // 2. GET DATA & FILTER
        $filterAktif = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $keyword     = isset($_GET['cari']) ? $_GET['cari'] : '';
        
        // Base Query
        $sqlBase = "SELECT * FROM alat_kalibrasi WHERE 1=1";
        if (!empty($keyword)) {
            $safe_key = mysqli_real_escape_string($this->conn, $keyword);
            $sqlBase .= " AND (kode_sn LIKE '%$safe_key%' OR nama_alat LIKE '%$safe_key%' OR merk_type LIKE '%$safe_key%' OR lokasi LIKE '%$safe_key%')";
        }
        $sqlBase .= " ORDER BY tgl_kalibrasi ASC";
        $query = mysqli_query($this->conn, $sqlBase);

        // Process Data (Assign Status & Badge)
        $dataAlat = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $sisaHari = $this->hitungSisaHari($row['tgl_kalibrasi']);
            
            // Tentukan Status Logic
            if ($sisaHari < 0) {
                $status_cal = 'LEWAT JADWAL'; 
                $badge_cal = 'bg-red-500 text-white'; 
                $kategori = 'overdue';
            } elseif ($sisaHari <= 60) { 
                $status_cal = 'SEGERA (' . $sisaHari . ' Hari)'; 
                $badge_cal = 'bg-yellow-500 text-white'; 
                $kategori = 'warning';
            } else {
                $status_cal = 'VALID'; 
                $badge_cal = 'bg-emerald-500 text-white'; 
                $kategori = 'good';
            }

            $row['status_cal_text'] = $status_cal; 
            $row['badge_cal'] = $badge_cal; 
            $row['kategori_filter'] = $kategori;

            $dataAlat[] = $row;
        }

        // 3. HITUNG STATISTIK (GLOBAL)
        $qStat = mysqli_query($this->conn, "SELECT tgl_kalibrasi FROM alat_kalibrasi");
        $stats = ['total' => 0, 'overdue' => 0, 'warning' => 0, 'good' => 0];
        
        while($r = mysqli_fetch_assoc($qStat)){
            $stats['total']++;
            $s = $this->hitungSisaHari($r['tgl_kalibrasi']);
            if($s < 0) $stats['overdue']++; 
            elseif($s <= 60) $stats['warning']++; 
            else $stats['good']++;
        }

        // 4. LOAD VIEW
        $layout = new LayoutController($this->conn);
        
        include 'views/partials/header.php';
        include 'views/pages/monitoring.php';
        include 'views/partials/footer.php';
    }
}
?>