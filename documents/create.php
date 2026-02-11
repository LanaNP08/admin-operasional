<?php
// public_html/documents/create.php

require_once '../app/config/database.php';
require_once '../app/controllers/LayoutController.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: ../login.php"); exit;
}

$layout = new LayoutController($conn);
include '../views/partials/header.php';

$msg = "";
$msgType = "";
$qrContent = ""; 
$successCert = ""; 

// --- LOGIC UTAMA: PARSE & SAVE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_generate'])) {
    
    // Contoh Input: 0038/26/MAR-D M0126010038DC0801 06/01/2026 M-31.f.0003
    $raw_input = trim($_POST['string_code']);
    
    // Split spasi
    $parts = preg_split('/\s+/', $raw_input);

    if (count($parts) < 4) {
        $msg = "GAGAL: Format Salah. Harus 4 bagian (Spasi).<br>Format: [Sertifikat] [Job] [Tgl] [Blanko]";
        $msgType = "error";
    } else {
        $cert_no    = trim($parts[0]);
        $job_number = trim($parts[1]);
        $tgl_raw    = trim($parts[2]);
        $no_blanko  = trim($parts[3]);

        // --- PERBAIKAN LOGIC QR (ANTI DETEKSI TELPON) ---
        // 1. Tambahkan Header "INFO DOKUMEN" di baris pertama
        // 2. Gunakan " : " (spasi titik dua spasi) agar lebih terbaca sebagai teks
        $qr_payload = "INFO DOKUMEN\n" . 
                      "------------------\n" .
                      "CERT_NO : " . $cert_no . "\n" .
                      "JOB_NO  : " . $job_number . "\n" .
                      "DATE    : " . $tgl_raw . "\n" .
                      "BLANKO  : " . $no_blanko;
        // ------------------------------------------------

        // Parse Tanggal
        $tgl_sql = date('Y-m-d');
        if (strpos($tgl_raw, '/') !== false) {
            $d = DateTime::createFromFormat('d/m/Y', $tgl_raw);
            if ($d) $tgl_sql = $d->format('Y-m-d');
        }

        // Cek Stok Blanko
        $qCek = mysqli_query($conn, "SELECT id, status FROM blankos WHERE blanko_number = '$no_blanko'");
        $dataBlanko = mysqli_fetch_assoc($qCek);

        if (!$dataBlanko) {
            $msg = "GAGAL: Blanko <b>$no_blanko</b> tidak ditemukan di Database Usage.<br>Mohon Input Stok dulu.";
            $msgType = "error";
        } elseif ($dataBlanko['status'] === 'used') {
            $msg = "GAGAL: Blanko <b>$no_blanko</b> SUDAH TERPAKAI!";
            $msgType = "error";
        } elseif ($dataBlanko['status'] === 'damaged') {
             $msg = "GAGAL: Blanko <b>$no_blanko</b> statusnya RUSAK!";
             $msgType = "error";
        } else {
            mysqli_begin_transaction($conn);
            try {
                $bId = $dataBlanko['id'];
                
                // 1. Update Status Blanko
                mysqli_query($conn, "UPDATE blankos SET status='used' WHERE id='$bId'");

                // 2. Simpan Dokumen (QR Token = Format Baru dengan Header)
                $sqlDoc = "INSERT INTO documents (blanko_id, job_number, certificate_number, completed_date, qr_token, status) 
                           VALUES ('$bId', '$job_number', '$cert_no', '$tgl_sql', '$qr_payload', 'valid')";
                
                if (!mysqli_query($conn, $sqlDoc)) {
                    throw new Exception("Gagal Simpan: " . mysqli_error($conn));
                }

                mysqli_commit($conn);
                
                $msg = "SUKSES! Data tersimpan.";
                $msgType = "success";
                $qrContent = $qr_payload; 
                $successCert = $cert_no;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $msg = "System Error: " . $e->getMessage();
                $msgType = "error";
            }
        }
    }
}
?>

