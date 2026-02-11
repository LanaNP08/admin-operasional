<?php
// app/controllers/InvoiceController.php

class InvoiceController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function handleRequest() {
        // 0. ROUTING EXTRA (Print, Receive & AJAX)
        $action = $_GET['action'] ?? null;

        if ($action === 'print_delivery') {
            $this->printDelivery();
            return;
        }

        if ($action === 'receive') {
            $this->handleReceivePage();
            return;
        }

        // 1. AJAX HANDLER (JSON)
        if (isset($_REQUEST['ajax_action'])) {
            $this->handleAjax();
            exit;
        }

        // 2. LOGIC POST FORM
        $pesan = "";

        // A. IMPORT PASTE
        if (isset($_POST['btn_import_paste'])) {
            $lines = explode("\n", $_POST['raw_data']); 
            $sukses = 0;
            foreach ($lines as $line) {
                $cols = explode("\t", trim($line));
                if (count($cols) >= 3) {
                    $doc_no = trim($cols[0] ?? '');
                    if (empty($doc_no) || strlen($doc_no) < 5 || stripos($doc_no, 'doc') !== false) continue;
                    
                    $doc_date = $this->parseTanggal($cols[2] ?? '');
                    $sent_date = $this->parseTanggal($cols[9] ?? '');
                    $net = preg_replace('/[^0-9]/', '', $cols[5] ?? 0);
                    $tax = preg_replace('/[^0-9]/', '', $cols[6] ?? 0);
                    $total = preg_replace('/[^0-9]/', '', $cols[7] ?? 0);

                    // INSERT / UPDATE
                    $stmt = $this->conn->prepare("INSERT INTO invoice_reports (doc_no, created_by, doc_date, remarks, customer_name, net, tax, total, fp, sent_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE created_by=VALUES(created_by), doc_date=VALUES(doc_date), remarks=VALUES(remarks), customer_name=VALUES(customer_name), net=VALUES(net), tax=VALUES(tax), total=VALUES(total), fp=VALUES(fp), sent_date=VALUES(sent_date)");
                    $u = $_SESSION['user_name'] ?? 'System';
                    $stmt->bind_param("sssssddsss", $doc_no, $u, $doc_date, $cols[3], $cols[4], $net, $tax, $total, $cols[8], $sent_date);
                    if($stmt->execute()) $sukses++;
                }
            }
            $pesan = "Import Selesai: $sukses data.";
        }

