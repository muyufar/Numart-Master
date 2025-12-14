<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';
error_reporting(0);

if ($levelLogin === "kurir") {
    echo "<script>document.location.href = 'bo';</script>";
}

$message = '';
$messageType = '';

// Get filter parameters
$filterType = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filterArea = isset($_GET['area']) ? $_GET['area'] : '';
$templateId = isset($_GET['template']) ? intval($_GET['template']) : 0;

// Get template if selected
$selectedTemplate = null;
if ($templateId > 0) {
    $tplResult = query("SELECT * FROM wa_templates WHERE id = $templateId");
    $selectedTemplate = !empty($tplResult) ? $tplResult[0] : null;
}

// Get all templates
$templates = query("SELECT * FROM wa_templates WHERE cabang = $sessionCabang OR cabang = 0 ORDER BY template_name");

// Get target settings for filter
$targetQuery = query("SELECT * FROM customer_target_settings WHERE cabang = $sessionCabang");
if (empty($targetQuery)) {
    $targetQuery = query("SELECT * FROM customer_target_settings WHERE cabang = 0");
}
$targetSettings = !empty($targetQuery) ? $targetQuery[0] : ['target_bulanan' => 100000];
$targetBulan = $targetSettings['target_bulanan'] ?? 100000;

// Date range for this month
$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');

// Build filter conditions
$whereConditions = "c.customer_cabang = $sessionCabang 
                    AND c.customer_id > 1 
                    AND c.customer_nama != 'Customer Umum' 
                    AND c.customer_status = '1'
                    AND c.customer_tlpn IS NOT NULL 
                    AND c.customer_tlpn != ''";

$havingCondition = "";
$joinCondition = "LEFT JOIN invoice i ON c.customer_id = i.invoice_customer 
                  AND i.invoice_date BETWEEN '$startOfMonth' AND '$endOfMonth'
                  AND i.invoice_cabang = $sessionCabang";

switch ($filterType) {
    case 'below_target':
        $havingCondition = "HAVING total_belanja < $targetBulan";
        break;
    case 'above_target':
        $havingCondition = "HAVING total_belanja >= $targetBulan";
        break;
    case 'inactive':
        $havingCondition = "HAVING total_belanja = 0";
        break;
    case 'birthday':
        $whereConditions .= " AND MONTH(c.customer_birthday) = MONTH(CURRENT_DATE())";
        break;
    case 'grosir':
        $whereConditions .= " AND c.customer_category = 2";
        break;
    case 'retail':
        $whereConditions .= " AND c.customer_category = 1";
        break;
}

if (!empty($filterArea)) {
    $filterArea = mysqli_real_escape_string($conn, $filterArea);
    $whereConditions .= " AND c.alamat_kabupaten = '$filterArea'";
}

// Get customers based on filter
$customersQuery = "SELECT 
                      c.customer_id,
                      c.customer_nama,
                      c.customer_tlpn,
                      c.alamat_kabupaten,
                      c.customer_category,
                      COALESCE(SUM(i.invoice_sub_total), 0) as total_belanja
                   FROM customer c
                   $joinCondition
                   WHERE $whereConditions
                   GROUP BY c.customer_id
                   $havingCondition
                   ORDER BY c.customer_nama";

$customers = query($customersQuery);

