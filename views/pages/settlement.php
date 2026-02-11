<div class="flex flex-col h-[calc(100vh-140px)] animate-fade-in-up">

    <div class="mb-4 flex flex-col md:flex-row justify-between items-center gap-4 shrink-0">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <button onclick="toggleImport()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg border border-indigo-500 shadow transition font-bold text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Import Excel
            </button>
            <form method="POST" onsubmit="return confirm('⚠️ Yakin hapus SEMUA DATA?');">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="btn_reset" class="bg-red-900/50 hover:bg-red-800 text-red-200 p-2 rounded-lg border border-red-800 transition" title="Kosongkan Database">🗑️</button>
            </form>
            <div>
                <h1 class="text-xl font-bold text-white flex items-center gap-2">
                    Settlement
                    <span class="bg-slate-800 text-slate-400 text-xs px-2 py-1 rounded-full border border-slate-700">
                        <?php echo number_format($viewData['totalData']); ?> Data
                    </span>
                </h1>
            </div>
        </div>
        <?php if($viewData['pesan']): ?>
            <div class="px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 animate-pulse <?php echo $viewData['tipePesan'] == 'success' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/50' : ($viewData['tipePesan'] == 'info' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/50' : 'bg-red-500/20 text-red-300'); ?>">
                <?php echo $viewData['pesan']; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 shadow-sm mb-4 shrink-0">
        <form method="GET" action="" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="cari" value="<?php echo htmlspecialchars($viewData['filters']['cari']); ?>" placeholder="Cari..." class="w-full bg-slate-950 border border-slate-700 text-white text-xs px-3 py-2 rounded-lg outline-none focus:border-indigo-500">
            </div>
            
            <label class="flex items-center gap-2 cursor-pointer bg-slate-800 px-3 py-2 rounded-lg border border-slate-700 hover:bg-slate-700 transition select-none">
                <input type="checkbox" name="f_empty_trans" value="1" onchange="this.form.submit()" <?php echo $viewData['filters']['f_empty_trans'] ? 'checked' : ''; ?> class="rounded text-indigo-500 focus:ring-0 bg-slate-900 border-slate-600">
                <span class="text-xs font-bold text-slate-300">⚠️ Hanya yg belum ada No Transaksi</span>
            </label>

            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg shadow text-xs font-bold">Filter</button>
            <?php if(!empty($viewData['filters']['cari']) || $viewData['filters']['f_empty_trans']): ?>
                <a href="settlemen.php" class="text-xs text-red-400 underline">Reset</a>
            <?php endif; ?>
            <div class="h-6 w-px bg-slate-700 mx-2"></div>
            <a href="export_settlemen.php" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2 text-xs transition">Excel</a>
        </form>
    </div>

    <div id="area_import" class="hidden mb-4 shrink-0 bg-slate-900 p-4 rounded-xl border border-slate-700 shadow-2xl relative animate-fade-in-up">
        <button onclick="toggleImport()" class="absolute top-2 right-2 text-slate-500 hover:text-white">✕</button>
        <h3 class="text-white font-bold mb-2 text-sm">Paste Excel</h3>
        <p class="text-[10px] text-slate-400 mb-2">Urutan: <b>Doc No | Date | Info | Amount | Desc</b></p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <textarea name="raw_data" rows="5" class="w-full bg-slate-950 border border-slate-700 text-white text-xs p-3 rounded focus:border-indigo-500 font-mono" placeholder="Paste data..."></textarea>
            <div class="mt-2 flex justify-end">
                <button type="submit" name="btn_preview_import" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded font-bold text-xs shadow">Preview</button>
            </div>
        </form>
    </div>

    <?php if(!empty($viewData['importPreview'])): ?>
    <div class="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-slate-900 w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden border border-slate-700 flex flex-col max-h-[90vh]">
            <div class="bg-indigo-600 p-4 flex justify-between items-center">
                <h3 class="text-white font-bold">Konfirmasi</h3>
                <a href="settlemen.php" class="text-white hover:text-red-200">✕ Batal</a>
            </div>
            <div class="p-4 overflow-auto custom-scrollbar flex-1 bg-slate-950">
                <table class="w-full text-xs text-left border-collapse text-slate-300">
                    <thead class="bg-slate-800 text-slate-400 uppercase sticky top-0">
                        <tr>
                            <th class="p-2 border border-slate-700">Doc No</th>
                            <th class="p-2 border border-slate-700">Info</th>
                            <th class="p-2 border border-slate-700 text-right">Amount</th>
                            <th class="p-2 border border-slate-700 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $adaDuplikat = false; foreach($viewData['importPreview'] as $row): 
                            if($row['is_duplicate']) $adaDuplikat = true;
                        ?>
                        <tr class="border-b border-slate-800">
                            <td class="p-2 font-mono"><?php echo $row['doc_no']; ?></td>
                            <td class="p-2"><?php echo $row['information']; ?></td>
                            <td class="p-2 text-right"><?php echo number_format($row['amount_cr']); ?></td>
                            <td class="p-2 text-center font-bold">
                                <?php echo $row['is_duplicate'] ? '<span class="text-red-400">DUPLIKAT</span>' : '<span class="text-green-400">BARU</span>'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800 bg-slate-900 flex justify-between items-center">
                <div class="flex flex-col">
                    <?php if($adaDuplikat): ?>
                    <span class="text-xs font-bold text-red-400 mb-1">Ada Duplikat!</span>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="duplicate_mode_radio" checked onchange="setMode('skip')" class="text-indigo-500"><span class="text-sm text-slate-300">Skip</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="duplicate_mode_radio" onchange="setMode('update')" class="text-red-500"><span class="text-sm text-slate-300">Timpa</span></label>
                    </div>
                    <?php else: ?><span class="text-xs text-green-400">Aman.</span><?php endif; ?>
                </div>
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="json_data" value='<?php echo json_encode($viewData['importPreview']); ?>'>
                    <input type="hidden" name="duplicate_mode" id="duplicate_mode_input" value="skip">
                    <button type="submit" name="btn_save_import" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded font-bold shadow text-sm">✅ Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" id="form_tabel" class="flex-grow flex flex-col min-h-0 bg-slate-900 rounded-xl border border-slate-800 shadow-xl overflow-hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="btn_simpan_transaksi" value="1">
        <input type="hidden" name="id_delete" id="input_del_id">
        <input type="hidden" name="btn_delete" id="btn_delete_trigger" disabled>

        <div class="overflow-auto flex-grow custom-scrollbar relative h-full">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-800 text-slate-300 uppercase font-bold text-xs sticky top-0 z-10 shadow-md">
                    <tr>
                        <th class="px-4 py-3 border-r border-slate-700 bg-slate-800">Doc No</th>
                        <th class="px-4 py-3 border-r border-slate-700 bg-slate-800">Date</th>
                        <th class="px-4 py-3 border-r border-slate-700 bg-slate-800">Information</th>
                        <th class="px-4 py-3 border-r border-slate-700 bg-slate-800 text-right">Amount</th>
                        <th class="px-4 py-3 border-r border-slate-700 bg-slate-800">Description</th>
                        <th class="px-4 py-3 bg-indigo-900/30 text-indigo-200 text-center border-l-2 border-indigo-500">No Transaksi</th>
                        <th class="px-4 py-3 text-center bg-slate-800">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php foreach ($viewData['data'] as $row): 
                        // Key
                        $keyId = $row['doc_no']; 
                        
                        // [FITUR] Logic Visual Lunas
                        $isLunas = !empty($row['no_transaksi']);
                        
                        // Style Baris (Kalau lunas, hijau tipis)
                        $rowClass = $isLunas ? "bg-emerald-900/10 border-l-4 border-l-emerald-500" : "hover:bg-slate-800/50 transition";
                        
                        // Style Input (Kalau lunas, hijau tebal)
                        $inputBorder = $isLunas ? "border-emerald-600 bg-emerald-900/30 text-emerald-300 font-bold" : "border-slate-600 bg-slate-800 text-white placeholder-slate-500";
                    ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td class="px-4 py-2 border-r border-slate-800 text-xs font-bold font-mono text-slate-300">
                            <?php echo $row['doc_no']; ?>
                            <?php if($isLunas) echo '<span class="ml-1 text-emerald-500" title="Sudah Lunas">✅</span>'; ?>
                        </td>
                        <td class="px-4 py-2 border-r border-slate-800 text-xs text-slate-400"><?php echo date('d/m/y', strtotime($row['doc_date'])); ?></td>
                        <td class="px-4 py-2 border-r border-slate-800">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded border <?php echo $row['class']; ?>"><?php echo $row['badge']; ?></span>
                            <div class="text-xs mt-1 text-slate-400"><?php echo htmlspecialchars($row['information']); ?></div>
                        </td>
                        <td class="px-4 py-2 border-r border-slate-800 text-right font-mono text-xs font-bold text-slate-200"><?php echo number_format($row['amount_cr'], 0, ',', '.'); ?></td>
                        <td class="px-4 py-2 border-r border-slate-800 text-xs text-slate-400 truncate max-w-xs"><?php echo htmlspecialchars($row['description']); ?></td>
                        
                        <td class="px-2 py-1 bg-slate-900/50 text-center border-l border-slate-800">
                            <input type="text" name="transaksi[<?php echo $keyId; ?>]" value="<?php echo htmlspecialchars($row['no_transaksi'] ?? ''); ?>" class="w-full text-center text-xs rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm border <?php echo $inputBorder; ?>" placeholder="-">
                        </td>
                        
                        <td class="px-2 py-1 text-center">
                            <button type="button" onclick="hapusData('<?php echo $keyId; ?>')" class="text-slate-600 hover:text-red-500 transition p-1">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>

    <div class="bg-slate-900 p-3 border-t border-slate-800 text-xs flex justify-between items-center sticky bottom-0 z-30 shrink-0">
        <button onclick="document.getElementById('form_tabel').submit()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-bold shadow text-xs transition">💾 Simpan No. Transaksi</button>

        <?php if($viewData['totalPages'] > 1): ?>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex items-center">
                <?php foreach($_GET as $k=>$v){ if($k!='page') echo "<input type='hidden' name='$k' value='$v'>"; } ?>
                <span class="mr-1 text-slate-500">Hal</span>
                <input type="number" name="page" value="<?php echo $viewData['page']; ?>" min="1" max="<?php echo $viewData['totalPages']; ?>" class="w-12 text-center bg-slate-950 border border-slate-700 text-white rounded py-1 text-xs outline-none">
                <button type="submit" class="ml-1 px-2 py-1 bg-indigo-900/50 text-indigo-300 border border-indigo-500/30 rounded text-xs font-bold hover:bg-indigo-900">Go</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function toggleImport() { document.getElementById('area_import').classList.toggle('hidden'); }
    function setMode(val) { document.getElementById('duplicate_mode_input').value = val; }
    function hapusData(docNo) {
        if(confirm('Yakin ingin menghapus data ' + docNo + '?')) {
            document.getElementById('input_del_id').value = docNo;
            const btn = document.getElementById('btn_delete_trigger');
            btn.disabled = false; btn.click();
        }
    }
</script>