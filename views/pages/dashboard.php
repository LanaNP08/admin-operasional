<div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4 animate-fade-in-up">
    <div>
        <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight">Executive Dashboard</h1>
        <p class="text-slate-400 mt-2 text-sm">Performance Overview Tahun <span id="labelTahunOverview" class="text-blue-400 font-bold"><?php echo $tahunIni; ?></span></p>
    </div>
    <div class="flex items-center gap-3">
        <div class="relative">
            <select id="yearFilter" onchange="loadDashboard(this.value)" class="appearance-none bg-slate-900 border border-slate-700 text-white py-2 pl-4 pr-10 rounded-xl text-sm focus:outline-none focus:border-blue-500 hover:border-slate-600 transition cursor-pointer font-bold shadow-lg">
                <?php foreach($years as $y): ?>
                    <option value="<?php echo $y; ?>" class="bg-slate-900 text-white"><?php echo $y; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <span class="px-3 py-2 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold flex items-center gap-2">Live</span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in-up" style="animation-delay: 0.1s;">
    <div class="relative group p-[1px] rounded-2xl bg-gradient-to-b from-blue-500/50 to-blue-900/10">
        <div class="relative h-full bg-slate-900/90 backdrop-blur-xl rounded-2xl p-6">
            <p class="text-blue-300 text-xs font-bold uppercase mb-2">Total Omzet (Net)</p>
            <h3 id="val_omzet" class="text-3xl font-bold text-white">...</h3>
            <div class="mt-4 flex items-center text-xs text-slate-400 bg-white/5 p-2 rounded-lg w-fit"><span class="text-emerald-400 font-bold mr-2">📊 Avg:</span> <span id="val_avg_omzet">...</span></div>
        </div>
    </div>
    <div class="relative group p-[1px] rounded-2xl bg-gradient-to-b from-red-500/50 to-red-900/10">
        <div class="relative h-full bg-slate-900/90 backdrop-blur-xl rounded-2xl p-6">
            <p class="text-red-300 text-xs font-bold uppercase mb-2">Potensi Pending</p>
            <h3 id="val_pending_rp" class="text-3xl font-bold text-white">...</h3>
            <div class="mt-4 flex items-center justify-between"><div class="inline-flex items-center px-2 py-1 rounded-lg bg-red-500/10 text-red-400 text-[10px] font-medium">⚠️ <span id="val_pending_count" class="mx-1 font-bold">...</span> Docs</div></div>
        </div>
    </div>
    <div class="relative group p-[1px] rounded-2xl bg-gradient-to-b from-emerald-500/50 to-emerald-900/10">
        <div class="relative h-full bg-slate-900/90 backdrop-blur-xl rounded-2xl p-6">
            <p class="text-emerald-300 text-xs font-bold uppercase mb-2">Invoice Terkirim</p>
            <h3 class="text-3xl font-bold text-white"><span id="val_success_count">...</span> <span class="text-lg text-slate-500 font-normal">Docs</span></h3>
            <div class="mt-4 text-xs text-slate-400"><span class="text-emerald-400 font-bold mr-2">✓ Rate:</span> <span id="val_success_rate">...</span></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 animate-fade-in-up" style="animation-delay: 0.2s;">
    <div class="lg:col-span-2 glass-panel rounded-2xl p-6 relative bg-slate-900 border border-white/5">
        <h3 class="text-base font-bold text-white mb-6 flex items-center gap-2">
            📊 Tren Pendapatan Bulanan
            <span class="text-[10px] text-slate-500 font-normal border border-slate-700 px-2 py-0.5 rounded ml-auto">Klik bar untuk detail</span>
        </h3>
        <div class="w-full h-72 relative" id="containerOmzetChart">
            <div id="loaderChart1" class="absolute inset-0 flex items-center justify-center text-slate-500 text-sm">Memuat Grafik...</div>
            <canvas id="omzetChart"></canvas>
        </div>
    </div>
    <div class="lg:col-span-1 glass-panel rounded-2xl p-6 bg-slate-900 border border-white/5">
        <h3 class="text-base font-bold text-white mb-6 flex items-center gap-2">
            🏆 Top 5 Pelanggan
            <span class="text-[10px] text-slate-500 font-normal border border-slate-700 px-2 py-0.5 rounded ml-auto">Klik utk detail</span>
        </h3>
        <div class="w-full h-60 relative" id="containerClientChart">
            <div id="loaderChart2" class="absolute inset-0 flex items-center justify-center text-slate-500 text-sm">Memuat Data...</div>
            <canvas id="clientChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8 animate-fade-in-up" style="animation-delay: 0.3s;">
    <a href="monitoring.php" class="bg-slate-900 p-4 rounded-xl border border-white/5 hover:border-blue-500/50 transition"><div id="val_ops_alat" class="text-2xl font-bold text-white mb-1">-</div><div class="text-[10px] text-slate-400 font-semibold uppercase">Total Alat</div></a>
    <a href="monitoring.php?filter=warning" class="bg-slate-900 p-4 rounded-xl border border-white/5 hover:border-yellow-400 transition"><div id="val_ops_warning" class="text-2xl font-bold text-white mb-1">-</div><div class="text-[10px] text-slate-400 font-semibold uppercase">Warning Kalibrasi</div></a>
    <a href="invoice.php?f_status=PENDING" class="bg-slate-900 p-4 rounded-xl border border-white/5 hover:border-red-400 transition"><div id="val_ops_doc" class="text-2xl font-bold text-white mb-1">...</div><div class="text-[10px] text-slate-400 font-semibold uppercase">Doc Pending</div></a>
    <a href="freelance.php" class="bg-slate-900 p-4 rounded-xl border border-white/5 hover:border-purple-500/50 transition"><div id="val_ops_helper" class="text-2xl font-bold text-white mb-1">-</div><div class="text-[10px] text-slate-400 font-semibold uppercase">Total Freelance</div></a>
    <a href="invoice.php?f_status=OVERDUE" class="bg-slate-900 p-4 rounded-xl border border-white/5 hover:border-red-500 transition"><div id="val_ops_overdue" class="text-2xl font-bold text-white mb-1">...</div><div class="text-[10px] text-slate-400 font-semibold uppercase">Overdue Delivery</div></a>
