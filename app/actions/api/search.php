<?php
// app/actions/api/search.php

// Pastikan koneksi $conn sudah tersedia
// Input dari parameter GET 'cari'
$keyword = isset($_GET['cari']) ? $_GET['cari'] : '';

if (strlen($keyword) >= 2) {
    $keyword = mysqli_real_escape_string($conn, $keyword);

    // Cari di Invoice Reports (Doc No, Customer, Remarks)
    $query = "SELECT id, doc_no, customer_name, description, 'Invoice' as tipe 
              FROM invoice_reports 
              WHERE (doc_no LIKE '%$keyword%' 
                 OR customer_name LIKE '%$keyword%' 
                 OR description LIKE '%$keyword%')
              AND is_deleted = 0
              LIMIT 5";
              
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Output berupa Baris Tabel (TR) karena akan di-inject ke dalam <tbody>
            // Sesuaikan styling dengan desain baru
            echo '<tr class="hover:bg-slate-800/50 transition cursor-pointer" onclick="window.location=\'invoice.php?search='.$row['doc_no'].'\'">';
            
            // Kolom 1: Doc No
            echo '<td class="px-6 py-4 font-mono text-emerald-400 font-bold text-xs border-b border-white/5">
                    '.$row['doc_no'].'
                  </td>';
            
            // Kolom 2: Customer
            echo '<td class="px-6 py-4 text-white text-sm border-b border-white/5">
                    '.$row['customer_name'].'
                  </td>';
            
            // Kolom 3: Description / Info
            echo '<td class="px-6 py-4 text-slate-400 text-xs border-b border-white/5">
                    '.$row['description'].'
                  </td>';
                  
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3" class="px-6 py-4 text-center text-slate-500 text-xs">Tidak ditemukan data.</td></tr>';
    }
}
exit;
?>