<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 animate-fade-in-up">
    <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <span class="bg-pink-600 p-1.5 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </span>
            Log Book - <?php echo $my_role == 'super_admin' ? 'Semua Karyawan' : htmlspecialchars($my_name); ?>
        </h1>
        <p class="text-slate-400 text-sm mt-1">
            <?php if($my_role == 'super_admin'): ?>
                Mode Super Admin: Anda dapat memantau aktivitas semua tim.
            <?php else: ?>
                Catatan aktivitas kerja pribadi Anda. Orang lain tidak bisa melihat ini.
            <?php endif; ?>
        </p>
    </div>
    <button onclick="bukaModal('modalTambah')" class="bg-pink-600 hover:bg-pink-500 text-white px-5 py-2.5 rounded-lg font-bold shadow-lg flex items-center gap-2 transition hover:scale-105 active:scale-95">
        <span>+</span> Catat Log Baru
    </button>
</div>

<?php if(!empty($pesan)): ?>
    <div class="mb-6 p-4 rounded-lg flex items-center gap-3 shadow-lg animate-fade-in-up <?php echo $tipe == 'success' ? 'bg-emerald-500/20 border border-emerald-500 text-emerald-200' : 'bg-red-500/20 border border-red-500 text-red-200'; ?>">
        <span class="text-xl"><?php echo $tipe == 'success' ? '✅' : '⚠️'; ?></span>
        <div><?php echo $pesan; ?></div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 animate-fade-in-up" style="animation-delay: 0.1s;">
    <div class="lg:col-span-1 bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg flex flex-col items-center justify-center">
        <h3 class="text-slate-300 font-bold mb-4 text-sm uppercase text-center w-full border-b border-slate-800 pb-2">
            Status Pekerjaan
        </h3>
        <div class="w-full h-40 flex justify-center relative">
            <canvas id="logChart"></canvas>
        </div>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg flex flex-col justify-center">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div class="col-span-2 md:col-span-1">
                <label class="text-xs text-slate-400 font-bold">Rentang Tanggal</label>
                <div class="flex gap-1">
                    <input type="date" name="tgl_mulai" value="<?php echo $f_mulai; ?>" class="w-full bg-slate-950 border border-slate-700 text-white text-xs p-2 rounded focus:border-pink-500 outline-none">
                    <input type="date" name="tgl_akhir" value="<?php echo $f_akhir; ?>" class="w-full bg-slate-950 border border-slate-700 text-white text-xs p-2 rounded focus:border-pink-500 outline-none">
                </div>
            </div>
            <div>
                <label class="text-xs text-slate-400 font-bold">Kategori / Status</label>
                <div class="flex gap-1">
    <select name="kategori" class="w-full bg-slate-950 border border-slate-700 text-white text-xs p-2 rounded focus:border-pink-500 outline-none">
        <option value="" class="bg-slate-900">Semua Kat</option>
        <option value="Dokumen" class="bg-slate-900" <?php echo $f_kategori=='Dokumen'?'selected':''; ?>>Dokumen</option>
        <option value="Alat/Aset" class="bg-slate-900" <?php echo $f_kategori=='Alat/Aset'?'selected':''; ?>>Alat/Aset</option>
        <option value="Invoice" class="bg-slate-900" <?php echo $f_kategori=='Invoice'?'selected':''; ?>>Invoice</option>
    </select>
    <select name="status" class="w-full bg-slate-950 border border-slate-700 text-white text-xs p-2 rounded focus:border-pink-500 outline-none">
        <option value="" class="bg-slate-900">Semua Sts</option>
        <option value="Pending" class="bg-slate-900" <?php echo $f_status=='Pending'?'selected':''; ?>>Pending</option>
        <option value="Selesai" class="bg-slate-900" <?php echo $f_status=='Selesai'?'selected':''; ?>>Selesai</option>
    </select>
</div>
            </div>
            <div class="flex gap-1">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white p-2 rounded flex-1 text-xs font-bold uppercase transition">Filter</button>
                <a href="logbook.php" class="bg-slate-700 hover:bg-slate-600 text-white p-2 rounded w-10 text-center transition" title="Reset">↺</a>
            </div>
        </form>
    </div>
</div>