        // B. CREATE BATCH (SURAT JALAN)
        if (isset($_POST['btn_create_batch'])) {
            if (!empty($_POST['selected_ids'])) {
                $ids = array_map('intval', $_POST['selected_ids']);
                $idList = implode(',', $ids);
                
                $address = isset($_POST['delivery_address']) ? mysqli_real_escape_string($this->conn, $_POST['delivery_address']) : '';
                $batchCode = "DLV-" . date('ymd') . "-" . strtoupper(substr(md5(time()), 0, 4));
                
                // 1. Insert Batch Baru
                if (mysqli_query($this->conn, "INSERT INTO delivery_batches (batch_code, delivery_address, created_at) VALUES ('$batchCode', '$address', NOW())")) {
                    
                    // 2. Ambil ID Batch dengan AMAN
                    $newBatchId = mysqli_insert_id($this->conn);
                    if ($newBatchId == 0) {
                        $qCek = mysqli_query($this->conn, "SELECT id FROM delivery_batches WHERE batch_code='$batchCode' LIMIT 1");
                        $rCek = mysqli_fetch_assoc($qCek);
                        $newBatchId = $rCek['id'];
                    }

                    // 3. Link Invoice ke Batch ID ini
                    mysqli_query($this->conn, "UPDATE invoice_reports SET batch_id = '$newBatchId' WHERE id IN ($idList)");
                    
                    // 4. Buka Tab Print
                    echo "<script>window.open('invoice.php?action=print_delivery&code=$batchCode', '_blank');</script>";
                    $pesan = "Tanda terima dibuat!";
                } else {
                    $pesan = "Gagal membuat batch: " . mysqli_error($this->conn);
                }
            }
        }
        if (isset($_POST['btn_bulk_excel'])) {
            if (!empty($_POST['selected_ids'])) {
                $ids = implode(',', array_map('intval', $_POST['selected_ids']));
                $query = mysqli_query($this->conn, "SELECT * FROM invoice_reports WHERE id IN ($ids) ORDER BY doc_date DESC");

                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=Data_Invoice_Terpilih_" . date('Y-m-d_H-i') . ".xls");
                header("Pragma: no-cache");
                header("Expires: 0");

                echo '<table border="1">';
                echo '<tr style="background-color:#f2f2f2; font-weight:bold;">
                        <th>No</th>
                        <th>Doc No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Remarks</th>
                        <th>Net</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Status</th>
                      </tr>';

                $no = 1;
                while ($row = mysqli_fetch_assoc($query)) {
                    $status = 'PENDING';
                    if (!empty($row['batch_id'])) {
                        $status = 'OTW / DIKIRIM'; 
                    } elseif (!empty($row['sent_date'])) {
                        $status = 'MANUAL SENT';
                    }

                    echo '<tr>';
                    echo '<td>' . $no++ . '</td>';
                    echo '<td style="mso-number-format:\@">' . $row['doc_no'] . '</td>'; 
                    echo '<td>' . date('d/m/Y', strtotime($row['doc_date'])) . '</td>';
                    echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['remarks']) . '</td>';
                    echo '<td>' . number_format($row['net'], 0, ',', '.') . '</td>';
                    echo '<td>' . number_format($row['tax'], 0, ',', '.') . '</td>';
                    echo '<td>' . number_format($row['total'], 0, ',', '.') . '</td>';
                    echo '<td>' . $status . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                exit; 
            }
        }

        // C. BULK SEND (UPDATE TANGGAL MANUAL)
        if (isset($_POST['btn_bulk_send'])) {
            if (!empty($_POST['selected_ids']) && !empty($_POST['bulk_date'])) {
                $ids = implode(',', array_map('intval', $_POST['selected_ids']));
                $date = mysqli_real_escape_string($this->conn, $_POST['bulk_date']);
                if(mysqli_query($this->conn, "UPDATE invoice_reports SET sent_date = '$date' WHERE id IN ($ids)")) {
                    $pesan = "Update tanggal berhasil.";
                }
            }
        }

        // D. MANUAL EDIT (MODAL)
        if (isset($_POST['btn_update_manual'])) {
            $id = $_POST['id_edit']; $rem = $_POST['remarks']; $fp = $_POST['fp']; 
            $net = str_replace('.', '', $_POST['net']); $tax = str_replace('.', '', $_POST['tax']); $total = str_replace('.', '', $_POST['total']);
            $sentVal = !empty($_POST['sent_date']) ? "'".mysqli_real_escape_string($this->conn, $_POST['sent_date'])."'" : "NULL";
            if(mysqli_query($this->conn, "UPDATE invoice_reports SET remarks='$rem', fp='$fp', sent_date=$sentVal, net='$net', tax='$tax', total='$total' WHERE id='$id'"))
                $pesan = "Data diperbarui.";
        }

        // E. DELETE ACTIONS
        if (isset($_POST['btn_soft_delete'])) { mysqli_query($this->conn, "UPDATE invoice_reports SET is_deleted=1 WHERE id='{$_POST['id_delete']}'"); }
        if (isset($_POST['btn_restore'])) { mysqli_query($this->conn, "UPDATE invoice_reports SET is_deleted=0 WHERE id='{$_POST['id_restore']}'"); }
        if (isset($_POST['btn_hard_delete'])) {
            $id = mysqli_real_escape_string($this->conn, $_POST['id_delete']);
            $cek = mysqli_query($this->conn, "SELECT batch_id FROM invoice_reports WHERE id='$id'");
            $dt = mysqli_fetch_assoc($cek);
            if(!empty($dt['batch_id'])) { $pesan = "GAGAL: Data terikat batch."; } 
            else { mysqli_query($this->conn, "DELETE FROM invoice_reports WHERE id='$id'"); }
        }
        if (isset($_POST['btn_reset_data'])) { mysqli_query($this->conn, "TRUNCATE TABLE invoice_reports"); $pesan = "Reset Berhasil."; }

