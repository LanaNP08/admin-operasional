<form method="POST" id="bulkForm" class="hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 bg-slate-800 text-white p-3 rounded-xl shadow-2xl flex items-center gap-3 border border-slate-700 animate-bounce-up">
    <div class="text-xs font-bold bg-blue-600 px-2 py-1 rounded text-white" id="countSelected">0 Terpilih</div>
    
    <button type="button" onclick="resetSelection()" class="text-[10px] text-slate-400 hover:text-red-400 border-r border-slate-600 pr-3 mr-1">
        Reset
    </button>

    <div class="flex items-center gap-2">
        <label class="text-[10px] uppercase font-bold text-slate-400">Manual:</label>
        <input type="date" name="bulk_date" class="bg-slate-900 border border-slate-600 rounded px-2 py-1 text-xs w-20 text-center text-white" placeholder="Pilih Tgl...">
        <button type="submit" name="btn_bulk_send" class="bg-emerald-600 px-3 py-1 rounded text-xs font-bold hover:bg-emerald-500">✓</button>
    </div>
    
    <div class="flex items-center gap-2 border-l border-slate-600 pl-3">
        <input type="hidden" name="delivery_address" id="hiddenAddress">
        
        <button type="submit" name="btn_bulk_excel" class="bg-green-600 hover:bg-green-500 text-white px-4 py-1.5 rounded text-xs font-bold flex items-center gap-2 shadow-lg transition transform hover:scale-105">
            Excel
        </button>

        <button type="button" onclick="openAddressModal()" class="bg-yellow-600 hover:bg-yellow-500 text-white px-4 py-1.5 rounded text-xs font-bold flex items-center gap-2 shadow-lg">
            Buat Tanda Terima (QR)
        </button>
        <button type="submit" name="btn_create_batch" id="realSubmitBatch" class="hidden"></button>
    </div>

    <div id="hiddenInputs"></div>
</form>

<div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <?php if(isset($viewData['viewMode']) && $viewData['viewMode']=='trash'): ?>
                <span class="bg-red-600 p-1.5 rounded-lg text-white">🗑️</span> Tong Sampah
            <?php else: ?>
                <span class="bg-cyan-600 p-1.5 rounded-lg text-white">📄</span> Report Invoice
            <?php endif; ?>
        </h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Total: <?php echo number_format($viewData['totalData'] ?? 0); ?> Data.</p>
    </div>
    
    <div class="flex gap-2 relative">
        <?php if(isset($viewData['viewMode']) && $viewData['viewMode'] == 'active'): ?>
            <div class="relative group">
                <button onclick="document.getElementById('colMenu').classList.toggle('hidden')" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-white px-4 py-2 rounded-lg font-bold text-sm flex items-center gap-2 shadow-sm">Atur Kolom ▾</button>
                <div id="colMenu" class="hidden absolute top-12 right-0 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 p-2">
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded text-sm text-slate-700 dark:text-slate-300"><input type="checkbox" onclick="toggleCol(6)" class="rounded text-cyan-600"> Tax</label>
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded text-sm text-slate-700 dark:text-slate-300"><input type="checkbox" onclick="toggleCol(7)" class="rounded text-cyan-600"> Total</label>
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded text-sm text-slate-700 dark:text-slate-300"><input type="checkbox" onclick="toggleCol(8)" class="rounded text-cyan-600"> No Faktur</label>
                </div>
            </div>
            <?php if(!$viewData['isTamu']): ?><button onclick="toggleImport()" class="bg-cyan-600 hover:bg-cyan-500 text-white px-4 py-2 rounded-lg font-bold text-sm shadow">+ Data</button><?php endif; ?>
            <a href="export_invoice.php" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg font-bold text-sm shadow">Excel</a>
            <a href="invoice.php?view=trash" class="bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 px-3 py-2 rounded-lg border border-red-200 dark:border-red-900 font-bold text-sm">🗑️</a>
        <?php else: ?>
            <a href="invoice.php" class="bg-slate-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow">Kembali</a>
        <?php endif; ?>
    </div>