</div>

<div id="modalDetail" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden animate-fade-in-up flex flex-col max-h-[80vh]">
        <div class="bg-slate-800 p-4 flex justify-between items-center border-b border-slate-700">
            <h3 class="text-white font-bold text-lg" id="modalTitle">Detail Invoice</h3>
            <button onclick="document.getElementById('modalDetail').classList.add('hidden')" class="text-slate-400 hover:text-white bg-slate-700 hover:bg-red-500 rounded-lg p-1.5 transition">✕</button>
        </div>
        <div class="p-0 overflow-auto flex-grow custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-800 text-slate-400 text-[10px] uppercase font-bold sticky top-0">
                    <tr>
                        <th class="px-4 py-3">Tgl</th>
                        <th class="px-4 py-3">No Doc</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3 text-right">Net</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="modalContent" class="text-sm divide-y divide-slate-800 text-slate-300">
                </tbody>
            </table>
        </div>
        <div class="bg-slate-800 p-3 text-right border-t border-slate-700">
             <button onclick="document.getElementById('modalDetail').classList.add('hidden')" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs font-bold">Tutup</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartOmzet = null;
let chartClient = null;

function fmtRp(n) {
    if(isNaN(n)) return "Rp 0";
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
}

function resetCanvas(id, containerId) {
    const container = document.getElementById(containerId);
    const oldCanvas = document.getElementById(id);
    if(container && oldCanvas) oldCanvas.remove();
    const newCanvas = document.createElement('canvas');
    newCanvas.id = id;
    container.appendChild(newCanvas);
    return newCanvas;
}

