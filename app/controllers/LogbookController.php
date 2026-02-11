<?php
// app/controllers/LogbookController.php

class LogbookController {
    private $conn;
    private $userId;
    private $userRole;
    private $userName;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        require_once 'app/controllers/LayoutController.php';
        $layout = new LayoutController($this->conn);
        $userData = $layout->getUserData();
        $this->userId = $_SESSION['user_id']; 
        $this->userRole = $_SESSION['role'] ?? 'user';
        $this->userName = $_SESSION['username'] ?? 'User';

        // BLOKIR AKSES TAMU
        if ($this->userRole === 'tamu') {
            header("Location: index.php");
            exit;
        }
    }

    // Helper: Upload Foto
    private function uploadFoto($file) {
        // Target folder: public/uploads/
        $targetDir = "public/uploads/";
        if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }
        
        $fileName = time() . "_" . basename($file["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                return $fileName;
            }
        }
        return false;
    }

    public function index() {
        $pesan = "";
        $tipe = "";

        // Filter Privasi Query
        // Jika Admin -> kosong (lihat semua). Jika User -> filter by user_id
        $filter_privasi = ($this->userRole == 'super_admin') ? "" : "AND user_id = '{$this->userId}'";

        // ==========================
        // 1. HANDLE POST (CRUD)
        // ==========================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // A. SIMPAN BARU
            if (isset($_POST['btn_simpan'])) {
                $tgl = $_POST['tanggal'];
                $jam = $_POST['jam'];
                $kat = mysqli_real_escape_string($this->conn, $_POST['kategori']);
                $des = mysqli_real_escape_string($this->conn, $_POST['deskripsi']);
                $pic = mysqli_real_escape_string($this->conn, $_POST['pic']);
                $sts = $_POST['status'];
                $cat = mysqli_real_escape_string($this->conn, $_POST['catatan']);
                
                $foto = "";
                if (!empty($_FILES["bukti_foto"]["name"])) {
                    $upload = $this->uploadFoto($_FILES["bukti_foto"]);
                    if ($upload) $foto = $upload;
                    else { $pesan = "Gagal upload foto (Format harus JPG/PNG)."; $tipe = "error"; }
                }

                if (empty($pesan)) {
                    $q = "INSERT INTO logbook (user_id, tanggal, jam, kategori, deskripsi, pic, status, catatan, bukti_foto) 
                          VALUES ('{$this->userId}', '$tgl', '$jam', '$kat', '$des', '$pic', '$sts', '$cat', '$foto')";
                    
                    if (mysqli_query($this->conn, $q)) {
                        $pesan = "Log berhasil dicatat!"; $tipe = "success";
                    } else {
                        $pesan = "Gagal simpan: " . mysqli_error($this->conn); $tipe = "error";
                    }
                }
            }

            // B. UPDATE
            if (isset($_POST['btn_update'])) {
                $id  = $_POST['id_edit'];
                $tgl = $_POST['tanggal'];
                $jam = $_POST['jam'];
                $kat = mysqli_real_escape_string($this->conn, $_POST['kategori']);
                $des = mysqli_real_escape_string($this->conn, $_POST['deskripsi']);
                $pic = mysqli_real_escape_string($this->conn, $_POST['pic']);
                $sts = $_POST['status'];
                $cat = mysqli_real_escape_string($this->conn, $_POST['catatan']);

                $foto_sql = "";
                if (!empty($_FILES["bukti_foto"]["name"])) {
                    $upload = $this->uploadFoto($_FILES["bukti_foto"]);
                    if ($upload) $foto_sql = ", bukti_foto='$upload'";
                }

                // Update dengan filter privasi agar tidak bisa edit punya orang lain (kecuali admin)
                $q = "UPDATE logbook SET 
                      tanggal='$tgl', jam='$jam', kategori='$kat', deskripsi='$des', 
                      pic='$pic', status='$sts', catatan='$cat' $foto_sql
                      WHERE id='$id' $filter_privasi";

                if (mysqli_query($this->conn, $q)) {
                    if (mysqli_affected_rows($this->conn) > 0) {
                        $pesan = "Data log diperbarui!"; $tipe = "success";
                    } else {
                        $pesan = "Tidak ada perubahan atau Akses Ditolak."; $tipe = "warning";
                    }
                } else {
                    $pesan = "Error DB: " . mysqli_error($this->conn); $tipe = "error";
                }
            }

            // C. HAPUS
            if (isset($_POST['btn_hapus'])) {
                $id = $_POST['id_hapus'];
                if (mysqli_query($this->conn, "DELETE FROM logbook WHERE id='$id' $filter_privasi")) {
                    if (mysqli_affected_rows($this->conn) > 0) {
                        $pesan = "Log dihapus."; $tipe = "success";
                    } else {
                        $pesan = "Gagal hapus (Akses Ditolak)."; $tipe = "error";
                    }
                }
            }
        }

        // ==========================
        // 2. PREPARE DATA (READ)
        // ==========================
        
        // A. Statistik Chart
        $qStat = mysqli_query($this->conn, "SELECT status, COUNT(*) as jumlah FROM logbook WHERE 1=1 $filter_privasi GROUP BY status");
        $statData = ['Pending' => 0, 'On Progress' => 0, 'Selesai' => 0];
        while($r = mysqli_fetch_assoc($qStat)) {
            $statData[$r['status']] = $r['jumlah'];
        }

        // B. Filter Pencarian
        $f_mulai  = $_GET['tgl_mulai'] ?? '';
        $f_akhir  = $_GET['tgl_akhir'] ?? '';
        $f_kategori = $_GET['kategori'] ?? '';
        $f_status = $_GET['status'] ?? '';
        $f_search = $_GET['search'] ?? '';

        // C. Query Utama
        $sql = "SELECT logbook.*, users.username as nama_pencatat 
                FROM logbook 
                LEFT JOIN users ON logbook.user_id = users.id 
                WHERE 1=1 $filter_privasi";

        if ($f_mulai && $f_akhir) { $sql .= " AND tanggal BETWEEN '$f_mulai' AND '$f_akhir'"; }
        if ($f_kategori) { $sql .= " AND kategori = '$f_kategori'"; }
        if ($f_status) { $sql .= " AND status = '$f_status'"; }
        if ($f_search) { 
            $safe_search = mysqli_real_escape_string($this->conn, $f_search);
            $sql .= " AND (deskripsi LIKE '%$safe_search%' OR pic LIKE '%$safe_search%')"; 
        }

        $sql .= " ORDER BY tanggal DESC, jam DESC";
        $result = mysqli_query($this->conn, $sql);
        
        $logs = [];
        while($row = mysqli_fetch_assoc($result)) {
            // Logic Overdue (Dipindah ke Controller agar View bersih)
            $tglLog = new DateTime($row['tanggal']);
            $hariIni = new DateTime();
            $diff = $hariIni->diff($tglLog)->days;
            $row['is_overdue'] = ($row['status'] == 'Pending' && $tglLog < $hariIni && $diff > 3);
            $row['days_late'] = $diff;
            $logs[] = $row;
        }

        // ==========================
        // 3. LOAD VIEW
        // ==========================
        // Kirim variable ke View
        $my_role = $this->userRole;
        $my_id = $this->userId;
        $my_name = $this->userName;
        
        // Load Layout
        $layout = new LayoutController($this->conn); // Re-init for header usage if needed
        include 'views/partials/header.php';
        include 'views/pages/logbook.php';
        include 'views/partials/footer.php';
    }
}
?>