</div>

<?php if(!empty($viewData['pesan'])): ?><div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200"><?php echo $viewData['pesan']; ?></div><?php endif; ?>

<?php if(!$viewData['isTamu']): ?>
<div id="area_import" class="hidden mb-6 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl relative animate-fade-in-up">
    <button onclick="toggleImport()" class="absolute top-4 right-4 text-slate-400 hover:text-red-500">✕</button>
    <h3 class="text-slate-800 dark:text-white font-bold mb-2">Paste Excel</h3>
    <form method="POST">
        <textarea name="raw_data" rows="8" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-white text-xs p-3 rounded focus:outline-none font-mono" placeholder="Paste di sini..."></textarea>
        <div class="mt-3 flex justify-end"><button type="submit" name="btn_import_paste" class="bg-blue-600 text-white px-4 py-2 rounded font-bold shadow">Proses</button></div>
    </form>
</div>
<?php endif; ?>

<form id="filterForm" method="GET" class="hidden"></form>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden flex flex-col">
    <div class="overflow-auto custom-scrollbar relative" style="max-height: 70vh;">
        <table class="w-full text-left text-sm border-collapse whitespace-nowrap" id="tableInvoice">
            <thead class="bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-400 uppercase font-bold text-xs border-b border-slate-200 dark:border-slate-700 sticky top-0 z-20">
                <tr>
                    <?php if($viewData['viewMode'] == 'active' && !$viewData['isTamu']): ?>
                    <th class="px-3 py-3 w-10 text-center border-r border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-950">
                        <input type="checkbox" id="checkAllBox" onclick="toggleSelectPage(this)" class="rounded border-slate-400">
                    </th>
                    <?php endif; ?>
                    <th class="px-4 py-3 w-12 text-center border-r border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-950">No</th>
                    <th class="col-1 px-2 py-3 min-w-[140px] border-r bg-slate-100 dark:bg-slate-950"><div class="mb-1">DOC NO</div><input type="text" name="f_doc" value="<?php echo htmlspecialchars($viewData['filters']['f_doc']); ?>" form="filterForm" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs px-2 py-1 rounded"></th>
                    <th class="col-2 px-2 py-3 min-w-[100px] border-r bg-slate-100 dark:bg-slate-950"><div class="mb-1">DATE</div><input type="text" name="f_date" value="<?php echo htmlspecialchars($viewData['filters']['f_date']); ?>" form="filterForm" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs px-2 py-1 rounded"></th>
                    <th class="col-3 px-2 py-3 min-w-[180px] border-r bg-slate-100 dark:bg-slate-950"><div class="mb-1">CUSTOMER</div><input type="text" name="f_cust" value="<?php echo htmlspecialchars($viewData['filters']['f_cust']); ?>" form="filterForm" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs px-2 py-1 rounded"></th>
                    <th class="col-4 px-2 py-3 min-w-[200px] border-r bg-slate-100 dark:bg-slate-950"><div class="mb-1">REMARKS</div><input type="text" name="f_rem" value="<?php echo htmlspecialchars($viewData['filters']['f_rem']); ?>" form="filterForm" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs px-2 py-1 rounded"></th>
                    
                    <th class="col-5 px-2 py-3 min-w-[100px] text-right border-r bg-slate-100 dark:bg-slate-950">NET</th>
                    <th class="col-6 hidden px-2 py-3 min-w-[100px] text-right border-r bg-slate-100 dark:bg-slate-950">TAX</th>
                    <th class="col-7 hidden px-2 py-3 min-w-[120px] text-right border-r bg-slate-100 dark:bg-slate-950 text-blue-600">TOTAL</th>
                    <th class="col-8 hidden px-2 py-3 min-w-[120px] border-r bg-slate-100 dark:bg-slate-950"><div class="mb-1">NO FAKTUR</div><input type="text" name="f_fp" value="<?php echo htmlspecialchars($viewData['filters']['f_fp']); ?>" form="filterForm" onkeydown="if(event.key==='Enter') this.form.submit()" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs px-2 py-1 rounded"></th>
                    
                    <th class="col-9 px-2 py-3 min-w-[120px] text-center border-r bg-slate-100 dark:bg-slate-950">
                        <div class="mb-1 text-cyan-600">STATUS</div>
                        <select name="f_status" form="filterForm" onchange="this.form.submit()" class="w-full bg-slate-900/60 backdrop-blur-md border border-white/10 text-white text-xs px-3 py-2 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500/50 focus:bg-slate-900 transition-all cursor-pointer outline-none">
                            <option value="" class="bg-slate-900 text-slate-400">Semua</option>
                            <option value="PENDING" <?php echo $viewData['filters']['f_status']=='PENDING'?'selected':''; ?> class="bg-slate-900 text-white-400">Pending</option>
                            <option value="OTW" <?php echo $viewData['filters']['f_status']=='OTW'?'selected':''; ?> class="bg-slate-900 text-white-400">Sedang Dikirim</option>
                            <option value="RECEIVED" <?php echo $viewData['filters']['f_status']=='RECEIVED'?'selected':''; ?> class="bg-slate-900 text-white-400">Diterima</option>
                            <option value="OVERDUE" <?php echo $viewData['filters']['f_status']=='OVERDUE'?'selected':''; ?> class="bg-slate-900 text-red-500 font-bold">⚠️ Overdue (>7 Hari)</option>
                        </select>
                    </th>
                    <?php if(!$viewData['isTamu']): ?><th class="px-4 py-3 text-center min-w-[100px] bg-slate-100 dark:bg-slate-950 sticky right-0 z-30 shadow-[-5px_0_10px_-5px_rgba(0,0,0,0.1)]">AKSI</th><?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                <?php if(count($viewData['data']) > 0): $no = ($viewData['page'] - 1) * 100 + 1; foreach($viewData['data'] as $row): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition group">
                    <?php if($viewData['viewMode'] == 'active' && !$viewData['isTamu']): ?>
                    <td class="px-3 py-2 border-r border-slate-100 dark:border-slate-800 text-center">
                        <input type="checkbox" value="<?php echo $row['id']; ?>" onchange="handleCheck(this)" class="row-checkbox rounded cursor-pointer">
                    </td>
                    <?php endif; ?>
                    
                    <td class="px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500"><?php echo $no++; ?></td>
                    <td class="col-1 px-4 py-2 border-r border-slate-100 dark:border-slate-800 font-mono text-xs font-bold"><?php echo $row['doc_no']; ?></td>
                    <td class="col-2 px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-xs"><?php echo date('d/m/y', strtotime($row['doc_date'])); ?></td>
                    <td class="col-3 px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-xs font-semibold truncate max-w-xs cursor-text hover:bg-yellow-50 dark:hover:bg-yellow-900/20" ondblclick="makeEditable(this)" onblur="saveInline(this, '<?php echo $row['id']; ?>', 'customer_name')"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td class="col-4 px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-xs truncate max-w-sm text-slate-500 cursor-text hover:bg-yellow-50 dark:hover:bg-yellow-900/20" ondblclick="makeEditable(this)" onblur="saveInline(this, '<?php echo $row['id']; ?>', 'remarks')"><?php echo htmlspecialchars($row['remarks']); ?></td>
                    <td class="col-5 px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-right font-mono text-xs cursor-text hover:bg-yellow-50 dark:hover:bg-yellow-900/20" ondblclick="makeEditable(this)" onblur="saveInline(this, '<?php echo $row['id']; ?>', 'net')"><?php echo number_format($row['net'], 0, ',', '.'); ?></td>
                    <td class="col-6 hidden px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-right font-mono text-xs text-red-500 cursor-text hover:bg-yellow-50 dark:hover:bg-yellow-900/20" ondblclick="makeEditable(this)" onblur="saveInline(this, '<?php echo $row['id']; ?>', 'tax')"><?php echo number_format($row['tax'], 0, ',', '.'); ?></td>
                    <td class="col-7 hidden px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-right font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400"><?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                    <td class="col-8 hidden px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-xs font-mono cursor-text hover:bg-yellow-50 dark:hover:bg-yellow-900/20" ondblclick="makeEditable(this)" onblur="saveInline(this, '<?php echo $row['id']; ?>', 'fp')"><?php echo $row['fp']; ?></td>
                    
                    <td class="col-9 px-4 py-2 border-r border-slate-100 dark:border-slate-800 text-center text-xs relative group/status">
                        <?php 
                        if (!empty($row['batch_id'])) {
                            if (!empty($row['received_at']) && $row['received_at'] != '0000-00-00 00:00:00') {
                                // STATUS: DITERIMA
                                echo "<div class='flex flex-col items-center cursor-help'>";
                                echo "<div class='flex items-center gap-2'><span class='text-emerald-600 dark:text-emerald-400 font-bold'>DITERIMA</span><button onclick='viewProof({$row['batch_id']})' type='button' class='text-slate-400 hover:text-blue-500'><svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z' /><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' /></svg></button></div>";
                                echo "<span class='text-[10px] text-slate-400'>" . date('d/m/y H:i', strtotime($row['received_at'])) . "</span>";
                                echo "</div>";
                            } else {
                                // CEK OVERDUE
                                $isOverdue = false;
                                if (!empty($row['batch_created_at'])) {
                                    $batchDate = new DateTime($row['batch_created_at']);
                                    $now = new DateTime();
                                    $interval = $batchDate->diff($now);
                                    if ($interval->days > 7 && $interval->invert == 0) $isOverdue = true;
                                }

                                echo "<div class='flex flex-col items-center gap-1 cursor-help'>";
                                if ($isOverdue) {
                                    // STATUS: OVERDUE
                                    echo "<span class='text-red-500 font-extrabold bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded animate-pulse flex items-center gap-1'><svg class='w-3 h-3' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' /></svg>OVERDUE</span>";
                                    
                                    if(!$viewData['isTamu']) {
                                        echo "<button type='button' onclick='openOverdueModal({$row['batch_id']})' class='mt-1 bg-blue-600 hover:bg-blue-500 text-white px-2 py-0.5 rounded text-[10px] font-bold shadow flex items-center gap-1'><span>✓</span> Manual Terima</button>";
                                    }
                                } else {
                                    // STATUS: OTW
                                    echo "<span class='text-blue-600 dark:text-blue-400 font-bold animate-pulse'>OTW / DIKIRIM</span>";
                                    echo "<span class='text-[10px] text-slate-400'>Menunggu Konfirmasi</span>";
                                }
                                echo "</div>";
                            }

                            // TOOLTIP HOVER INFO
                            $tglKirim = !empty($row['batch_created_at']) ? date('d/m/Y H:i', strtotime($row['batch_created_at'])) : '-';
                            $tglTerima = !empty($row['received_at']) && $row['received_at'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($row['received_at'])) : 'Belum Diterima';
                            
                            echo "
                            <div class='absolute hidden group-hover/status:block z-50 bottom-full mb-2 left-1/2 transform -translate-x-1/2 w-48 bg-slate-800 text-white text-[10px] p-2 rounded shadow-xl border border-slate-600 text-left pointer-events-none'>
                                <div class='mb-1 border-b border-slate-600 pb-1 font-bold text-center text-yellow-400'>DETAIL PENGIRIMAN</div>
                                <div class='grid grid-cols-3 gap-1 mt-1'>
                                    <span class='text-slate-400'>Kirim:</span>
                                    <span class='col-span-2 font-mono'>$tglKirim</span>
                                    <span class='text-slate-400'>Terima:</span>
                                    <span class='col-span-2 font-mono ".($tglTerima=='Belum Diterima'?'text-red-300':'text-green-300')."'>$tglTerima</span>
                                </div>
                                <div class='mt-1 text-xs text-center text-slate-500'>Batch ID: #{$row['batch_id']}</div>
                            </div>";

                        } else {
                            // STATUS: MANUAL / PENDING
                            if ($row['sent_date'] && $row['sent_date'] != '0000-00-00') echo "<span class='text-emerald-600 font-bold cursor-help' title='Dikirim Manual: ".date('d/m/Y', strtotime($row['sent_date']))."'>TERKIRIM (MANUAL)</span><br><span class='text-[10px] text-slate-400'>".date('d/m/y', strtotime($row['sent_date']))."</span>";
                            else echo "<span class='text-red-500 font-bold'>PENDING</span>";
                        }
                        ?>
                    </td>

                    <?php if(!$viewData['isTamu']): ?>
                    <td class="px-4 py-2 text-center sticky right-0 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800 z-20 shadow-[-5px_0_10px_-5px_rgba(0,0,0,0.1)]">
                        <?php if($viewData['viewMode'] == 'active'): ?>
                            <button onclick="loadEditData(<?php echo $row['id']; ?>)" class="text-blue-500 hover:text-blue-700 p-1"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <form method="POST" onsubmit="return confirm('Buang?');" style="display:inline;"><input type="hidden" name="id_delete" value="<?php echo $row['id']; ?>"><button type="submit" name="btn_soft_delete" class="text-red-500 hover:text-red-700 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                        <?php else: ?>
                            <form method="POST" style="display:inline;"><input type="hidden" name="id_restore" value="<?php echo $row['id']; ?>"><button type="submit" name="btn_restore" class="text-emerald-500 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button></form>
                            <form method="POST" onsubmit="return confirm('Hapus Permanen?');" style="display:inline;"><input type="hidden" name="id_delete" value="<?php echo $row['id']; ?>"><button type="submit" name="btn_hard_delete" class="text-red-600 hover:text-red-800 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button></form>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; else: ?><tr><td colspan="15" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="bg-white dark:bg-slate-900 p-3 border-t border-slate-200 dark:border-slate-800 text-xs flex justify-between items-center">
        <span class="text-slate-500">Hal <?php echo $viewData['page']; ?></span>
        <div class="flex gap-1">
            <?php $qStr=$_GET; if($viewData['page']>1){$qStr['page']=$viewData['page']-1; echo '<a href="?'.http_build_query($qStr).'" class="px-3 py-1 bg-slate-200 rounded bg-white dark:bg-slate-800">Prev</a>';} if($viewData['page']<$viewData['totalPages']){$qStr['page']=$viewData['page']+1; echo '<a href="?'.http_build_query($qStr).'" class="px-3 py-1 bg-slate-200 rounded bg-white dark:bg-slate-800">Next</a>';} ?>
        </div>
    </div>