<div class="bg-slate-900 rounded-xl border border-slate-800 shadow-xl overflow-hidden animate-fade-in-up" style="animation-delay: 0.2s;">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-950 text-slate-300 uppercase font-bold text-xs border-b border-slate-700">
                <tr>
                    <th class="px-4 py-4 w-10 text-center">No</th>
                    <th class="px-4 py-4 w-28">Waktu</th>
                    <?php if($my_role == 'super_admin'): ?>
                        <th class="px-4 py-4 w-28 text-pink-400">Oleh</th>
                    <?php endif; ?>
                    <th class="px-4 py-4 min-w-[200px]">Deskripsi</th>
                    <th class="px-4 py-4 w-24">Kat / PIC</th>
                    <th class="px-4 py-4 w-32 text-center">Status</th>
                    <th class="px-4 py-4 w-20 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                <?php 
                $no = 1;
                if(count($logs) > 0):
                    foreach($logs as $row): 
                        $rowClass = $row['is_overdue'] ? "bg-red-900/10 hover:bg-red-900/20" : "hover:bg-slate-800/50";
                        
                        $st = $row['status'];
                        if($st == 'Pending') $badge = 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20';
                        elseif($st == 'On Progress') $badge = 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
                        elseif($st == 'Selesai') $badge = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                        else $badge = 'bg-slate-700 text-slate-300';
                ?>
                <tr class="<?php echo $rowClass; ?> transition duration-150">
                    <td class="px-4 py-4 text-center border-r border-slate-800/50"><?php echo $no++; ?></td>
                    
                    <td class="px-4 py-4 border-r border-slate-800/50">
                        <div class="font-bold text-white"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></div>
                        <div class="text-xs text-slate-500"><?php echo date('H:i', strtotime($row['jam'])); ?> WIB</div>
                        <?php if($row['is_overdue']): ?>
                            <div class="mt-2 text-[10px] bg-red-600 text-white px-2 py-0.5 rounded animate-pulse font-bold inline-block">Telat <?php echo $row['days_late']; ?> Hari</div>
                        <?php endif; ?>
                    </td>

                    <?php if($my_role == 'super_admin'): ?>
                        <td class="px-4 py-4 border-r border-slate-800/50 text-pink-400 font-bold">
                            <?php echo $row['nama_pencatat'] ?: 'Unknown'; ?>
                        </td>
                    <?php endif; ?>

                    <td class="px-4 py-4 border-r border-slate-800/50">
                        <div class="text-white font-medium mb-1"><?php echo nl2br($row['deskripsi']); ?></div>
                        <?php if($row['catatan']): ?>
                            <div class="text-xs text-slate-500 border-l-2 border-pink-500 pl-2 mt-1"><?php echo $row['catatan']; ?></div>
                        <?php endif; ?>
                    </td>

                    <td class="px-4 py-4 border-r border-slate-800/50 text-xs">
                        <span class="bg-slate-700 px-1.5 py-0.5 rounded text-white"><?php echo $row['kategori']; ?></span>
                        <div class="text-slate-400 mt-2">PIC: <span class="text-white"><?php echo $row['pic']; ?></span></div>
                    </td>

                    <td class="px-4 py-4 border-r border-slate-800/50 text-center">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase <?php echo $badge; ?>"><?php echo $st; ?></span>
                        <?php if(!empty($row['bukti_foto'])): ?>
                            <div class="mt-2">
                                <a href="public/uploads/<?php echo $row['bukti_foto']; ?>" target="_blank" class="text-xs text-blue-400 hover:text-blue-300 underline flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Bukti
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td class="px-4 py-4 text-center">
                        <?php if($my_role == 'super_admin' || $row['user_id'] == $my_id): ?>
                            <div class="flex justify-center gap-2">
                                <button onclick='editData(<?php echo json_encode($row); ?>)' class="text-blue-400 hover:text-white p-1 rounded hover:bg-slate-800 transition" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form method="POST" onsubmit="return confirm('Hapus log ini?');">
                                    <input type="hidden" name="id_hapus" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="btn_hapus" class="text-red-400 hover:text-white p-1 rounded hover:bg-slate-800 transition" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-slate-600 italic">No Access</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" class="p-8 text-center text-slate-500">Tidak ada data log yang ditampilkan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalForm" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-slate-900 w-full max-w-xl rounded-xl border border-slate-700 shadow-2xl overflow-hidden animate-fade-in-up">
        <div class="bg-slate-800 p-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white" id="modalTitle">Catat Log Baru</h3>
            <button onclick="tutupModal()" class="text-slate-400 hover:text-white transition">✕</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 grid grid-cols-2 gap-4">
            <input type="hidden" name="id_edit" id="id_edit">
            
            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">TANGGAL</label>
                <input type="date" name="tanggal" id="tanggal" value="<?php echo date('Y-m-d'); ?>" required class="w-full bg-slate-950 border border-slate-600 text-white rounded p-2 focus:border-pink-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">JAM</label>
                <input type="time" name="jam" id="jam" value="<?php echo date('H:i'); ?>" required class="w-full bg-slate-950 border border-slate-600 text-white rounded p-2 focus:border-pink-500 outline-none">
            </div>
            
            <div>
    <label class="block text-xs font-bold text-slate-400 mb-1">KATEGORI</label>
    <select name="kategori" id="kategori" required class="w-full bg-slate-950 border border-slate-600 text-white rounded p-2 focus:border-pink-500 outline-none">
        <option value="Dokumen" class="bg-slate-900">Dokumen</option>
        <option value="Alat/Aset" class="bg-slate-900">Alat/Aset</option>
        <option value="Invoice" class="bg-slate-900">Invoice</option>
        <option value="Lainnya" class="bg-slate-900">Lainnya</option>
    </select>