// Get unique areas for filter
$areas = query("SELECT DISTINCT alamat_kabupaten FROM customer 
                WHERE customer_cabang = $sessionCabang 
                AND alamat_kabupaten IS NOT NULL 
                AND alamat_kabupaten != '' 
                ORDER BY alamat_kabupaten");

// Get WA blast history
$blastHistory = query("SELECT 
                          h.*, 
                          u.user_nama,
                          (SELECT COUNT(*) FROM wa_blast_recipients WHERE blast_id = h.id AND status = 'sent') as sent_count
                       FROM wa_blast_history h
                       JOIN user u ON h.user_id = u.user_id
                       WHERE h.cabang = $sessionCabang
                       ORDER BY h.created_at DESC
                       LIMIT 10");
?>

<style>
    .wa-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .wa-header {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 20px;
    }
    .recipient-list {
        max-height: 400px;
        overflow-y: auto;
    }
    .recipient-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
        transition: background 0.2s;
    }
    .recipient-item:hover {
        background: #f8f9fa;
    }
    .recipient-check {
        margin-right: 15px;
    }
    .recipient-info {
        flex: 1;
    }
    .message-preview {
        background: #DCF8C6;
        border-radius: 10px;
        padding: 15px;
        margin: 15px 0;
        font-size: 0.95rem;
        white-space: pre-wrap;
        position: relative;
    }
    .message-preview::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 15px;
        border-width: 10px;
        border-style: solid;
        border-color: transparent #DCF8C6 transparent transparent;
    }
    .filter-btn {
        border-radius: 20px;
        margin: 3px;
    }
    .filter-btn.active {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        border-color: transparent;
        color: white;
    }
    .stats-box {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 10px 15px;
        text-align: center;
    }
    .template-select {
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s;
    }
    .template-select:hover, .template-select.selected {
        border-color: #25D366;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fab fa-whatsapp"></i> WhatsApp Blast</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="bo">Home</a></li>
                        <li class="breadcrumb-item"><a href="customer-management">Customer Management</a></li>
                        <li class="breadcrumb-item active">WA Blast</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($message) : ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= $message ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Left: Recipients Selection -->
                <div class="col-lg-5 mb-4">
                    <div class="card wa-card">
                        <div class="wa-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0"><i class="fas fa-users"></i> Pilih Penerima</h4>
                                <div class="stats-box">
                                    <strong id="selectedCount">0</strong> / <?= count($customers) ?> dipilih
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Filter Buttons -->
                            <div class="p-3 border-bottom">
                                <div class="mb-2">
                                    <small class="text-muted">Filter Cepat:</small>
                                </div>
                                <a href="?filter=all" class="btn btn-outline-success btn-sm filter-btn <?= $filterType == 'all' ? 'active' : '' ?>">Semua</a>
                                <a href="?filter=below_target" class="btn btn-outline-success btn-sm filter-btn <?= $filterType == 'below_target' ? 'active' : '' ?>">Belum Target</a>
                                <a href="?filter=inactive" class="btn btn-outline-success btn-sm filter-btn <?= $filterType == 'inactive' ? 'active' : '' ?>">Tidak Aktif</a>
                                <a href="?filter=birthday" class="btn btn-outline-success btn-sm filter-btn <?= $filterType == 'birthday' ? 'active' : '' ?>">Ultah Bulan Ini</a>
                                <a href="?filter=grosir" class="btn btn-outline-success btn-sm filter-btn <?= $filterType == 'grosir' ? 'active' : '' ?>">Grosir</a>
                                <a href="?filter=retail" class="btn btn-outline-success btn-sm filter-btn <?= $filterType == 'retail' ? 'active' : '' ?>">Retail</a>
                                
                                <?php if (!empty($areas)) : ?>
                                <div class="mt-2">
                                    <select class="form-control form-control-sm" onchange="location.href='?filter=<?= $filterType ?>&area='+this.value">
                                        <option value="">-- Filter Area --</option>
                                        <?php foreach ($areas as $area) : ?>
                                        <option value="<?= $area['alamat_kabupaten'] ?>" <?= $filterArea == $area['alamat_kabupaten'] ? 'selected' : '' ?>>
                                            <?= $area['alamat_kabupaten'] ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Select All -->
                            <div class="p-3 border-bottom bg-light">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">Pilih Semua</label>
                                </div>
                            </div>
                            
                            <!-- Recipients List -->
                            <div class="recipient-list">
                                <?php foreach ($customers as $cust) : 
                                    $phone = preg_replace('/^0/', '62', $cust['customer_tlpn']);
                                    $phone = preg_replace('/[^0-9]/', '', $phone);
                                ?>
                                <div class="recipient-item">
                                    <div class="custom-control custom-checkbox recipient-check">
                                        <input type="checkbox" class="custom-control-input recipient-checkbox" 
                                               id="cust<?= $cust['customer_id'] ?>"
                                               data-id="<?= $cust['customer_id'] ?>"
                                               data-name="<?= htmlspecialchars($cust['customer_nama']) ?>"
                                               data-phone="<?= $phone ?>"
                                               data-spending="<?= $cust['total_belanja'] ?>">
                                        <label class="custom-control-label" for="cust<?= $cust['customer_id'] ?>"></label>
                                    </div>
                                    <div class="recipient-info">
                                        <strong><?= $cust['customer_nama'] ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-phone"></i> <?= $cust['customer_tlpn'] ?>
                                            <?php if ($cust['alamat_kabupaten']) : ?>
                                            | <i class="fas fa-map-marker-alt"></i> <?= $cust['alamat_kabupaten'] ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <small class="text-muted">Rp <?= number_format($cust['total_belanja'], 0, ',', '.') ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($customers)) : ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-user-slash fa-3x mb-3"></i>
                                    <p>Tidak ada customer yang sesuai filter</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Message Composer -->
                <div class="col-lg-7 mb-4">
                    <div class="card wa-card">
                        <div class="wa-header">
                            <h4 class="mb-0"><i class="fas fa-edit"></i> Buat Pesan</h4>
                        </div>
                        <div class="card-body">
                            <!-- Template Selection -->
                            <div class="mb-4">
                                <label class="font-weight-bold">Template Pesan:</label>
                                <div class="row">
                                    <?php foreach ($templates as $tpl) : ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="card template-select <?= $templateId == $tpl['id'] ? 'selected' : '' ?>" 
                                             onclick="selectTemplate(<?= $tpl['id'] ?>, '<?= addslashes($tpl['template_content']) ?>')">
                                            <div class="card-body p-2 text-center">
                                                <small><strong><?= $tpl['template_name'] ?></strong></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Message Input -->
                            <div class="form-group">
                                <label class="font-weight-bold">Pesan:</label>
                                <textarea id="messageText" class="form-control" rows="6" placeholder="Tulis pesan Anda di sini...