<div class="max-w-3xl mx-auto min-h-screen">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Generate QR Code</h1>
            <p class="text-slate-400 text-sm">Input Kode String &rarr; Update Usage</p>
        </div>
        <a href="../blanko/usage.php" class="text-indigo-400 text-sm hover:text-white underline">Cek Stok</a>
    </div>

    <?php if ($msg): ?>
        <div class="mb-6 p-4 rounded-xl border <?php echo $msgType=='success' ? 'bg-emerald-900/20 border-emerald-500/50 text-emerald-400' : 'bg-red-900/20 border-red-500/50 text-red-400'; ?>">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <?php if ($qrContent): ?>
        <div class="bg-slate-900 border border-emerald-500/30 p-8 rounded-2xl mb-8 text-center animate-fade-in-up shadow-2xl">
            <h2 class="text-lg font-bold text-white mb-4">QR Code Ready</h2>
            
            <div class="bg-white p-4 inline-block rounded-lg mb-4">
                <div id="qrcode"></div>
            </div>
            
            <div class="text-left inline-block bg-slate-950 p-4 rounded border border-slate-800">
                <pre class="text-xs text-slate-400 font-mono whitespace-pre-wrap leading-relaxed"><?php echo $qrContent; ?></pre>
            </div>
            
            <div class="flex flex-col items-center justify-center gap-4 mt-6">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-slate-300">Pilih Ukuran:</label>
                    <select id="qrSize" class="bg-slate-800 text-white text-sm rounded-lg px-3 py-2 border border-slate-700 outline-none cursor-pointer" onchange="renderQR()">
                        <option value="75">Kecil (75px)</option>
                        <option value="100" selected>Sedang (100px)</option>
                        <option value="150">Besar (150px)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button onclick="downloadQR()" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold shadow-lg transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Download PNG
                    </button>
                    <button onclick="location.href='create.php'" class="px-6 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm">Input Baru</button>
                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
            <script>
                // Encode ke JSON agar newline aman di JS
                var rawText = <?php echo json_encode($qrContent); ?>;
                var certName = "<?php echo str_replace(['/','\\'],'-',$successCert); ?>";
                var qrcodeDiv = document.getElementById("qrcode");
                var qrObj = null;

                function renderQR() {
                    var size = parseInt(document.getElementById("qrSize").value);
                    qrcodeDiv.innerHTML = ""; 
                    
                    qrObj = new QRCode(qrcodeDiv, {
                        text: rawText,
                        width: size,
                        height: size,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.M
                    });
                }

                setTimeout(renderQR, 100);

                function downloadQR() {
                    var img = qrcodeDiv.querySelector("img");
                    if (img && img.src) {
                        var link = document.createElement('a');
                        link.download = "QR_" + certName + ".png";
                        link.href = img.src;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert("Tunggu sebentar, QR sedang dirender...");
                    }
                }
            </script>
        </div>
    <?php endif; ?>

    <?php if (!$qrContent): ?>
    <div class="bg-slate-900 border border-slate-700 rounded-2xl p-6 shadow-xl">
        <form method="POST">
            <label class="block text-slate-300 text-sm mb-2">Paste String Code:</label>
            <div class="text-[10px] text-slate-500 mb-2 font-mono bg-slate-950 p-2 rounded border border-slate-800">
                Format: [Sertifikat] [Job] [Tgl] [Blanko]<br>
                Contoh: 0038/26/MAR-D M0126010038DC0801 06/01/2026 M-31.f.0003
            </div>
            
            <textarea name="string_code" rows="4" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-4 text-white font-mono focus:border-indigo-500 outline-none placeholder-slate-600" placeholder="Paste di sini..." required></textarea>
            
            <button type="submit" name="btn_generate" class="w-full mt-4 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold shadow-lg transition transform active:scale-95">
                Generate & Update Usage
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include '../views/partials/footer.php'; ?>