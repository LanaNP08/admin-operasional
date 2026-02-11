<div class="flex justify-between items-center mb-6 animate-fade-in-up">
    <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <span class="bg-blue-600 p-1.5 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </span>
            Kelola Pengguna (Users)
        </h1>
        <p class="text-slate-400 text-sm mt-1">Manajemen akses login karyawan.</p>
    </div>
</div>

<?php if(!empty($pesan)): ?>
    <div class="mb-6 p-4 rounded-lg flex items-center gap-3 shadow-lg animate-fade-in-up <?php echo $tipe == 'success' ? 'bg-emerald-500/20 border border-emerald-500 text-emerald-200' : 'bg-red-500/20 border border-red-500 text-red-200'; ?>">
        <span class="text-xl"><?php echo $tipe == 'success' ? '✅' : '⚠️'; ?></span>
        <div><?php echo $pesan; ?></div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 0.1s;">
    
    <div class="lg:col-span-1">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg sticky top-24">
            <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-800 pb-2">Tambah User Baru</h3>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">USERNAME</label>
                    <input type="text" name="username" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl p-3 focus:border-blue-500 focus:outline-none transition placeholder-slate-600 text-sm" placeholder="cth: admin_gudang">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">PASSWORD</label>
                    <input type="text" name="password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl p-3 focus:border-blue-500 focus:outline-none transition placeholder-slate-600 text-sm" placeholder="Password...">
                </div>
                <div>
    <label class="block text-xs font-bold text-slate-400 mb-1">LEVEL AKSES (ROLE)</label>
    <select name="role" class="w-full bg-slate-950 border border-slate-700 text-white rounded p-2 focus:border-blue-500 focus:outline-none">
        <option value="tamu" class="bg-slate-900">Tamu (Hanya Lihat)</option>
        <option value="admin" class="bg-slate-900">Admin (Bisa Edit Invoice)</option>
        <option value="super_admin" class="bg-slate-900">Super Admin (Full Akses)</option>
    </select>
</div>
                <button type="submit" name="btn_add" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition shadow-lg mt-2 transform active:scale-[0.98]">
                    + Simpan User
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-xs border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4 text-center">Terdaftar</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    <?php $no=1; foreach($users as $row): ?>
                    <tr class="hover:bg-slate-800/50 transition duration-150">
                        <td class="px-6 py-4 text-center border-r border-slate-800/50"><?php echo $no++; ?></td>
                        <td class="px-6 py-4 font-bold text-white"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="px-6 py-4">
                            <?php 
                                $bg = 'bg-slate-700 text-slate-300';
                                if($row['role'] == 'super_admin') $bg = 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
                                if($row['role'] == 'admin') $bg = 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
                            ?>
                            <span class="px-2.5 py-1 rounded-md text-[10px] uppercase font-bold tracking-wide <?php echo $bg; ?>">
                                <?php echo str_replace('_', ' ', $row['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-xs text-slate-500">
                            <?php echo ($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($row['id'] != $currentUserId): ?>
                                <form method="POST" onsubmit="return confirm('Yakin hapus user <?php echo $row['username']; ?>?');">
                                    <input type="hidden" name="id_user" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="btn_delete" class="text-red-400 hover:text-white bg-slate-800 hover:bg-red-500/80 p-2 rounded-lg transition" title="Hapus User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-[10px] text-slate-500 italic bg-slate-800 px-2 py-1 rounded">(Akun Anda)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>