</div>

<div id="modalProof" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-bounce-up border border-slate-200 dark:border-slate-700">
        <div class="bg-slate-800 p-4 flex justify-between items-center">
            <h3 class="text-white font-bold">Bukti Penerimaan</h3>
            <button onclick="document.getElementById('modalProof').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">✕</button>
        </div>
        <div class="p-6">
            <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-lg mb-4 border border-slate-200 dark:border-slate-700">
                <div class="flex justify-between items-center mb-3">
                    <div><p class="text-[10px] text-slate-500 uppercase font-bold">Penerima</p><p id="proofName" class="text-sm font-bold text-slate-800 dark:text-white">-</p></div>
                    <div class="text-right"><p class="text-[10px] text-slate-500 uppercase font-bold">Waktu</p><p id="proofTime" class="text-sm font-mono text-slate-700 dark:text-slate-300">-</p></div>
                </div>
                <a id="proofLink" href="#" target="_blank" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 rounded-lg transition shadow">📄 Lihat Foto Bukti</a>
            </div>
            <p class="text-xs text-slate-500 uppercase font-bold mb-2">Dokumen dalam paket ini:</p>
            <div class="max-h-48 overflow-y-auto custom-scrollbar border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800">
                <ul id="proofList" class="text-xs text-slate-700 dark:text-slate-300 divide-y divide-slate-100 dark:divide-slate-700 p-2"></ul>
            </div>
        </div>
    </div>