        // F. MANUAL RECEIVE KHUSUS OVERDUE (+ UPLOAD BUKTI)
        if (isset($_POST['btn_overdue_receive'])) {
            $batchId = (int)$_POST['overdue_batch_id'];
            $recName = mysqli_real_escape_string($this->conn, $_POST['recipient_name']);
            $recDate = mysqli_real_escape_string($this->conn, $_POST['received_date']);
            $fullDate = $recDate . ' ' . date('H:i:s');

            $fotoSql = "";
            if (!empty($_FILES['manual_proof_photo']['name'])) {
                $ext = pathinfo($_FILES['manual_proof_photo']['name'], PATHINFO_EXTENSION);
                $fotoNama = "MANUAL_" . time() . "." . $ext;
                if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
                if(move_uploaded_file($_FILES['manual_proof_photo']['tmp_name'], "uploads/" . $fotoNama)) {
                    $fotoSql = ", proof_photo='$fotoNama'";
                }
            }

            $qBatch = "UPDATE delivery_batches SET recipient_name='$recName', received_at='$fullDate', is_read=0 $fotoSql WHERE id='$batchId'";
            
            if(mysqli_query($this->conn, $qBatch)) {
                mysqli_query($this->conn, "UPDATE invoice_reports SET sent_date = '$recDate' WHERE batch_id = '$batchId'");
                $pesan = "Status Overdue berhasil diubah menjadi DITERIMA.";
            } else {
                $pesan = "Gagal update batch: " . mysqli_error($this->conn);
            }
        }

