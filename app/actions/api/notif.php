<?php
// app/actions/api/notif.php

// Pastikan koneksi $conn sudah tersedia dari file pemanggil
header('Content-Type: application/json');

date_default_timezone_set('Asia/Jakarta');

// 1. Logic Tandai Sudah Dibaca (POST)
if (isset($_POST['mark_all_read'])) {
    mysqli_query($conn, "UPDATE delivery_batches SET is_read = 1 WHERE is_read = 0");
    echo json_encode(['status' => 'success']);
    exit;
}

// 2. Ambil 5 History Terakhir (GET)
// Logic: Mengambil data delivery_batches yang sudah diterima (received_at IS NOT NULL)
$qList = mysqli_query($conn, "SELECT * FROM delivery_batches WHERE received_at IS NOT NULL ORDER BY received_at DESC LIMIT 5");

$list = [];
while($r = mysqli_fetch_assoc($qList)) {
    // Format Waktu
    $waktu = date('d/m H:i', strtotime($r['received_at']));
    
    $list[] = [
        'id' => $r['id'],
        'batch_code' => $r['batch_code'],
        'text' => "Dokumen <b>{$r['batch_code']}</b> diterima oleh: {$r['recipient_name']}.",
        'waktu' => $waktu,
        'icon' => '📦',
        'is_read' => $r['is_read'],
        
        // --- PERBAIKAN DI SINI ---
        // Arahkan langsung ke modal bukti penerimaan (batch spesifik)
        'link' => "invoice.php?open_proof=" . $r['id']
    ];
}

// 3. Hitung Jumlah Belum Dibaca (Badge Merah)
$qCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM delivery_batches WHERE received_at IS NOT NULL AND is_read = 0");
$unread = mysqli_fetch_assoc($qCount)['total'];

echo json_encode([
    'unread' => $unread,
    'list' => $list
]);
exit;
?>