</div>

<?php if(!$viewData['isTamu']): ?>
<div id="modalEdit" class="fixed inset-0 bg-black/50 dark:bg-black/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">
        <div class="bg-slate-800 p-4 flex justify-between items-center">
            <h3 class="text-white font-bold">Edit Invoice</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-white hover:text-red-400 cursor-pointer">✕</button>
        </div>
        <form method="POST" class="p-6 grid grid-cols-2 gap-4">
            <input type="hidden" name="id_edit" id="id_edit">
            <div class="col-span-1"><label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">DOC NO</label><input type="text" name="doc_no" id="edit_doc_no" readonly class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded p-2"></div>
            <div class="col-span-1"><label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">DATE</label><input type="text" name="sent_date" id="edit_sent_date" class="datepicker w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2"></div>
            <div class="col-span-1"><label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">FAKTUR</label><input type="text" name="fp" id="edit_fp" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2"></div>
            <div class="col-span-1"><label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">NET</label><input type="text" name="net" id="edit_net" onkeyup="hitungTotal()" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2"></div>
            <div class="col-span-1"><label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">TAX</label><input type="text" name="tax" id="edit_tax" onkeyup="hitungTotalManual()" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2"></div>
            <div class="col-span-1"><label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">TOTAL</label><input type="text" name="total" id="edit_total" readonly class="w-full bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-500 text-emerald-700 dark:text-emerald-400 font-bold rounded p-2"></div>
            <div class="col-span-2"><label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">REMARKS</label><textarea name="remarks" id="edit_remarks" rows="3" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2"></textarea></div>
            <div class="col-span-2 flex justify-end gap-2 pt-2"><a id="btn_print_modal" href="#" target="_blank" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white rounded font-bold hover:bg-slate-300">Print</a><button type="submit" name="btn_update_manual" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-bold shadow">Simpan</button></div>
        </form>
    </div>
