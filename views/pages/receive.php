<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Konfirmasi Penerimaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8fafc; } @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } } .animate-enter { animation: fadeIn 0.5s ease-out forwards; }</style>
</head>
<body class="min-h-screen flex flex-col justify-center py-6 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6 animate-enter">
        <img class="mx-auto h-20 w-auto object-contain drop-shadow-sm" src="public/assets/img/logo.png" alt="Logo Perusahaan">
        <h2 class="mt-4 text-center text-xl font-bold tracking-tight text-slate-800">Sistem Pelacakan Dokumen</h2>
        <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1">Ref Batch: <?php echo $code; ?></p>
    </div>
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-enter" style="animation-delay: 0.1s;">
        <div class="bg-white py-8 px-6 shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] rounded-2xl border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-50 rounded-full blur-2xl opacity-50"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-purple-50 rounded-full blur-3xl opacity-50"></div>
            <div class="relative z-10">
                <?php if (!$batch): ?>
                    <div class="text-center py-10"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-4"><svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></div><h3 class="text-lg font-bold text-slate-900">Data Tidak Ditemukan</h3><p class="mt-2 text-sm text-slate-500">Kode QR tidak valid atau batch pengiriman telah dihapus.</p></div>
                <?php elseif (!empty($batch['received_at']) && $batch['received_at'] != '0000-00-00 00:00:00' && !isset($_GET['edit_mode'])): ?>
                    <div class="text-center">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 mb-6 shadow-inner ring-4 ring-white"><svg class="h-10 w-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></div>
                        <h3 class="text-2xl font-extrabold text-slate-900">Paket Diterima</h3><p class="text-sm text-slate-500 mt-1">Terima kasih telah melakukan konfirmasi.</p>
                        <div class="mt-8 bg-slate-50 rounded-xl p-5 text-left border border-slate-200">
                            <div class="flex justify-between items-start mb-4 border-b border-slate-200 pb-4"><div><p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Penerima</p><p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($batch['recipient_name']); ?></p></div><div class="text-right"><p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Waktu</p><p class="text-sm font-mono font-semibold text-blue-600"><?php echo date('H:i', strtotime($batch['received_at'])); ?></p><p class="text-[10px] text-slate-500"><?php echo date('d M Y', strtotime($batch['received_at'])); ?></p></div></div>
                            <?php if($batch['proof_photo']): ?><div><p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2">Foto Bukti</p><img src="uploads/<?php echo $batch['proof_photo']; ?>" class="w-full h-32 object-cover rounded-lg border border-slate-200 shadow-sm opacity-90 hover:opacity-100 transition"></div><?php endif; ?>
                        </div>
                        <div class="mt-8"><a href="?action=receive&code=<?php echo $code; ?>&edit_mode=1" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-blue-600 transition font-medium px-4 py-2 rounded-full hover:bg-slate-50">Koreksi Data Penerima</a></div>
                    </div>
                <?php else: ?>
                    <div class="text-center mb-8"><h3 class="text-xl font-bold text-slate-900">Konfirmasi Penerimaan</h3><p class="text-sm text-slate-500 mt-1">Mohon lengkapi data di bawah ini.</p></div>
                    <?php if($pesan): ?><div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700 font-medium border border-green-200 text-center animate-pulse"><?php echo $pesan; ?></div><?php endif; ?>
                    <form class="space-y-6" method="POST" enctype="multipart/form-data">
                        <div><label class="block text-xs font-bold text-slate-700 uppercase mb-2 ml-1">Nama Penerima / Security</label><input type="text" name="recipient_name" required class="block w-full rounded-xl border-slate-300 pl-3 focus:border-blue-500 focus:ring-blue-500 py-3 text-sm bg-slate-50" placeholder="Contoh: Bpk. Ujang (Security)" value="<?php echo isset($batch['recipient_name']) ? $batch['recipient_name'] : ''; ?>"></div>
                        <div><label class="block text-xs font-bold text-slate-700 uppercase mb-2 ml-1">Foto Bukti / Lokasi</label><label class="flex justify-center rounded-xl border-2 border-dashed border-slate-300 px-6 pt-5 pb-6 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition group"><div class="space-y-1 text-center"><div class="flex text-sm text-slate-600 justify-center"><span class="relative rounded-md bg-white font-medium text-blue-600 px-2">Ambil Foto</span></div><p class="text-xs text-slate-400">Ketuk untuk buka kamera</p><p id="file-chosen" class="text-xs font-bold text-emerald-600 mt-2 min-h-[1.5em]"></p></div><input type="file" name="proof_photo" class="sr-only" accept="image/*" capture="environment" onchange="updateFileName(this)"></label></div>
                        <button type="submit" name="btn_terima" class="flex w-full justify-center rounded-xl border border-transparent bg-slate-900 py-4 px-4 text-sm font-bold text-white shadow-lg hover:bg-slate-800 transition"><?php echo ($batch['received_at']) ? 'SIMPAN PERUBAHAN' : 'KONFIRMASI TERIMA BARANG'; ?></button>
                        <?php if ($batch['received_at']): ?><a href="?action=receive&code=<?php echo $code; ?>" class="block w-full text-center mt-4 text-sm text-slate-400 hover:text-slate-600">Batalkan Edit</a><?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <p class="mt-8 text-center text-xs text-slate-400 font-medium">&copy; <?php echo date('Y'); ?> Internal Logistic System <br>Secure Document Tracking</p>
    </div>
    <script>function updateFileName(input) { const fileChosen = document.getElementById('file-chosen'); if (input.files && input.files.length > 0) { fileChosen.textContent = "✅ " + input.files[0].name; fileChosen.classList.add('animate-pulse'); } else { fileChosen.textContent = ""; } }</script>
</body>
</html>