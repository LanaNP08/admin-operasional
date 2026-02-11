<div class="mb-8 flex flex-col md:flex-row justify-between items-center gap-6 animate-fade-in-up">
    <div class="w-full md:w-auto">
        <h1 class="text-3xl font-bold text-white tracking-tight">Monitoring Kalibrasi</h1>
        <p class="text-slate-400 text-sm mt-1">Pantau status alat dan jadwal kalibrasi secara real-time.</p>
    </div>
    
    <div class="flex gap-3 w-full md:w-auto">
        <form method="GET" action="" class="relative w-full md:w-64 group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <input type="text" name="cari" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search..." class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/50 border border-white/10 focus:ring-2 focus:ring-blue-500/50 focus:bg-slate-900 transition text-sm text-white placeholder-slate-500">
            <?php if($filterAktif != 'all'): ?>
                <input type="hidden" name="filter" value="<?php echo $filterAktif; ?>">
            <?php endif; ?>
        </form>

        <a href="export.php" target="_blank" class="bg-white/5 hover:bg-white/10 text-white border border-white/10 px-4 py-2.5 rounded-xl font-bold transition text-sm flex items-center gap-2">Export</a>
        
        <button onclick="bukaModalTambah()" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/20 transition transform active:scale-95 text-sm flex items-center gap-2">
            <span>+</span> Alat Baru
        </button>
    </div>
</div>

<?php if($notifPesan): ?>
    <div class="mb-6 p-4 rounded-xl flex items-center gap-3 backdrop-blur-md animate-fade-in-up <?php echo $notifTipe == 'success' ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-red-500/10 border border-red-500/30 text-red-400'; ?>">
        <span class="text-lg font-bold"><?php echo $notifTipe == 'success' ? '✓' : '✕'; ?></span>
        <div class="font-medium text-sm"><?php echo $notifPesan; ?></div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8 animate-fade-in-up" style="animation-delay: 0.1s;">
    <?php 
    $statCards = [
        ['label' => 'Total Aset', 'val' => $stats['total'], 'color' => 'blue', 'link' => 'all'],
        ['label' => 'Persiapan (H-60)', 'val' => $stats['warning'], 'color' => 'yellow', 'link' => 'warning'],
        ['label' => 'Overdue', 'val' => $stats['overdue'], 'color' => 'red', 'link' => 'overdue'],
        ['label' => 'Valid', 'val' => $stats['good'], 'color' => 'emerald', 'link' => 'good']
    ];
    foreach($statCards as $s): 
        $colorClass = $s['color'] == 'blue' ? 'text-blue-400' : ($s['color'] == 'yellow' ? 'text-yellow-400' : ($s['color'] == 'red' ? 'text-red-400' : 'text-emerald-400'));
        $activeClass = ($filterAktif == $s['link']) ? 'ring-2 ring-'.$s['color'].'-500 bg-white/10' : '';
    ?>
    <a href="monitoring.php?filter=<?php echo $s['link']; ?>" class="apple-card p-5 rounded-2xl group hover:bg-white/5 transition relative overflow-hidden <?php echo $activeClass; ?>">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity transform group-hover:scale-110">
            <svg class="w-16 h-16 <?php echo $colorClass; ?>" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm0 18a8 8 0 118-8 8 8 0 01-8 8z"/></svg>
        </div>
        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1"><?php echo $s['label']; ?></p>
        <h3 class="text-3xl font-bold text-white tracking-tight"><?php echo $s['val']; ?></h3>
    </a>
    <?php endforeach; ?>
</div>

