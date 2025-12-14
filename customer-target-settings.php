<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';
error_reporting(0);

if ($levelLogin !== "super admin" && $levelLogin !== "admin") {
    echo "<script>alert('Akses ditolak!'); document.location.href = 'bo';</script>";
    exit;
}

$message = '';
$messageType = '';

// Handle form submission
if (isset($_POST['save_target'])) {
    $targetHarian = floatval($_POST['target_harian']) ?? 0;
    $targetMingguan = floatval($_POST['target_mingguan']) ?? 0;
    $targetBulanan = floatval($_POST['target_bulanan']) ?? 100000;
    $targetTahunan = floatval($_POST['target_tahunan']) ?? 1200000;
    
    // Check if setting exists
    $existing = query("SELECT id FROM customer_target_settings WHERE cabang = $sessionCabang");
    
    if (!empty($existing)) {
        $query = "UPDATE customer_target_settings SET 
                    target_harian = $targetHarian,
                    target_mingguan = $targetMingguan,
                    target_bulanan = $targetBulanan,
                    target_tahunan = $targetTahunan,
                    updated_at = NOW()
                  WHERE cabang = $sessionCabang";
    } else {
        $query = "INSERT INTO customer_target_settings (cabang, target_harian, target_mingguan, target_bulanan, target_tahunan) 
                  VALUES ($sessionCabang, $targetHarian, $targetMingguan, $targetBulanan, $targetTahunan)";
    }
    
    if (mysqli_query($conn, $query)) {
        $message = 'Target berhasil disimpan!';
        $messageType = 'success';
    } else {
        $message = 'Gagal menyimpan target: ' . mysqli_error($conn);
        $messageType = 'danger';
    }
}

// Handle tag management
if (isset($_POST['add_tag'])) {
    $tagName = mysqli_real_escape_string($conn, $_POST['tag_name']);
    $tagColor = mysqli_real_escape_string($conn, $_POST['tag_color']);
    
    $query = "INSERT INTO customer_tags (cabang, tag_name, tag_color) VALUES ($sessionCabang, '$tagName', '$tagColor')
              ON DUPLICATE KEY UPDATE tag_color = '$tagColor'";
    
    if (mysqli_query($conn, $query)) {
        $message = 'Tag berhasil ditambahkan!';
        $messageType = 'success';
    } else {
        $message = 'Gagal menambahkan tag!';
        $messageType = 'danger';
    }
}

if (isset($_GET['delete_tag'])) {
    $tagId = intval($_GET['delete_tag']);
    mysqli_query($conn, "DELETE FROM customer_tags WHERE id = $tagId AND cabang = $sessionCabang");
    mysqli_query($conn, "DELETE FROM customer_tag_relations WHERE tag_id = $tagId");
    header("Location: customer-target-settings");
    exit;
}

// Get current settings
$settings = query("SELECT * FROM customer_target_settings WHERE cabang = $sessionCabang");
if (empty($settings)) {
    $settings = query("SELECT * FROM customer_target_settings WHERE cabang = 0");
}
$currentSettings = !empty($settings) ? $settings[0] : [
    'target_harian' => 0,
    'target_mingguan' => 0,
    'target_bulanan' => 100000,
    'target_tahunan' => 1200000
];

// Get tags
$tags = query("SELECT * FROM customer_tags WHERE cabang = $sessionCabang OR cabang = 0 ORDER BY tag_name");

// Get WA templates
$templates = query("SELECT * FROM wa_templates WHERE cabang = $sessionCabang OR cabang = 0 ORDER BY template_name");
?>

<style>
    .settings-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .settings-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
    }
    .target-input {
        font-size: 1.2rem;
        font-weight: bold;
        text-align: right;
    }
    .tag-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin: 3px;
        display: inline-flex;
        align-items: center;
    }
    .tag-delete {
        margin-left: 8px;
        cursor: pointer;
        opacity: 0.7;
    }
    .tag-delete:hover {
        opacity: 1;
    }
    .template-card {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .template-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .input-group-text {
        min-width: 50px;
        justify-content: center;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-cog"></i> Pengaturan Target Customer</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="bo">Home</a></li>
                        <li class="breadcrumb-item"><a href="customer-management">Customer Management</a></li>
                        <li class="breadcrumb-item active">Pengaturan</li>
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
                <!-- Target Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="card settings-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bullseye"></i> Target Belanja Customer</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <p class="text-muted mb-4">
                                    Atur target belanja minimum untuk customer. Customer yang belanjanya kurang dari target akan muncul di alert.
                                </p>
                                
                                <div class="form-group">
                                    <label>Target Harian</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="target_harian" class="form-control target-input" 
                                               value="<?= $currentSettings['target_harian'] ?>" min="0">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Target Mingguan</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="target_mingguan" class="form-control target-input" 
                                               value="<?= $currentSettings['target_mingguan'] ?>" min="0">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Target Bulanan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="target_bulanan" class="form-control target-input" 
                                               value="<?= $currentSettings['target_bulanan'] ?>" min="0" required>
                                    </div>
                                    <small class="text-muted">Target utama yang digunakan untuk alert</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Target Tahunan</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="target_tahunan" class="form-control target-input" 
                                               value="<?= $currentSettings['target_tahunan'] ?>" min="0">
                                    </div>
                                </div>
                                
                                <button type="submit" name="save_target" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Simpan Target
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tags Management -->
                <div class="col-lg-6 mb-4">
                    <div class="card settings-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tags"></i> Label / Tag Customer</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Buat label untuk mengkategorikan customer (VIP, Loyal, dll)
                            </p>
                            
                            <form method="POST" action="" class="mb-4">
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" name="tag_name" class="form-control" placeholder="Nama Tag" required>
                                    </div>
                                    <div class="col-3">
                                        <input type="color" name="tag_color" class="form-control" value="#007bff" style="height: 38px;">
                                    </div>
                                    <div class="col-3">
                                        <button type="submit" name="add_tag" class="btn btn-success btn-block">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <div class="tags-container">
                                <?php foreach ($tags as $tag) : ?>
                                <span class="tag-badge" style="background: <?= $tag['tag_color'] ?>; color: white;">
                                    <?= $tag['tag_name'] ?>
                                    <a href="?delete_tag=<?= $tag['id'] ?>" class="tag-delete" onclick="return confirm('Hapus tag ini?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                                <?php endforeach; ?>
                                <?php if (empty($tags)) : ?>
                                <p class="text-muted">Belum ada tag</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WA Templates -->
            <div class="card settings-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fab fa-whatsapp"></i> Template Pesan WhatsApp</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Template pesan untuk WA Blast. Gunakan variabel: <code>{nama_customer}</code>, <code>{total_belanja}</code>, <code>{nama_toko}</code>
                    </p>
                    
                    <div class="row">
                        <?php foreach ($templates as $tpl) : ?>
                        <div class="col-md-6">
                            <div class="template-card">
                                <h5><?= $tpl['template_name'] ?></h5>
                                <pre style="white-space: pre-wrap; background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 0.85rem;">
<?= htmlspecialchars($tpl['template_content']) ?>
                                </pre>
                                <a href="customer-wa-blast?template=<?= $tpl['id'] ?>" class="btn btn-sm btn-success">
                                    <i class="fab fa-whatsapp"></i> Gunakan Template
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include '_footer.php'; ?>
</body>
</html>


