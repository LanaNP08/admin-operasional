<?php
// === KONFIGURASI URL MANUAL (FINAL) ===
// Pastikan domain benar & tanpa slash di akhir
$baseUrl = 'https://anindyamarine.site';

// Data User & Notif
$user = $layout->getUserData();
$notifList = $layout->getNotifications();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Operasional Pro</title>
    
    <script>
        const BASE_URL = '<?php echo $baseUrl; ?>';
        console.log("System Running at: " + BASE_URL);
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/css/style.css?v=<?php echo time(); ?>">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { dark: { 900: '#0f172a', 950: '#020617' } },
                    animation: { 'fade-in-up': 'fadeInUp 0.5s ease-out' },
                    keyframes: {
                        fadeInUp: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } }
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased selection:bg-blue-500/30 selection:text-blue-200 bg-[#020617] text-slate-300">
    
    <div class="hidden md:-ml-64"></div>


    <div class="flex h-screen w-full">
        
        <aside id="sidebar" class="fixed md:relative inset-y-0 left-0 w-64
         bg-[#020617] border-r border-white/5
         flex flex-col z-50
         transform -translate-x-full md:translate-x-0
         transition-all duration-300 ease-in-out">


            
            <div class="h-20 flex items-center gap-3 px-6 border-b border-white/5 shrink-0">
                <img src="<?php echo $baseUrl; ?>/public/assets/img/logo.png" alt="Logo" class="h-8 w-auto">
                <div id="sidebarText" class="overflow-hidden whitespace-nowrap transition-all duration-300">
                    <h1 class="text-sm font-bold text-white tracking-wide">OPERATIONAL</h1>
                    <p class="text-[10px] text-blue-400 font-medium tracking-wider">DASHBOARD SYSTEM</p>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto custom-scrollbar">
                <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-2">Menu Utama</p>
                
                <a href="<?php echo $baseUrl; ?>/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('index.php'); ?>">
                    <svg class="h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    <span class="font-medium text-sm whitespace-nowrap">Dashboard</span>
                </a>
                
                <?php if($user['role'] != 'tamu'): ?>
                    <a href="<?php echo $baseUrl; ?>/logbook.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('logbook.php'); ?>">
                        <svg class="h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span class="font-medium text-sm whitespace-nowrap">Log Book Pekerjaan</span>
                    </a>
                <?php endif; ?>

                <a href="<?php echo $baseUrl; ?>/invoice.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('invoice.php'); ?>">
                    <svg class="h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 4h6m-6 4h6M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="font-medium text-sm whitespace-nowrap">Report Invoice</span>
                </a>

                <?php if($user['role'] != 'tamu'): ?>
    <a href="<?php echo $baseUrl; ?>/documents/create.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('create.php'); ?>">
        <svg class="h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        <span class="font-medium text-sm whitespace-nowrap">Generate Sertifikat QR</span>
    </a>
<?php endif; ?>

                <a href="<?php echo $baseUrl; ?>/monitoring.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('monitoring.php'); ?>">
                    <svg class="h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <span class="font-medium text-sm whitespace-nowrap">Monitoring Alat</span>
                </a>

                <?php if($user['role'] != 'tamu'): ?>
                    <a href="<?php echo $baseUrl; ?>/settlemen.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('settlemen.php'); ?>">
                        <svg class="h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span class="font-medium text-sm whitespace-nowrap">Doc Settlemen</span>
                    </a>
                <?php endif; ?>

                <a href="<?php echo $baseUrl; ?>/freelance.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('freelance.php'); ?>">
                    <svg class="h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span class="font-medium text-sm whitespace-nowrap">Data Freelance</span>
                </a>

                <?php if($user['role'] == 'super_admin'): ?>
                    <div class="px-4 pt-6 pb-2"><p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Admin Zone</p></div>
                    <a href="<?php echo $baseUrl; ?>/users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl group transition-all <?php echo $layout->isActive('users.php'); ?>">
                        <svg class="h-5 w-5 text-purple-400 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span class="font-medium text-sm whitespace-nowrap text-purple-200">Kelola Users</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="p-4 border-t border-white/5 bg-slate-900/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs uppercase shadow-lg border border-white/10 shrink-0">
                            <?php echo $user['initial']; ?>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-bold text-white truncate"><?php echo $user['username']; ?></p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-wider truncate"><?php echo str_replace('_', ' ', $user['role']); ?></p>
                        </div>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/logout.php" class="text-slate-500 hover:text-red-400 p-2 rounded-lg hover:bg-white/5 transition" title="Keluar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </a>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 relative h-screen bg-[#020617] w-full transition-all duration-300" id="mainContent">
            
            <header class="h-20 bg-[#020617]/80 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-4 md:px-8 sticky top-0 z-40">
                <button id="sidebarToggle" class="relative z-[100] p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                
                <div class="flex items-center gap-6">
                    <div class="text-right hidden md:block">
                        <div id="liveClock" class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400 font-mono tracking-widest leading-none">00:00:00</div>
                        <div id="liveDate" class="text-[10px] text-slate-500 font-bold uppercase leading-none mt-1">Memuat...</div>
                    </div>

                    <div class="relative group">
                        <button id="btnNotif" class="p-2.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition relative">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span id="notifBadge" class="hidden absolute top-2 right-2 h-2.5 w-2.5 bg-red-500 rounded-full border border-[#020617] animate-pulse"></span>
                        </button>

                        <div id="notifDropdown" class="hidden absolute right-0 mt-4 w-80 bg-slate-900 border border-white/10 rounded-2xl overflow-hidden z-50 origin-top-right transition-all shadow-2xl">
                            <div class="p-4 border-b border-white/5 flex justify-between bg-slate-950">
                                <span class="text-xs font-bold text-white uppercase tracking-wider">Notifikasi</span>
                                <span id="notifCount" class="text-[10px] bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded-full border border-blue-500/20">0 Baru</span>
                            </div>
                            <div id="notifListContainer" class="max-h-72 overflow-y-auto custom-scrollbar bg-slate-900">
                                <div class="p-6 text-center text-slate-500 text-xs">Belum ada notifikasi baru.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar relative">
                <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
                    <div class="absolute top-0 left-1/4 w-[500px] h-[500px] bg-blue-600/5 rounded-full blur-[120px]"></div>
                </div>
                <div class="relative z-10">