</div>
<?php endif; ?>

<div id="modalAddress" class="fixed inset-0 bg-black/80 hidden z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-xl shadow-2xl overflow-hidden animate-bounce-up border border-slate-200 dark:border-slate-700">
        <div class="bg-yellow-600 p-4 flex justify-between items-center">
            <h3 class="text-white font-bold">Tujuan Pengiriman</h3>
            <button onclick="document.getElementById('modalAddress').classList.add('hidden')" class="text-white hover:text-slate-200 cursor-pointer">✕</button>
        </div>
        <div class="p-6">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Masukkan Alamat Lengkap / Tujuan:</label>
            <textarea id="inputAddressText" rows="4" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="Contoh: PT. ABC KREASI (UP: IBU SITI - FINANCE) &#10;Jl. Sudirman No. 5, Jakarta Pusat"></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="document.getElementById('modalAddress').classList.add('hidden')" class="px-4 py-2 text-slate-500 hover:text-slate-700 text-sm">Batal</button>
                <button onclick="submitBatchWithAddress()" class="px-6 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg font-bold shadow text-sm">Cetak QR Code</button>
            </div>
        </div>
    </div>
</div>

<div id="modalOverdue" class="fixed inset-0 bg-black/80 hidden z-[70] flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-xl shadow-2xl overflow-hidden animate-bounce-up border border-slate-200 dark:border-slate-700">
        <div class="bg-red-600 p-4 flex justify-between items-center">
            <h3 class="text-white font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Update Manual Overdue
            </h3>
            <button onclick="document.getElementById('modalOverdue').classList.add('hidden')" class="text-white hover:text-red-200 cursor-pointer">✕</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="overdue_batch_id" id="overdueBatchId">
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Diterima Asli</label>
                <input type="date" name="received_date" required class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded p-2 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-red-500 outline-none">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Penerima</label>
                <input type="text" name="recipient_name" required placeholder="Misal: Security / Resepsionis" class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded p-2 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-red-500 outline-none">
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Upload Bukti (Foto/Surat Jalan)</label>
                <input type="file" name="manual_proof_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                <p class="text-[10px] text-slate-400 mt-1 italic">*Opsional, tapi disarankan.</p>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalOverdue').classList.add('hidden')" class="px-4 py-2 text-slate-500 text-sm hover:bg-slate-100 rounded">Batal</button>
                <button type="submit" name="btn_overdue_receive" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded font-bold text-sm shadow">Simpan Status</button>
            </div>
        </form>
    </div>
