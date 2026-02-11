<?php
// public_html/verify/document.php

// 1. Load Database Only (No Auth)
require_once '../app/config/database.php';

$token = isset($_GET['token']) ? trim(mysqli_real_escape_string($conn, $_GET['token'])) : '';
$data = null;
$isValidToken = false;

// 2. Cek Token
if (!empty($token)) {
    // [MODIFIKASI] Hapus filter "AND d.status = 'active'" agar status lain tetap terbaca
    $query = "SELECT d.*, b.blanko_number 
              FROM documents d 
              JOIN blankos b ON d.blanko_id = b.id 
              WHERE d.qr_token = '$token' 
              LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $isValidToken = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen - Official</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
        
        <div class="bg-slate-900 p-6 text-center">
            <img src="../public/assets/img/logo.png" alt="Company Logo" class="h-16 mx-auto mb-4 object-contain">
            <h1 class="text-slate-400 text-xs font-bold tracking-[0.2em] uppercase mb-1">System Verification</h1>
            <p class="text-white font-bold text-lg">Document Authenticity</p>
        </div>

        <div class="p-8">
            <?php if ($isValidToken && $data): ?>
                
                <?php if ($data['status'] === 'valid'): ?>
                    <div class="flex flex-col items-center justify-center text-center mb-8">
                        <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30 mb-4">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-emerald-600">DOKUMEN ASLI</h2>
                        <p class="text-sm text-slate-500 font-medium mt-1">Status: <span class="uppercase font-bold">VALID</span></p>
                    </div>

                <?php else: ?>
                    <div class="flex flex-col items-center justify-center text-center mb-8">
                        <div class="w-20 h-20 bg-red-600 rounded-full flex items-center justify-center shadow-lg shadow-red-600/30 mb-4">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-red-600">DOKUMEN TIDAK BERLAKU</h2>
                        <p class="text-sm text-red-800 font-bold mt-1 bg-red-100 px-3 py-1 rounded-lg uppercase">
                            STATUS: <?php echo $data['status']; ?>
                        </p>
                        <p class="text-xs text-slate-500 mt-2 px-4">
                            Dokumen ini tercatat di sistem kami namun statusnya telah <strong>dicabut</strong> atau <strong>dibatalkan</strong>.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="space-y-4 opacity-90">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Nomor Sertifikat</p>
                        <p class="text-lg font-bold text-slate-800 font-mono break-words"><?php echo htmlspecialchars($data['certificate_number']); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Job / SO No.</p>
                            <p class="text-sm font-bold text-slate-800 break-words break-all"><?php echo htmlspecialchars($data['job_number']); ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Blanko ID</p>
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($data['blanko_number']); ?></p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Tanggal Selesai</p>
                        <p class="text-sm font-bold text-slate-800"><?php echo date('d F Y', strtotime($data['completed_date'])); ?></p>
                    </div>
                </div>

            <?php else: ?>

                <div class="flex flex-col items-center justify-center text-center py-10">
                    <div class="w-20 h-20 bg-gray-400 rounded-full flex items-center justify-center shadow-lg shadow-gray-400/30 mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-600 mb-2">DATA TIDAK DITEMUKAN</h2>
                    <p class="text-slate-500 mb-6 px-4 text-sm">
                        QR Code ini tidak terdaftar di sistem kami.
                    </p>
                </div>

            <?php endif; ?>
        </div>
        
        <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
            <p class="text-[10px] text-slate-400 font-bold">&copy; <?php echo date('Y'); ?> Operational System Verification</p>
        </div>

    </div>

</body>
</html>