Variabel yang tersedia:
{nama_customer} - Nama customer
{total_belanja} - Total belanja customer
{nama_toko} - Nama toko"><?= $selectedTemplate ? $selectedTemplate['template_content'] : '' ?></textarea>
                                <small class="text-muted">Gunakan variabel untuk personalisasi pesan</small>
                            </div>
                            
                            <!-- Preview -->
                            <div class="mb-4">
                                <label class="font-weight-bold">Preview:</label>
                                <div class="message-preview" id="messagePreview">
                                    Tulis pesan di atas untuk melihat preview...
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <button type="button" class="btn btn-success btn-lg btn-block" onclick="startBlast()">
                                        <i class="fab fa-whatsapp"></i> Mulai Kirim WA Blast
                                    </button>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-lg btn-block" onclick="copyAllNumbers()">
                                        <i class="fas fa-copy"></i> Copy Semua Nomor
                                    </button>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Cara Kerja:</strong> WA Blast akan membuka tab baru untuk setiap penerima dengan pesan yang sudah dipersonalisasi. 
                                Anda perlu mengklik "Kirim" di setiap tab WhatsApp Web.
                            </div>
                        </div>
                    </div>

                    <!-- Blast Progress -->
                    <div class="card wa-card mt-4" id="blastProgress" style="display: none;">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-spinner fa-spin"></i> Proses Pengiriman</h5>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                     id="blastProgressBar" style="width: 0%">0%</div>
                            </div>
                            <p id="blastStatus">Mempersiapkan...</p>
                            <div id="blastLog" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;"></div>
                        </div>
                    </div>

                    <!-- History -->
                    <?php if (!empty($blastHistory)) : ?>
                    <div class="card wa-card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Blast</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Oleh</th>
                                            <th>Penerima</th>
                                            <th>Tipe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($blastHistory as $hist) : ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($hist['created_at'])) ?></td>
                                            <td><?= $hist['user_nama'] ?></td>
                                            <td><?= $hist['total_recipients'] ?> customer</td>
                                            <td><span class="badge badge-info"><?= $hist['blast_type'] ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '_footer.php'; ?>