</div>

<script>
// --- JAVASCRIPT LOGIC ---
const STORAGE_KEY = 'invoice_selected_ids';
let selectedSet = new Set(JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'));

document.addEventListener("DOMContentLoaded", () => {
    restoreSelection();
    updateBulkUI();
    
    // Auto-scroll jika ada parameter status
    const u=new URLSearchParams(window.location.search);
    if(u.get('f_status') === 'OVERDUE') document.getElementById('tableInvoice')?.scrollIntoView({behavior: "smooth"});
    if(u.get('open_proof')) viewProof(u.get('open_proof'));
});

// LOGIC CHECKBOX
function restoreSelection() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        if (selectedSet.has(parseInt(cb.value))) {
            cb.checked = true;
        }
    });
}

function handleCheck(el) {
    const id = parseInt(el.value);
    if (el.checked) selectedSet.add(id);
    else {
        selectedSet.delete(id);
        const master = document.getElementById('checkAllBox');
        if(master) master.checked = false;
    }
    saveToStorage();
    updateBulkUI();
}

function toggleSelectPage(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = source.checked;
        const id = parseInt(cb.value);
        if (source.checked) selectedSet.add(id);
        else selectedSet.delete(id);
    });
    saveToStorage();
    updateBulkUI();
}

function saveToStorage() {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...selectedSet]));
}

