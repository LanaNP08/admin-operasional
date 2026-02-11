<?php
// app/controllers/DashboardController.php

class DashboardController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function index() {
        // [UPDATE] Routing AJAX
        if (isset($_GET['ajax_action'])) {
            if (ob_get_length()) ob_clean(); 
            
            if ($_GET['ajax_action'] == 'get_stats') {
                $this->getStats();
            } 
            elseif ($_GET['ajax_action'] == 'get_month_details') {
                $this->getMonthDetails();
            }
            // [BARU] Action untuk Detail Client
            elseif ($_GET['ajax_action'] == 'get_client_details') {
                $this->getClientDetails();
            }
            exit;
        }

        // LOGIC TAHUN
        $years = [];
        $qYear = mysqli_query($this->conn, "SELECT DISTINCT YEAR(doc_date) as tahun FROM invoice_reports WHERE is_deleted=0 ORDER BY tahun DESC");
        while($r = mysqli_fetch_assoc($qYear)) {
            $years[] = $r['tahun'];
        }
        if(empty($years)) $years[] = date('Y');
        $tahunIni = $years[0];
        
        require_once 'app/controllers/LayoutController.php';
        $layout = new LayoutController($this->conn);
        include 'views/partials/header.php';
        include 'views/pages/dashboard.php';
        include 'views/partials/footer.php';
    }

    // METHOD 1: STATISTIK UTAMA (SAMA)
    private function getStats() {
        header('Content-Type: application/json');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        // Logic kalkulasi summary (tetap sama)
        $sql = "SELECT 
            SUM(i.net) as total_omzet,
            COUNT(i.id) as total_docs,
            SUM(CASE WHEN (i.batch_id IS NULL OR i.batch_id = 0) AND (i.sent_date IS NULL OR i.sent_date = '0000-00-00') THEN i.net ELSE 0 END) as pending_rp,
            COUNT(CASE WHEN (i.batch_id IS NULL OR i.batch_id = 0) AND (i.sent_date IS NULL OR i.sent_date = '0000-00-00') THEN 1 END) as pending_count,
            COUNT(CASE WHEN 
                (i.batch_id > 0 AND b.received_at IS NOT NULL AND b.received_at != '0000-00-00 00:00:00') 
                OR 
                ((i.batch_id IS NULL OR i.batch_id = 0) AND i.sent_date IS NOT NULL AND i.sent_date != '0000-00-00') 
            THEN 1 END) as success_count,
            COUNT(CASE WHEN 
                (i.batch_id > 0 AND (b.received_at IS NULL OR b.received_at = '0000-00-00 00:00:00') AND b.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY))
            THEN 1 END) as overdue_count
            FROM invoice_reports i 
            LEFT JOIN delivery_batches b ON i.batch_id = b.id
            WHERE i.is_deleted = 0 AND YEAR(i.doc_date) = '$year'";

        $result = mysqli_query($this->conn, $sql);
        $data = mysqli_fetch_assoc($result);

        $chartSql = "SELECT MONTH(doc_date) as bulan, SUM(net) as total FROM invoice_reports WHERE is_deleted = 0 AND YEAR(doc_date) = '$year' GROUP BY MONTH(doc_date)";
        $qChart = mysqli_query($this->conn, $chartSql);
        $chartData = array_fill(1, 12, 0);
        while($r = mysqli_fetch_assoc($qChart)) $chartData[intval($r['bulan'])] = floatval($r['total']);

        $clientSql = "SELECT customer_name, SUM(net) as total FROM invoice_reports WHERE is_deleted = 0 AND YEAR(doc_date) = '$year' GROUP BY customer_name ORDER BY total DESC LIMIT 5";
        $qClient = mysqli_query($this->conn, $clientSql);
        $clients = [];
        while($r = mysqli_fetch_assoc($qClient)) $clients[] = $r;

        echo json_encode(['summary' => $data, 'chart_monthly' => array_values($chartData), 'top_clients' => $clients]);
    }

    // METHOD 2: DETAIL BULAN (SAMA)
    private function getMonthDetails() {
        header('Content-Type: application/json');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $month = isset($_GET['month']) ? intval($_GET['month']) : 1;

        $query = "SELECT i.doc_no, i.doc_date, i.customer_name, i.net, i.sent_date, i.batch_id,
                  b.received_at, b.created_at as batch_created_at
                  FROM invoice_reports i 
                  LEFT JOIN delivery_batches b ON i.batch_id = b.id
                  WHERE i.is_deleted = 0 
                  AND YEAR(i.doc_date) = '$year' 
                  AND MONTH(i.doc_date) = '$month'
                  ORDER BY i.doc_date DESC";
        
        $this->renderDetails($query);
    }

    // [BARU] METHOD 3: DETAIL CLIENT
    private function getClientDetails() {
        header('Content-Type: application/json');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        // Decode URL customer name
        $cust = isset($_GET['customer']) ? mysqli_real_escape_string($this->conn, $_GET['customer']) : '';

        $query = "SELECT i.doc_no, i.doc_date, i.customer_name, i.net, i.sent_date, i.batch_id,
                  b.received_at, b.created_at as batch_created_at
                  FROM invoice_reports i 
                  LEFT JOIN delivery_batches b ON i.batch_id = b.id
                  WHERE i.is_deleted = 0 
                  AND YEAR(i.doc_date) = '$year' 
                  AND i.customer_name = '$cust'
                  ORDER BY i.doc_date DESC";
        
        $this->renderDetails($query);
    }

    // [HELPER] RENDER QUERY KE JSON (DIPAKAI OLEH METHOD 2 & 3)
    private function renderDetails($query) {
        $result = mysqli_query($this->conn, $query);
        $details = [];
        while($row = mysqli_fetch_assoc($result)) {
            $status = 'PENDING';
            $statusClass = 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20';

            if (!empty($row['received_at']) && $row['received_at'] != '0000-00-00 00:00:00') {
                $status = 'RECEIVED';
                $statusClass = 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20';
            } elseif (!empty($row['sent_date']) && $row['sent_date'] != '0000-00-00') {
                 if(empty($row['batch_id'])) {
                    $status = 'MANUAL SENT';
                    $statusClass = 'text-blue-400 bg-blue-400/10 border-blue-400/20';
                 } else {
                    $status = 'OTW';
                    $statusClass = 'text-blue-400 bg-blue-400/10 border-blue-400/20';
                 }
            } elseif (!empty($row['batch_id'])) {
                 $isOverdue = false;
                 if (!empty($row['batch_created_at'])) {
                    $batchDate = new DateTime($row['batch_created_at']);
                    $now = new DateTime();
                    $interval = $batchDate->diff($now);
                    if ($interval->days > 7 && $interval->invert == 0) $isOverdue = true;
                 }
                 if($isOverdue) {
                    $status = 'OVERDUE';
                    $statusClass = 'text-red-400 bg-red-400/10 border-red-400/20 animate-pulse';
                 } else {
                    $status = 'OTW';
                    $statusClass = 'text-blue-400 bg-blue-400/10 border-blue-400/20';
                 }
            }

            $row['status_label'] = $status;
            $row['status_class'] = $statusClass;
            $details[] = $row;
        }
        echo json_encode($details);
    }
}
?>