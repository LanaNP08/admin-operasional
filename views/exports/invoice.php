<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: Arial; }
        th, td { border: 1px solid #000; padding: 5px; vertical-align: top; font-size: 12px; }
        th { background-color: #008B8B; color: white; }
    </style>
</head>
<body>
    <center>
        <h3>REPORT INVOICE & DELIVERY</h3>
        <p>Export Date: <?php echo date("d F Y H:i"); ?></p>
    </center>

    <table>
        <thead>
            <tr>
                <th>Doc No</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Description / Remarks</th>
                <th>Net Amount</th>
                <th>Tax</th>
                <th>Total Amount</th>
                <th>Faktur Pajak</th>
                <th>Tanggal Kirim</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?php echo $row['doc_no']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['doc_date'])); ?></td>
                <td><?php echo $row['customer_name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td align="right"><?php echo number_format($row['net'], 0, ',', '.'); ?></td>
                <td align="right"><?php echo number_format($row['tax'], 0, ',', '.'); ?></td>
                <td align="right" style="font-weight:bold;"><?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                <td><?php echo $row['fp']; ?></td>
                <td align="center">
                    <?php echo $row['sent_date'] ? date('d/m/Y', strtotime($row['sent_date'])) : '-'; ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>