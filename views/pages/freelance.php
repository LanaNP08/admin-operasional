<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 animate-fade-in-up">
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Data Karyawan Freelance</h1>
        <p class="text-slate-400 text-sm mt-1">Manajemen data helper dan teknisi freelance.</p>
    </div>
    <button onclick="bukaModalTambah()" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Data
    </button>
</div>

<?php if (!empty($notifPesan)): ?>
    <div class="mb-6 animate-fade-in-up">
        <div class="p-4 rounded-xl border <?php echo ($notifTipe == 'success') ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border-red-500/20 text-red-400'; ?> flex items-center gap-3">
            <span class="text-xl"><?php echo ($notifTipe == 'success') ? '✅' : '❌'; ?></span>
            <p class="text-sm font-medium"><?php echo $notifPesan; ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="glass-panel rounded-2xl overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s;">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-slate-900/40 text-xs uppercase tracking-wider text-slate-400">
                    <th class="p-4 font-semibold">Nama Lengkap</th>
                    <th class="p-4 font-semibold">Posisi / Cabang</th>
                    <th class="p-4 font-semibold">NIK</th>
                    <th class="p-4 font-semibold">Bank Info</th>
                    <th class="p-4 font-semibold">Bergabung</th>
                    <th class="p-4 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm">
                <?php if (count($dataFreelance) > 0): ?>
                    <?php foreach ($dataFreelance as $row): ?>
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="p-4">
                                <div class="font-bold text-white"><?php echo $row['nama_lengkap']; ?></div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs text-slate-300">
                                    <?php echo $row['cabang_posisi']; ?>
                                </span>
                            </td>
                            <td class="p-4 text-slate-400 font-mono"><?php echo $row['nik']; ?></td>
                            <td class="p-4 text-slate-400">
                                <div class="text-white text-xs font-bold"><?php echo $row['nama_bank']; ?></div>
                                <div class="font-mono text-xs mt-0.5"><?php echo $row['no_rekening']; ?></div>
                                <div class="text-[10px] uppercase mt-0.5 opacity-60"><?php echo $row['nama_rekening']; ?></div>
                            </td>
                            <td class="p-4 text-slate-400">
                                <?php echo date('d M Y', strtotime($row['tgl_bergabung'])); ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick='bukaModalEdit(<?php echo json_encode($row); ?>)' 
                                            class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <a href="freelance.php?hapus_id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Yakin ingin menghapus data <?php echo $row['nama_lengkap']; ?>?')" 
                                       class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">
                            Belum ada data freelance. Silakan tambah data baru.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalTambah" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="glass-panel w-full max-w-2xl rounded-2xl shadow-2xl p-6 relative animate-fade-in-up">
        <h3 class="text-xl font-bold text-white mb-6 border-b border-white/10 pb-4">Tambah Freelance Baru</h3>
        
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">NIK (KTP)</label>
                    <input type="number" name="nik" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Posisi / Cabang</label>
                    <input type="text" name="posisi" placeholder="Contoh: Helper Jakarta" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Tanggal Bergabung</label>
                    <input type="date" name="tgl_gabung" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition datepicker">
                </div>
            </div>

            <h4 class="text-sm font-bold text-blue-400 uppercase tracking-widest mb-3">Informasi Rekening</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Nama Bank</label>
                    <input type="text" name="bank" placeholder="BCA/Mandiri" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">No. Rekening</label>
                    <input type="number" name="norek" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Atas Nama</label>
                    <input type="text" name="an_rek" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-5 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition">Batal</button>
                <button type="submit" name="btn_tambah" class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg transition">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="glass-panel w-full max-w-2xl rounded-2xl shadow-2xl p-6 relative animate-fade-in-up">
        <h3 class="text-xl font-bold text-white mb-6 border-b border-white/10 pb-4">Edit Data Freelance</h3>
        
        <form method="POST" action="">
            <input type="hidden" name="id_edit" id="id_edit">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_edit" id="nama_edit" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">NIK (KTP)</label>
                    <input type="number" name="nik_edit" id="nik_edit" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Posisi / Cabang</label>
                    <input type="text" name="posisi_edit" id="posisi_edit" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Tanggal Bergabung</label>
                    <input type="text" name="tgl_gabung_edit" id="tgl_gabung_edit" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition datepicker">
                </div>
            </div>

            <h4 class="text-sm font-bold text-blue-400 uppercase tracking-widest mb-3">Informasi Rekening</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Nama Bank</label>
                    <input type="text" name="bank_edit" id="bank_edit" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">No. Rekening</label>
                    <input type="number" name="norek_edit" id="norek_edit" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Atas Nama</label>
                    <input type="text" name="an_rek_edit" id="an_rek_edit" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-2 text-white focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-5 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition">Batal</button>
                <button type="submit" name="btn_update" class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg transition">Update Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalTambah() {
        document.getElementById('modalTambah').classList.remove('hidden');
    }
    
    function bukaModalEdit(data) {
        document.getElementById('id_edit').value = data.id;
        document.getElementById('nama_edit').value = data.nama_lengkap;
        document.getElementById('nik_edit').value = data.nik;
        document.getElementById('posisi_edit').value = data.cabang_posisi;
        document.getElementById('tgl_gabung_edit').value = data.tgl_bergabung;
        document.getElementById('bank_edit').value = data.nama_bank;
        document.getElementById('norek_edit').value = data.no_rekening;
        document.getElementById('an_rek_edit').value = data.nama_rekening;

        // Reset Datepicker jika perlu (untuk Flatpickr instance jika ada)
        // const fp = document.querySelector("#tgl_gabung_edit")._flatpickr;
        // if(fp) fp.setDate(data.tgl_bergabung);

        document.getElementById('modalEdit').classList.remove('hidden');
    }

    // Close Modal on Click Outside
    window.onclick = function(event) {
        const m1 = document.getElementById('modalTambah');
        const m2 = document.getElementById('modalEdit');
        if (event.target == m1) m1.classList.add('hidden');
        if (event.target == m2) m2.classList.add('hidden');
    }
</script>