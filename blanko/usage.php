<?php
// public_html/blanko/usage.php

require_once '../app/config/database.php';
require_once '../app/controllers/LayoutController.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: ../login.php"); exit;
}

$layout = new LayoutController($conn);
include '../views/partials/header.php';

$pesan = "";
$pesanType = "";

// --- 1. FITUR INPUT STOK BARU ---
if (isset($_POST['btn_input_stok'])) {
    $raw = $_POST['raw_stok'];
    $lines = explode("\n", $raw);
    $sukses = 0; $gagal = 0;

    foreach ($lines as $line) {
        $no_blanko = trim($line);
        if (empty($no_blanko)) continue;

        $cek = mysqli_query($conn, "SELECT id FROM blankos WHERE blanko_number = '$no_blanko'");
        if (mysqli_num_rows($cek) == 0) {
            $q = "INSERT INTO blankos (blanko_number, status) VALUES ('$no_blanko', 'registered')";
            if (mysqli_query($conn, $q)) $sukses++;
            else $gagal++;
        } else {
            $gagal++; 
        }
    }
    $pesan = "Input Stok Selesai. Sukses: $sukses, Skip/Gagal: $gagal";
    $pesanType = ($sukses > 0) ? "success" : "warning";
}

// --- 2. FITUR UPDATE STATUS (Edit) ---
if (isset($_POST['btn_update_status'])) {
    $id = (int)$_POST['id_edit'];
    $status = $_POST['status_edit'];
    $reason = ($status === 'damaged') ? trim($_POST['reason_edit']) : NULL;
    
    $safe_status = mysqli_real_escape_string($conn, $status);
    $safe_reason = $reason ? "'" . mysqli_real_escape_string($conn, $reason) . "'" : "NULL";

    if(empty($status)) {
        $pesan = "Gagal: Status harus dipilih!";
        $pesanType = "error";
    } else {
        // [AUTO-FIX DATABASE]
        // Baris ini akan mengubah kolom status jadi VARCHAR biar bisa terima 'damaged'
        // Ini solusi masalah 'status tidak berubah'
        mysqli_query($conn, "ALTER TABLE blankos MODIFY status VARCHAR(50) DEFAULT 'registered'");

        // Jika diubah jadi Available/Damaged, hapus dokumen terkait biar bersih
        if ($status == 'registered' || $status == 'damaged') {
            mysqli_query($conn, "DELETE FROM documents WHERE blanko_id = $id");
        }

        $q = "UPDATE blankos SET status = '$safe_status', reason = $safe_reason WHERE id = $id";
        if (mysqli_query($conn, $q)) {
            $pesan = "Status blanko berhasil diperbarui.";
            $pesanType = "success";
        } else {
            $pesan = "Error Database: " . mysqli_error($conn);
            $pesanType = "error";
        }
    }
}

// --- 3. FITUR RESET TOTAL ---
if (isset($_POST['btn_reset_all']) && $_POST['confirm_text'] === 'RESET') {
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
    mysqli_query($conn, "TRUNCATE TABLE documents");
    mysqli_query($conn, "TRUNCATE TABLE blankos");
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
    $pesan = "DATABASE DI-WIPE BERSIH.";
    $pesanType = "error";
}

// --- 4. LOAD DATA ---
$keyword = isset($_GET['q']) ? $_GET['q'] : '';
$where = "WHERE 1=1";
if ($keyword) {
    $safe_key = mysqli_real_escape_string($conn, $keyword);
    $where .= " AND (b.blanko_number LIKE '%$safe_key%' OR d.job_number LIKE '%$safe_key%' OR d.certificate_number LIKE '%$safe_key%')";
}

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Query Utama
$query = "SELECT b.id as blanko_id, b.blanko_number, b.status, b.reason, 
                 d.job_number, d.certificate_number, d.completed_date 
          FROM blankos b 
          LEFT JOIN documents d ON b.id = d.blanko_id 
          $where 
          ORDER BY b.id ASC 
          LIMIT $offset, $limit";

$result = mysqli_query($conn, $query);

// Hitung total
$qCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM blankos b LEFT JOIN documents d ON b.id = d.blanko_id $where");
$totalData = mysqli_fetch_assoc($qCount)['total'];
$totalPages = ceil($totalData / $limit);
?>