<script>
const tokoName = '<?= addslashes($dataTokoLogin['toko_nama'] ?? 'Numart') ?>';

// Update selected count
function updateSelectedCount() {
    const count = document.querySelectorAll('.recipient-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.recipient-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

// Individual checkbox change
document.querySelectorAll('.recipient-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

// Update preview on message change
document.getElementById('messageText').addEventListener('input', function() {
    updatePreview();
});

function updatePreview() {
    let message = document.getElementById('messageText').value;
    message = message.replace('{nama_customer}', 'John Doe');
    message = message.replace('{total_belanja}', 'Rp 150.000');
    message = message.replace('{nama_toko}', tokoName);
    document.getElementById('messagePreview').textContent = message || 'Tulis pesan di atas untuk melihat preview...';
}

function selectTemplate(id, content) {
    document.getElementById('messageText').value = content.replace(/\\n/g, '\n');
    document.querySelectorAll('.template-select').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    updatePreview();
}

function formatCurrency(num) {
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function startBlast() {
    const selectedCustomers = [];
    document.querySelectorAll('.recipient-checkbox:checked').forEach(cb => {
        selectedCustomers.push({
            id: cb.dataset.id,
            name: cb.dataset.name,
            phone: cb.dataset.phone,
            spending: cb.dataset.spending
        });
    });
    
    if (selectedCustomers.length === 0) {
        alert('Pilih minimal 1 penerima!');
        return;
    }
    
    const message = document.getElementById('messageText').value;
    if (!message.trim()) {
        alert('Tulis pesan terlebih dahulu!');
        return;
    }
    
    if (!confirm(`Kirim WA ke ${selectedCustomers.length} customer?`)) {
        return;
    }
    
    // Show progress
    document.getElementById('blastProgress').style.display = 'block';
    const progressBar = document.getElementById('blastProgressBar');
    const statusText = document.getElementById('blastStatus');
    const logDiv = document.getElementById('blastLog');
    
    let current = 0;
    const total = selectedCustomers.length;
    const delay = 2000; // 2 seconds between each
    
    function sendNext() {
        if (current >= total) {
            statusText.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Selesai!</span>';
            progressBar.classList.remove('progress-bar-animated');
            
            // Save history via AJAX
            saveBlastHistory(total, message);
            return;
        }
        
        const cust = selectedCustomers[current];
        let personalizedMessage = message
            .replace('{nama_customer}', cust.name)
            .replace('{total_belanja}', formatCurrency(cust.spending))
            .replace('{nama_toko}', tokoName);
        
        const waUrl = `https://wa.me/${cust.phone}?text=${encodeURIComponent(personalizedMessage)}`;
        
        // Open WhatsApp
        window.open(waUrl, '_blank');
        
        // Update progress
        current++;
        const percentage = Math.round((current / total) * 100);
        progressBar.style.width = percentage + '%';
        progressBar.textContent = percentage + '%';
        statusText.textContent = `Mengirim ke ${current} dari ${total}...`;
        logDiv.innerHTML += `<div class="text-success"><i class="fas fa-check"></i> ${cust.name} - ${cust.phone}</div>`;
        logDiv.scrollTop = logDiv.scrollHeight;
        
        // Send next after delay
        setTimeout(sendNext, delay);
    }
    
    sendNext();
}

function saveBlastHistory(total, message) {
    fetch('api/save-wa-blast-history.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            total_recipients: total,
            message_template: message,
            blast_type: '<?= $filterType ?>'
        })
    });
}

function copyAllNumbers() {
    const phones = [];
    document.querySelectorAll('.recipient-checkbox:checked').forEach(cb => {
        phones.push(cb.dataset.phone);
    });
    
    if (phones.length === 0) {
        alert('Pilih customer terlebih dahulu!');
        return;
    }
    
    navigator.clipboard.writeText(phones.join('\n')).then(() => {
        alert(`${phones.length} nomor berhasil disalin!`);
    });
}

// Initial preview update
updatePreview();
</script>
</body>
</html>

