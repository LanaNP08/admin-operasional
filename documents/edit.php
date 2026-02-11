<?php
// public_html/documents/edit.php

require_once '../app/config/database.php';
require_once '../app/controllers/LayoutController.php';

$layout = new LayoutController($conn); // Cek Login
include '../views/partials/header.php';

// Cek Role (Hanya Admin)
if (!isset($_SESSION['role']) || ($_SESSION['role'] === 'tamu')) {
    echo "<script>alert('Akses Ditolak'); window.location='../index.php';</script>";
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    // Validasi enum manual
    if (in_array($newStatus, ['valid', 'revoked', 'void'])) {
        $stmt = mysqli_prepare($conn, "UPDATE documents SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "Status berhasil diupdate menjadi: <b>" . strtoupper($newStatus) . "</b>";
        } else {
            $msg = "Gagal update database.";
        }
    }
}

// Get Data
$q = mysqli_query($conn, "SELECT * FROM documents WHERE id = '$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<div class='p-8 text-white'>Dokumen tidak ditemukan.</div>";
    include '../views/partials/footer.php';
    exit;
}
?>

<div class="max-w-2xl mx-auto min-h-[60vh]">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Edit Status Dokumen</h1>
        <a href="../blanko/usage.php" class="text-slate-400 hover:text-white">&larr; Kembali</a>
    </div>

    <?php if ($msg): ?>
        <div class="bg-blue-500/10 text-blue-400 p-4 rounded-xl border border-blue-500/20 mb-6">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-900 border border-white/10 rounded-2xl p-8">
        <div class="mb-6 border-b border-white/5 pb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-500">Nomor Sertifikat</p>
                    <p class="text-white font-mono font-bold"><?php echo htmlspecialchars($data['certificate_number']); ?></p>
                </div>
                <div>
                    <p class="text-slate-500">Job Number</p>
                    <p class="text-white font-bold"><?php echo htmlspecialchars($data['job_number']); ?></p>
                </div>
            </div>
        </div>

        <form method="POST">
            <label class="block text-sm text-slate-400 mb-2">Set Status Dokumen</label>
            <select name="status" class="w-full bg-slate-950 border border-white/10 text-white rounded-xl px-4 py-3 mb-6 focus:border-blue-500 outline-none">
                <option value="valid" <?php echo $data['status'] == 'valid' ? 'selected' : ''; ?>>VALID (Aktif)</option>
                <option value="revoked" <?php echo $data['status'] == 'revoked' ? 'selected' : ''; ?>>REVOKED (Dicabut)</option>
                <option value="void" <?php echo $data['status'] == 'void' ? 'selected' : ''; ?>>VOID (Dibatalkan)</option>
            </select>
            
            <button type="submit" name="update_status" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition">
                Simpan Perubahan
            </button>
        </form>

        <p class="text-xs text-slate-500 mt-4 italic">
            *Mengubah status menjadi Revoked/Void akan membuat dokumen muncul sebagai "TIDAK BERLAKU" saat di-scan, namun data tetap tersimpan.
        </p>
    </div>
</div>

<?php include '../views/partials/footer.php'; ?>