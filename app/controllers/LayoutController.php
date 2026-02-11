<?php
// app/controllers/LayoutController.php

class LayoutController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkAuth();
    }

    private function checkAuth() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) { 
            // Sesuaikan path redirect jika perlu
            header("Location: login.php"); 
            exit; 
        }
    }

    public function getNotifications() {
        $notifList = [];

        // A. CEK ALAT EXPIRED (Critical)
        $qAlatExp = mysqli_query($this->conn, "SELECT nama_alat, tgl_kalibrasi FROM alat_kalibrasi WHERE tgl_kalibrasi < CURDATE() LIMIT 5");
        while($row = mysqli_fetch_assoc($qAlatExp)) {
            $notifList[] = [
                'type' => 'danger',
                'icon' => '🚨',
                'text' => "<b>{$row['nama_alat']}</b> sudah lewat jadwal kalibrasi!",
                'link' => 'monitoring.php?filter=overdue'
            ];
        }

        // B. CEK ALAT WARNING (H-60)
        $qAlatWarn = mysqli_query($this->conn, "SELECT nama_alat, DATEDIFF(tgl_kalibrasi, CURDATE()) as sisa FROM alat_kalibrasi WHERE tgl_kalibrasi BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) LIMIT 5");
        while($row = mysqli_fetch_assoc($qAlatWarn)) {
            $notifList[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'text' => "<b>{$row['nama_alat']}</b> kalibrasi dalam {$row['sisa']} hari.",
                'link' => 'monitoring.php?filter=warning'
            ];
        }

        // C. CEK INVOICE LAMA BELUM DIKIRIM (>30 Hari)
        $qInv = mysqli_query($this->conn, "SELECT doc_no, customer_name FROM invoice_reports WHERE (sent_date IS NULL OR sent_date = '0000-00-00') AND doc_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) LIMIT 5");
        while($row = mysqli_fetch_assoc($qInv)) {
            $cust = substr($row['customer_name'], 0, 15);
            $notifList[] = [
                'type' => 'info',
                'icon' => '📝',
                'text' => "Invoice <b>{$row['doc_no']}</b> ($cust) > 30 hari belum dikirim.",
                'link' => 'invoice.php?search=' . $row['doc_no']
            ];
        }

        return $notifList;
    }

    public function isActive($pageName) {
        $current = basename($_SERVER['PHP_SELF']);
        if (strpos($current, $pageName) !== false) { 
            return 'bg-blue-600/10 text-blue-400 font-semibold border border-blue-500/20 shadow-[0_0_15px_-3px_rgba(59,130,246,0.3)]'; 
        }
        return 'text-slate-400 hover:text-slate-100 hover:bg-white/5 border border-transparent transition-all duration-200';
    }

    public function getUserData() {
        return [
            'username' => $_SESSION['username'] ?? 'User',
            'role' => $_SESSION['role'] ?? '',
            'initial' => substr($_SESSION['username'] ?? 'U', 0, 2)
        ];
    }
}
?>