        // 3. RENDER PAGE UTAMA
        $this->index($pesan);
    }

    private function index($pesan = "") {
        // FILTER LOGIC
        $f_doc = $_GET['f_doc'] ?? ''; $f_date = $_GET['f_date'] ?? ''; $f_cust = $_GET['f_cust'] ?? '';
        $f_rem = $_GET['f_rem'] ?? ''; $f_fp = $_GET['f_fp'] ?? ''; $f_status = $_GET['f_status'] ?? '';
        
        $viewMode = isset($_GET['view']) && $_GET['view'] == 'trash' ? 'trash' : 'active';
        $del = ($viewMode == 'trash') ? 1 : 0;
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 100; $offset = ($page - 1) * $limit;

        $where = "WHERE i.is_deleted = $del";
        if($f_doc) $where .= " AND i.doc_no LIKE '%$f_doc%'";
        if($f_date) $where .= " AND i.doc_date LIKE '%$f_date%'";
        if($f_cust) $where .= " AND i.customer_name LIKE '%$f_cust%'";
        if($f_rem) $where .= " AND i.remarks LIKE '%$f_rem%'";
        if($f_fp) $where .= " AND i.fp LIKE '%$f_fp%'";
        
        if($f_status == 'PENDING') {
            $where .= " AND (i.batch_id IS NULL OR i.batch_id = 0) AND (i.sent_date IS NULL OR i.sent_date = '0000-00-00')"; 
        }
        elseif($f_status == 'OTW') {
            $where .= " AND (i.batch_id > 0 AND (b.received_at IS NULL OR b.received_at = '0000-00-00 00:00:00'))"; 
        }
        elseif($f_status == 'RECEIVED') {
            $where .= " AND ( 
                (i.batch_id > 0 AND b.received_at IS NOT NULL AND b.received_at != '0000-00-00 00:00:00') 
                OR 
                ((i.batch_id IS NULL OR i.batch_id = 0) AND i.sent_date IS NOT NULL AND i.sent_date != '0000-00-00') 
            )"; 
        }
        elseif($f_status == 'OVERDUE') {
            $where .= " AND (i.batch_id > 0 AND (b.received_at IS NULL OR b.received_at = '0000-00-00 00:00:00') AND b.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY))";
        }

        $totalQ = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM invoice_reports i LEFT JOIN delivery_batches b ON i.batch_id = b.id $where");
        $totalData = mysqli_fetch_assoc($totalQ)['total'];
        $totalPages = ceil($totalData / $limit);

        $query = "SELECT i.*, b.id as batch_id, b.proof_photo, b.recipient_name, b.received_at, b.created_at as batch_created_at 
                  FROM invoice_reports i LEFT JOIN delivery_batches b ON i.batch_id = b.id 
                  $where ORDER BY i.doc_date DESC, i.id DESC LIMIT $offset, $limit";
        $result = mysqli_query($this->conn, $query);
        $data = []; while($row = mysqli_fetch_assoc($result)) $data[] = $row;

        // DATA UNTUK VIEW
        $viewData = [
            'data' => $data, 'totalData' => $totalData, 'page' => $page, 'totalPages' => $totalPages,
            'filters' => compact('f_doc', 'f_date', 'f_cust', 'f_rem', 'f_fp', 'f_status'),
            'viewMode' => $viewMode, 'isTamu' => (isset($_SESSION['role']) && $_SESSION['role'] == 'tamu'),
            'pesan' => $pesan
        ];

        require_once 'app/controllers/LayoutController.php';
        $layout = new LayoutController($this->conn);
        include 'views/partials/header.php';
        include 'views/pages/invoice.php';
        include 'views/partials/footer.php';
    }

    private function parseTanggal($val) {
        if (empty($val) || $val == '-' || strlen($val) < 6) return NULL;
        $val = trim($val); $val_clean = str_replace(['/', ' '], ['.', ''], $val); 
        $d = DateTime::createFromFormat('d.m.y', $val_clean); if ($d) return $d->format('Y-m-d');
        $d = DateTime::createFromFormat('d.m.Y', $val_clean); if ($d) return $d->format('Y-m-d');
        $d = DateTime::createFromFormat('d/m/Y', str_replace('.', '/', $val)); if ($d) return $d->format('Y-m-d');
        return NULL;
    }

    private function handleAjax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['is_logged_in'])) { 
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak (Session Expired)']); 
            return; 
        }

        $action = $_REQUEST['ajax_action'];

        if ($action == 'get_data') {
            $id = mysqli_real_escape_string($this->conn, $_GET['id']);
            $q = mysqli_query($this->conn, "SELECT * FROM invoice_reports WHERE id='$id'");
            echo json_encode(mysqli_fetch_assoc($q));
            return;
        }

        if ($action == 'get_proof') {
            $batch_id = mysqli_real_escape_string($this->conn, $_GET['batch_id'] ?? 0);
            
            $qBatch = mysqli_query($this->conn, "SELECT * FROM delivery_batches WHERE id='$batch_id'");
            $data = mysqli_fetch_assoc($qBatch);

            if (!$data) {
                echo json_encode([
                    'recipient_name' => 'Data tidak ditemukan', 
                    'received_at_indo' => '-', 
                    'invoice_list' => [],
                    'proof_photo' => null
                ]);
                return;
            }

            $tglIndo = '-';
            if (!empty($data['received_at']) && $data['received_at'] != '0000-00-00 00:00:00') {
                $t = strtotime($data['received_at']);
                $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agust','Sep','Okt','Nov','Des'];
                $tglIndo = $hari[date('w',$t)] . ", " . date('d',$t) . " " . $bulan[(int)date('m',$t)] . " " . date('H:i',$t) . " WIB";
            }
            $data['received_at_indo'] = $tglIndo;

            $invoices = [];
            $qList = mysqli_query($this->conn, "SELECT doc_no, customer_name, remarks FROM invoice_reports WHERE batch_id='$batch_id' ORDER BY doc_no ASC");
            while($inv = mysqli_fetch_assoc($qList)) {
                $invoices[] = $inv;
            }
            $data['invoice_list'] = $invoices;

            echo json_encode($data);
            return;
        }

        if ($action == 'inline_update') {
            $id = mysqli_real_escape_string($this->conn, $_POST['id']);
            $field = mysqli_real_escape_string($this->conn, $_POST['field']); 
            $value = mysqli_real_escape_string($this->conn, $_POST['value']);
            if(in_array($field, ['net', 'tax', 'total'])) {
                $value = str_replace('.', '', $value); if(!is_numeric($value)) $value = 0;
            }
            $query = "UPDATE invoice_reports SET $field = '$value' WHERE id='$id'";
            if (mysqli_query($this->conn, $query)) {
                if ($field == 'net' || $field == 'tax') mysqli_query($this->conn, "UPDATE invoice_reports SET total = net + tax WHERE id='$id'");
                echo json_encode(['status' => 'success']);
            } else echo json_encode(['status' => 'error']);
            return;
        }
    }

    private function printDelivery() {
        if (empty($_GET['code'])) die("Akses ditolak.");
        $code = mysqli_real_escape_string($this->conn, $_GET['code']);
        
        $qBatch = mysqli_query($this->conn, "SELECT * FROM delivery_batches WHERE batch_code='$code'");
        $batch = mysqli_fetch_assoc($qBatch);
        if (!$batch) die("Data tidak ditemukan.");

        $qInv = mysqli_query($this->conn, "SELECT * FROM invoice_reports WHERE batch_id='{$batch['id']}'");
        $invoices = [];
        while($r = mysqli_fetch_assoc($qInv)) $invoices[] = $r;

        $scan_url = BASE_URL . "/invoice.php?action=receive&code=" . $code;

        include 'views/pages/print_delivery.php';
    }

    private function handleReceivePage() {
        date_default_timezone_set('Asia/Jakarta'); 
        $code = isset($_GET['code']) ? mysqli_real_escape_string($this->conn, $_GET['code']) : '';
        $pesan = '';
        
        $qBatch = mysqli_query($this->conn, "SELECT * FROM delivery_batches WHERE batch_code='$code'");
        $batch = mysqli_fetch_assoc($qBatch);

        if (isset($_POST['btn_terima']) && $batch) {
            $penerima = mysqli_real_escape_string($this->conn, $_POST['recipient_name']);
            $tgl = date('Y-m-d H:i:s');
            
            $fotoSql = "";
            if (!empty($_FILES['proof_photo']['name'])) {
                $ext = pathinfo($_FILES['proof_photo']['name'], PATHINFO_EXTENSION);
                $fotoNama = "PROOF_" . time() . "." . $ext;
                if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
                if(move_uploaded_file($_FILES['proof_photo']['tmp_name'], "uploads/" . $fotoNama)) {
                    $fotoSql = ", proof_photo='$fotoNama'";
                }
            }

            $waktuSql = "";
            if (empty($batch['received_at']) || $batch['received_at'] == '0000-00-00 00:00:00') {
                $waktuSql = ", received_at='$tgl'"; 
            }

            $qUpdate = "UPDATE delivery_batches SET recipient_name='$penerima', is_read=0 $fotoSql $waktuSql WHERE id='{$batch['id']}'";
            
            if (mysqli_query($this->conn, $qUpdate)) {
                $tglSaja = date('Y-m-d');
                mysqli_query($this->conn, "UPDATE invoice_reports SET sent_date = '$tglSaja' WHERE batch_id = '{$batch['id']}'");
                $pesan = "Konfirmasi Berhasil Disimpan!";
                
                $qBatch = mysqli_query($this->conn, "SELECT * FROM delivery_batches WHERE batch_code='$code'");
                $batch = mysqli_fetch_assoc($qBatch);
            } else {
                $pesan = "Gagal menyimpan.";
            }
        }
        include 'views/pages/receive.php';
    }
}
?>