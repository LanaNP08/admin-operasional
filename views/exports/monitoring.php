<!DOCTYPE html>
<html>
<head>
    <title>Export Data</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; vertical-align: top; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <center>
        <h3>DATA ALAT & JADWAL KALIBRASI OPERASIONAL</h3>
        <p>Per Tanggal: <?php echo date("d F Y"); ?></p>
    </center>
 
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Kalibrasi</th>
                <th>Nama Alat</th>
                <th>Merk/Type</th>
                <th>Lokasi</th>
                <th>Kondisi</th>
                <th>Terakhir Kalibrasi</th>
                <th>Rencana Kalibrasi</th>
                <th>Status Jadwal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = mysqli_fetch_array($data)){
                // Logic Warna / Status
                $dateNext = new DateTime($row['tgl_kalibrasi']);
                $today = new DateTime();
                $today->setTime(0,0); $dateNext->setTime(0,0);
                
                if($dateNext < $today) {
                    $status = "LEWAT JADWAL";
                    $bg_color = "#ffcccc"; // Merah
                } elseif ($today->diff($dateNext)->days <= 60) {
                    $status = "SEGERA (Mendekati)";
                    $bg_color = "#fff4cc"; // Kuning
                } else {
                    $status = "AMAN";
                    $bg_color = "#ffffff"; // Putih
                }
            ?>
            <tr>
                <td align="center"><?php echo $no++; ?></td>
                <td><?php echo $row['kode_sn']; ?></td>
                <td><?php echo $row['nama_alat']; ?></td>
                <td><?php echo $row['merk_type']; ?></td>
                <td><?php echo $row['lokasi']; ?></td>
                <td align="center"><?php echo $row['status_alat']; ?></td>
                <td align="center"><?php echo date('d-M-Y', strtotime($row['tgl_terakhir'])); ?></td>
                <td align="center" style="background-color: <?php echo $bg_color; ?>">
                    <?php echo date('d-M-Y', strtotime($row['tgl_kalibrasi'])); ?>
                </td>
                <td align="center" style="background-color: <?php echo $bg_color; ?>">
                    <b><?php echo $status; ?></b>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>