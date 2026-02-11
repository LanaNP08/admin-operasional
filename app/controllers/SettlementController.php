<?php
// app/controllers/SettlementController.php

// Matikan debug mode (Production Ready)
ini_set('display_errors', 0);
error_reporting(E_ALL);

class SettlementController
{
    private $conn;
    private $role;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $this->role = $_SESSION['role'] ?? 'tamu';

        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
            header("Location: login.php"); exit;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function index()
    {
        try {
            $pesan = "";
            $tipePesan = ""; 
            $importPreview = null;

            // ==========================
            // HANDLE POST REQUEST
            // ==========================
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    throw new Exception("Security Token Expired. Refresh halaman.");
                }

                // A. IMPORT PREVIEW
                if (isset($_POST['btn_preview_import'])) {
                    $lines = explode("\n", $_POST['raw_data']);
                    $previewData = [];
                    $stmtCek = $this->conn->prepare("SELECT doc_no FROM doc_settlemen WHERE doc_no = ?");

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        $cols = explode("\t", $line);
                        
                        if (count($cols) >= 4) {
                            $doc_no = trim($cols[0] ?? '-');
                            if (strlen($doc_no) < 2) continue;

                            $raw_date = $cols[1] ?? '';
                            $doc_date = date('Y-m-d');
                            
                            // Safe Date Parsing
                            if (is_numeric($raw_date) && $raw_date > 20000) {
                                $doc_date = date('Y-m-d', ($raw_date - 25569) * 86400);
                            } elseif (strpos($raw_date, '/') !== false || strpos($raw_date, '-') !== false) {
                                $cleanDate = str_replace('/', '-', $raw_date);
                                $ts = strtotime($cleanDate);
                                if ($ts) $doc_date = date('Y-m-d', $ts);
                            }

                            $amount = (float)preg_replace('/[^0-9]/', '', $cols[3] ?? 0);
                            
                            // Cek Duplikat
                            $isDup = false;
                            $stmtCek->bind_param("s", $doc_no);
                            $stmtCek->execute();
                            $stmtCek->store_result();
                            if ($stmtCek->num_rows > 0) $isDup = true;

                            $previewData[] = [
                                'doc_no' => $doc_no,
                                'doc_date' => $doc_date,
                                'information' => trim($cols[2] ?? '-'),
                                'amount_cr' => $amount,
                                'description' => trim($cols[4] ?? '-'),
                                'is_duplicate' => $isDup
                            ];
                        }
                    }
                    if(count($previewData)>0) {
                        $importPreview = $previewData;
                        $pesan = "Preview Data Siap.";
                        $tipePesan = "info";
                    }
                }

                // B. SAVE IMPORT
                if (isset($_POST['btn_save_import'])) {
                    $data = json_decode($_POST['json_data'], true);
                    $mode = $_POST['duplicate_mode'] ?? 'skip';
                    
                    if (is_array($data)) {
                        $stmtIns = $this->conn->prepare("INSERT INTO doc_settlemen (doc_no, doc_date, information, amount_cr, description) VALUES (?, ?, ?, ?, ?)");
                        $stmtUpd = $this->conn->prepare("UPDATE doc_settlemen SET doc_date=?, information=?, amount_cr=?, description=? WHERE doc_no=?");
                        
                        $sukses = 0; $update = 0;
                        foreach($data as $row) {
                            if($row['is_duplicate']) {
                                if($mode == 'update') {
                                    $stmtUpd->bind_param("ssiss", $row['doc_date'], $row['information'], $row['amount_cr'], $row['description'], $row['doc_no']);
                                    if($stmtUpd->execute()) $update++;
                                }
                            } else {
                                $stmtIns->bind_param("sssis", $row['doc_no'], $row['doc_date'], $row['information'], $row['amount_cr'], $row['description']);
                                if($stmtIns->execute()) $sukses++;
                            }
                        }
                        $pesan = "Selesai: $sukses Baru, $update Update.";
                        $tipePesan = "success";
                    }
                }