<div class="max-w-7xl mx-auto pb-10">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Monitoring Blanko</h1>
            <p class="text-slate-400 text-sm">Master Stok & Usage (Total: <?php echo number_format($totalData); ?>)</p>
        </div>
        <div class="flex gap-2">
            <button onclick="toggleModal('modalStok')" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-bold text-sm shadow">
                + Input Stok
            </button>
            <button onclick="toggleModal('modalReset')" class="bg-red-900/50 text-red-200 border border-red-800 px-3 py-2 rounded-lg font-bold text-xs">
                Reset DB
            </button>
        </div>
    </div>

    <div class="mb-4">
        <form action="" method="GET" class="flex gap-2">
            <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg px-4 py-2 w-full md:w-64 outline-none focus:border-indigo-500" placeholder="Cari No Blanko / Job...">
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm">Cari</button>
        </form>
    </div>

    <?php if($pesan): ?>
        <div class="p-4 mb-4 rounded-lg border <?php echo $pesanType=='success' ? 'bg-emerald-900/20 border-emerald-500 text-emerald-400' : 'bg-red-900/20 border-red-500 text-red-400'; ?>">
            <?php echo $pesan; ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-900 border border-slate-700 rounded-xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto" style="min-height: 300px;"> <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-950 text-slate-200 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4">No. Blanko</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Used By (Job/Cert)</th>
                        <th class="px-6 py-4">Tgl Setifikat</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4 font-mono text-white font-medium"><?php echo $row['blanko_number']; ?></td>
                            <td class="px-6 py-4">
                                <?php 
                                $st = strtolower($row['status'] ?? ''); 
                                
                                if($st == 'used') {
                                    echo '<span class="px-2 py-1 rounded bg-emerald-900/30 text-emerald-400 text-xs font-bold border border-emerald-500/30">TERPAKAI</span>';
                                } elseif($st == 'damaged') {
                                    // VISUAL BARU: Badge + Icon Tooltip
                                    echo '<div class="flex items-center gap-2">';
                                    echo '<span class="px-2 py-1 rounded bg-red-900/30 text-red-400 text-xs font-bold border border-red-500/30">RUSAK</span>';
                                    
                                    if(!empty($row['reason'])) {
                                        // Tooltip CSS Murni
                                        echo '<div class="group relative flex items-center justify-center cursor-help">';
                                        echo '<span class="text-base">📝</span>'; // Ikon
                                        echo '<div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-48 p-2 bg-slate-800 text-white text-xs rounded border border-slate-600 shadow-xl z-50 text-center">';
                                        echo htmlspecialchars($row['reason']);
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                } else {
                                    // Ini fallback kalau status kosong atau registered
                                    echo '<span class="px-2 py-1 rounded bg-blue-900/30 text-blue-400 text-xs font-bold border border-blue-500/30">AVAILABLE</span>'; 
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                if($row['job_number']) {
                                    echo '<div class="font-mono text-slate-200">'.$row['job_number'].'</div>';
                                    echo '<div class="text-[10px] text-slate-500">'.$row['certificate_number'].'</div>';
                                } else {
                                    echo '-'; 
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4"><?php echo ($row['completed_date'] && $row['completed_date'] != '0000-00-00') ? date('d/m/Y', strtotime($row['completed_date'])) : '-'; ?></td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="editStatus(<?php echo $row['blanko_id']; ?>, '<?php echo $row['status']; ?>', '<?php echo htmlspecialchars($row['reason'] ?? ''); ?>')" class="text-slate-500 hover:text-yellow-400 transition p-2 rounded hover:bg-slate-800" title="Edit Status">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="p-8 text-center text-slate-500 italic">Data tidak ditemukan. Coba input stok dulu.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($totalPages > 1): ?>
        <div class="bg-slate-950 px-6 py-3 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-500">Hal <?php echo $page; ?> dari <?php echo $totalPages; ?></span>
            <div class="flex gap-1">
                <?php if($page > 1): ?><a href="?page=<?php echo $page-1; ?>&q=<?php echo $keyword; ?>" class="px-3 py-1 bg-slate-800 rounded text-xs text-slate-300">Prev</a><?php endif; ?>
                <?php if($page < $totalPages): ?><a href="?page=<?php echo $page+1; ?>&q=<?php echo $keyword; ?>" class="px-3 py-1 bg-indigo-600 rounded text-xs text-white">Next</a><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div id="modalStok" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-slate-900 w-full max-w-lg rounded-xl border border-slate-700 p-6 shadow-2xl animate-fade-in-up">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white font-bold">Input Stok Blanko</h3>
            <button onclick="toggleModal('modalStok')" class="text-slate-500 hover:text-white">✕</button>
        </div>
        <form method="POST">
            <p class="text-xs text-slate-400 mb-2">Paste Nomor Blanko (Satu per baris):</p>
            <textarea name="raw_stok" rows="8" class="w-full bg-slate-950 border border-slate-700 text-white p-3 rounded font-mono text-sm mb-4 focus:border-indigo-500 outline-none" placeholder="M-31.f.0001&#10;M-31.f.0002"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="toggleModal('modalStok')" class="text-slate-400 px-4 py-2 hover:text-white">Batal</button>
                <button type="submit" name="btn_input_stok" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded font-bold text-sm shadow">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-slate-900 w-full max-w-md rounded-xl border border-slate-700 shadow-2xl animate-fade-in-up">
        <div class="bg-slate-800 p-4 flex justify-between items-center rounded-t-xl border-b border-slate-700">
            <h3 class="text-white font-bold">Edit Status Blanko</h3>
            <button onclick="toggleModal('modalEdit')" class="text-slate-400 hover:text-white">✕</button>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="id_edit" id="id_edit">
            <div class="mb-4 space-y-2">
                <label class="flex items-center p-3 border border-slate-700 rounded-lg cursor-pointer hover:bg-slate-800 transition">
                    <input type="radio" name="status_edit" value="registered" onclick="toggleReason(false)" class="text-blue-600 focus:ring-0">
                    <span class="ml-2 text-sm text-blue-400 font-bold">Available (Reset)</span>
                </label>
                <label class="flex items-center p-3 border border-slate-700 rounded-lg cursor-pointer hover:bg-slate-800 transition">
                    <input type="radio" name="status_edit" value="used" onclick="toggleReason(false)" class="text-emerald-600 focus:ring-0">
                    <span class="ml-2 text-sm text-emerald-400 font-bold">Used (Terpakai)</span>
                </label>
                <label class="flex items-center p-3 border border-red-900/30 rounded-lg cursor-pointer hover:bg-red-900/10 transition">
                    <input type="radio" name="status_edit" value="damaged" onclick="toggleReason(true)" class="text-red-600 focus:ring-0">
                    <span class="ml-2 text-sm text-red-400 font-bold">Damaged (Rusak)</span>
                </label>
            </div>
            
            <div id="areaReason" class="hidden mb-4 animate-fade-in">
                <label class="block text-xs font-bold text-red-400 mb-1">Alasan Rusak (Wajib):</label>
                <textarea name="reason_edit" id="reason_edit" rows="2" class="w-full bg-slate-950 border border-red-900/50 text-white text-sm p-2 rounded focus:outline-none placeholder-slate-600" placeholder="Contoh: Salah cetak, tinta luntur..."></textarea>
            </div>
            
            <button type="submit" name="btn_update_status" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2 rounded font-bold shadow text-sm transition">Simpan Perubahan</button>
        </form>
    </div>
</div>

<script>
function toggleModal(id) { document.getElementById(id).classList.toggle('hidden'); }
function editStatus(id, st, r) {
    document.getElementById('id_edit').value = id;
    document.getElementById('reason_edit').value = r;
    
    // Reset radio
    const radios = document.getElementsByName('status_edit');
    let found = false;
    radios.forEach(el => { 
        if(el.value === st) { el.checked = true; found = true; }
        else el.checked = false;
    });
    
    // Fallback kalau status kosong, visualnya pilih registered
    if(!found) radios[0].checked = true;

    toggleReason(st === 'damaged');
    toggleModal('modalEdit');
}
function toggleReason(show) {
    const el = document.getElementById('areaReason');
    const input = document.getElementById('reason_edit');
    if (show) { el.classList.remove('hidden'); input.required = true; } 
    else { el.classList.add('hidden'); input.required = false; }
}
</script>

<?php include '../views/partials/footer.php'; ?>