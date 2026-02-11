// public/js/script.js

document.addEventListener("DOMContentLoaded", function () {
    console.log("System Ready. Connected to: " + (typeof BASE_URL !== 'undefined' ? BASE_URL : 'Unknown'));

    // === 1. CLOCK & DATE ===
    function updateTime() {
        const now = new Date();
        const clock = document.getElementById("liveClock");
        const date = document.getElementById("liveDate");
        
        if(clock) clock.innerText = now.toLocaleTimeString("id-ID", { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
        if(date) date.innerText = now.toLocaleDateString("id-ID", { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
    setInterval(updateTime, 1000);
    updateTime();

    // === 2. SIDEBAR TOGGLE UNIVERSAL (DESKTOP + MOBILE) ===
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");

    if (sidebar && toggleBtn) {
        toggleBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (window.innerWidth < 768) {
                // MOBILE
                sidebar.classList.toggle("-translate-x-full");
                document.body.classList.toggle("overflow-hidden");
            } else {
                // DESKTOP
                sidebar.classList.toggle("md:-ml-64");

                const hidden = sidebar.classList.contains("md:-ml-64");
                localStorage.setItem("sidebarState", hidden ? "hidden" : "visible");
            }
        });

        // Klik luar (mobile)
        document.addEventListener("click", function (e) {
            if (
                window.innerWidth < 768 &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)
            ) {
                sidebar.classList.add("-translate-x-full");
                document.body.classList.remove("overflow-hidden");
            }
        });

        // Restore desktop state
        const isHidden = localStorage.getItem("sidebarState") === "hidden";
        if (window.innerWidth >= 768 && isHidden) {
            sidebar.classList.add("md:-ml-64");
        }
    }

    // === 3. NOTIFIKASI REAL-TIME ===
    const btnNotif = document.getElementById("btnNotif");
    const notifDropdown = document.getElementById("notifDropdown");
    const badge = document.getElementById("notifBadge");
    const countEl = document.getElementById("notifCount");
    const listContainer = document.getElementById("notifListContainer");

    // Toggle Notif
    if(btnNotif && notifDropdown) {
        btnNotif.addEventListener("click", function (e) {
            e.stopPropagation();
            notifDropdown.classList.toggle("hidden");
            
            // Mark read saat dibuka
            if (!notifDropdown.classList.contains("hidden") && badge && !badge.classList.contains("hidden")) {
                markAsRead();
            }
        });
    }

    // Close Notif outside
    document.addEventListener("click", function(e) {
        if(notifDropdown && !notifDropdown.contains(e.target) && btnNotif && !btnNotif.contains(e.target)) {
            notifDropdown.classList.add("hidden");
        }
    });

    function fetchNotif() {
        if (typeof BASE_URL === 'undefined') return;

        fetch(BASE_URL + '/ajax_notif.php')
            .then(r => r.json())
            .then(data => {
                // Badge Logic
                if (data.unread > 0) {
                    if(badge) badge.classList.remove("hidden");
                    if(countEl) {
                        countEl.innerText = data.unread + " Baru";
                        countEl.className = "text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full border border-red-500/50";
                    }
                } else {
                    if(badge) badge.classList.add("hidden");
                    if(countEl) {
                        countEl.innerText = "0 Baru";
                        countEl.className = "text-[10px] bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded-full border border-blue-500/20";
                    }
                }

                // List Logic
                if (listContainer && data.list && data.list.length > 0) {
                    listContainer.innerHTML = data.list.map(item => `
                        <a href="${item.link}" class="block p-4 border-b border-white/5 hover:bg-white/5 transition ${item.is_read == 0 ? 'bg-slate-800/50' : ''}">
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 rounded-full bg-blue-600/20 flex items-center justify-center text-blue-400 text-xs shrink-0">📦</div>
                                <div>
                                    <p class="text-xs text-slate-300 leading-relaxed">${item.text}</p>
                                    <p class="text-[10px] text-slate-500 mt-1">${item.waktu} WIB</p>
                                </div>
                            </div>
                        </a>
                    `).join('');
                } else if(listContainer) {
                    listContainer.innerHTML = '<div class="p-6 text-center text-slate-500 text-xs">Belum ada notifikasi.</div>';
                }
            })
            .catch(() => {});
    }

    function markAsRead() {
        if (typeof BASE_URL === 'undefined') return;
        const fd = new FormData();
        fd.append('mark_all_read', '1');
        fetch(BASE_URL + '/ajax_notif.php', { method: 'POST', body: fd })
            .then(() => {
                if(badge) badge.classList.add("hidden");
                if(countEl) countEl.innerText = "0 Baru";
            });
    }

    // Jalankan
    fetchNotif();
    setInterval(fetchNotif, 5000);
});