                // C. DELETE (BY DOC_NO)
                if (isset($_POST['btn_delete'])) {
                    $doc_del = mysqli_real_escape_string($this->conn, $_POST['id_delete']); 
                    mysqli_query($this->conn, "DELETE FROM doc_settlemen WHERE doc_no='$doc_del'");
                    $pesan = "Data $doc_del dihapus.";
                    $tipePesan = "success";
                }

                // D. RESET
                if (isset($_POST['btn_reset'])) {
                    mysqli_query($this->conn, "TRUNCATE TABLE doc_settlemen");
                    $pesan = "Database Reset.";
                    $tipePesan = "error";
                }
                
                // E. SIMPAN NO TRANSAKSI (BY DOC_NO)
                if (isset($_POST['btn_simpan_transaksi'])) {
                    $inputs = $_POST['transaksi'] ?? [];
                    foreach ($inputs as $doc_no => $val) {
                        $safe_doc = mysqli_real_escape_string($this->conn, $doc_no);
                        $no = mysqli_real_escape_string($this->conn, trim($val));
                        
                        mysqli_query($this->conn, "UPDATE doc_settlemen SET no_transaksi='$no' WHERE doc_no='$safe_doc'");
                    }
                    $pesan = "Perubahan No Transaksi Disimpan.";
                    $tipePesan = "success";
                }
            }

            // ==========================
            // VIEW DATA LOGIC
            // ==========================
            $keyword = mysqli_real_escape_string($this->conn, $_GET['cari'] ?? '');
            
            // [FITUR] Filter Transaksi Kosong (Diaktifkan Kembali)
            $onlyEmpty = isset($_GET['f_empty_trans']) ? true : false;
            
            $where = "WHERE 1=1";
            
            if($keyword) {
                $where .= " AND (doc_no LIKE '%$keyword%' OR information LIKE '%$keyword%' OR description LIKE '%$keyword%' OR no_transaksi LIKE '%$keyword%')";
            }
            
            // Logic Filter Empty
            if($onlyEmpty) {
                $where .= " AND (no_transaksi IS NULL OR no_transaksi = '')";
            }

            // Pagination
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = 50;
            
            $qCount = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM doc_settlemen $where");
            $totalData = $qCount ? mysqli_fetch_assoc($qCount)['total'] : 0;
            $totalPages = ceil($totalData / $limit);
            $offset = ($page - 1) * $limit;

            // Load Data
            $qLoad = mysqli_query($this->conn, "SELECT * FROM doc_settlemen $where ORDER BY doc_date DESC, doc_no DESC LIMIT $offset, $limit");
            
            $dataTabel = [];
            if($qLoad) {
                while($row = mysqli_fetch_assoc($qLoad)) {
                    $info = strtoupper($row['information']);
                    if (strpos($info, 'SETTLE') !== false) {
                        $row['class'] = 'bg-blue-100 text-blue-800 border-blue-200';
                        $row['badge'] = 'SETTLEMENT';
                    } elseif (strpos($info, 'ADVANCE') !== false) {
                        $row['class'] = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                        $row['badge'] = 'ADVANCE';
                    } else {
                        $row['class'] = 'bg-slate-100 text-slate-600 border-slate-200';
                        $row['badge'] = 'OTHER';
                    }
                    $dataTabel[] = $row;
                }
            }

            $viewData = [
                'data' => $dataTabel,
                'totalData' => $totalData,
                'page' => $page,
                'totalPages' => $totalPages,
                'filters' => ['cari' => $keyword, 'f_empty_trans' => $onlyEmpty],
                'pesan' => $pesan,
                'tipePesan' => $tipePesan,
                'importPreview' => $importPreview
            ];

            require_once 'app/controllers/LayoutController.php';
            $layout = new LayoutController($this->conn);
            include 'views/partials/header.php';
            include 'views/pages/settlement.php';
            include 'views/partials/footer.php';

        } catch (Throwable $e) {
            echo "<div style='background:red; color:white; padding:20px;'><b>ERROR SYSTEM:</b> " . $e->getMessage() . "</div>";
            die();
        }
    }
}
?>