function resetSelection() {
    selectedSet.clear();
    saveToStorage();
    document.querySelectorAll('.row-checkbox').forEach(c => c.checked = false);
    const master = document.getElementById('checkAllBox');
    if(master) master.checked = false;
    updateBulkUI();
}

// LOGIC UI BULK ACTION
function updateBulkUI() {
    const count = selectedSet.size;
    const bar = document.getElementById('bulkForm');
    const label = document.getElementById('countSelected');
    const container = document.getElementById('hiddenInputs');

    if (count > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
        label.innerText = count + " Terpilih";
        
        container.innerHTML = '';
        selectedSet.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_ids[]';
            input.value = id;
            container.appendChild(input);
        });
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
        container.innerHTML = '';
    }
}

// LOGIC MODALS
function openAddressModal() {
    document.getElementById('inputAddressText').value = '';
    document.getElementById('modalAddress').classList.remove('hidden');
    setTimeout(() => document.getElementById('inputAddressText').focus(), 100);
}
function submitBatchWithAddress() {
    document.getElementById('hiddenAddress').value = document.getElementById('inputAddressText').value;
    document.getElementById('realSubmitBatch').click();
    document.getElementById('modalAddress').classList.add('hidden');
}

// LOGIC BUKTI (AJAX)
function viewProof(batchId) {
    if(!batchId) return;
    document.getElementById('proofList').innerHTML = '<li class="p-4 text-center text-slate-500 animate-pulse">Memuat data...</li>';
    document.getElementById('modalProof').classList.remove('hidden');
    fetch('invoice.php?ajax_action=get_proof&batch_id='+batchId).then(r => r.json()).then(d => {
        document.getElementById('proofName').innerText = d.recipient_name || '-';
        document.getElementById('proofTime').innerText = d.received_at_indo || '-';
        const linkBtn = document.getElementById('proofLink');
        if(d.proof_photo) {
            linkBtn.href = 'uploads/' + d.proof_photo; 
            linkBtn.classList.remove('hidden');
            linkBtn.innerText = '📄 Lihat Foto Bukti';
        } else {
            linkBtn.href = '#'; linkBtn.innerText = 'Foto Tidak Tersedia'; linkBtn.classList.add('hidden'); 
        }
        let listHtml = '';
        if (d.invoice_list && d.invoice_list.length > 0) {
            d.invoice_list.forEach(inv => {
                listHtml += `<li class="p-3 hover:bg-slate-100 dark:hover:bg-slate-700 border-b border-slate-100 dark:border-slate-700 last:border-0 transition"><div class="flex justify-between items-start"><span class="font-bold text-slate-700 dark:text-slate-200">${inv.doc_no}</span><span class="text-[10px] bg-slate-200 dark:bg-slate-600 px-1 rounded text-slate-600 dark:text-slate-300">${inv.customer_name}</span></div><div class="text-[10px] text-slate-400 mt-1 truncate">${inv.remarks || '-'}</div></li>`;
            });
        } else { listHtml = '<li class="p-4 text-center text-slate-500 text-xs">Tidak ada rincian invoice.</li>'; }
        document.getElementById('proofList').innerHTML = listHtml;
    }).catch(err => { console.error(err); alert('Gagal memuat data bukti.'); });
}