function loadDashboard(year) {
    document.getElementById('labelTahunOverview').innerText = year;
    const elems = ['val_omzet', 'val_avg_omzet', 'val_pending_rp', 'val_pending_count', 'val_success_count', 'val_ops_doc', 'val_ops_overdue'];
    elems.forEach(id => document.getElementById(id).innerText = "...");

    fetch('dashboard.php?ajax_action=get_stats&year=' + year)
    .then(r => r.json())
    .then(d => {
        const s = d.summary;
        
        document.getElementById('val_omzet').innerText = fmtRp(s.total_omzet || 0);
        document.getElementById('val_avg_omzet').innerText = fmtRp((s.total_omzet || 0) / 12);
        document.getElementById('val_pending_rp').innerText = fmtRp(s.pending_rp || 0);
        document.getElementById('val_pending_count').innerText = s.pending_count;
        document.getElementById('val_ops_doc').innerText = s.pending_count;
        document.getElementById('val_success_count').innerText = s.success_count || 0;
        document.getElementById('val_ops_overdue').innerText = s.overdue_count || 0;
        
        const total = parseInt(s.total_docs || 1);
        const success = parseInt(s.success_count || 0);
        document.getElementById('val_success_rate').innerText = total > 0 ? Math.round((success/total)*100) + "%" : "0%";

        // 1. CHART OMZET
        document.getElementById('loaderChart1').style.display = 'none';
        resetCanvas('omzetChart', 'containerOmzetChart');
        const ctx1 = document.getElementById('omzetChart').getContext('2d');
        
        chartOmzet = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agust','Sep','Okt','Nov','Des'],
                datasets: [{
                    label: 'Omzet',
                    data: d.chart_monthly,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: '#3b82f6', borderWidth: 1, borderRadius: 4, hoverBackgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#94a3b8' } }, 
                    x: { grid: { display: false }, ticks: { color: '#94a3b8' } } 
                },
                onClick: (e, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const month = index + 1;
                        openDetailModal(year, month);
                    }
                }
            }
        });

        // 2. CHART CLIENT (DENGAN KLIK EVENT BARU)
        document.getElementById('loaderChart2').style.display = 'none';
        resetCanvas('clientChart', 'containerClientChart');
        const ctx2 = document.getElementById('clientChart').getContext('2d');
        
        chartClient = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: d.top_clients.map(c => c.customer_name),
                datasets: [{
                    data: d.top_clients.map(c => c.total),
                    backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'], borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { color: '#cbd5e1', font: { size: 10 } } } },
                // [BARU] EVENT KLIK CLIENT
                onClick: (e, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const customerName = chartClient.data.labels[index];
                        openClientModal(year, customerName);
                    }
                }
            }
        });
    });
}

// FUNGSI MODAL BULANAN
function openDetailModal(year, month) {
    const months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    document.getElementById('modalTitle').innerText = `Invoice Bulan: ${months[month]} ${year}`;
    fetchData(`dashboard.php?ajax_action=get_month_details&year=${year}&month=${month}`);
}

// [BARU] FUNGSI MODAL CLIENT
function openClientModal(year, customerName) {
    document.getElementById('modalTitle').innerText = `Invoice: ${customerName}`;
    // Encode URI Component penting untuk nama PT yang ada spasi/titik
    fetchData(`dashboard.php?ajax_action=get_client_details&year=${year}&customer=${encodeURIComponent(customerName)}`);
}

// FUNGSI FETCH & RENDER (HELPER AGAR TIDAK DUPLIKASI KODE)
function fetchData(url) {
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-slate-500 animate-pulse">Memuat data...</td></tr>';
    document.getElementById('modalDetail').classList.remove('hidden');

    fetch(url)
    .then(r => r.json())
    .then(data => {
        let html = '';
        if(data.length === 0) {
            html = '<tr><td colspan="5" class="p-6 text-center text-slate-500">Tidak ada data.</td></tr>';
        } else {
            data.forEach(row => {
                let tgl = row.doc_date.split('-')[2] + '/' + row.doc_date.split('-')[1];
                let net = fmtRp(row.net);
                html += `
                <tr class="hover:bg-slate-800/50 transition border-b border-slate-800 last:border-0">
                    <td class="px-4 py-3 text-slate-400 font-mono">${tgl}</td>
                    <td class="px-4 py-3 font-bold text-white">${row.doc_no}</td>
                    <td class="px-4 py-3 truncate max-w-[150px]" title="${row.customer_name}">${row.customer_name}</td>
                    <td class="px-4 py-3 text-right font-mono text-emerald-400">${net}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border ${row.status_class}">
                            ${row.status_label}
                        </span>
                    </td>
                </tr>`;
            });
        }
        modalContent.innerHTML = html;
    })
    .catch(err => {
        modalContent.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-500">Gagal memuat detail.</td></tr>';
        console.error(err);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => { loadDashboard(document.getElementById('yearFilter').value); }, 500);
});
</script>