<div class="apple-card rounded-2xl overflow-hidden flex flex-col h-[600px] animate-fade-in-up" style="animation-delay: 0.2s;">
    <div class="overflow-auto custom-scrollbar flex-grow relative">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="text-slate-400 uppercase font-bold text-[10px] tracking-wider sticky top-0 z-10 backdrop-blur-md bg-slate-900/80">
                <tr class="border-b border-white/5">
                    <th class="px-6 py-4">Kode / SN</th>
                    <th class="px-6 py-4">Nama Alat</th>
                    <th class="px-6 py-4">Merk / Type</th>
                    <th class="px-6 py-4">Lokasi</th>
                    <th class="px-6 py-4">Last Cal</th>
                    <th class="px-6 py-4">Next Cal</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-300">
                <?php foreach ($dataAlat as $row): 
                    // Logic Filter Tampilan
                    if ($filterAktif != 'all' && $row['kategori_filter'] != $filterAktif) continue;
                ?>
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-white font-semibold text-xs border-r border-white/5 bg-white/[0.01]"><?php echo $row['kode_sn']; ?></td>
                    <td class="px-6 py-4 font-medium text-white"><?php echo $row['nama_alat']; ?></td>
                    <td class="px-6 py-4 text-slate-400"><?php echo $row['merk_type']; ?></td>
                    <td class="px-6 py-4 text-slate-400"><?php echo $row['lokasi']; ?></td>
                    <td class="px-6 py-4 text-xs"><?php echo ($row['tgl_terakhir'] && $row['tgl_terakhir'] != '0000-00-00') ? date('d M Y', strtotime($row['tgl_terakhir'])) : '-'; ?></td>
                    <td class="px-6 py-4 text-xs font-bold <?php echo $row['badge_cal'] == 'bg-red-500 text-white' ? 'text-red-400' : 'text-blue-400'; ?>">
                        <?php echo date('d M Y', strtotime($row['tgl_kalibrasi'])); ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold border border-white/10 <?php echo str_replace('bg-', 'bg-opacity-20 text-', $row['badge_cal']); ?> bg-opacity-20 shadow-sm">
                            <?php echo $row['status_cal_text']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center flex justify-center gap-2">
                        <button onclick='bukaModalEdit(<?php echo json_encode($row); ?>)' class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>
                        <form method="POST" onsubmit="return confirm('Hapus alat ini?');" style="display:inline;">
                            <input type="hidden" name="id_hapus" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="btn_hapus" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($dataAlat)): ?>
                    <tr><td colspan="8" class="p-10 text-center text-slate-500 italic">Tidak ada data ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalTambah" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="apple-card w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden bg-[#0f172a] animate-fade-in-up">
        <div class="p-6 border-b border-white/10 bg-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Tambah Alat Baru</h3>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-white transition">✕</button>
        </div>
        <form method="POST" class="p-8 grid grid-cols-2 gap-6">
            <div class="space-y-4">
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Kode Kalibrasi</label><input type="text" name="kode_sn" required class="w-full px-4 py-3 text-sm"></div>
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Nama Alat</label><input type="text" name="nama_alat" required class="w-full px-4 py-3 text-sm"></div>
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Merk / Type</label><input type="text" name="merk_type" class="w-full px-4 py-3 text-sm"></div>
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Lokasi</label><input type="text" name="lokasi" required class="w-full px-4 py-3 text-sm"></div>
            </div>
            <div class="space-y-4">
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Tgl Terakhir</label><input type="date" name="tgl_terakhir" class="w-full px-4 py-3 text-sm text-slate-300 datepicker"></div>
                <div><label class="text-[10px] text-blue-400 font-bold uppercase mb-1 block">Next Kalibrasi</label><input type="date" name="tgl_kalibrasi" required class="w-full px-4 py-3 text-sm bg-blue-500/10 border-blue-500/30 text-blue-100 datepicker"></div>
                <div>
                 <label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Kondisi</label>
                 <select name="kondisi" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 text-sm rounded-xl focus:border-blue-500 outline-none">
                 <option value="Baik" class="bg-slate-900">Baik</option>
                 <option value="Rusak" class="bg-slate-900">Rusak</option>
                 <option value="Hilang" class="bg-slate-900">Hilang</option>
                 </select>
                 </div>
            </div>
            <div class="col-span-2 flex justify-end gap-3 mt-4 pt-4 border-t border-white/10">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-6 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 font-medium text-sm transition">Batal</button>
                <button type="submit" name="btn_tambah" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 text-sm transition">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="apple-card w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden bg-[#0f172a]">
        <div class="p-6 border-b border-white/10 bg-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Edit Data Alat</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-slate-400 hover:text-white transition">✕</button>
        </div>
        <form method="POST" class="p-8 grid grid-cols-2 gap-6">
            <input type="hidden" name="id_edit" id="id_edit">
            <div class="space-y-4">
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Kode Kalibrasi</label><input type="text" name="kode_edit" id="kode_edit" required class="w-full px-4 py-3 text-sm"></div>
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Nama Alat</label><input type="text" name="nama_edit" id="nama_edit" required class="w-full px-4 py-3 text-sm"></div>
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Merk / Type</label><input type="text" name="merk_edit" id="merk_edit" class="w-full px-4 py-3 text-sm"></div>
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Lokasi</label><input type="text" name="lokasi_edit" id="lokasi_edit" required class="w-full px-4 py-3 text-sm"></div>
            </div>
            <div class="space-y-4">
                <div><label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Tgl Terakhir</label><input type="text" name="tgl_last_edit" id="tgl_last_edit" class="w-full px-4 py-3 text-sm text-slate-300 datepicker"></div>
                <div><label class="text-[10px] text-blue-400 font-bold uppercase mb-1 block">Next Kalibrasi</label><input type="text" name="tgl_next_edit" id="tgl_next_edit" required class="w-full px-4 py-3 text-sm bg-blue-500/10 border-blue-500/30 text-blue-100 datepicker"></div>
                <div>
                <label class="text-[10px] text-slate-400 font-bold uppercase mb-1 block">Kondisi</label>
                <select name="kondisi_edit" id="kondisi_edit" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 text-sm rounded-xl focus:border-blue-500 outline-none">
                <option value="Baik" class="bg-slate-900">Baik</option>
                <option value="Rusak" class="bg-slate-900">Rusak</option>
                <option value="Hilang" class="bg-slate-900">Hilang</option>
                </select>
            </div>
            </div>
            <div class="col-span-2 flex justify-end gap-3 mt-4 pt-4 border-t border-white/10">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-6 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 font-medium text-sm transition">Batal</button>
                <button type="submit" name="btn_simpan_edit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 text-sm transition">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalTambah() { document.getElementById('modalTambah').classList.remove('hidden'); }
function bukaModalEdit(data) {
    document.getElementById('id_edit').value = data.id;
    document.getElementById('kode_edit').value = data.kode_sn;
    document.getElementById('nama_edit').value = data.nama_alat;
    document.getElementById('merk_edit').value = data.merk_type;
    document.getElementById('lokasi_edit').value = data.lokasi;
    
    // Set value untuk flatpickr (jika ada) atau input biasa
    const dateLast = document.getElementById('tgl_last_edit');
    const dateNext = document.getElementById('tgl_next_edit');
    
    dateLast.value = data.tgl_terakhir;
    dateNext.value = data.tgl_kalibrasi;
    
    // Jika menggunakan flatpickr, update instancenya
    if(dateLast._flatpickr) dateLast._flatpickr.setDate(data.tgl_terakhir);
    if(dateNext._flatpickr) dateNext._flatpickr.setDate(data.tgl_kalibrasi);

    document.getElementById('kondisi_edit').value = data.status_alat;
    document.getElementById('modalEdit').classList.remove('hidden');
}

// Close modal on click outside
window.onclick = function(e) {
    if(e.target.id == 'modalTambah') document.getElementById('modalTambah').classList.add('hidden');
    if(e.target.id == 'modalEdit') document.getElementById('modalEdit').classList.add('hidden');
}
</script>