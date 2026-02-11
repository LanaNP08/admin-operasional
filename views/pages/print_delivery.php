<?php
// views/pages/print_delivery.php
$alamatTujuan = "";
if (!empty($batch['delivery_address'])) {
    $alamatTujuan = nl2br(htmlspecialchars($batch['delivery_address']));
} else {
    $custName = !empty($invoices) ? $invoices[0]['customer_name'] : "Finance Dept";
    $alamatTujuan = "<strong>" . $custName . "</strong><br><span class='text-xs'>UP: Finance / Purchasing Dept.</span>";
}
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']); 
$scan_url = $base_url . "/invoice.php?action=receive&code=" . $code;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Doc Delivery - <?php echo $code; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; color: #1f2937; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sticker-container { break-inside: avoid; page-break-inside: avoid; margin-top: 20px; }
        }
        .cut-line { border-top: 2px dashed #9ca3af; height: 20px; position: relative; text-align: center; margin: 40px 0; }
        .cut-text { background: #fff; padding: 0 15px; position: relative; top: -12px; font-size: 10px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-[210mm] mx-auto bg-white p-10 shadow-xl border border-gray-200 min-h-screen relative">
        <div class="flex justify-between items-start border-b-2 border-gray-800 pb-6 mb-8">
            <div class="flex items-center gap-5">
                <img src="public/assets/img/logo.png" alt="Logo" class="h-16 w-auto object-contain">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 uppercase tracking-tight">Tanda Terima Dokumen</h1>
                    <p class="text-sm text-gray-500 font-semibold mt-1">Ref No: #<?php echo $code; ?></p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-bold text-gray-800">Admin Operasional</p>
                <p class="text-sm text-gray-500"><?php echo date('d F Y', strtotime($batch['created_at'])); ?></p>
            </div>
        </div>
        <div class="mb-10">
            <p class="mb-3 text-sm text-gray-600">Mohon diterima dokumen invoice asli berikut ini:</p>
            <table class="w-full text-sm border-collapse border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2 text-center w-12">No</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">No Invoice</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Customer</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($invoices as $row): ?>
                    <tr class="even:bg-gray-50">
                        <td class="border border-gray-300 px-3 py-2 text-center"><?php echo $no++; ?></td>
                        <td class="border border-gray-300 px-3 py-2 font-mono font-bold text-slate-700"><?php echo $row['doc_no']; ?></td>
                        <td class="border border-gray-300 px-3 py-2"><?php echo $row['customer_name']; ?></td>
                        <td class="border border-gray-300 px-3 py-2 text-gray-500 truncate max-w-xs"><?php echo $row['remarks']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-end pb-4">
            <div class="text-center">
                 <div class="border border-gray-200 p-1 inline-block rounded">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo urlencode($scan_url); ?>" alt="Scan QR" class="w-16 h-16 opacity-60">
                </div>
                <p class="text-[9px] text-gray-400 mt-1">Internal Use</p>
            </div>
            <div class="text-center w-64">
                <p class="text-sm text-gray-600 mb-12">Diterima Oleh,</p>
                <div class="border-b border-gray-400 border-dashed"></div>
                <p class="text-xs text-gray-400 mt-2">( Nama Jelas, Tanda Tangan & Stempel )</p>
            </div>
        </div>
        <div class="cut-line">
            <span class="cut-text">✂️ POTONG DI SINI - TEMPEL BAGIAN BAWAH PADA AMPLOP</span>
        </div>
        <div class="sticker-container border-2 border-slate-800 rounded-lg overflow-hidden bg-white">
            <div class="bg-slate-800 text-white p-3 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="public/assets/img/logo.png" class="h-6 w-auto bg-white p-0.5 rounded-sm"> <span class="font-bold tracking-wider text-sm">DIGITAL DOCUMENT TRACKING</span>
                </div>
                <span class="text-xs font-mono text-slate-300"><?php echo $code; ?></span>
            </div>
            <div class="p-6 flex flex-row">
                <div class="w-2/3 pr-6 flex flex-col justify-between">
                    <div>
                        <p class="text-[15px] font-bold text-slate-500 uppercase mb-2">DESTINATION / TUJUAN:</p>
                        <div class="text-[15px] text-slate-900 leading-snug border border-slate-200 bg-slate-50 p-3 rounded-lg min-h-[80px]">
                            <?php echo $alamatTujuan; ?>
                        </div>
                    </div>
                    <div class="mt-6 border-l-4 border-blue-600 pl-3">
                        <p class="text-xs font-bold text-blue-700 uppercase mb-1">PENTING - KONFIRMASI PENERIMAAN</p>
                        <p class="text-sm text-slate-600 leading-snug">
                            Mohon bantuan <b>Security / Resepsionis</b> untuk scan QR Code di samping guna update status kedatangan dokumen ini secara otomatis.
                        </p>
                    </div>
                </div>
                <div class="w-1/3 flex flex-col items-center justify-center border-l border-dashed border-slate-300 pl-4">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($scan_url); ?>" alt="Scan QR" class="w-32 h-32 mb-2">
                    <div class="bg-slate-800 text-white px-3 py-1 rounded-full text-[10px] font-bold">
                        SCAN ME
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="fixed bottom-8 right-8 no-print flex gap-3">
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-full shadow-lg text-sm font-bold">Tutup</button>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full shadow-xl font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"></path></svg>
            Cetak Dokumen
        </button>
    </div>
</body>
</html>