// LOGIC INLINE EDIT
function makeEditable(e){ e.contentEditable=true; e.focus(); }
function saveInline(e,id,f){
    e.contentEditable=false;
    let fd = new FormData(); fd.append('id',id); fd.append('field',f); fd.append('value',e.innerText.trim());
    fetch('invoice.php?ajax_action=inline_update', {method:'POST', body:fd}).then(r=>r.json()).then(d=>{
        if(d.status=='success'){ e.classList.add('bg-green-100'); setTimeout(()=>e.classList.remove('bg-green-100'),1000); }
        else alert(d.message);
    });
}
function toggleCol(i){document.querySelectorAll(`.col-${i}`).forEach(e=>e.classList.toggle('hidden'));}
function loadEditData(id){
    fetch('invoice.php?ajax_action=get_data&id='+id).then(r=>r.json()).then(d=>{
        document.getElementById('modalEdit').classList.remove('hidden');
        document.getElementById('id_edit').value=d.id;
        document.getElementById('edit_doc_no').value=d.doc_no;
        document.getElementById('edit_fp').value=d.fp;
        document.getElementById('edit_remarks').value=d.remarks;
        document.getElementById('edit_net').value=fmt(d.net);
        document.getElementById('edit_tax').value=fmt(d.tax);
        document.getElementById('edit_total').value=fmt(d.total);
        if(d.sent_date) document.querySelector("#edit_sent_date")._flatpickr.setDate(d.sent_date.split(' ')[0]);
    });
}
function hitungTotal(){
    let n=parseFloat(document.getElementById('edit_net').value.replace(/\./g,''))||0;
    let t=Math.round(n*0.11);
    document.getElementById('edit_net').value=fmt(n);
    document.getElementById('edit_tax').value=fmt(t);
    document.getElementById('edit_total').value=fmt(n+t);
}
function hitungTotalManual(){
    let n=parseFloat(document.getElementById('edit_net').value.replace(/\./g,''))||0;
    let t=parseFloat(document.getElementById('edit_tax').value.replace(/\./g,''))||0;
    document.getElementById('edit_tax').value=fmt(t);
    document.getElementById('edit_total').value=fmt(n+t);
}
function fmt(n){return new Intl.NumberFormat('id-ID').format(n);}
function toggleImport(){document.getElementById('area_import').classList.toggle('hidden');}

// LOGIC OVERDUE
function openOverdueModal(batchId) {
    document.getElementById('overdueBatchId').value = batchId;
    document.getElementById('modalOverdue').classList.remove('hidden');
}
</script>