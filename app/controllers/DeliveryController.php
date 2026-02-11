<?php
// app/controllers/DeliveryController.php

class DeliveryController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // A. LOGIC CETAK SURAT JALAN
    public function print($code) {
        // Cek Auth (Hanya internal yang boleh cetak)
        require_once 'app/controllers/LayoutController.php';
        $layout = new LayoutController($this->conn);

        $code = mysqli_real_escape_string($this->conn, $code);

        // 1. Ambil Data Batch
        $qBatch = mysqli_query($this->conn, "SELECT * FROM delivery_batches WHERE batch_code='$code'");
        $batch = mysqli_fetch_assoc($qBatch);

        if (!$batch) die("Data pengiriman tidak ditemukan.");

        // 2. Ambil List Invoice
        $qInv = mysqli_query($this->conn, "SELECT * FROM invoice_reports WHERE batch_id='{$batch['id']}'");
        $invoices = [];
        while($r = mysqli_fetch_assoc($qInv)){
            $invoices[] = $r;
        }

        // 3. Logika Alamat
        $alamatTujuan = "";
        if (!empty($batch['delivery_address'])) {
            $alamatTujuan = nl2br(htmlspecialchars($batch['delivery_address']));
        } else {
            $custName = !empty($invoices) ? $invoices[0]['customer_name'] : "Finance Dept";
            $alamatTujuan = "<strong>" . $custName . "</strong><br><span class='text-xs'>UP: Finance / AP Dept</span>";
        }

        // 4. URL QR Code (Menuju halaman receive.php)
        // Pastikan domain sesuai production nanti
        $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']);
        // Fix jika dirname cuma '/'
        if(substr($baseUrl, -1) == '/') $baseUrl = rtrim($baseUrl, '/');
        // Bersihkan path jika ada admin-ops/app/controllers (kasus path relative)
        // Kita asumsikan receive.php ada di root sejajar dengan index.php
        // Maka URLnya: domain.com/folder/receive.php?code=...
        $scan_url = str_replace("/app/controllers", "", $baseUrl) . "/receive.php?code=" . $code;
        // Koreksi manual path jika perlu, tapi logic di atas mencoba mendeteksi root
        // Simplest: ambil URL root dari $_SERVER dan tempel receive.php
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $root_url = str_replace("print_delivery.php", "receive.php", $actual_link);
        // Hapus query string lama
        $root_url = explode('?', $root_url)[0] . "?code=" . $code;
        $scan_url = $root_url;

        include 'views/pages/print_delivery.php';
    }

    // B. LOGIC TERIMA BARANG (PUBLIC ACCESS)
    public function receive($code) {
        date_default_timezone_set('Asia/Jakarta'); 
        $code = mysqli_real_escape_string($this->conn, $code);
        $pesan = '';
        $tipe = '';

        // 1. Cek Validitas Kode
        $qBatch = mysqli_query($this->conn, "SELECT * FROM delivery_batches WHERE batch_code='$code'");
        $batch = mysqli_fetch_assoc($qBatch);

        // 2. Handle Submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_terima']) && $batch) {
            $penerima = mysqli_real_escape_string($this->conn, $_POST['recipient_name']);
            
            // Logic Foto
            $fotoSql = "";
            if (!empty($_FILES['proof_photo']['name'])) {
                $targetDir = "public/uploads/"; // Folder baru
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

                $ext = pathinfo($_FILES['proof_photo']['name'], PATHINFO_EXTENSION);
                $fotoNama = "PROOF_" . time() . "." . $ext;
                
                if(move_uploaded_file($_FILES['proof_photo']['tmp_name'], $targetDir . $fotoNama)){
                    $fotoSql = ", proof_photo='$fotoNama'";
                }
            }

            // Logic Update Waktu (Hanya isi jika belum ada)
            $waktuSql = "";
            if (empty($batch['received_at']) || $batch['received_at'] == '0000-00-00 00:00:00') {
                $now = date('Y-m-d H:i:s');
                $waktuSql = ", received_at='$now'";
            }

            $qUpdate = "UPDATE delivery_batches SET recipient_name='$penerima' $fotoSql $waktuSql WHERE id='{$batch['id']}'";
            
            if (mysqli_query($this->conn, $qUpdate)) {
                // Refresh data
                $qBatch = mysqli_query($this->conn, "SELECT * FROM delivery_batches WHERE batch_code='$code'");
                $batch = mysqli_fetch_assoc($qBatch);
                $pesan = "Berhasil! Data penerimaan telah tersimpan.";
                $tipe = 'success';
            } else {
                $pesan = "Gagal menyimpan: " . mysqli_error($this->conn);
                $tipe = 'error';
            }
        }

        include 'views/pages/receive.php';
    }
}
?>