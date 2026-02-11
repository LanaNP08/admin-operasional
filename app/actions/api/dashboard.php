<?php
// app/actions/api/dashboard.php

// Matikan error display agar JSON bersih
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Pastikan koneksi tersedia
if (!isset($conn)) exit;

$type = $_POST['type'] ?? '';
// Ambil tahun dari request, default ke tahun sekarang jika kosong
$year = isset($_POST['year']) && !empty($_POST['year']) ? mysqli_real_escape_string($conn, $_POST['year']) : date('Y');

// =======================================================================
// A. INIT DASHBOARD (DATA UTAMA)
// =======================================================================
if ($type == 'init_dashboard') {
    
    // 1. OMZET (Net Income)
    $qOmzet = mysqli_query($conn, "SELECT SUM(net) as total FROM invoice_reports WHERE YEAR(doc_date) = '$year' AND is_deleted = 0");
    $dOmzet = mysqli_fetch_assoc($qOmzet);
    $totalOmzet = (float)($dOmzet['total'] ?? 0);
    
    // Hitung rata-rata bulanan (jika tahun berjalan, bagi bulan yg sudah lewat saja)
    $bulanPembagi = ($year == date('Y')) ? (int)date('n') : 12;
    $avgOmzet = $bulanPembagi > 0 ? $totalOmzet / $bulanPembagi : 0;

    // 2. PENDING (FIXED LOGIC)
    // Pending adalah: Belum ada sent_date (NULL/Kosong/'0000-00-00')
    $qPending = mysqli_query($conn, "SELECT SUM(net) as total, COUNT(*) as jumlah 
        FROM invoice_reports 
        WHERE YEAR(doc_date) = '$year' 
        AND is_deleted = 0 
        AND (sent_date IS NULL OR sent_date = '' OR sent_date = '0000-00-00')");
    
    $dPending = mysqli_fetch_assoc($qPending);
    $pendingTotal = (float)($dPending['total'] ?? 0);
    $pendingCount = (int)($dPending['jumlah'] ?? 0);
    
    // Ratio Pending vs Total Omzet
    $potensiTotal = $totalOmzet + $pendingTotal; // Total potensi duit masuk
    $pendingRatio = $potensiTotal > 0 ? round(($pendingTotal / $potensiTotal) * 100, 1) : 0;

    // 3. SUCCESS (TERKIRIM)
    $qSuccess = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM invoice_reports 
        WHERE YEAR(doc_date) = '$year' 
        AND is_deleted = 0 
        AND (sent_date IS NOT NULL AND sent_date != '' AND sent_date != '0000-00-00')");
    $successCount = (int)(mysqli_fetch_assoc($qSuccess)['jumlah'] ?? 0);
    
    $totalDocs = $successCount + $pendingCount;
    $successRate = $totalDocs > 0 ? round(($successCount / $totalDocs) * 100, 1) : 0;

    // 4. OPERASIONAL (DETAIL BREAKDOWN)
    // A. Alat
    $qAlat = mysqli_query($conn, "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status_alat = 'Baik' THEN 1 ELSE 0 END) as baik,
        SUM(CASE WHEN status_alat = 'Rusak' THEN 1 ELSE 0 END) as rusak
        FROM alat_kalibrasi");
    $dAlat = mysqli_fetch_assoc($qAlat);

    // B. Warning Kalibrasi (H-60)
    $qWarn = mysqli_query($conn, "SELECT COUNT(*) as warning FROM alat_kalibrasi WHERE tgl_kalibrasi BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)");
    $dWarn = mysqli_fetch_assoc($qWarn);

    // C. Doc Settlement Pending
    $qDoc = mysqli_query($conn, "SELECT COUNT(*) as pending FROM doc_settlemen WHERE no_transaksi IS NULL OR no_transaksi = ''");
    $dDoc = mysqli_fetch_assoc($qDoc);

    // D. Helper (Asumsi aktif semua untuk sederhana)
    $qHelp = mysqli_query($conn, "SELECT COUNT(*) as total FROM data_helper");
    $dHelp = mysqli_fetch_assoc($qHelp);

    // E. Overdue Delivery (Invoice Pending > 7 Hari dari Doc Date)
    $qOver = mysqli_query($conn, "SELECT COUNT(*) as total FROM invoice_reports 
        WHERE is_deleted = 0 
        AND (sent_date IS NULL OR sent_date = '' OR sent_date = '0000-00-00')
        AND doc_date < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $dOver = mysqli_fetch_assoc($qOver);


    // 5. CHART DATA
    // A. Bulanan
    $chartQuery = "SELECT MONTH(doc_date) as bulan, SUM(net) as omzet 
                   FROM invoice_reports 
                   WHERE YEAR(doc_date) = '$year' AND is_deleted = 0 
                   GROUP BY MONTH(doc_date) ORDER BY bulan ASC";
    $chartRes = mysqli_query($conn, $chartQuery);
    
    $listBulan = []; $listOmzet = [];
    for($i=1; $i<=12; $i++) { 
        $listBulan[] = date("M", mktime(0, 0, 0, $i, 10)); 
        $listOmzet[$i-1] = 0; // Index 0-11
    }
    if($chartRes) {
        while($row = mysqli_fetch_assoc($chartRes)) { 
            $idx = (int)$row['bulan'] - 1;
            if($idx >= 0 && $idx < 12) $listOmzet[$idx] = (float)$row['omzet']; 
        }
    }

    // B. Top Client
    $clientQuery = "SELECT customer_name, SUM(net) as total_duit 
                    FROM invoice_reports 
                    WHERE YEAR(doc_date) = '$year' AND is_deleted = 0 
                    GROUP BY customer_name 
                    ORDER BY total_duit DESC LIMIT 5"; // Top 5 aja biar rapi
    $clientRes = mysqli_query($conn, $clientQuery);
    $cLabels = []; $cFullNames = []; $cValues = [];
    if($clientRes) {
        while($row = mysqli_fetch_assoc($clientRes)) {
            $cFullNames[] = $row['customer_name'];
            // Pendekkan nama untuk label chart
            $namaSingkat = strlen($row['customer_name']) > 12 ? substr($row['customer_name'], 0, 12).'...' : $row['customer_name'];
            $cLabels[] = $namaSingkat;
            $cValues[] = (float)$row['total_duit'];
        }
    }

    echo json_encode([
        'year_display' => $year,
        'omzet' => [
            'total_formatted' => 'Rp ' . number_format($totalOmzet, 0, ',', '.'),
            'average_formatted' => 'Rp ' . number_format($avgOmzet, 0, ',', '.')
        ],
        'pending' => [
            'total_formatted' => 'Rp ' . number_format($pendingTotal, 0, ',', '.'),
            'count' => number_format($pendingCount),
            'ratio' => $pendingRatio
        ],
        'success' => [
            'count' => number_format($successCount),
            'rate' => $successRate
        ],
        'ops' => [
            'alat' => [
                'total' => number_format($dAlat['total']),
                'baik' => number_format($dAlat['baik']),
                'rusak' => number_format($dAlat['rusak'])
            ],
            'warning' => number_format($dWarn['warning']),
            'helper' => number_format($dHelp['total']),
            'doc_pending' => number_format($dDoc['pending']),
            'overdue' => number_format($dOver['total'])
        ],
        'charts' => [
            'bulan' => ['labels' => $listBulan, 'values' => array_values($listOmzet)],
            'client' => ['labels' => $cLabels, 'full_names' => $cFullNames, 'values' => $cValues]
        ]
    ]);
    exit;
}

// =======================================================================
// B. MODAL DETAIL (SAAT KLIK GRAFIK)
// =======================================================================
if ($type == 'detail_bulan' || $type == 'detail_client') {
    $param = isset($_POST['param']) ? mysqli_real_escape_string($conn, $_POST['param']) : '';
    
    $where = ""; $title = "";
    if ($type == 'detail_bulan') {
        // Param = Index Bulan (0-11) -> Jadi 1-12
        $bulanAngka = (int)$param + 1; 
        $namaBulan  = date("F", mktime(0, 0, 0, $bulanAngka, 10));
        $where = "WHERE MONTH(doc_date) = '$bulanAngka' AND YEAR(doc_date) = '$year' AND is_deleted = 0";
        $title = "Invoice Bulan: <b>$namaBulan $year</b>";
    } elseif ($type == 'detail_client') {
        $where = "WHERE customer_name = '$param' AND YEAR(doc_date) = '$year' AND is_deleted = 0";
        $title = "Invoice Client: <b>$param</b> ($year)";
    }

    $query = "SELECT * FROM invoice_reports $where ORDER BY doc_date DESC LIMIT 50";
    $result = mysqli_query($conn, $query);

    echo "<h3 class='text-lg font-bold text-white mb-4 border-b border-slate-700 pb-2 flex justify-between'>
            <span>$title</span>
            <span class='text-xs font-normal text-slate-400 mt-1'>Tahun $year</span>
          </h3>";
    
    echo "<div class='overflow-auto max-h-[400px] custom-scrollbar'>
            <table class='w-full text-left text-sm whitespace-nowrap'>
                <thead class='text-slate-400 font-bold text-xs uppercase bg-slate-800 sticky top-0'>
                    <tr>
                        <th class='p-3'>Tgl</th>
                        <th class='p-3'>No Doc</th>
                        <th class='p-3'>Customer</th>
                        <th class='p-3 text-right text-blue-400'>NET</th>
                        <th class='p-3 text-center'>Status</th>
                    </tr>
                </thead>
                <tbody class='divide-y divide-slate-800 text-slate-300'>";
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tgl = date('d/m', strtotime($row['doc_date']));
            $nilaiNet = number_format($row['net'], 0, ',', '.');
            
            // Cek status
            $isSent = ($row['sent_date'] && $row['sent_date'] != '0000-00-00');
            $statusBadge = $isSent 
                ? "<span class='text-emerald-400 text-[10px] border border-emerald-500/30 px-2 py-0.5 rounded bg-emerald-500/10'>Terkirim</span>" 
                : "<span class='text-yellow-400 text-[10px] border border-yellow-500/30 px-2 py-0.5 rounded bg-yellow-500/10'>Pending</span>";

            echo "<tr class='hover:bg-slate-700/50 transition'>
                    <td class='p-3'>$tgl</td>
                    <td class='p-3 font-mono text-xs text-white font-bold'>{$row['doc_no']}</td>
                    <td class='p-3 truncate max-w-[150px]' title='{$row['customer_name']}'>{$row['customer_name']}</td>
                    <td class='p-3 text-right font-mono'>$nilaiNet</td>
                    <td class='p-3 text-center'>$statusBadge</td>
                  </tr>";
        }
    } else { 
        echo "<tr><td colspan='5' class='p-8 text-center text-slate-500 italic'>Tidak ada data ditemukan.</td></tr>"; 
    }
    echo "</tbody></table></div>";
    exit;
}
?>