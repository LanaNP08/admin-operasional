<?php
// app/controllers/ExportController.php

class ExportController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // FUNGSI EXPORT SETTLEMENT
    public function settlement() {
        // Nama File
        $filename = "Data_Settlement_" . date('Ymd_Hi') . ".xls";

        // Header agar browser menganggap ini file Excel
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Query Data (Tanpa ID, Urut berdasarkan Tanggal)
        $query = "SELECT * FROM doc_settlemen ORDER BY doc_date DESC, doc_no DESC";
        $result = mysqli_query($this->conn, $query);

        // Bikin Tabel HTML (Excel akan membacanya sebagai sel)
        echo '<table border="1">';
        // Judul Kolom
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">
                <th>Doc No</th>
                <th>Date</th>
                <th>Information</th>
                <th>Amount (IDR)</th>
                <th>Description</th>
                <th>No Transaksi</th>
              </tr>';

        // Isi Data
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $row['doc_no'] . '</td>'; // Doc No text
            echo '<td>' . $row['doc_date'] . '</td>';
            echo '<td>' . htmlspecialchars($row['information']) . '</td>';
            echo '<td>' . $row['amount_cr'] . '</td>'; // Angka polos biar bisa disum di excel
            echo '<td>' . htmlspecialchars($row['description']) . '</td>';
            echo '<td>' . htmlspecialchars($row['no_transaksi'] ?? '-') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    // FUNGSI EXPORT INVOICE
    public function invoice() {
        $filename = "Data_Invoice_" . date('Ymd_Hi') . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $query = "SELECT * FROM invoice_reports ORDER BY doc_date DESC";
        $result = mysqli_query($this->conn, $query);

        echo '<table border="1">';
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">
                <th>Doc No</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Remarks</th>
                <th>Net</th>
                <th>Tax</th>
                <th>Total</th>
                <th>Faktur Pajak</th>
                <th>Status Kirim</th>
              </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            // Tentukan status
            $status = 'PENDING';
            if (!empty($row['sent_date'])) {
                $status = 'SENT (' . $row['sent_date'] . ')';
            } elseif (!empty($row['batch_id'])) {
                $status = 'OTW (Batch #' . $row['batch_id'] . ')';
            }

            echo '<tr>';
            echo '<td>\'' . $row['doc_no'] . '</td>'; // Pakai kutip satu biar gak jadi scientific number di excel
            echo '<td>' . $row['doc_date'] . '</td>';
            echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['remarks']) . '</td>';
            echo '<td>' . $row['net'] . '</td>';
            echo '<td>' . $row['tax'] . '</td>';
            echo '<td>' . $row['total'] . '</td>';
            echo '<td>\'' . $row['fp'] . '</td>';
            echo '<td>' . $status . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
}
?>