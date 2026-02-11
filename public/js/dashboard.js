// public/js/dashboard.js

// FIX: Cek apakah library Chart sudah termuat agar tidak error console
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
}

let chartOmzetInstance = null;
let chartClientInstance = null;
let currentYear = new Date().getFullYear();

document.addEventListener("DOMContentLoaded", function() {
    const yearSelect = document.getElementById('yearFilter');
    if(yearSelect) {
        currentYear = yearSelect.value;
        yearSelect.addEventListener('change', function() {
            currentYear = this.value;
            fetchDataDashboard();
        });
    }
    
    fetchDataDashboard();
});

function fetchDataDashboard() {
    const loader = document.getElementById('val_omzet');
    if(loader) loader.innerText = "Loading...";
    
    const formData = new FormData();
    formData.append('type', 'init_dashboard');
    formData.append('year', currentYear);

    // Pastikan BASE_URL ada
    const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/ajax_dashboard.php';

    fetch(url, { method: 'POST', body: formData })
        .then(response => {
            if(!response.ok) throw new Error("HTTP Error " + response.status);
            return response.json();
        })
        .then(data => {
            if(data.status === 'error') {
                console.error("API Error:", data.message);
                return;
            }
            updateUI(data);
            initCharts(data);
        })
        .catch(err => {
            console.error("Dashboard Fetch Error:", err);
            if(loader) loader.innerText = "Error";
        });
}

function updateUI(data) {
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if(el) {
            el.innerText = val;
            el.classList.remove('animate-pulse');
        }
    };

    const labelTahun = document.getElementById('labelTahunOverview');
    if(labelTahun) labelTahun.innerText = data.year_display;

    if(data.omzet) {
        setText('val_omzet', data.omzet.total_formatted);
        setText('val_avg_omzet', data.omzet.average_formatted);
    }

    if(data.pending) {
        setText('val_pending_rp', data.pending.total_formatted);
        setText('val_pending_count', data.pending.count);
        setText('val_pending_ratio', data.pending.ratio + '%');
    }

    if(data.success) {
        setText('val_success_count', data.success.count);
        setText('val_success_rate', data.success.rate + '%');
    }

    if(data.ops) {
        setText('val_ops_alat', data.ops.alat.total);
        setText('detail_ops_alat', `✅ ${data.ops.alat.baik} Baik • ❌ ${data.ops.alat.rusak} Rusak`);
        setText('val_ops_helper', data.ops.helper);
        setText('val_ops_warning', data.ops.warning);
        setText('val_ops_doc', data.ops.doc_pending);
        setText('val_ops_overdue', data.ops.overdue);

        const cardWarn = document.getElementById('card_warning');
        if(cardWarn) {
            if(data.ops.warning > 0) cardWarn.classList.add('border-yellow-500/50', 'bg-yellow-900/10');
            else cardWarn.classList.remove('border-yellow-500/50', 'bg-yellow-900/10');
        }

        const cardOver = document.getElementById('card_overdue');
        const valOver = document.getElementById('val_ops_overdue');
        if(cardOver) {
            if(data.ops.overdue > 0) {
                cardOver.classList.add('border-red-500', 'bg-red-900/20');
                if(valOver) valOver.classList.add('text-red-400');
            } else {
                cardOver.classList.remove('border-red-500', 'bg-red-900/20');
                if(valOver) valOver.classList.remove('text-red-400');
            }
        }
    }
}

function initCharts(data) {
    // FIX: Safety check jika library Chart gagal load
    if (typeof Chart === 'undefined') return;

    // 1. Chart Omzet (Bar)
    const ctxBar = document.getElementById('omzetChart');
    if(ctxBar && data.charts && data.charts.bulan) {
        if(chartOmzetInstance) chartOmzetInstance.destroy();
        
        const gradient = ctxBar.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#3b82f6');
        gradient.addColorStop(1, '#1e40af');

        chartOmzetInstance = new Chart(ctxBar, {
            type: 'bar',
            data: { 
                labels: data.charts.bulan.labels, 
                datasets: [{ 
                    label: 'Omzet', 
                    data: data.charts.bulan.values, 
                    backgroundColor: gradient, 
                    hoverBackgroundColor: '#60a5fa',
                    borderRadius: 4
                }] 
            },
            options: {
                responsive: true, 
                maintainAspectRatio: false,
                onClick: (e, elements) => {
                    if(elements.length > 0) {
                        const index = elements[0].index;
                        showDetail('detail_bulan', index);
                    }
                },
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                    y: { 
                        grid: { color: '#1e293b' }, 
                        ticks: { 
                            callback: (val) => (val/1000000) + 'jt',
                            color: '#94a3b8'
                        } 
                    } 
                }
            }
        });
    }

    // 2. Chart Client (Doughnut)
    const ctxPie = document.getElementById('clientChart');
    if(ctxPie && data.charts && data.charts.client) {
        if(chartClientInstance) chartClientInstance.destroy();
        
        chartClientInstance = new Chart(ctxPie, {
            type: 'doughnut',
            data: { 
                labels: data.charts.client.labels, 
                datasets: [{ 
                    data: data.charts.client.values, 
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e', '#10b981'], 
                    borderColor: '#0f172a', borderWidth: 4
                }] 
            },
            options: {
                responsive: true, 
                maintainAspectRatio: false,
                cutout: '70%',
                onClick: (e, elements) => {
                    if(elements.length > 0) {
                        const index = elements[0].index;
                        const clientName = data.charts.client.full_names[index];
                        showDetail('detail_client', clientName);
                    }
                },
                plugins: { 
                    legend: { position: 'right', labels: { color: '#cbd5e1', boxWidth: 10, font: { size: 11 } } } 
                }
            }
        });
    }
    
    document.querySelectorAll('[id^="loaderChart"]').forEach(el => el.style.display = 'none');
}

function showDetail(type, param) {
    const modal = document.getElementById('modalDetail');
    const content = document.getElementById('modalContent');
    
    if(modal && content) {
        modal.classList.remove('hidden');
        content.innerHTML = '<div class="text-center py-10 text-slate-500 animate-pulse">Mengambil data detail...</div>';
        
        const fd = new FormData();
        fd.append('type', type);
        fd.append('param', param);
        fd.append('year', currentYear);

        const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/ajax_dashboard.php';

        fetch(url, { method: 'POST', body: fd })
            .then(r => r.text())
            .then(html => { content.innerHTML = html; })
            .catch(err => {
                content.innerHTML = '<div class="text-center py-10 text-red-500">Gagal memuat detail.</div>';
            });
    }
}