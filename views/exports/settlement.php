<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: Arial; }
        th, td { border: 1px solid #000; padding: 8px; vertical-align: top; }
        th { background-color: #4472C4; color: white; }
    </style>
</head>
<body>
    <h3>DATA DOKUMEN SETTLEMEN</h3>
    <table>
        <thead>
            <tr>
                <th>Doc No</th>
                <th>Date</th>
                <th>Information</th>
                <th>Amount (CR)</th>
                <th>Description</th>
                <th>No Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($data)) {
                $info = strtoupper($row['information']);
                
                // Logic Warna Excel
                $bg = '#FFCC80'; // Default Orange
                if (strpos($info, 'SETTLE') !== false) $bg = '#90CAF9'; // Biru
                elseif (strpos($info, 'ADVANCE') !== false) $bg = '#FFF59D'; // Kuning
            ?>
            <tr style="background-color: <?php echo $bg; ?>">
                <td><?php echo $row['doc_no']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['doc_date'])); ?></td>
                <td><?php echo $row['information']; ?></td>
                <td align="right"><?php echo number_format($row['amount_cr'], 0, ',', '.'); ?></td>
                <td><?php echo $row['description']; ?></td>
                <td align="center" style="font-weight:bold;"><?php echo $row['no_transaksi']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>