</div>
            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">PIC / TUJUAN</label>
                <input type="text" name="pic" id="pic" value="<?php echo htmlspecialchars($my_name); ?>" required class="w-full bg-slate-950 border border-slate-600 text-white rounded p-2 focus:border-pink-500 outline-none">
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-400 mb-1">DESKRIPSI AKTIVITAS</label>
                <textarea name="deskripsi" id="deskripsi" rows="3" required class="w-full bg-slate-950 border border-slate-600 text-white rounded p-2 focus:border-pink-500 outline-none" placeholder="Apa yang dikerjakan..."></textarea>
            </div>

            <div>
    <label class="block text-xs font-bold text-slate-400 mb-1">STATUS SAAT INI</label>
    <select name="status" id="status" class="w-full bg-slate-950 border border-slate-600 text-white rounded p-2 focus:border-pink-500 outline-none">
        <option value="Pending" class="bg-slate-900">🟡 Pending</option>
        <option value="On Progress" class="bg-slate-900">🔵 On Progress</option>
        <option value="Selesai" class="bg-slate-900">🟢 Selesai</option>
    </select>
</div>
            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">UPLOAD BUKTI (FOTO)</label>
                <input type="file" name="bukti_foto" id="bukti_foto" accept="image/*" class="w-full bg-slate-950 border border-slate-600 text-white text-xs rounded p-1.5 focus:border-pink-500 outline-none">
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-400 mb-1">CATATAN TAMBAHAN</label>
                <textarea name="catatan" id="catatan" rows="2" class="w-full bg-slate-950 border border-slate-600 text-white rounded p-2 focus:border-pink-500 outline-none" placeholder="Info tambahan..."></textarea>
            </div>

            <div class="col-span-2 flex justify-end gap-3 pt-4 border-t border-slate-700 mt-2">
                <button type="button" onclick="tutupModal()" class="px-4 py-2 text-slate-400 hover:text-white hover:bg-white/5 rounded transition">Batal</button>
                <button type="submit" name="btn_simpan" id="btn_submit" class="px-6 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded font-bold shadow-lg transition">Simpan Log</button>
            </div>
        </form>
    </div>
</div>

<script>
// CHART JS
const ctx = document.getElementById('logChart').getContext('2d');
const logChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'On Progress', 'Selesai'],
        datasets: [{
            data: [<?php echo $statData['Pending']; ?>, <?php echo $statData['On Progress']; ?>, <?php echo $statData['Selesai']; ?>],
            backgroundColor: ['#eab308', '#3b82f6', '#10b981'], // Warna disesuaikan dengan tema
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { color: '#cbd5e1', font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" } } } }
    }
});

function bukaModal(mode) {
    document.getElementById('modalForm').classList.remove('hidden');
    if (mode === 'modalTambah') {
        document.getElementById('modalTitle').innerText = "Catat Log Baru";
        document.getElementById('btn_submit').name = "btn_simpan";
        document.getElementById('btn_submit').innerText = "Simpan Log";
        document.getElementById('id_edit').value = "";
        document.getElementById('deskripsi').value = "";
        document.getElementById('pic').value = "<?php echo htmlspecialchars($my_name); ?>"; 
        document.getElementById('catatan').value = "";
        document.getElementById('status').value = "Pending";
        document.getElementById('bukti_foto').value = "";
    }
}

function editData(data) {
    document.getElementById('modalForm').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = "Edit Catatan Log";
    document.getElementById('btn_submit').name = "btn_update";
    document.getElementById('btn_submit').innerText = "Update Perubahan";
    
    document.getElementById('id_edit').value = data.id;
    document.getElementById('tanggal').value = data.tanggal;
    document.getElementById('jam').value = data.jam;
    document.getElementById('kategori').value = data.kategori;
    document.getElementById('pic').value = data.pic;
    document.getElementById('deskripsi').value = data.deskripsi;
    document.getElementById('status').value = data.status;
    document.getElementById('catatan').value = data.catatan;
}

function tutupModal() {
    document.getElementById('modalForm').classList.add('hidden');
}

// Close Modal on Outside Click
window.onclick = function(e) {
    if(e.target.id == 'modalForm') tutupModal();
}
</script>