<?php

// koneksi ke database
include 'koneksi.php';


function query($query)
{
	global $conn;
	$result = mysqli_query($conn, $query);
	$rows = [];
	if ($result && mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}
	}
	return $rows;
}
function tanggal_indo($tanggal)
{
	$bulan = array(
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$split = explode('-', $tanggal);
	return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

function singkat_angka($n, $presisi = 1)
{
	if ($n < 900) {
		$format_angka = number_format($n, $presisi);
		$simbol = '';
	} else if ($n < 900000) {
		$format_angka = number_format($n / 1000, $presisi);
		$simbol = ' rb';
	} else if ($n < 900000000) {
		$format_angka = number_format($n / 1000000, $presisi);
		$simbol = ' jt';
	} else if ($n < 900000000000) {
		$format_angka = number_format($n / 1000000000, $presisi);
		$simbol = ' M';
	} else {
		$format_angka = number_format($n / 1000000000000, $presisi);
		$simbol = ' T';
	}

	if ($presisi > 0) {
		$pisah = '.' . str_repeat('0', $presisi);
		$format_angka = str_replace($pisah, '', $format_angka);
	}

	return $format_angka . $simbol;
}

// ================================================ USER ====================================== //

function tambahUser($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$user_nama = htmlspecialchars($data["user_nama"]);
	$user_no_hp = htmlspecialchars($data["user_no_hp"]);
	$user_alamat = htmlspecialchars($data["user_alamat"]);
	$user_email = htmlspecialchars($data["user_email"]);
	$user_password = md5(md5(htmlspecialchars($data["user_password"])));
	$user_create = date("d F Y g:i:s a");
	$user_level = htmlspecialchars($data["user_level"]);
	$user_status = htmlspecialchars($data["user_status"]);
	$user_cabang = htmlspecialchars($data["user_cabang"]);

	// Cek Email
	$email_user_cek = mysqli_num_rows(mysqli_query($conn, "select * from user where user_email = '$user_email' "));

	if ($email_user_cek > 0) {
		echo "
			<script>
				alert('Email Sudah Terdaftar');
			</script>
		";
	} else {
		// query insert data
		$query = "INSERT INTO user VALUES ('', '$user_nama', '$user_no_hp', '$user_alamat', '$user_email', '$user_password', '$user_create', '$user_level' , '$user_status', '$user_cabang')";
		mysqli_query($conn, $query);

		return mysqli_affected_rows($conn);
	}
}

function editUser($data)
{
	global $conn;
	$id = $data["user_id"];


	// ambil data dari tiap elemen dalam form
	$user_nama = htmlspecialchars($data["user_nama"]);
	$user_no_hp = htmlspecialchars($data["user_no_hp"]);
	$user_email = htmlspecialchars($data["user_email"]);
	$user_alamat = htmlspecialchars($data["user_alamat"]);
	$user_password = md5(md5(htmlspecialchars($data["user_password"])));
	$user_level = htmlspecialchars($data["user_level"]);
	$user_status = htmlspecialchars($data["user_status"]);

	// query update data
	$query = "UPDATE user SET 
						user_nama      = '$user_nama',
						user_no_hp     = '$user_no_hp',
						user_alamat    = '$user_alamat',
						user_email     = '$user_email',
						user_password  = '$user_password',
						user_level     = '$user_level',
						user_status    = '$user_status'
						WHERE user_id  = $id
				";
	// var_dump($query); die();
	mysqli_query($conn, $query);

	return mysqli_affected_rows($conn);
}

function hapusUser($id)
{
	global $conn;
	mysqli_query($conn, "DELETE FROM user WHERE user_id = $id");

	return mysqli_affected_rows($conn);
}
// ========================================= Toko ======================================== //
function tambahToko($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$toko_nama      = htmlspecialchars($data["toko_nama"]);
	$toko_kota      = htmlspecialchars($data["toko_kota"]);
	$toko_alamat    = htmlspecialchars($data["toko_alamat"]);
	$toko_tlpn      = htmlspecialchars($data["toko_tlpn"]);
	$toko_wa        = htmlspecialchars($data["toko_wa"]);
	$toko_email     = htmlspecialchars($data["toko_email"]);
	$toko_print     = htmlspecialchars($data["toko_print"]);
	$toko_status    = htmlspecialchars($data["toko_status"]);
	$toko_ongkir    = htmlspecialchars($data["toko_ongkir"]);
	$toko_cabang    = htmlspecialchars($data["toko_cabang"]);


	// query insert data toko
	$query = "INSERT INTO toko VALUES ('', '$toko_nama', '$toko_kota', '$toko_alamat', '$toko_tlpn', '$toko_wa', '$toko_email', '$toko_print' ,'$toko_status', '$toko_ongkir', '$toko_cabang')";
	mysqli_query($conn, $query);

	// query insert data laba bersih
	$query2 = "INSERT INTO laba_bersih VALUES ('', '', '', '', '', '', '', '' ,'', '', '$toko_cabang')";
	mysqli_query($conn, $query2);


	return mysqli_affected_rows($conn);
}

function editToko($data)
{
	global $conn;
	$id = $data["toko_id"];

	// ambil data dari tiap elemen dalam form
	$toko_nama      = htmlspecialchars($data["toko_nama"]);
	$toko_kota      = htmlspecialchars($data["toko_kota"]);
	$toko_alamat    = htmlspecialchars($data["toko_alamat"]);
	$toko_tlpn      = htmlspecialchars($data["toko_tlpn"]);
	$toko_wa        = htmlspecialchars($data["toko_wa"]);
	$toko_email     = htmlspecialchars($data["toko_email"]);
	$toko_print     = htmlspecialchars($data["toko_print"]);
	$toko_status    = htmlspecialchars($data["toko_status"]);
	$toko_ongkir    = htmlspecialchars($data["toko_ongkir"]);

	// query update data
	$query = "UPDATE toko SET 
				toko_nama       = '$toko_nama',
				toko_kota       = '$toko_kota',
				toko_alamat     = '$toko_alamat',
				toko_tlpn       = '$toko_tlpn',
				toko_wa         = '$toko_wa',
				toko_email      = '$toko_email',
				toko_print      = '$toko_print',
				toko_status     = '$toko_status',
				toko_ongkir		= '$toko_ongkir'
				WHERE toko_id   = $id
				";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}
function hapusToko($id)
{
	global $conn;

	$cabang = mysqli_query($conn, "select toko_cabang from toko where toko_id = " . $id . " ");
	$cabang = mysqli_fetch_array($cabang);
	$toko_cabang = $cabang['toko_cabang'];

	mysqli_query($conn, "DELETE FROM toko WHERE toko_id = $id");
	mysqli_query($conn, "DELETE FROM laba_bersih WHERE lb_cabang = $toko_cabang");

	mysqli_query($conn, "DELETE FROM supplier WHERE supplier_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM kategori WHERE kategori_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM satuan WHERE satuan_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM barang WHERE barang_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM barang_sn WHERE barang_sn_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM invoice_pembelian WHERE invoice_pembelian_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM pembelian WHERE pembelian_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM transfer WHERE transfer_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM transfer_produk_keluar WHERE tpk_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM transfer_produk_masuk WHERE tpm_cabang = $toko_cabang");
	mysqli_query($conn, "DELETE FROM user WHERE user_cabang = $toko_cabang");

	return mysqli_affected_rows($conn);
}

// ========================================= Kategori ======================================= //
function tambahKategori($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$kategori_nama = htmlspecialchars($data['kategori_nama']);
	$kategori_status = $data['kategori_status'];
	$kategori_cabang = $data['kategori_cabang'];

	// query insert data
	$query = "INSERT INTO kategori VALUES ('', '$kategori_nama', '$kategori_status', '$kategori_cabang')";
	mysqli_query($conn, $query);

	return mysqli_affected_rows($conn);
}

function editKategori($data)
{
	global $conn;
	$id = $data["kategori_id"];

	// ambil data dari tiap elemen dalam form
	$kategori_nama = htmlspecialchars($data['kategori_nama']);
	$kategori_status = $data['kategori_status'];

	// query update data
	$query = "UPDATE kategori SET 
				kategori_nama   = '$kategori_nama',
				kategori_status = '$kategori_status'
				WHERE kategori_id = $id
				";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

function hapusKategori($id)
{
	global $conn;
	mysqli_query($conn, "DELETE FROM kategori WHERE kategori_id = $id");

	return mysqli_affected_rows($conn);
}


// ======================================= Satuan ========================================= //
function tambahSatuan($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$satuan_nama = htmlspecialchars($data['satuan_nama']);
	$satuan_status = $data['satuan_status'];
	$satuan_cabang = $data['satuan_cabang'];

	// query insert data
	$query = "INSERT INTO satuan VALUES ('', '$satuan_nama', '$satuan_status', '$satuan_cabang')";
	mysqli_query($conn, $query);

	return mysqli_affected_rows($conn);
}

function editSatuan($data)
{
	global $conn;
	$id = $data["satuan_id"];

	// ambil data dari tiap elemen dalam form
	$satuan_nama = htmlspecialchars($data['satuan_nama']);
	$satuan_status = $data['satuan_status'];

	// query update data
	$query = "UPDATE satuan SET 
				satuan_nama   = '$satuan_nama',
				satuan_status = '$satuan_status'
				WHERE satuan_id = $id
				";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

function hapusSatuan($id)
{
	global $conn;
	mysqli_query($conn, "DELETE FROM satuan WHERE satuan_id = $id");

	return mysqli_affected_rows($conn);
}


// ===================================== ekspedisi ========================================= //
function tambahEkspedisi($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$ekspedisi_nama = htmlspecialchars($data['ekspedisi_nama']);
	$ekspedisi_status = $data['ekspedisi_status'];
	$ekspedisi_cabang = $data['ekspedisi_cabang'];

	// query insert data
	$query = "INSERT INTO ekspedisi VALUES ('', '$ekspedisi_nama', '$ekspedisi_status', '$ekspedisi_cabang')";
	mysqli_query($conn, $query);

	return mysqli_affected_rows($conn);
}

function editEkspedisi($data)
{
	global $conn;
	$id = $data["ekspedisi_id"];

	// ambil data dari tiap elemen dalam form
	$ekspedisi_nama = htmlspecialchars($data['ekspedisi_nama']);
	$ekspedisi_status = $data['ekspedisi_status'];

	// query update data
	$query = "UPDATE ekspedisi SET 
				ekspedisi_nama   = '$ekspedisi_nama',
				ekspedisi_status = '$ekspedisi_status'
				WHERE ekspedisi_id = $id
				";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

function hapusEkspedisi($id)
{
	global $conn;
	mysqli_query($conn, "DELETE FROM ekspedisi WHERE ekspedisi_id = $id");

	return mysqli_affected_rows($conn);
}


// ======================================== Barang =============================== //
function tambahBarang($data)
{
    global $conn;
    // ambil data dari tiap elemen dalam form
    $barang_kode              = htmlspecialchars($data["barang_kode"]);
    $barang_kode_slug         = str_replace(" ", "-", $barang_kode);
    $barang_kode_count        = htmlspecialchars($data["barang_kode_count"]);
    $barang_nama              = htmlspecialchars($data["barang_nama"]);
    $barang_deskripsi         = htmlspecialchars($data["barang_deskripsi"]);

    $barang_harga             = htmlspecialchars($data["barang_harga"]);
    $barang_harga_beli        = htmlspecialchars($data["barang_harga_beli"]);
    $barang_harga_grosir_1    = htmlspecialchars($data["barang_harga_grosir_1"]);
    $barang_harga_grosir_2    = htmlspecialchars($data["barang_harga_grosir_2"]);

    $barang_harga_s2          = htmlspecialchars($data["barang_harga_s2"]);
    $barang_harga_grosir_1_s2 = htmlspecialchars($data["barang_harga_grosir_1_s2"]);
    $barang_harga_grosir_2_s2 = htmlspecialchars($data["barang_harga_grosir_2_s2"]);

    $barang_harga_s3          = htmlspecialchars($data["barang_harga_s3"]);
    $barang_harga_grosir_1_s3 = htmlspecialchars($data["barang_harga_grosir_1_s3"]);
    $barang_harga_grosir_2_s3 = htmlspecialchars($data["barang_harga_grosir_2_s3"]);

    $kategori_id              = $data["kategori_id"];

    $satuan_id                = $data["satuan_id"];
    $satuan_id_2              = $data["satuan_id_2"];
    $satuan_id_3              = $data["satuan_id_3"];

    $satuan_isi_1             = 1;
    $satuan_isi_2             = $data["satuan_isi_2"];
    $satuan_isi_3             = $data["satuan_isi_3"];

    $barang_tanggal           = date("d F Y g:i:s a");
    $barang_stock             = htmlspecialchars($data["barang_stock"]);
    $barang_option_sn         = $data["barang_option_sn"];
    $barang_option_konsi      = $data["barang_option_konsi"];
    $barang_status            = '1';
    $kode_suplier             = $data["kode_suplier"];

    // Cek Email
    $barang_kode_cek = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM barang WHERE barang_kode = '" . $barang_kode . "'"));

    if ($barang_kode_cek > 0) {
        echo "
            <script>
                alert('Kode Barang Sudah Ada Coba Kode yang Lain !!!');
            </script>
        ";
        return 0;
    }

    // Jika tidak ada toko yang dipilih, tambahkan ke semua toko
    if (!isset($data['toko_cabang'])) {
        $toko_ids = query("SELECT toko_cabang FROM toko WHERE toko_status = '1'");
        $toko_ids = array_column($toko_ids, 'toko_cabang');
    } else {
        $toko_ids = $data['toko_cabang'];
    }

    $success = true;

    foreach ($toko_ids as $toko_id) {
        // query insert data
        $query = "INSERT INTO barang VALUES ('', '$barang_kode', '$barang_kode_slug', '$barang_kode_count', '$barang_nama', '$barang_harga_beli', '$barang_harga', '$barang_harga_grosir_1', '$barang_harga_grosir_2', '$barang_harga_s2', '$barang_harga_grosir_1_s2', '$barang_harga_grosir_2_s2', '$barang_harga_s3', '$barang_harga_grosir_1_s3', '$barang_harga_grosir_2_s3', '$barang_stock', '$barang_tanggal', '$kategori_id', '$kategori_id', '$satuan_id', '$satuan_id', '$satuan_id_2', '$satuan_id_3', '$satuan_isi_1', '$satuan_isi_2', '$satuan_isi_3', '$barang_deskripsi', '$barang_option_sn', '', '$toko_id', '$barang_option_konsi', '$barang_status', '$kode_suplier')";
        if (!mysqli_query($conn, $query)) {
            $success = false;
        }
    }

    return $success ? count($toko_ids) : 0;
}


function editBarang($data)
{
    global $conn;

    // Ambil data dari form
    $barang_kode = mysqli_real_escape_string($conn, $data["barang_kode"]);
    $kode_suplier = mysqli_real_escape_string($conn, $data["kode_suplier"]);
    $barang_nama = mysqli_real_escape_string($conn, $data["barang_nama"]);
    $barang_deskripsi = mysqli_real_escape_string($conn, $data["barang_deskripsi"]);
    $barang_harga_beli = mysqli_real_escape_string($conn, $data["barang_harga_beli"]);
    $barang_harga = mysqli_real_escape_string($conn, $data["barang_harga"]);
    $barang_harga_grosir_1 = mysqli_real_escape_string($conn, $data["barang_harga_grosir_1"]);
    $barang_harga_grosir_2 = mysqli_real_escape_string($conn, $data["barang_harga_grosir_2"]);
    $barang_harga_s2 = mysqli_real_escape_string($conn, $data["barang_harga_s2"]);
    $barang_harga_grosir_1_s2 = mysqli_real_escape_string($conn, $data["barang_harga_grosir_1_s2"]);
    $barang_harga_grosir_2_s2 = mysqli_real_escape_string($conn, $data["barang_harga_grosir_2_s2"]);
    $barang_harga_s3 = mysqli_real_escape_string($conn, $data["barang_harga_s3"]);
    $barang_harga_grosir_1_s3 = mysqli_real_escape_string($conn, $data["barang_harga_grosir_1_s3"]);
    $barang_harga_grosir_2_s3 = mysqli_real_escape_string($conn, $data["barang_harga_grosir_2_s3"]);
    $barang_kategori_id = mysqli_real_escape_string($conn, $data["barang_kategori_id"]);
    $kategori_id = mysqli_real_escape_string($conn, $data["kategori_id"]);
    $satuan_id = mysqli_real_escape_string($conn, $data["satuan_id"]);
    $satuan_id_2 = mysqli_real_escape_string($conn, $data["satuan_id_2"]);
    $satuan_id_3 = mysqli_real_escape_string($conn, $data["satuan_id_3"]);
    $satuan_isi_2 = mysqli_real_escape_string($conn, $data["satuan_isi_2"]);
    $satuan_isi_3 = mysqli_real_escape_string($conn, $data["satuan_isi_3"]);
    $barang_option_sn = mysqli_real_escape_string($conn, $data["barang_option_sn"]);
    $barang_option_konsi = mysqli_real_escape_string($conn, $data["barang_konsi"]);
    $barang_stock = mysqli_real_escape_string($conn, $data["barang_stock"]);

    $barang_satuan_id = $satuan_id; 
    // Ambil daftar toko aktif
    $toko_ids = query("SELECT toko_cabang FROM toko WHERE toko_status = '1'");
    $toko_ids = array_column($toko_ids, 'toko_cabang');
    $success = true;

    // Update semua atribut kecuali barang_stock
    foreach ($toko_ids as $toko_id) {
        $query = "UPDATE barang SET 
                    barang_kode = '$barang_kode',
                    kode_suplier = '$kode_suplier',
                    barang_nama = '$barang_nama',
                    barang_harga = '$barang_harga',
                    barang_harga_beli = '$barang_harga_beli',
                    barang_harga_grosir_1 = '$barang_harga_grosir_1',
                    barang_harga_grosir_2 = '$barang_harga_grosir_2',
                    barang_harga_s2 = '$barang_harga_s2',
                    barang_harga_grosir_1_s2 = '$barang_harga_grosir_1_s2',
                    barang_harga_grosir_2_s2 = '$barang_harga_grosir_2_s2',
                    barang_harga_s3 = '$barang_harga_s3',
                    barang_harga_grosir_1_s3 = '$barang_harga_grosir_1_s3',
                    barang_harga_grosir_2_s3 = '$barang_harga_grosir_2_s3',
                    barang_kategori_id = '$barang_kategori_id',
                    kategori_id = '$kategori_id',
                    satuan_id = '$satuan_id',
                    barang_satuan_id = '$barang_satuan_id',
                    satuan_id_2 = '$satuan_id_2',
                    satuan_id_3 = '$satuan_id_3',
                    satuan_isi_2 = '$satuan_isi_2',
                    satuan_isi_3 = '$satuan_isi_3',
                    barang_deskripsi = '$barang_deskripsi',
                    barang_option_sn = '$barang_option_sn',
                    barang_konsi = '$barang_option_konsi'
                  WHERE barang_kode = '$barang_kode' AND barang_cabang = '$toko_id'";
        
             if (!mysqli_query($conn, $query)) {
            echo "Error: " . mysqli_error($conn); // Debugging
            $success = false;
        }
    }

    // Update barang_stock hanya untuk toko target
    $query_stock = "UPDATE barang SET 
                    barang_stock = '$barang_stock'
                    WHERE barang_kode = '$barang_kode' AND barang_cabang = '0'";

    if (!mysqli_query($conn, $query_stock)) {
        echo "Error: " . mysqli_error($conn); // Debugging
        $success = false;
    }
    

    return $success ? count($toko_ids) : 0;
}

function editBarangCabang($data)
{
    global $conn;

    // Sanitasi input
    $barang_kode = mysqli_real_escape_string($conn, $data["barang_id"]);
    $barang_stock = mysqli_real_escape_string($conn, $data["barang_stock"]);
    $barang_satuan_id = mysqli_real_escape_string($conn, $data["barang_satuan_id"]);
    $satuan_id = mysqli_real_escape_string($conn, $data["satuan_id"]);
    $satuan_id_2 = mysqli_real_escape_string($conn, $data["satuan_id_2"]);
    $satuan_id_3 = mysqli_real_escape_string($conn, $data["satuan_id_3"]);
    $barang_kategori_id = mysqli_real_escape_string($conn, $data["barang_kategori_id"]);
    $kategori_id = mysqli_real_escape_string($conn, $data["kategori_id"]);

    // Query update
    $query = "
        UPDATE barang 
        SET 
            barang_stock = '$barang_stock', 
            barang_satuan_id = '$barang_satuan_id', 
            satuan_id = '$satuan_id', 
            satuan_id_2 = '$satuan_id_2', 
            satuan_id_3 = '$satuan_id_3', 
            barang_kategori_id = '$barang_kategori_id',
            kategori_id = '$kategori_id'
        WHERE 
            barang_id = '$barang_kode'
    ";

    mysqli_query($conn, $query);

    // Mengembalikan jumlah baris yang terpengaruh
    return mysqli_affected_rows($conn);
}


function hapusBarang($id)
{
	global $conn;

	// Ambil ID produk
	$data_id = $id;

	// Mencari No. Invoice
	$sn = mysqli_query($conn, "select barang_option_sn from barang where barang_id = '" . $data_id . "'");
	$sn = mysqli_fetch_array($sn);
	$sn = $sn["barang_option_sn"];

	$barang = mysqli_query($conn, "select barang_kode_slug, barang_cabang, barang_kode from barang where barang_id = " . $data_id . " ");
	$barang = mysqli_fetch_array($barang);
	$barang_kode_slug 	= $barang['barang_kode_slug'];
	$barang_cabang 		= $barang['barang_cabang'];
	$barang_kode 		= $barang['barang_kode'];

	$countBarangSn = mysqli_query($conn, "select * from barang_sn where barang_kode_slug = '" . $barang_kode_slug . "' && barang_sn_status > 0 && barang_sn_cabang = " . $barang_cabang . " ");
	$countBarangSn = mysqli_num_rows($countBarangSn);

	if ($sn < 1) {
		mysqli_query($conn, "UPDATE barang SET barang_status = '0' WHERE barang_kode = '$barang_kode'");
		return mysqli_affected_rows($conn);
	} else {
		mysqli_query($conn, "UPDATE barang SET barang_status = '0' WHERE barang_kode = '$barang_kode'");

		if ($countBarangSn > 0) {
			mysqli_query($conn, "DELETE FROM barang_sn WHERE barang_kode_slug = '" . $barang_kode_slug . "' && barang_sn_status > 0 && barang_sn_cabang = $barang_cabang ");
		}
		return mysqli_affected_rows($conn);
	}
}

function aktifBarang($id)
{
	global $conn;

	// Ambil ID produk
	$data_id = $id;

	// Mencari No. Invoice
	$sn = mysqli_query($conn, "select barang_option_sn from barang where barang_id = '" . $data_id . "'");
	$sn = mysqli_fetch_array($sn);
	$sn = $sn["barang_option_sn"];

	$barang = mysqli_query($conn, "select barang_kode_slug, barang_cabang, barang_kode from barang where barang_id = " . $data_id . " ");
	$barang = mysqli_fetch_array($barang);
	$barang_kode_slug 	= $barang['barang_kode_slug'];
	$barang_cabang 		= $barang['barang_cabang'];
	$barang_kode 		= $barang['barang_kode'];

	$countBarangSn = mysqli_query($conn, "select * from barang_sn where barang_kode_slug = '" . $barang_kode_slug . "' && barang_sn_status > 1 && barang_sn_cabang = " . $barang_cabang . " ");
	$countBarangSn = mysqli_num_rows($countBarangSn);

	if ($sn < 1) {
		mysqli_query($conn, "UPDATE barang SET barang_status = '1' WHERE barang_kode = '$barang_kode'");
		return mysqli_affected_rows($conn);
	} else {
		mysqli_query($conn, "UPDATE barang SET barang_status = '1' WHERE barang_kode = '$barang_kode'");

		if ($countBarangSn > 0) {
			mysqli_query($conn, "DELETE FROM barang_sn WHERE barang_kode_slug = '" . $barang_kode_slug . "' && barang_sn_status > 1 && barang_sn_cabang = $barang_cabang ");
		}
		return mysqli_affected_rows($conn);
	}
}

// function hapusBarang($id)
// {
// 	global $conn;

// 	// Ambil ID produk
// 	$data_id = $id;

// 	// Mencari No. Invoice
// 	$sn = mysqli_query($conn, "select barang_option_sn from barang where barang_id = '" . $data_id . "'");
// 	$sn = mysqli_fetch_array($sn);
// 	$sn = $sn["barang_option_sn"];

// 	$barang = mysqli_query($conn, "select barang_kode_slug, barang_cabang, barang_kode from barang where barang_id = " . $data_id . " ");
// 	$barang = mysqli_fetch_array($barang);
// 	$barang_kode_slug 	= $barang['barang_kode_slug'];
// 	$barang_cabang 		= $barang['barang_cabang'];
// 	$barang_kode 		= $barang['barang_kode'];

// 	$countBarangSn = mysqli_query($conn, "select * from barang_sn where barang_kode_slug = '" . $barang_kode_slug . "' && barang_sn_status > 0 && barang_sn_cabang = " . $barang_cabang . " ");
// 	$countBarangSn = mysqli_num_rows($countBarangSn);

// 	if ($sn < 1) {
// 		mysqli_query($conn, "DELETE FROM barang WHERE barang_id = $id);
// 		return mysqli_affected_rows($conn);
// 	} else {
// 		mysqli_query($conn, "DELETE FROM barang WHERE barang_id = $id");

// 		if ($countBarangSn > 0) {
// 			mysqli_query($conn, "DELETE FROM barang_sn WHERE barang_kode_slug = '" . $barang_kode_slug . "' && barang_sn_status > 0 && barang_sn_cabang = $barang_cabang ");
// 		}
// 		return mysqli_affected_rows($conn);
// 	}
// }

// ===================================== Barang SN ========================================= //
function tambahBarangSn($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$barang_sn_desc 			= $data['barang_sn_desc'];
	$barang_kode_slug 			= $data['barang_kode_slug'];
	$barang_sn_status 			= $data['barang_sn_status'];
	$barang_sn_cabang 			= $data['barang_sn_cabang'];

	$jumlah = count($barang_kode_slug);

	// query insert data
	for ($x = 0; $x < $jumlah; $x++) {
		$query = "INSERT INTO barang_sn VALUES ('', '$barang_sn_desc[$x]', '$barang_kode_slug[$x]', '$barang_sn_status[$x]', '$barang_sn_cabang[$x]')";

		mysqli_query($conn, $query);
	}

	return mysqli_affected_rows($conn);
}

function editBarangSn($data)
{
	global $conn;
	$id = $data["barang_sn_id"];

	// ambil data dari tiap elemen dalam form
	$barang_sn_desc 	= htmlspecialchars($data['barang_sn_desc']);
	$barang_sn_status 	= $data['barang_sn_status'];

	// query update data
	$query = "UPDATE barang_sn SET 
				barang_sn_desc    = '$barang_sn_desc',
				barang_sn_status  = '$barang_sn_status'
				WHERE barang_sn_id = $id
				";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

function hapusBarangSn($id)
{
	global $conn;
	mysqli_query($conn, "DELETE FROM barang_sn WHERE barang_sn_id = $id");

	return mysqli_affected_rows($conn);
}

// ===================================== Keranjang ========================================= //
function tambahKeranjang(
	$keranjang_cabang,
	$barang_id,
	$barang_kode_slug,
	$keranjang_nama,
	$keranjang_harga_beli,
	$keranjang_harga,
	$keranjang_satuan,
	$keranjang_id_kasir,
	$keranjang_qty,
	$keranjang_konversi_isi,
	$keranjang_barang_sn_id,
	$keranjang_barang_option_sn,
	$keranjang_sn,
	$keranjang_id_cek,
	$customer
) {
	global $conn;

	$q = "select * from keranjang where barang_id = " . $barang_id . " AND keranjang_tipe_customer = $customer ";
	// Cek STOCK
	$barang_id_cek = mysqli_num_rows(mysqli_query($conn, $q));
	// echo json_encode($q);
	// die;
	if ($barang_id_cek > 0 && $keranjang_barang_option_sn < 1) {
		$keranjangParent = mysqli_query($conn, "select keranjang_qty, keranjang_qty_view, keranjang_konversi_isi from keranjang where keranjang_id_cek = '" . $keranjang_id_cek . "'");
		$kp = mysqli_fetch_array($keranjangParent);
		// $kp += $keranjang_qty;
		$keranjang_qty_view_keranjang 		= $kp['keranjang_qty_view'];
		$keranjang_qty_keranjang 			= $kp['keranjang_qty'];
		$keranjang_konversi_isi_keranjang 	= $kp['keranjang_konversi_isi'];

		$kqvk = $keranjang_qty_view_keranjang + $keranjang_qty;
		$kqkk = $keranjang_qty_keranjang + $keranjang_konversi_isi_keranjang;

		$query = "UPDATE keranjang SET 
					keranjang_qty   	= '$kqkk',
					keranjang_qty_view  = '$kqvk'
					WHERE keranjang_id_cek = $keranjang_id_cek
					";
		mysqli_query($conn, $query);
		return mysqli_affected_rows($conn);
	} else {
		// query insert data
		$query = "INSERT INTO keranjang VALUES (null, 
		'$keranjang_nama', 
		'$keranjang_harga_beli', 
		'$keranjang_harga',
		'$keranjang_harga', 
		'0',
		'$keranjang_satuan', 
		'$barang_id', 
		'$barang_kode_slug', 
		'$keranjang_qty', 
		'$keranjang_qty', 
		'$keranjang_konversi_isi', 
		'$keranjang_barang_sn_id', 
		'$keranjang_barang_option_sn', 
		'$keranjang_sn', 
		'$keranjang_id_kasir', 
		'$keranjang_id_cek', 
		'$customer', 
		'$keranjang_cabang')";

		mysqli_query($conn, $query);

		return mysqli_affected_rows($conn);
	}
}

function tambahKeranjangDraft(
	$keranjang_cabang,
	$barang_id,
	$barang_kode_slug,
	$keranjang_nama,
	$keranjang_harga_beli,
	$keranjang_harga,
	$keranjang_satuan,
	$keranjang_id_kasir,
	$keranjang_qty,
	$keranjang_konversi_isi,
	$keranjang_barang_sn_id,
	$keranjang_barang_option_sn,
	$keranjang_sn,
	$keranjang_id_cek,
	$invoice,
	$customer
) {
	global $conn;


	// Cek STOCK
	$barang_id_cek = mysqli_num_rows(mysqli_query($conn, "select * from keranjang_draft where barang_id = " . $barang_id . " && keranjang_invoice = " . $invoice . " && keranjang_cabang = " . $keranjang_cabang . " "));

	if ($barang_id_cek > 0 && $keranjang_barang_option_sn < 1) {
		$keranjangParent = mysqli_query($conn, "select keranjang_qty, keranjang_qty_view, keranjang_konversi_isi from keranjang_draft where keranjang_id_cek = '" . $keranjang_id_cek . "'");
		$kp = mysqli_fetch_array($keranjangParent);
		// $kp += $keranjang_qty;
		$keranjang_qty_view_keranjang 		= $kp['keranjang_qty_view'];
		$keranjang_qty_keranjang 			= $kp['keranjang_qty'];
		$keranjang_konversi_isi_keranjang 	= $kp['keranjang_konversi_isi'];

		$kqvk = $keranjang_qty_view_keranjang + $keranjang_qty;
		$kqkk = $keranjang_qty_keranjang + $keranjang_konversi_isi_keranjang;

		$query = "UPDATE keranjang_draft SET 
					keranjang_qty   	= '$kqkk',
					keranjang_qty_view  = '$kqvk'
					WHERE keranjang_id_cek = $keranjang_id_cek
					";

		mysqli_query($conn, $query);
		return mysqli_affected_rows($conn);
	} else {
		// query insert data
		$query = "INSERT INTO keranjang_draft VALUES (null, 
		'$keranjang_nama', 
		'$keranjang_harga_beli', 
		'$keranjang_harga',
		'$keranjang_harga', 
		'0', 
		'$keranjang_satuan', 
		'$barang_id', 
		'$barang_kode_slug', 
		'$keranjang_qty', 
		'$keranjang_qty', 
		'$keranjang_konversi_isi', 
		'$keranjang_barang_sn_id', 
		'$keranjang_barang_option_sn', 
		'$keranjang_sn', 
		'$keranjang_id_kasir', 
		'$keranjang_id_cek', 
		'$customer', 
		'1',
		'$invoice',
		'$keranjang_cabang')";
		mysqli_query($conn, $query);

		return mysqli_affected_rows($conn);
	}
}

function tambahKeranjangBarcode($data)
{
	global $conn;

	$barang_kode 		= htmlspecialchars($data['inputbarcode']);
	$keranjang_id_kasir = $data['keranjang_id_kasir'];
	$tipe_harga 		= $data['tipe_harga'];
	$keranjang_cabang   = $data['keranjang_cabang'];

	// Ambil Data Barang berdasarkan Kode Barang 
	$barang 	= mysqli_query($conn, "select barang_id, 
		barang_nama, 
		barang_harga_beli, 
		barang_harga, 
		barang_harga_grosir_1, 
		barang_harga_grosir_2, 
		barang_stock, 
		barang_kode_slug, 
		satuan_id,
		satuan_isi_1,
		barang_option_sn from barang where barang_kode = '" . $barang_kode . "' && barang_cabang = " . $keranjang_cabang . " ");
	$br 		= mysqli_fetch_array($barang);

	$barang_id  				= $br["barang_id"];
	$keranjang_nama  			= $br["barang_nama"];
	$keranjang_harga_beli  		= $br["barang_harga_beli"];
	$keranjang_satuan           = $br["satuan_id"];
	$keranjang_konversi_isi     = $br["satuan_isi_1"];

	if ($tipe_harga == 1) {
		$keranjang_harga  = $br["barang_harga_grosir_1"];
	} elseif ($tipe_harga == 2) {
		$keranjang_harga  = $br["barang_harga_grosir_2"];
	} else {
		$keranjang_harga  = $br["barang_harga"];
	}

	$barang_stock 				= $br["barang_stock"];
	$barang_kode_slug 			= $br["barang_kode_slug"];
	$keranjang_barang_option_sn = $br["barang_option_sn"];
	$keranjang_qty      		= 1;
	$keranjang_konversi_isi     = $br['satuan_isi_1'];
	$keranjang_barang_sn_id     = 0;
	$keranjang_sn       		= 0;
	$keranjang_tipe_customer    = $tipe_harga;
	$keranjang_id_cek   		= $barang_id . $keranjang_id_kasir . $keranjang_cabang;


	// Kondisi jika scan Barcode Tidak sesuai
	if ($barang_id != null) {

		// Cek apakah data barang sudah sesuai dengan jumlah stok saat Insert Ke Keranjang dan jika melebihi stok maka akan dikembalikan
		$idBarang = mysqli_query($conn, "select keranjang_qty, keranjang_konversi_isi, keranjang_tipe_customer from keranjang where barang_id = " . $barang_id . " ");
		$idBarang = mysqli_fetch_array($idBarang);
		$keranjang_qty_stock = $idBarang['keranjang_qty'] * $idBarang['keranjang_konversi_isi'];

		if ($keranjang_qty_stock >= $barang_stock) {
			echo '
				<script>
					alert("Produk TIDAK BISA DITAMBAHKAN Karena Jumlah QTY Melebihi Stock yang Ada di Semua Transaksi Kasir & Mohon di Cek Kembali !!!");
					document.location.href = "";
				</script>
			';
		} else {
			// Cek STOCK
			$barang_id_cek = mysqli_num_rows(mysqli_query($conn, "select * from keranjang where keranjang_id_cek = " . $keranjang_id_cek . " "));

			if ($barang_id_cek > 0 && $keranjang_barang_option_sn < 1) {
				$keranjangParent = mysqli_query($conn, "select keranjang_qty, keranjang_qty_view, keranjang_konversi_isi from keranjang where keranjang_id_cek = '" . $keranjang_id_cek . "'");
				$kp = mysqli_fetch_array($keranjangParent);
				// $kp += $keranjang_qty;
				$keranjang_qty_view_keranjang 		= $kp['keranjang_qty_view'];
				$keranjang_qty_keranjang 			= $kp['keranjang_qty'];
				$keranjang_konversi_isi_keranjang 	= $kp['keranjang_konversi_isi'];

				$kqvk = $keranjang_qty_view_keranjang + $keranjang_qty;
				$kqkk = $keranjang_qty_keranjang + $keranjang_konversi_isi_keranjang;

				$query = "UPDATE keranjang SET 
							keranjang_qty   	= '$kqkk',
							keranjang_qty_view  = '$kqvk'
							WHERE keranjang_id_cek = $keranjang_id_cek
							";
				mysqli_query($conn, $query);
				return mysqli_affected_rows($conn);
			} else {
				// query insert data
				$query = "INSERT INTO keranjang VALUES (null, 
				'$keranjang_nama', 
				'$keranjang_harga_beli', 
				'$keranjang_harga',
				'$keranjang_harga', 
				'0',
				'$keranjang_satuan',
				'$barang_id', 
				'$barang_kode_slug', 
				'$keranjang_qty', 
				'$keranjang_qty',
				'$keranjang_konversi_isi',
				'$keranjang_barang_sn_id', 
				'$keranjang_barang_option_sn', 
				'$keranjang_sn', 
				'$keranjang_id_kasir', 
				'$keranjang_id_cek', 
				'$keranjang_tipe_customer',
				'$keranjang_cabang')";
				mysqli_query($conn, $query);

				return mysqli_affected_rows($conn);
			}
		}
	} else {
		echo '
			<script>
				alert("Kode Produk Tidak ada di Data Master Barang dan Coba Cek Kembali !! ");
				document.location.href = "";
			</script>
		';
	}
}

function tambahKeranjangBarcodeDraft($data)
{
	global $conn;

	$barang_kode 		= htmlspecialchars($data['inputbarcodeDraft']);
	$keranjang_id_kasir = $data['keranjang_id_kasir'];
	$tipe_harga 		= $data['tipe_harga'];
	$keranjang_invoice  = $data['keranjang_invoice'];
	$keranjang_cabang   = $data['keranjang_cabang'];

	// Ambil Data Barang berdasarkan Kode Barang 
	$barang 	= mysqli_query($conn, "select barang_id, 
		barang_nama, 
		barang_harga_beli, 
		barang_harga, 
		barang_harga_grosir_1, 
		barang_harga_grosir_2, 
		barang_stock, 
		barang_kode_slug, 
		satuan_id,
		satuan_isi_1,
		barang_option_sn from barang where barang_kode = '" . $barang_kode . "' && barang_cabang = " . $keranjang_cabang . " ");
	$br 		= mysqli_fetch_array($barang);

	$barang_id  				= $br["barang_id"];
	$keranjang_nama  			= $br["barang_nama"];
	$keranjang_harga_beli  		= $br["barang_harga_beli"];
	$keranjang_satuan           = $br["satuan_id"];
	$keranjang_konversi_isi     = $br["satuan_isi_1"];

	if ($tipe_harga == 1) {
		$keranjang_harga  = $br["barang_harga_grosir_1"];
	} elseif ($tipe_harga == 2) {
		$keranjang_harga  = $br["barang_harga_grosir_2"];
	} else {
		$keranjang_harga  = $br["barang_harga"];
	}

	$barang_stock 				= $br["barang_stock"];
	$barang_kode_slug 			= $br["barang_kode_slug"];
	$keranjang_barang_option_sn = $br["barang_option_sn"];
	$keranjang_qty      		= 1;
	$keranjang_konversi_isi     = $br['satuan_isi_1'];
	$keranjang_barang_sn_id     = 0;
	$keranjang_sn       		= 0;
	$keranjang_tipe_customer    = $tipe_harga;
	$keranjang_id_cek   		= $barang_id . $keranjang_id_kasir . $keranjang_cabang;


	// Kondisi jika scan Barcode Tidak sesuai
	if ($barang_id != null) {

		// Cek apakah data barang sudah sesuai dengan jumlah stok saat Insert Ke Keranjang dan jika melebihi stok maka akan dikembalikan
		$idBarang = mysqli_query($conn, "select keranjang_qty, keranjang_konversi_isi, keranjang_tipe_customer from keranjang_draft where barang_id = " . $barang_id . " ");
		$idBarang = mysqli_fetch_array($idBarang);
		$keranjang_qty_stock = $idBarang['keranjang_qty'] + $idBarang['keranjang_konversi_isi'];

		if ($keranjang_qty_stock >= $barang_stock) {
			echo '
				<script>
					alert("Produk TIDAK BISA DITAMBAHKAN Karena Jumlah QTY Melebihi Stock yang Ada di Semua Transaksi Kasir & Mohon di Cek Kembali !!!");
					document.location.href = "";
				</script>
			';
		} else {
			// Cek STOCK
			$barang_id_cek = mysqli_num_rows(mysqli_query($conn, "select * from keranjang_draft where barang_id = " . $barang_id . " && keranjang_invoice = " . $keranjang_invoice . " && keranjang_cabang = " . $keranjang_cabang . " "));

			if ($barang_id_cek > 0 && $keranjang_barang_option_sn < 1) {
				$keranjangParent = mysqli_query($conn, "select keranjang_qty, keranjang_qty_view, keranjang_konversi_isi from keranjang_draft where keranjang_id_cek = '" . $keranjang_id_cek . "'");
				$kp = mysqli_fetch_array($keranjangParent);
				// $kp += $keranjang_qty;
				$keranjang_qty_view_keranjang 		= $kp['keranjang_qty_view'];
				$keranjang_qty_keranjang 			= $kp['keranjang_qty'];
				$keranjang_konversi_isi_keranjang 	= $kp['keranjang_konversi_isi'];

				$kqvk = $keranjang_qty_view_keranjang + $keranjang_qty;
				$kqkk = $keranjang_qty_keranjang + $keranjang_konversi_isi_keranjang;

				$query = "UPDATE keranjang_draft SET 
							keranjang_qty   	= '$kqkk',
							keranjang_qty_view  = '$kqvk'
							WHERE keranjang_id_cek = $keranjang_id_cek
							";
				mysqli_query($conn, $query);
				return mysqli_affected_rows($conn);
			} else {
				// query insert data
				$query = "INSERT INTO keranjang_draft VALUES ('', 
				'$keranjang_nama', 
				'$keranjang_harga_beli', 
				'$keranjang_harga', 
				'$keranjang_harga', 
				'0',
				'$keranjang_satuan',
				'$barang_id', 
				'$barang_kode_slug', 
				'$keranjang_qty', 
				'$keranjang_qty',
				'$keranjang_konversi_isi',
				'$keranjang_barang_sn_id', 
				'$keranjang_barang_option_sn', 
				'$keranjang_sn', 
				'$keranjang_id_kasir', 
				'$keranjang_id_cek', 
				'$keranjang_tipe_customer',
				'1',
				'$keranjang_invoice',
				'$keranjang_cabang')";
				mysqli_query($conn, $query);

				return mysqli_affected_rows($conn);
			}
		}
	} else {
		echo '
			<script>
				alert("Kode Produk Tidak ada di Data Master Barang dan Coba Cek Kembali !! ");
				document.location.href = "";
			</script>
		';
	}
}

function updateSn($data)
{
	global $conn;
	$id = $data["keranjang_id"];


	// ambil data dari tiap elemen dalam form
	$barang_sn_id  = $data["barang_sn_id"];


	$barang_sn_desc = mysqli_query($conn, "select barang_sn_desc from barang_sn where barang_sn_id = '" . $barang_sn_id . "'");
	$barang_sn_desc = mysqli_fetch_array($barang_sn_desc);
	$barang_sn_desc = $barang_sn_desc['barang_sn_desc'];

	// query update data
	$query = "UPDATE keranjang SET 
						keranjang_barang_sn_id  = '$barang_sn_id',
						keranjang_sn            = '$barang_sn_desc'
						WHERE keranjang_id      = $id
				";

	$query2 = "UPDATE barang_sn SET 
						barang_sn_status     = 0
						WHERE barang_sn_id = $barang_sn_id
				";

	mysqli_query($conn, $query);
	mysqli_query($conn, $query2);

	return mysqli_affected_rows($conn);
}

function updateSnDrfat($data)
{
	global $conn;
	$id = $data["keranjang_draf_id"];


	// ambil data dari tiap elemen dalam form
	$barang_sn_id  = $data["barang_sn_id"];


	$barang_sn_desc = mysqli_query($conn, "select barang_sn_desc from barang_sn where barang_sn_id = '" . $barang_sn_id . "'");
	$barang_sn_desc = mysqli_fetch_array($barang_sn_desc);
	$barang_sn_desc = $barang_sn_desc['barang_sn_desc'];

	// query update data
	$query = "UPDATE keranjang_draft SET 
						keranjang_barang_sn_id  = '$barang_sn_id',
						keranjang_sn            = '$barang_sn_desc'
						WHERE keranjang_draf_id      = $id
				";

	$query2 = "UPDATE barang_sn SET 
						barang_sn_status     = 0
						WHERE barang_sn_id = $barang_sn_id
				";

	mysqli_query($conn, $query);
	mysqli_query($conn, $query2);

	return mysqli_affected_rows($conn);
}

// function updateHarga($data){
// 	global $conn;
// 	$id 				= $data["keranjang_id"];
// 	$keranjang_harga 	= htmlspecialchars($data["keranjang_harga"]);

// 	$query = "UPDATE keranjang SET 
// 						keranjang_harga  		= '$keranjang_harga'
// 						WHERE keranjang_id      = $id
// 				";

// 	mysqli_query($conn, $query);
// 	return mysqli_affected_rows($conn);
// }

// function updateQTY($data) {
// 	global $conn;
// 	$id = $data["keranjang_id"];

// 	// ambil data dari tiap elemen dalam form
// 	$keranjang_qty = htmlspecialchars($data['keranjang_qty']);
// 	$stock_brg = $data['stock_brg'];

// 	if ( $keranjang_qty > $stock_brg ) {
// 		echo"
// 			<script>
// 				alert('QTY Melebihi Stock Barang.. Coba Cek Lagi !!!');
// 				document.location.href = 'beli-langsung.php';
// 			</script>
// 		";
// 	} else {
// 		// query update data
// 		$query = "UPDATE keranjang SET 
// 					keranjang_qty   = '$keranjang_qty'
// 					WHERE keranjang_id = $id
// 					";
// 		mysqli_query($conn, $query);
// 		return mysqli_affected_rows($conn);
// 	}
// }

function updateQTYHarga($data)
{
	global $conn;
	$id = $data["keranjang_id"];

	// ambil data dari tiap elemen dalam form
	$keranjang_qty_view 		= htmlspecialchars($data['keranjang_qty_view']);
	$keranjang_barang_option_sn = $data['keranjang_barang_option_sn'];

	$keranjang_satuan_end_isi   = $data['keranjang_satuan_end_isi'];
	$pecah_data 				= explode("-", $keranjang_satuan_end_isi);

	if ($keranjang_barang_option_sn < 1) {
		$keranjang_satuan   		= $pecah_data[0];
		$keranjang_konversi_isi 	= $pecah_data[1];
		$checkboxHarga              = $data['checkbox-harga'];
		if ($checkboxHarga > 0) {
			$keranjang_harga 		= htmlspecialchars($data["keranjang_harga"]);
		} else {
			$keranjang_harga 	    = $pecah_data[2];
		}
	} else {
		$keranjang_satuan   		= $data['keranjang_satuan'];
		$keranjang_konversi_isi 	= $data['keranjang_konversi_isi'];
		$checkboxHarga              = $data['checkbox-harga'];
		$keranjang_harga 			= htmlspecialchars($data["keranjang_harga"]);
	}

	$stock_brg 			        = $data['stock_brg'];
	$keranjang_qty              = $keranjang_qty_view * $keranjang_konversi_isi;

	if ($keranjang_qty > $stock_brg) {
		echo "
			<script>
				alert('QTY Melebihi Stock Barang.. Coba Cek Lagi !!!');
				document.location.href = '';
			</script>
		";
	} else {
		// query update data
		$query = "UPDATE keranjang SET 
					keranjang_harga  		= '$keranjang_harga',
					keranjang_harga_edit  	= '$checkboxHarga',
					keranjang_satuan        = '$keranjang_satuan',
					keranjang_qty   		= '$keranjang_qty',
					keranjang_qty_view   	= '$keranjang_qty_view',
					keranjang_konversi_isi  = '$keranjang_konversi_isi'
					WHERE keranjang_id 		= $id
					";
		mysqli_query($conn, $query);
		return mysqli_affected_rows($conn);
	}
}

function updateQTYHargaDraft($data)
{
	global $conn;
	$id = $data["keranjang_draf_id"];


	// ambil data dari tiap elemen dalam form
	$keranjang_qty_view 		= htmlspecialchars($data['keranjang_qty_view']);
	$keranjang_barang_option_sn = $data['keranjang_barang_option_sn'];

	$keranjang_satuan_end_isi   = $data['keranjang_satuan_end_isi'];
	$pecah_data 				= explode("-", $keranjang_satuan_end_isi);
	$keranjang_satuan   		= $pecah_data[0];
	$keranjang_konversi_isi 	= $pecah_data[1];

	if ($keranjang_barang_option_sn < 1) {
		$keranjang_harga 	        = $pecah_data[2];
	} else {
		$keranjang_harga 			= htmlspecialchars($data["keranjang_harga"]);
	}

	$stock_brg 			        = $data['stock_brg'];
	$keranjang_qty              = $keranjang_qty_view * $keranjang_konversi_isi;

	if ($keranjang_qty > $stock_brg) {
		echo "
			<script>
				alert('QTY Melebihi Stock Barang.. Coba Cek Lagi !!!');
				document.location.href = '';
			</script>
		";
	} else {
		// query update data
		$query = "UPDATE keranjang_draft SET 
					keranjang_harga  		= '$keranjang_harga',
					keranjang_satuan        = '$keranjang_satuan',
					keranjang_qty   		= '$keranjang_qty',
					keranjang_qty_view   	= '$keranjang_qty_view',
					keranjang_konversi_isi  = '$keranjang_konversi_isi'
					WHERE keranjang_draf_id 		= $id
					";
		mysqli_query($conn, $query);
		return mysqli_affected_rows($conn);
	}
}

function hapusKeranjang($id)
{
	global $conn;


	// Ambil ID produk
	$data_id = $id;

	// Mencari keranjang_barang_sn_id
	$keranjang_barang_sn_id = mysqli_query($conn, "select keranjang_barang_sn_id from keranjang where keranjang_id = '" . $data_id . "'");
	$keranjang_barang_sn_id = mysqli_fetch_array($keranjang_barang_sn_id);
	$keranjang_barang_sn_id = $keranjang_barang_sn_id["keranjang_barang_sn_id"];



	if ($keranjang_barang_sn_id > 0) {
		$query2 = "UPDATE barang_sn SET 
					barang_sn_status    = 1
					WHERE barang_sn_id  = $keranjang_barang_sn_id
					";
		mysqli_query($conn, $query2);
	}

	mysqli_query($conn, "DELETE FROM keranjang WHERE keranjang_id = $id");

	return mysqli_affected_rows($conn);
}

function hapusKeranjangDraft($id)
{
	global $conn;
	// Ambil ID produk
	$data_id = $id;

	// Mencari keranjang_barang_sn_id
	$keranjang_barang_sn_id = mysqli_query($conn, "select keranjang_barang_sn_id from keranjang_draft where keranjang_draf_id = '" . $data_id . "'");
	$keranjang_barang_sn_id = mysqli_fetch_array($keranjang_barang_sn_id);
	$keranjang_barang_sn_id = $keranjang_barang_sn_id["keranjang_barang_sn_id"];


	if ($keranjang_barang_sn_id > 0) {
		$query2 = "UPDATE barang_sn SET 
					barang_sn_status    = 1
					WHERE barang_sn_id  = $keranjang_barang_sn_id
					";
		mysqli_query($conn, $query2);
	}

	mysqli_query($conn, "DELETE FROM keranjang_draft WHERE keranjang_draf_id = $id");

	return mysqli_affected_rows($conn);
}

function updateStock($data)
{
	global $conn;
	
	// Validasi data yang diperlukan
	if (empty($data['barang_ids']) || !is_array($data['barang_ids'])) {
		error_log("Error: barang_ids is empty or not an array");
		return 0;
	}
	
	$id                  		= $data['barang_ids'];
	$keranjang_qty       		= $data['keranjang_qty'] ?? [];
	$keranjang_qty_view       	= $data['keranjang_qty_view'] ?? [];
	$keranjang_konversi_isi     = $data['keranjang_konversi_isi'] ?? [];
	$keranjang_satuan           = $data['keranjang_satuan'] ?? [];
	$keranjang_harga_beli       = $data['keranjang_harga_beli'] ?? [];
	$keranjang_harga			= $data['keranjang_harga'] ?? [];
	$keranjang_harga_parent		= $data['keranjang_harga_parent'] ?? [];
	$keranjang_harga_edit		= $data['keranjang_harga_edit'] ?? [];
	$keranjang_id_kasir  		= $data['keranjang_id_kasir'] ?? [];
	$penjualan_invoice   		= $data['penjualan_invoice'] ?? [];
	$keranjang_barang_option_sn = $data['keranjang_barang_option_sn'] ?? [];
	$keranjang_barang_sn_id     = $data['keranjang_barang_sn_id'] ?? [];
	$keranjang_sn               = $data['keranjang_sn'] ?? [];
	$invoice_customer_category2 = $data['invoice_customer_category2'] ?? [];
	$penjualan_cabang        	= $data['penjualan_cabang'] ?? [];

	$kik                 		= $data['kik'];
	$penjualan_invoice2  		= $data['penjualan_invoice2'];
	$invoice_tgl         		= date("d F Y g:i:s a");
	$invoice_total_beli       	= $data['invoice_total_beli'];
	$invoice_total       		= $data['invoice_total'];
	$invoice_ongkir      		= htmlspecialchars($data['invoice_ongkir']);
	$invoice_diskon      		= htmlspecialchars($data['invoice_diskon']);

	$invoice_sub_total   		= $invoice_total + $invoice_ongkir;
	$invoice_sub_total   		= $invoice_sub_total - $invoice_diskon;
	$invoice_bayar       		= htmlspecialchars($data['angka1']);
	if ($invoice_bayar == null) {
		echo "
			<script>
				alert('Anda Belum Input Nominal BAYAR !!!');
				document.location.href = '';
			</script>
		";
		exit();
	}

	$invoice_kembali     		= $invoice_bayar - $invoice_sub_total;
	$invoice_date        		= date("Y-m-d");
	$invoice_date_year_month    = date("Y-m");
	$penjualan_date      		= $data['penjualan_date'];
	$invoice_customer    		= $data['invoice_customer'];
	$invoice_customer_category  = $data['invoice_customer_category'];
	$invoice_kurir    	 		= $data['invoice_kurir'];
	$invoice_tipe_transaksi  	= $data['invoice_tipe_transaksi'];
	$penjualan_invoice_count 	= $data['penjualan_invoice_count'];
	$invoice_piutang			= $data['invoice_piutang'];
	if ($invoice_piutang == 1) {
		$invoice_piutang_dp = $invoice_bayar;
	} else {
		$invoice_piutang_dp = 0;
	}
	$invoice_piutang_jatuh_tempo = $data['invoice_piutang_jatuh_tempo'];
	$invoice_piutang_lunas		= $data['invoice_piutang_lunas'];
	$invoice_cabang             = $data['invoice_cabang'];


	if ($invoice_customer == 1) {
		$invoice_marketplace = htmlspecialchars($data['invoice_marketplace']);
		$invoice_ekspedisi   = htmlspecialchars($data['invoice_ekspedisi']);
		$invoice_no_resi     = htmlspecialchars($data['invoice_no_resi']);
	} else {
		$invoice_marketplace = "";
		$invoice_ekspedisi   = 0;
		$invoice_no_resi     = "-";
	}
	// Pastikan $keranjang_id_kasir adalah array
	if (!is_array($keranjang_id_kasir)) {
		error_log("Error: keranjang_id_kasir is not an array. Value: " . print_r($keranjang_id_kasir, true));
		return 0;
	}
	
	$jumlah = count($keranjang_id_kasir);
	
	// Validasi jumlah item
	if ($jumlah == 0) {
		error_log("Error: No items in cart. keranjang_id_kasir count: " . $jumlah);
		error_log("Data received: " . print_r($data, true));
		return 0;
	}
	
	// Debug: Log jumlah item
	error_log("Processing " . $jumlah . " items for invoice: " . $penjualan_invoice2);

	if ($invoice_piutang == 0 && $invoice_bayar < $invoice_sub_total) {
		echo "
			<script>
				alert('Transaksi TIDAK BISA Dilanjutakn !!! Nominal Pembayaran LEBIH KECIL dari Total Pembayaran.. Silahkan Melakukan Transaksi PIUTANG jika Nominal Kurang Dari Total Pembayaran');
				document.location.href = '';
			</script>
		";
		exit();
	} elseif ($invoice_piutang == 1 && $invoice_bayar >= $invoice_sub_total) {
		echo "
			<script>
				alert('Transaksi TIDAK BISA Dilanjutakn !!! Nominal DP LEBIH BESAR / SAMA dari Total Piutang.. Silahkan Melakukan Transaksi CASH jika Nominal Lebih Besar / Sama Dari Total Pembayaran');
				document.location.href = '';
			</script>
		";
		exit();
	} else {
		// Escape semua nilai untuk keamanan
		$penjualan_invoice2 = mysqli_real_escape_string($conn, $penjualan_invoice2);
		$penjualan_invoice_count = mysqli_real_escape_string($conn, $penjualan_invoice_count);
		$invoice_tgl = mysqli_real_escape_string($conn, $invoice_tgl);
		$invoice_customer = mysqli_real_escape_string($conn, $invoice_customer);
		$invoice_customer_category = mysqli_real_escape_string($conn, $invoice_customer_category);
		$invoice_kurir = mysqli_real_escape_string($conn, $invoice_kurir);
		$invoice_tipe_transaksi = mysqli_real_escape_string($conn, $invoice_tipe_transaksi);
		$invoice_total_beli = floatval($invoice_total_beli);
		$invoice_total = floatval($invoice_total);
		$invoice_ongkir = floatval($invoice_ongkir);
		$invoice_diskon = floatval($invoice_diskon);
		$invoice_sub_total = floatval($invoice_sub_total);
		$invoice_bayar = floatval($invoice_bayar);
		$invoice_kembali = floatval($invoice_kembali);
		$kik = intval($kik);
		$invoice_date = mysqli_real_escape_string($conn, $invoice_date);
		$invoice_date_year_month = mysqli_real_escape_string($conn, $invoice_date_year_month);
		$invoice_marketplace = mysqli_real_escape_string($conn, $invoice_marketplace);
		$invoice_ekspedisi = mysqli_real_escape_string($conn, $invoice_ekspedisi);
		$invoice_no_resi = mysqli_real_escape_string($conn, $invoice_no_resi);
		$invoice_piutang = intval($invoice_piutang);
		$invoice_piutang_dp = floatval($invoice_piutang_dp);
		$invoice_piutang_jatuh_tempo = mysqli_real_escape_string($conn, $invoice_piutang_jatuh_tempo);
		$invoice_piutang_lunas = intval($invoice_piutang_lunas);
		$invoice_cabang = intval($invoice_cabang);
		
		// query insert invoice
		$query1 = "INSERT INTO invoice VALUES ('', '$penjualan_invoice2', '$penjualan_invoice_count', '$invoice_tgl', '$invoice_customer', '$invoice_customer_category', '$invoice_kurir', '1','2', '$invoice_tipe_transaksi', '$invoice_total_beli', '$invoice_total', '$invoice_ongkir', '$invoice_diskon', '$invoice_sub_total', '$invoice_bayar', '$invoice_kembali', '$kik', '$invoice_date', '$invoice_date_year_month', ' ', ' ', '$invoice_total_beli', '$invoice_total', '$invoice_ongkir', '$invoice_sub_total', '$invoice_bayar', '$invoice_kembali', '$invoice_marketplace', '$invoice_ekspedisi', '$invoice_no_resi', '-', '$invoice_piutang', '$invoice_piutang_dp', '$invoice_piutang_jatuh_tempo', '$invoice_piutang_lunas', 0, '$invoice_cabang')";
		
		// Debug: Log query sebelum eksekusi
		error_log("Inserting invoice: " . $penjualan_invoice2);
		
		$result_invoice = mysqli_query($conn, $query1);
		if (!$result_invoice) {
			$error_msg = mysqli_error($conn);
			error_log("Error inserting invoice: " . $error_msg);
			error_log("Query: " . $query1);
			error_log("Invoice data: " . print_r([
				'invoice' => $penjualan_invoice2,
				'customer' => $invoice_customer,
				'total' => $invoice_sub_total,
				'bayar' => $invoice_bayar
			], true));
			return 0;
		}
		
		// Debug: Log success
		error_log("Invoice inserted successfully: " . $penjualan_invoice2);

		for ($x = 0; $x < $jumlah; $x++) {
			// Escape semua nilai untuk keamanan
			$barang_id = intval($id[$x]);
			$qty_view = floatval($keranjang_qty_view[$x]);
			$qty = floatval($keranjang_qty[$x]);
			$konversi_isi = floatval($keranjang_konversi_isi[$x]);
			$satuan = intval($keranjang_satuan[$x]);
			$harga_beli = floatval($keranjang_harga_beli[$x]);
			$harga = floatval($keranjang_harga[$x]);
			$harga_parent = floatval($keranjang_harga_parent[$x]);
			$harga_edit = floatval($keranjang_harga_edit[$x]);
			$id_kasir = intval($keranjang_id_kasir[$x]);
			$penjualan_inv = mysqli_real_escape_string($conn, $penjualan_invoice[$x]);
			$penjualan_dt = mysqli_real_escape_string($conn, $penjualan_date[$x]);
			$date_ym = mysqli_real_escape_string($conn, $invoice_date_year_month);
			$option_sn = intval($keranjang_barang_option_sn[$x]);
			$sn_id = !empty($keranjang_barang_sn_id[$x]) ? intval($keranjang_barang_sn_id[$x]) : 0;
			$sn = mysqli_real_escape_string($conn, $keranjang_sn[$x] ?? '');
			$customer_cat = intval($invoice_customer_category2[$x]);
			$cabang = intval($penjualan_cabang[$x]);
			
			$query = "INSERT INTO penjualan VALUES ('', '$barang_id', '$barang_id', '$qty_view', '$qty', '$konversi_isi', '$satuan','$harga_beli', '$harga', '$harga_parent', '$harga_edit', '$id_kasir', '$penjualan_inv' , '$penjualan_dt', '$date_ym', '$qty_view', '$qty_view', '$option_sn', '$sn_id', '$sn', '$customer_cat', '$cabang')";
			$query2 = "INSERT INTO terlaris VALUES ('', '$barang_id', '$qty')";

			$result_penjualan = mysqli_query($conn, $query);
			if (!$result_penjualan) {
				$error_msg = mysqli_error($conn);
				error_log("Error inserting penjualan: " . $error_msg);
				error_log("Query: " . $query);
				return 0;
			}
			
			$result_terlaris = mysqli_query($conn, $query2);
			if (!$result_terlaris) {
				$error_msg = mysqli_error($conn);
				error_log("Error inserting terlaris: " . $error_msg);
			}
			
			// NOTE:
			// Stok barang TIDAK di-update di sini.
			// Di beberapa instalasi, stok sudah di-handle otomatis oleh DB (mis. trigger saat INSERT penjualan).
			// Jika kita update stok lagi di PHP, stok akan terpotong 2x (contoh: 240 -> -240).
			
			// Update status barang_sn jika menggunakan SN
			if ($keranjang_barang_option_sn[$x] > 0 && !empty($keranjang_barang_sn_id[$x])) {
				$barang_sn_id = $keranjang_barang_sn_id[$x];
				$query_update_sn = "UPDATE barang_sn SET barang_sn_status = 2 WHERE barang_sn_id = $barang_sn_id";
				mysqli_query($conn, $query_update_sn);
			}
		}


		mysqli_query($conn, "DELETE FROM keranjang WHERE keranjang_id_kasir = $kik");
		
		// Update saldo Piutang Dagang (1-1300) jika transaksi piutang
		if ($invoice_piutang == 1) {
			// Total piutang adalah total invoice_sub_total
			$total_piutang = $invoice_sub_total;
			
			// Cek apakah kolom cabang ada di table laba_kategori
			$check_cabang_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
			$cabang_column_result = mysqli_query($conn, $check_cabang_column);
			$cabang_column_exists = ($cabang_column_result && mysqli_num_rows($cabang_column_result) > 0);
			
			// Query untuk mencari akun Piutang Dagang dengan kode_akun = '1-1300'
			if ($cabang_column_exists) {
				// Cari akun dengan kode_akun 1-1300 untuk cabang ini atau cabang 0 (default)
				$query_piutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '1-1300' AND (cabang = $invoice_cabang OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
			} else {
				$query_piutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '1-1300' LIMIT 1";
			}
			
			$result_piutang = mysqli_query($conn, $query_piutang);
			
			if ($result_piutang && mysqli_num_rows($result_piutang) > 0) {
				// Akun Piutang Dagang sudah ada, update saldo
				$row_piutang = mysqli_fetch_assoc($result_piutang);
				$saldo_piutang_sekarang = floatval($row_piutang['saldo']);
				$saldo_piutang_baru = $saldo_piutang_sekarang + $total_piutang;
				
				// Update saldo
				$update_piutang_query = "UPDATE laba_kategori SET saldo = $saldo_piutang_baru WHERE id = " . intval($row_piutang['id']);
				mysqli_query($conn, $update_piutang_query);
			} else {
				// Akun Piutang Dagang belum ada, buat baru
				// Cari kategori 'aktiva' untuk menentukan kategori yang tepat
				$query_kategori = "SELECT kategori, tipe_akun FROM laba_kategori WHERE kategori = 'aktiva' LIMIT 1";
				$result_kategori = mysqli_query($conn, $query_kategori);
				
				$kategori_piutang = 'aktiva';
				$tipe_akun_piutang = 'debit';
				
				if ($result_kategori && mysqli_num_rows($result_kategori) > 0) {
					$row_kategori = mysqli_fetch_assoc($result_kategori);
					$tipe_akun_piutang = $row_kategori['tipe_akun'] ?? 'debit';
				}
				
				// Insert akun Piutang Dagang baru
				if ($cabang_column_exists) {
					$insert_piutang_query = "INSERT INTO laba_kategori (name, kode_akun, kategori, tipe_akun, saldo, cabang) VALUES ('Piutang Dagang', '1-1300', '$kategori_piutang', '$tipe_akun_piutang', $total_piutang, $invoice_cabang)";
				} else {
					$insert_piutang_query = "INSERT INTO laba_kategori (name, kode_akun, kategori, tipe_akun, saldo) VALUES ('Piutang Dagang', '1-1300', '$kategori_piutang', '$tipe_akun_piutang', $total_piutang)";
				}
				
				mysqli_query($conn, $insert_piutang_query);
			}
		}
		
		// Update saldo laba_kategori jika pembayaran Transfer
		if ($invoice_tipe_transaksi == 1) { // 1 = Transfer
			// Query untuk mendapatkan saldo saat ini dengan kategori_name = 2 dan cabang = 0
			$query_saldo = "SELECT saldo, id FROM laba_kategori WHERE kategori_name = '2' AND cabang = 0 LIMIT 1";
			
			$result_saldo = mysqli_query($conn, $query_saldo);
			if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
				// Data sudah ada, update saldo
				$row_saldo = mysqli_fetch_assoc($result_saldo);
				$saldo_sekarang = floatval($row_saldo['saldo']);
				$saldo_baru = $saldo_sekarang + $invoice_sub_total;
				
				// Update saldo dengan kategori_name = 2 dan cabang = 0
				$update_saldo_query = "UPDATE laba_kategori SET saldo = $saldo_baru WHERE kategori_name = '2' AND cabang = 0 LIMIT 1";
				mysqli_query($conn, $update_saldo_query);
			} else {
				// Data belum ada, insert baru
				$saldo_baru = $invoice_sub_total;
				$insert_saldo_query = "INSERT INTO laba_kategori (kategori_name, cabang, saldo) VALUES ('2', 0, $saldo_baru)";
				mysqli_query($conn, $insert_saldo_query);
			}
		}
		
		// Update saldo laba_kategori jika pembayaran cash
		if ($invoice_tipe_transaksi == 0) { // 0 = Cash
			// Cek apakah kolom cabang ada di table laba_kategori
			$check_cabang_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
			$cabang_column_result = mysqli_query($conn, $check_cabang_column);
			$cabang_column_exists = ($cabang_column_result && mysqli_num_rows($cabang_column_result) > 0);
			
			// Cek apakah kolom kategori_name ada di table laba_kategori
			$check_kategori_name_column = "SHOW COLUMNS FROM laba_kategori LIKE 'kategori_name'";
			$kategori_name_column_result = mysqli_query($conn, $check_kategori_name_column);
			$kategori_name_column_exists = ($kategori_name_column_result && mysqli_num_rows($kategori_name_column_result) > 0);
			
			// Query untuk mendapatkan saldo saat ini dengan kategori_name = 1 atau kategori = '1' dan cabang = cabang toko
			if ($kategori_name_column_exists) {
				if ($cabang_column_exists) {
					$query_saldo = "SELECT saldo, id FROM laba_kategori WHERE kategori_name = '1' AND cabang = $invoice_cabang LIMIT 1";
				} else {
					$query_saldo = "SELECT saldo, id FROM laba_kategori WHERE kategori_name = '1' LIMIT 1";
				}
			} else {
				if ($cabang_column_exists) {
					$query_saldo = "SELECT saldo, id FROM laba_kategori WHERE kategori = '1' AND cabang = $invoice_cabang LIMIT 1";
				} else {
					$query_saldo = "SELECT saldo, id FROM laba_kategori WHERE kategori = '1' LIMIT 1";
				}
			}
			
			$result_saldo = mysqli_query($conn, $query_saldo);
			if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
				// Data sudah ada, update saldo
				$row_saldo = mysqli_fetch_assoc($result_saldo);
				$saldo_sekarang = floatval($row_saldo['saldo']);
				$saldo_baru = $saldo_sekarang + $invoice_sub_total;
				
				// Update saldo dengan kategori_name = 1 atau kategori = '1' dan cabang = cabang toko
				if ($kategori_name_column_exists) {
					if ($cabang_column_exists) {
						$update_saldo_query = "UPDATE laba_kategori SET saldo = $saldo_baru WHERE kategori_name = '1' AND cabang = $invoice_cabang LIMIT 1";
					} else {
						$update_saldo_query = "UPDATE laba_kategori SET saldo = $saldo_baru WHERE kategori_name = '1' LIMIT 1";
					}
				} else {
					if ($cabang_column_exists) {
						$update_saldo_query = "UPDATE laba_kategori SET saldo = $saldo_baru WHERE kategori = '1' AND cabang = $invoice_cabang LIMIT 1";
					} else {
						$update_saldo_query = "UPDATE laba_kategori SET saldo = $saldo_baru WHERE kategori = '1' LIMIT 1";
					}
				}
				
				mysqli_query($conn, $update_saldo_query);
			} else {
				// Data belum ada, insert baru
				$saldo_baru = $invoice_sub_total;
				
				if ($kategori_name_column_exists) {
					if ($cabang_column_exists) {
						$insert_saldo_query = "INSERT INTO laba_kategori (kategori_name, cabang, saldo) VALUES ('1', $invoice_cabang, $saldo_baru)";
					} else {
						$insert_saldo_query = "INSERT INTO laba_kategori (kategori_name, saldo) VALUES ('1', $saldo_baru)";
					}
				} else {
					if ($cabang_column_exists) {
						$insert_saldo_query = "INSERT INTO laba_kategori (kategori, cabang, saldo) VALUES ('1', $invoice_cabang, $saldo_baru)";
					} else {
						$insert_saldo_query = "INSERT INTO laba_kategori (kategori, saldo) VALUES ('1', $saldo_baru)";
					}
				}
				
				mysqli_query($conn, $insert_saldo_query);
			}
		}
		
		// Return 1 jika berhasil (karena invoice sudah di-insert)
		return 1;
	}
	return 0;
}

function updateStockDraft($data)
{
	global $conn;
	$id                  		= $data['barang_ids'];
	$keranjang_qty       		= $data['keranjang_qty'];
	$keranjang_qty_view       	= $data['keranjang_qty_view'];
	$keranjang_konversi_isi     = $data['keranjang_konversi_isi'];
	$keranjang_satuan           = $data['keranjang_satuan'];
	$keranjang_harga_beli       = $data['keranjang_harga_beli'];
	$keranjang_harga			= $data['keranjang_harga'];
	$keranjang_harga_parent		= $data['keranjang_harga_parent'];
	$keranjang_harga_edit		= $data['keranjang_harga_edit'];
	$keranjang_id_kasir  		= $data['keranjang_id_kasir'];
	$penjualan_invoice   		= $data['penjualan_invoice'];
	$keranjang_barang_option_sn = $data['keranjang_barang_option_sn'];
	$keranjang_barang_sn_id     = $data['keranjang_barang_sn_id'];
	$keranjang_sn               = $data['keranjang_sn'];
	$invoice_customer_category2 = $data['invoice_customer_category2'];
	$keranjang_nama 			= $data['keranjang_nama'];
	$barang_kode_slug 			= $data['barang_kode_slug'];
	$keranjang_id_cek 			= $data['keranjang_id_cek'];
	$penjualan_cabang        	= $data['penjualan_cabang'];

	$kik                 		= $data['kik'];
	$penjualan_invoice2  		= $data['penjualan_invoice2'];
	$invoice_tgl         		= date("d F Y g:i:s a");
	$invoice_total_beli       	= $data['invoice_total_beli'];
	$invoice_total       		= $data['invoice_total'];
	$invoice_ongkir      		= htmlspecialchars($data['invoice_ongkir']);
	$invoice_diskon      		= htmlspecialchars($data['invoice_diskon']);

	$invoice_sub_total   		= $invoice_total + $invoice_ongkir;
	$invoice_sub_total   		= $invoice_sub_total - $invoice_diskon;
	$invoice_bayar       		= htmlspecialchars($data['angka1']);


	$invoice_kembali     		= $invoice_bayar - $invoice_sub_total;
	$invoice_date        		= date("Y-m-d");
	$invoice_date_year_month    = date("Y-m");
	$penjualan_date      		= $data['penjualan_date'];
	$invoice_customer    		= $data['invoice_customer'];
	$invoice_customer_category  = $data['invoice_customer_category'];
	$invoice_kurir    	 		= $data['invoice_kurir'];
	$invoice_tipe_transaksi  	= $data['invoice_tipe_transaksi'];
	$penjualan_invoice_count 	= $data['penjualan_invoice_count'];
	$invoice_piutang			= $data['invoice_piutang'];
	if ($invoice_piutang == 1) {
		$invoice_piutang_dp = $invoice_bayar;
	} else {
		$invoice_piutang_dp = 0;
	}
	$invoice_piutang_jatuh_tempo = $data['invoice_piutang_jatuh_tempo'];
	$invoice_piutang_lunas		= $data['invoice_piutang_lunas'];
	$invoice_cabang             = $data['invoice_cabang'];


	if ($invoice_customer == 1) {
		$invoice_marketplace = htmlspecialchars($data['invoice_marketplace']);
		$invoice_ekspedisi   = htmlspecialchars($data['invoice_ekspedisi']);
		$invoice_no_resi     = htmlspecialchars($data['invoice_no_resi']);
	} else {
		$invoice_marketplace = "";
		$invoice_ekspedisi   = 0;
		$invoice_no_resi     = "-";
	}
	$jumlah = count($keranjang_id_kasir);


	// query insert invoice
	$query1 = "INSERT INTO invoice VALUES ('', '$penjualan_invoice2', '$penjualan_invoice_count', '$invoice_tgl', '$invoice_customer', '$invoice_customer_category', '$invoice_kurir', '1', '$invoice_tipe_transaksi', '$invoice_total_beli', '$invoice_total', '$invoice_ongkir', '$invoice_diskon', '$invoice_sub_total', '$invoice_bayar', '$invoice_kembali', '$kik', '$invoice_date', '$invoice_date_year_month', ' ', ' ', '$invoice_total_beli', '$invoice_total', '$invoice_ongkir', '$invoice_sub_total', '$invoice_bayar', '$invoice_kembali', '$invoice_marketplace', '$invoice_ekspedisi', '$invoice_no_resi', '-', '$invoice_piutang', '$invoice_piutang_dp', '$invoice_piutang_jatuh_tempo', '$invoice_piutang_lunas', 1, '$invoice_cabang')";
	// var_dump($query1); die();
	mysqli_query($conn, $query1);

	for ($x = 0; $x < $jumlah; $x++) {

		$query = "INSERT INTO keranjang_draft VALUES ('', '$keranjang_nama[$x]', '$keranjang_harga_beli[$x]', '$keranjang_harga[$x]', '$keranjang_harga_parent[$x]', '$keranjang_harga_edit[$x]', '$keranjang_satuan[$x]', '$id[$x]', '$barang_kode_slug[$x]', '$keranjang_qty[$x]', '$keranjang_qty_view[$x]', '$keranjang_konversi_isi[$x]', '$keranjang_barang_sn_id[$x]', '$keranjang_barang_option_sn[$x]', '$keranjang_sn[$x]', '$keranjang_id_kasir[$x]', '$keranjang_id_cek[$x]', '$invoice_customer_category2[$x]', 1, '$penjualan_invoice[$x]', '$penjualan_cabang[$x]')";
		mysqli_query($conn, $query);
	}


	mysqli_query($conn, "DELETE FROM keranjang WHERE keranjang_id_kasir = $kik");
	return mysqli_affected_rows($conn);
}


function updateStockSaveDraft($data)
{
	global $conn;
	$id                  		= $data['barang_ids'];
	$keranjang_qty       		= $data['keranjang_qty'];
	$keranjang_qty_view       	= $data['keranjang_qty_view'];
	$keranjang_konversi_isi     = $data['keranjang_konversi_isi'];
	$keranjang_satuan           = $data['keranjang_satuan'];
	$keranjang_harga_beli       = $data['keranjang_harga_beli'];
	$keranjang_harga			= $data['keranjang_harga'];
	$keranjang_harga_parent		= $data['keranjang_harga_parent'];
	$keranjang_harga_edit		= $data['keranjang_harga_edit'];
	$keranjang_id_kasir  		= $data['keranjang_id_kasir'];
	$penjualan_invoice   		= $data['penjualan_invoice'];
	$keranjang_barang_option_sn = $data['keranjang_barang_option_sn'];
	$keranjang_barang_sn_id     = $data['keranjang_barang_sn_id'];
	$keranjang_sn               = $data['keranjang_sn'];
	$invoice_customer_category2 = $data['invoice_customer_category2'];
	$penjualan_cabang        	= $data['penjualan_cabang'];

	$invoice_id 				= $data['invoice_id'];
	$kik                 		= $data['kik'];
	$penjualan_invoice2  		= $data['penjualan_invoice2'];
	$invoice_tgl         		= date("d F Y g:i:s a");
	$invoice_total_beli       	= $data['invoice_total_beli'];
	$invoice_total       		= $data['invoice_total'];
	$invoice_ongkir      		= htmlspecialchars($data['invoice_ongkir']);
	$invoice_diskon      		= htmlspecialchars($data['invoice_diskon']);

	$invoice_sub_total   		= $invoice_total + $invoice_ongkir;
	$invoice_sub_total   		= $invoice_sub_total - $invoice_diskon;
	$invoice_bayar       		= htmlspecialchars($data['angka1']);


	$invoice_kembali     		= $invoice_bayar - $invoice_sub_total;
	$invoice_date        		= date("Y-m-d");
	$invoice_date_year_month    = date("Y-m");
	$penjualan_date      		= $data['penjualan_date'];
	$invoice_customer    		= $data['invoice_customer'];
	$invoice_customer_category  = $data['invoice_customer_category'];
	$invoice_kurir    	 		= $data['invoice_kurir'];
	$invoice_tipe_transaksi  	= $data['invoice_tipe_transaksi'];
	$penjualan_invoice_count 	= $data['penjualan_invoice_count'];
	$invoice_piutang			= $data['invoice_piutang'];
	if ($invoice_piutang == 1) {
		$invoice_piutang_dp = $invoice_bayar;
	} else {
		$invoice_piutang_dp = 0;
	}
	$invoice_piutang_jatuh_tempo = $data['invoice_piutang_jatuh_tempo'];
	$invoice_piutang_lunas		= $data['invoice_piutang_lunas'];
	$invoice_cabang             = $data['invoice_cabang'];


	if ($invoice_customer == 1) {
		$invoice_marketplace = htmlspecialchars($data['invoice_marketplace']);
		$invoice_ekspedisi   = htmlspecialchars($data['invoice_ekspedisi']);
		$invoice_no_resi     = htmlspecialchars($data['invoice_no_resi']);
	} else {
		$invoice_marketplace = "";
		$invoice_ekspedisi   = 0;
		$invoice_no_resi     = "-";
	}
	$jumlah = count($keranjang_id_kasir);


	if ($invoice_bayar == null) {
		echo "
			<script>
				alert('Anda Belum Input Nominal BAYAR !!!');
				document.location.href = '';
			</script>
		";
	} else {
		// query Update invoice
		$query1 = "UPDATE invoice SET  
				invoice_tgl 				= '$invoice_tgl', 
				invoice_customer 			= '$invoice_customer', 
				invoice_customer_category 	= '$invoice_customer_category', 
				invoice_tipe_transaksi 		= '$invoice_tipe_transaksi', 
				invoice_total_beli 			= '$invoice_total_beli', 
				invoice_total 				= '$invoice_total', 
				invoice_ongkir 				= '$invoice_ongkir', 
				invoice_diskon 				= '$invoice_diskon', 
				invoice_sub_total 			= '$invoice_sub_total', 
				invoice_bayar 				= '$invoice_bayar', 
				invoice_kembali 			= '$invoice_kembali', 
				invoice_kasir 				= '$kik', 
				invoice_date 				= '$invoice_date', 
				invoice_date_year_month 	= '$invoice_date_year_month', 
				invoice_total_beli_lama 	= '$invoice_total_beli', 
				invoice_total_lama 			= '$invoice_total', 
				invoice_ongkir_lama 		= '$invoice_ongkir', 
				invoice_sub_total_lama 		= '$invoice_sub_total', 
				invoice_bayar_lama 			= '$invoice_bayar', 
				invoice_kembali_lama 		= '$invoice_kembali',  
				invoice_piutang 			= '$invoice_piutang', 
				invoice_piutang_dp 			= '$invoice_piutang_dp', 
				invoice_piutang_jatuh_tempo = '$invoice_piutang_jatuh_tempo', 
				invoice_piutang_lunas 		= '$invoice_piutang_lunas', 
				invoice_draft 				= 0, 
				invoice_cabang 				= '$invoice_cabang'
				WHERE invoice_id 			= $invoice_id
		";
		// var_dump($query1); die();
		mysqli_query($conn, $query1);

		for ($x = 0; $x < $jumlah; $x++) {
			$query = "INSERT INTO penjualan VALUES ('', '$id[$x]', '$id[$x]', '$keranjang_qty_view[$x]', '$keranjang_qty[$x]', '$keranjang_konversi_isi[$x]', '$keranjang_satuan[$x]','$keranjang_harga_beli[$x]', '$keranjang_harga[$x]', '$keranjang_harga_parent[$x]', '$keranjang_harga_edit[$x]', '$keranjang_id_kasir[$x]', '$penjualan_invoice[$x]' , '$penjualan_date[$x]', '$invoice_date_year_month', '$keranjang_qty_view[$x]', '$keranjang_qty_view[$x]', '$keranjang_barang_option_sn[$x]', '$keranjang_barang_sn_id[$x]', '$keranjang_sn[$x]', '$invoice_customer_category2[$x]', '$penjualan_cabang[$x]')";
			$query2 = "INSERT INTO terlaris VALUES ('', '$id[$x]', '$keranjang_qty[$x]')";
			// var_dump($query); die();
			mysqli_query($conn, $query);
			mysqli_query($conn, $query2);
		}


		mysqli_query($conn, "DELETE FROM keranjang_draft WHERE keranjang_invoice = $penjualan_invoice2 && keranjang_cabang = $invoice_cabang ");
		return mysqli_affected_rows($conn);
	}
}

function hapusDraft($invoice, $cabang)
{
	global $conn;

	$countDraft = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keranjang_draft WHERE keranjang_invoice = $invoice && keranjang_cabang = $cabang"));
	// var_dump($countDraft); die();
	if ($countDraft > 0) {
		mysqli_query($conn, "DELETE FROM invoice WHERE penjualan_invoice = $invoice && invoice_cabang = $cabang");

		mysqli_query($conn, "DELETE FROM keranjang_draft WHERE keranjang_invoice = $invoice && keranjang_cabang = $cabang");
		return mysqli_affected_rows($conn);
	} else {
		mysqli_query($conn, "DELETE FROM invoice WHERE penjualan_invoice = $invoice && invoice_cabang = $cabang");
		return mysqli_affected_rows($conn);
	}
}

// =========================================== CUSTOMER ====================================== //

function tambahCustomer($data)
{
    global $conn;
    // Ambil data dari form dan amankan
    $customer_nama     = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_nama"]));
    $customer_kartu    = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_kartu"]));
    $customer_tlpn     = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_tlpn"]));
    $customer_email    = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_email"]));
    $customer_alamat   = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_alamat"]));
    $customer_create   = date("Y-m-d H:i:s");
    $customer_status   = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_status"]));
    $customer_category = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_category"]));
    $customer_cabang   = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_cabang"]));

    // Cek apakah nomor telepon sudah ada
    $check_query = "SELECT * FROM customer WHERE customer_tlpn = '$customer_tlpn'";
    $result = mysqli_query($conn, $check_query);

    if (!$result) {
        echo "Error in query: " . mysqli_error($conn);
        return false;
    }

    $customer_tlpn_cek = mysqli_num_rows($result);

    if ($customer_tlpn_cek > 0) {
        echo "
            <script>
                alert('Customer dengan nomor telepon ini sudah terdaftar!');
            </script>
        ";
        return 0;
    }

    // Check if new columns exist
    $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM customer LIKE 'alamat_provinsi'");
    $hasNewColumns = mysqli_num_rows($checkColumn) > 0;

    if ($hasNewColumns) {
        // New address fields
        $alamat_dusun          = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_dusun"] ?? ''));
        $alamat_desa           = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_desa"] ?? ''));
        $alamat_kecamatan      = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kecamatan"] ?? ''));
        $alamat_kabupaten      = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kabupaten"] ?? ''));
        $alamat_provinsi       = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_provinsi"] ?? ''));
        $alamat_kode_provinsi  = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_provinsi"] ?? ''));
        $alamat_kode_kabupaten = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_kabupaten"] ?? ''));
        $alamat_kode_kecamatan = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_kecamatan"] ?? ''));
        $alamat_kode_desa      = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_desa"] ?? ''));
        $customer_birthday     = !empty($data["customer_birthday"]) ? mysqli_real_escape_string($conn, $data["customer_birthday"]) : null;
        $birthdayValue = $customer_birthday ? "'$customer_birthday'" : "NULL";

        // Query dengan kolom baru
        $query = "INSERT INTO customer 
                  (customer_nama, customer_kartu, customer_tlpn, customer_email, customer_alamat, customer_create, customer_status, customer_category, customer_cabang, alamat_dusun, alamat_desa, alamat_kecamatan, alamat_kabupaten, alamat_provinsi, alamat_kode_provinsi, alamat_kode_kabupaten, alamat_kode_kecamatan, alamat_kode_desa, customer_birthday) 
                  VALUES 
                  ('$customer_nama', '$customer_kartu', '$customer_tlpn', '$customer_email', '$customer_alamat', '$customer_create', '$customer_status', '$customer_category', '$customer_cabang', '$alamat_dusun', '$alamat_desa', '$alamat_kecamatan', '$alamat_kabupaten', '$alamat_provinsi', '$alamat_kode_provinsi', '$alamat_kode_kabupaten', '$alamat_kode_kecamatan', '$alamat_kode_desa', $birthdayValue)";
    } else {
        // Query tanpa kolom baru (backwards compatible)
        $query = "INSERT INTO customer 
                  (customer_nama, customer_kartu, customer_tlpn, customer_email, customer_alamat, customer_create, customer_status, customer_category, customer_cabang) 
                  VALUES 
                  ('$customer_nama', '$customer_kartu', '$customer_tlpn', '$customer_email', '$customer_alamat', '$customer_create', '$customer_status', '$customer_category', '$customer_cabang')";
    }

    if (!mysqli_query($conn, $query)) {
        echo "<script>console.error('SQL Error: " . addslashes(mysqli_error($conn)) . "');</script>";
        return false;
    }

    return mysqli_affected_rows($conn);
}


function editCustomer($data)
{
	global $conn;
	$id = intval($data["customer_id"]);

	// ambil data dari tiap elemen dalam form
	$customer_nama     = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_nama"]));
	$customer_kartu    = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_kartu"]));
	$customer_tlpn     = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_tlpn"]));
	$customer_email    = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_email"]));
	$customer_alamat   = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_alamat"]));
	$customer_status   = mysqli_real_escape_string($conn, htmlspecialchars($data["customer_status"]));
	$customer_category = mysqli_real_escape_string($conn, $data["customer_category"]);

	// Check if new columns exist
	$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM customer LIKE 'alamat_provinsi'");
	$hasNewColumns = mysqli_num_rows($checkColumn) > 0;

	if ($hasNewColumns) {
		// New address fields
		$alamat_dusun          = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_dusun"] ?? ''));
		$alamat_desa           = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_desa"] ?? ''));
		$alamat_kecamatan      = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kecamatan"] ?? ''));
		$alamat_kabupaten      = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kabupaten"] ?? ''));
		$alamat_provinsi       = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_provinsi"] ?? ''));
		$alamat_kode_provinsi  = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_provinsi"] ?? ''));
		$alamat_kode_kabupaten = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_kabupaten"] ?? ''));
		$alamat_kode_kecamatan = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_kecamatan"] ?? ''));
		$alamat_kode_desa      = mysqli_real_escape_string($conn, htmlspecialchars($data["alamat_kode_desa"] ?? ''));
		$customer_birthday     = !empty($data["customer_birthday"]) ? mysqli_real_escape_string($conn, $data["customer_birthday"]) : null;
		$birthdayValue = $customer_birthday ? "customer_birthday = '$customer_birthday'," : "customer_birthday = NULL,";

		// Query dengan kolom baru
		$query = "UPDATE customer SET 
							customer_nama     = '$customer_nama',
							customer_kartu    = '$customer_kartu',
							customer_tlpn     = '$customer_tlpn',
							customer_email    = '$customer_email',
							customer_alamat   = '$customer_alamat',
							customer_status   = '$customer_status',
							customer_category = '$customer_category',
							alamat_dusun      = '$alamat_dusun',
							alamat_desa       = '$alamat_desa',
							alamat_kecamatan  = '$alamat_kecamatan',
							alamat_kabupaten  = '$alamat_kabupaten',
							alamat_provinsi   = '$alamat_provinsi',
							alamat_kode_provinsi  = '$alamat_kode_provinsi',
							alamat_kode_kabupaten = '$alamat_kode_kabupaten',
							alamat_kode_kecamatan = '$alamat_kode_kecamatan',
							alamat_kode_desa  = '$alamat_kode_desa',
							$birthdayValue
							customer_id = customer_id
							WHERE customer_id = $id";
	} else {
		// Query tanpa kolom baru (backwards compatible)
		$query = "UPDATE customer SET 
							customer_nama     = '$customer_nama',
							customer_kartu    = '$customer_kartu',
							customer_tlpn     = '$customer_tlpn',
							customer_email    = '$customer_email',
							customer_alamat   = '$customer_alamat',
							customer_status   = '$customer_status',
							customer_category = '$customer_category'
							WHERE customer_id = $id";
	}

	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}


function hapusCustomer($id)
{
	global $conn;
	mysqli_query($conn, "DELETE FROM customer WHERE customer_id = $id");

	return mysqli_affected_rows($conn);
}


// =========================================== Panjualan ===================================== //
function hapusPenjualan($id)
{
	global $conn;

	mysqli_query($conn, "DELETE FROM penjualan WHERE penjualan_id = $id");

	return mysqli_affected_rows($conn);
}

function hapusPenjualanInvoice($id)
{
	global $conn;

	// Mencari Invoive Penjualan dan cabang
	$invoiceTbl = mysqli_query($conn, "select penjualan_invoice, invoice_cabang from invoice where invoice_id = '" . $id . "'");

	$ivc = mysqli_fetch_array($invoiceTbl);
	$penjualan_invoice  = $ivc["penjualan_invoice"];
	$invoice_cabang  	= $ivc["invoice_cabang"];


	// Mencari banyak barang SN
	$barang_option_sn = mysqli_query($conn, "select barang_option_sn from penjualan where penjualan_invoice = '" . $penjualan_invoice . "' && barang_option_sn > 0 && penjualan_cabang = '" . $invoice_cabang . "' ");
	$barang_option_sn = mysqli_num_rows($barang_option_sn);

	// Menghitung data di tabel piutang sesuai No. Invoice
	$piutang = mysqli_query($conn, "select * from piutang where piutang_invoice = '" . $penjualan_invoice . "' && piutang_cabang = '" . $invoice_cabang . "' ");
	$jmlPiutang = mysqli_num_rows($piutang);


	// Mencari ID SN
	if ($barang_option_sn > 0) {
		$barang_sn_id = query("SELECT * FROM penjualan WHERE penjualan_invoice = $penjualan_invoice && barang_option_sn > 0 && penjualan_cabang = $invoice_cabang ");

		foreach ($barang_sn_id as $row) :
			$barang_sn_id = $row['barang_sn_id'];

			$barang = count($barang_sn_id);
			for ($i = 0; $i < $barang; $i++) {
				$query = "UPDATE barang_sn SET 
						barang_sn_status     = 3
						WHERE barang_sn_id = $barang_sn_id
				";
			}
			mysqli_query($conn, $query);
		endforeach;
	}

	// Kondisi Hapus jika terdapat cicilan di tabel Piutang
	if ($jmlPiutang > 0) {
		mysqli_query($conn, "DELETE FROM piutang WHERE piutang_invoice = $penjualan_invoice && piutang_cabang = $invoice_cabang ");

		mysqli_query($conn, "DELETE FROM penjualan WHERE penjualan_invoice = $penjualan_invoice && penjualan_cabang = $invoice_cabang ");

		mysqli_query($conn, "DELETE FROM invoice WHERE invoice_id = $id");
	} else {
		// Kondisi Hapus jika Tanpa cicilan di tabel Piutang
		mysqli_query($conn, "DELETE FROM penjualan WHERE penjualan_invoice = $penjualan_invoice && penjualan_cabang = $invoice_cabang ");

		mysqli_query($conn, "DELETE FROM invoice WHERE invoice_id = $id");
	}



	return mysqli_affected_rows($conn);
}

function updateQTY2($data)
{
	global $conn;
	$id = $data["penjualan_id"];
	$bid = $data["barang_id"];

	// ambil data dari tiap elemen dalam form
	$barang_qty      			= htmlspecialchars($data['barang_qty']);
	$barang_qty_lama 			= $data['barang_qty_lama'];
	$barang_terjual  			= $data['barang_terjual'];
	$barang_qty_konversi_isi 	= $data['barang_qty_konversi_isi'];

	// Edit No SN Jika Produk Menggunakan SN
	$barang_option_sn 			= $data['barang_option_sn'];
	$barang_sn_id     			= $data['barang_sn_id'];

	// retur
	$barang_stock           	= $data['barang_stock'];
	$barang_stock_kurang    	= $barang_qty_lama - $barang_qty;
	$barang_stock_kurang       *= $barang_qty_konversi_isi;

	$barang_stock_hasil     	= $barang_stock + $barang_stock_kurang;
	$barang_terjual         	= $barang_terjual - $barang_stock_kurang;
	// var_dump($barang_stock_hasil); die();

	if ($barang_qty > $barang_qty_lama) {
		echo "
			<script>
				alert('Jika Anda Ingin Menambahkan QTY Barang.. Lakukan Transaksi Invoice Baru !!!');
			</script>
		";
	} else {
		// query update data

		$query = "UPDATE penjualan SET 
					barang_qty       = '$barang_qty'
					WHERE penjualan_id = $id
					";
		$query1 = "UPDATE barang SET 
					barang_stock   = '$barang_stock_hasil',
					barang_terjual = '$barang_terjual'
					WHERE barang_id = $bid
					";
		if ($barang_option_sn > 0) {
			$query2 = "UPDATE barang_sn SET 
					barang_sn_status = 2
					WHERE barang_sn_id = $barang_sn_id
				";
			mysqli_query($conn, $query2);
		}

		mysqli_query($conn, $query);
		mysqli_query($conn, $query1);

		return mysqli_affected_rows($conn);
		// $query1 = "INSERT INTO retur VALUES ('', '$retur_barang_id', '$retur_invoice', '$retur_admin_id', '$retur_date', ' ', '$barang_stock')";
		// mysqli_query($conn, $query1);

	}
}

function updateInvoice($data)
{
	global $conn;
	$id = $data["invoice_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_total_beli   = htmlspecialchars($data['invoice_total_beli']);
	$invoice_total        = htmlspecialchars($data['invoice_total']);
	$invoice_ongkir       = $data['invoice_ongkir'];
	$invoice_sub_total    = $data['invoice_sub_total'];
	$invoice_bayar        = htmlspecialchars($data['angka1']);
	$invoice_kembali      = $invoice_bayar - $invoice_sub_total;
	$invoice_kasir_edit   = $data['invoice_kasir_edit'];
	$invoice_date_edit    = date('Y-m-d');

	// query update data
	$query = "UPDATE invoice SET 
					invoice_total_beli = '$invoice_total_beli',
					invoice_total      = '$invoice_total',
					invoice_ongkir     = '$invoice_ongkir',
					invoice_sub_total  = '$invoice_sub_total',
					invoice_bayar      = '$invoice_bayar',
					invoice_kembali    = '$invoice_kembali',
					invoice_date_edit  = '$invoice_date_edit',
					invoice_kasir_edit = '$invoice_kasir_edit'
					WHERE invoice_id = $id
					";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

function editInvoiceEkspedisi($data)
{
	global $conn;
	$id = $data["invoice_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_marketplace        = htmlspecialchars($data['invoice_marketplace']);
	$invoice_ekspedisi          = htmlspecialchars($data['invoice_ekspedisi']);
	$invoice_no_resi            = htmlspecialchars($data['invoice_no_resi']);
	$invoice_total              = $data['invoice_total'];
	$invoice_ongkir             = htmlspecialchars($data['invoice_ongkir']);
	$invoice_sub_total          = $invoice_total + $invoice_ongkir;
	$invoice_bayar              = $data['invoice_bayar'];
	$invoice_kembali            = $invoice_bayar - $invoice_sub_total;

	// query update data
	$query = "UPDATE invoice SET 
					invoice_total          = '$invoice_total',
					invoice_ongkir         = '$invoice_ongkir',
					invoice_sub_total      = '$invoice_sub_total',
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali',
					invoice_marketplace    = '$invoice_marketplace',
					invoice_ekspedisi      = '$invoice_ekspedisi',
					invoice_no_resi        = '$invoice_no_resi'
					WHERE invoice_id = $id
					";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

function editInvoiceKurir($data)
{
	global $conn;
	$id = $data["invoice_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_total              = $data['invoice_total'];
	$invoice_ongkir             = htmlspecialchars($data['invoice_ongkir']);
	$invoice_sub_total          = $invoice_total + $invoice_ongkir;
	$invoice_bayar              = $data['invoice_bayar'];
	$invoice_kembali            = $invoice_bayar - $invoice_sub_total;
	$invoice_kurir              = htmlspecialchars($data['invoice_kurir']);
	$invoice_status_kurir       = htmlspecialchars($data['invoice_status_kurir']);

	// query update data
	$query = "UPDATE invoice SET 
					invoice_kurir 		   = '$invoice_kurir',
					invoice_status_kurir   = '$invoice_status_kurir',
					invoice_total          = '$invoice_total',
					invoice_ongkir         = '$invoice_ongkir',
					invoice_sub_total      = '$invoice_sub_total',
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali'
					WHERE invoice_id = $id
					";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

// ============================================ Supplier ====================================== // 
function tambahSupplier($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$supplier_nama      = htmlspecialchars($data["supplier_nama"]);
	$supplier_wa 		= htmlspecialchars($data["supplier_wa"]);
	$supplier_alamat    = htmlspecialchars($data["supplier_alamat"]);
	$supplier_company   = htmlspecialchars($data["supplier_company"]);
	$supplier_status    = htmlspecialchars($data["supplier_status"]);
	$supplier_create    = date("d F Y g:i:s a");
	$supplier_cabang    = htmlspecialchars($data["supplier_cabang"]);

	// Cek Email
	$supplier_wa_cek = mysqli_num_rows(mysqli_query($conn, "select * from supplier where supplier_wa = '$supplier_wa' "));

	if ($supplier_wa_cek > 0) {
		echo "
			<script>
				alert('No. WhatsApp Sudah Terdaftar');
			</script>
		";
	} else {
		// query insert data
		$query = "INSERT INTO supplier VALUES ('', '$supplier_nama', '$supplier_wa', '$supplier_alamat', '$supplier_company', '$supplier_status', '$supplier_create', '$supplier_cabang')";
		mysqli_query($conn, $query);

		return mysqli_affected_rows($conn);
	}
}

function editSupplier($data)
{
	global $conn;
	$id = $data["supplier_id"];


	// ambil data dari tiap elemen dalam form
	$supplier_nama      = htmlspecialchars($data["supplier_nama"]);
	$supplier_wa 		= htmlspecialchars($data["supplier_wa"]);
	$supplier_alamat    = htmlspecialchars($data["supplier_alamat"]);
	$supplier_company   = htmlspecialchars($data["supplier_company"]);
	$supplier_status    = htmlspecialchars($data["supplier_status"]);

	// query update data
	$query = "UPDATE supplier SET 
						supplier_nama      = '$supplier_nama',
						supplier_wa        = '$supplier_wa',
						supplier_alamat    = '$supplier_alamat',
						supplier_company   = '$supplier_company',
						supplier_status    = '$supplier_status'
						WHERE supplier_id  = $id
				";
	// var_dump($query); die();
	mysqli_query($conn, $query);

	return mysqli_affected_rows($conn);
}

function hapusSupplier($id)
{
	global $conn;
	mysqli_query($conn, "DELETE FROM supplier WHERE supplier_id = $id");

	return mysqli_affected_rows($conn);
}

// ===================================== Keranjang Pembelian =============================== //
function tambahKeranjangPembelian($barang_id, $keranjang_nama, $keranjang_harga, $keranjang_id_kasir, $keranjang_qty, $keranjang_cabang, $keranjang_id_cek)
{
	global $conn;

	// Cek STOCK
	$barang_id_cek = mysqli_num_rows(mysqli_query($conn, "select * from keranjang_pembelian where keranjang_id_cek = '$keranjang_id_cek' "));

	// Kondisi jika scan Barcode Tidak sesuai
	if ($barang_id != null) {
		if ($barang_id_cek > 0) {
			$keranjangParent = mysqli_query($conn, "select keranjang_qty from keranjang_pembelian where keranjang_id_cek = '" . $keranjang_id_cek . "'");
			$kp = mysqli_fetch_array($keranjangParent);
			$kp = $kp['keranjang_qty'];
			$kp += $keranjang_qty;

			$query = "UPDATE keranjang_pembelian SET 
							keranjang_qty   = '$kp'
							WHERE keranjang_id_cek = $keranjang_id_cek
							";
			mysqli_query($conn, $query);
			return mysqli_affected_rows($conn);
		} else {
			// query insert data
			$query = "INSERT INTO keranjang_pembelian VALUES ('', '$keranjang_nama', '$keranjang_harga', '$barang_id', '$keranjang_qty', '$keranjang_id_kasir', '$keranjang_id_cek', '$keranjang_cabang')";

			mysqli_query($conn, $query);

			return mysqli_affected_rows($conn);
		}
	} else {
		echo '
			<script>
				alert("Kode Produk Tidak ada di Data Master Barang dan Coba Cek Kembali !! ");
				document.location.href = "transaksi-pembelian";
			</script>
		';
	}
}

function tambahKeranjangPembelianBarcode($data)
{
	global $conn;
	$barang_kode 		= htmlspecialchars($data['inputbarcode']);
	$keranjang_id_kasir = $data['keranjang_id_kasir'];
	$keranjang_cabang   = $data['keranjang_cabang'];

	// Ambil Data Barang berdasarkan Kode Barang 
	$barang 	= mysqli_query($conn, "select barang_id, barang_nama from barang where barang_kode = '" . $barang_kode . "' && barang_cabang = '" . $keranjang_cabang . "' ");
	$br 		= mysqli_fetch_array($barang);

	$barang_id          = $br['barang_id'];
	$keranjang_nama     = $br['barang_nama'];
	$keranjang_harga    = 0;
	$keranjang_qty      = 1;
	$keranjang_id_cek   = $barang_id . $keranjang_id_kasir . $keranjang_cabang;

	// Cek STOCK
	$barang_id_cek = mysqli_num_rows(mysqli_query($conn, "select * from keranjang_pembelian where keranjang_id_cek = '$keranjang_id_cek' "));

	// Kondisi jika scan Barcode Tidak sesuai
	if ($barang_id != null) {
		if ($barang_id_cek > 0) {
			$keranjangParent = mysqli_query($conn, "select keranjang_qty from keranjang_pembelian where keranjang_id_cek = '" . $keranjang_id_cek . "'");
			$kp = mysqli_fetch_array($keranjangParent);
			$kp = $kp['keranjang_qty'];
			$kp += $keranjang_qty;

			$query = "UPDATE keranjang_pembelian SET 
							keranjang_qty   = '$kp'
							WHERE keranjang_id_cek = $keranjang_id_cek
							";
			mysqli_query($conn, $query);
			return mysqli_affected_rows($conn);
		} else {
			// query insert data
			$query = "INSERT INTO keranjang_pembelian VALUES ('', '$keranjang_nama', '$keranjang_harga', '$barang_id', '$keranjang_qty', '$keranjang_id_kasir', '$keranjang_id_cek', '$keranjang_cabang')";

			mysqli_query($conn, $query);

			return mysqli_affected_rows($conn);
		}
	} else {
		echo '
			<script>
				alert("Kode Produk Tidak ada di Data Master Barang dan Coba Cek Kembali !! ");
				document.location.href = "transaksi-pembelian";
			</script>
		';
	}
}

function hapusKeranjangPembelian($id)
{
	global $conn;

	mysqli_query($conn, "DELETE FROM keranjang_pembelian WHERE keranjang_id = $id");

	return mysqli_affected_rows($conn);
}

function updateQTYpembelian($data)
{
	global $conn;
	$id = $data["keranjang_id"];

	// ambil data dari tiap elemen dalam form
	$keranjang_qty = htmlspecialchars($data['keranjang_qty']);
	$stock_brg = $data['stock_brg'];


	// query update data
	$query = "UPDATE keranjang_pembelian SET 
				keranjang_qty   = '$keranjang_qty'
				WHERE keranjang_id = $id
			";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

// ============================================== Transaksi Pembelian ======================== //
function updateStockPembelian($data)
{
	global $conn;
	$id                  = $data["barang_ids"];
	$keranjang_qty       = $data["keranjang_qty"];
	$keranjang_id_kasir  = $data['keranjang_id_kasir'];
	$pembelian_invoice   = $data['pembelian_invoice'];
	$kik                 = $data['kik'];
	$barang_harga_beli   = $data['barang_harga_beli'];
	$pembelian_invoice_parent = $data['pembelian_invoice_parent'];
	$invoice_pembelian_cabang = $data['invoice_pembelian_cabang'];

	$pembelian_invoice2  = $data['pembelian_invoice2'];
	$invoice_tgl         = date("d F Y - g:i:s a");
	$invoice_supplier    = $data['invoice_supplier'];
	$invoice_total       = $data['invoice_total'];
	$invoice_bayar       = $data['angka1'];
	$invoice_kembali     = $invoice_bayar - $invoice_total;
	$invoice_date        = date("Y-m-d");
	$pembelian_date      = $data['pembelian_date'];
	$invoice_pembelian_number_delete = $data['invoice_pembelian_number_delete'];
	$pembelian_invoice_parent2       = $data['pembelian_invoice_parent2'];
	$invoice_hutang				 	 = $data['invoice_hutang'];
	if ($invoice_hutang == 1) {
		$invoice_hutang_dp = $invoice_bayar;
	} else {
		$invoice_hutang_dp = 0;
	}
	$invoice_hutang_jatuh_tempo	    = $data['invoice_hutang_jatuh_tempo'];
	$invoice_hutang_lunas			= $data['invoice_hutang_lunas'];
	$pembelian_cabang				= $data['pembelian_cabang'];

	$jumlah = count($keranjang_id_kasir);

	// Cek No. Invoice
// 	$invoice_cek = mysqli_num_rows(mysqli_query($conn, "select * from invoice_pembelian where pembelian_invoice = '$pembelian_invoice2' && invoice_pembelian_cabang = '$invoice_pembelian_cabang' "));

	if ($invoice_cek > 0) {
		echo "
			<script>
				alert('No. Invoice Pembelian Sudah Digunakan Sebelumnya !!');
			</script>
		";
	} else {
		// query insert invoice
		$query1 = "INSERT INTO invoice_pembelian VALUES ('', '$pembelian_invoice2', '$pembelian_invoice_parent2', '$invoice_tgl', '$invoice_supplier', '$invoice_total', '$invoice_bayar', '$invoice_kembali', '$kik', '$invoice_date', ' ', ' ', '$invoice_total', '$invoice_bayar', '$invoice_kembali', '$invoice_hutang', '$invoice_hutang_dp', '$invoice_hutang_jatuh_tempo', '$invoice_hutang_lunas', '$invoice_pembelian_cabang')";
		// var_dump($query1); die();
		mysqli_query($conn, $query1);


		for ($x = 0; $x < $jumlah; $x++) {
			$query = "INSERT INTO pembelian VALUES ('', '$id[$x]', '$id[$x]', '$keranjang_qty[$x]', '$keranjang_id_kasir[$x]', '$pembelian_invoice[$x]', '$pembelian_invoice_parent[$x]', '$pembelian_date[$x]', '$keranjang_qty[$x]', '$keranjang_qty[$x]', '$barang_harga_beli[$x]', '$pembelian_cabang[$x]')";
			mysqli_query($conn, $query);

			// Mencari Rata-rata Pembelian
// 			$hargaBeli = mysqli_query($conn, "SELECT AVG(barang_harga_beli) AS average FROM pembelian WHERE barang_id = $id[$x]");
// 			$hargaBeli = mysqli_fetch_assoc($hargaBeli);
// 			$hargaBeli = ceil($hargaBeli['average']);

			// Edit Data
// 			$query2 = "UPDATE barang SET 
// 						barang_harga_beli     = '$hargaBeli'
// 						WHERE barang_id       = $id[$x]
// 				";

// 			mysqli_query($conn, $query2);
		}


		mysqli_query($conn, "DELETE FROM keranjang_pembelian WHERE keranjang_id_kasir = $kik");
		mysqli_query($conn, "DELETE FROM invoice_pembelian_number WHERE invoice_pembelian_number_delete = $invoice_pembelian_number_delete");
		
		// Update saldo Hutang Dagang (2-1100) jika transaksi hutang
		if ($invoice_hutang == 1) {
			// Total hutang adalah total invoice_total
			$total_hutang = floatval($invoice_total);
			
			// Cek apakah kolom cabang ada di table laba_kategori
			$check_cabang_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
			$cabang_column_result = mysqli_query($conn, $check_cabang_column);
			$cabang_column_exists = ($cabang_column_result && mysqli_num_rows($cabang_column_result) > 0);
			
			// Query untuk mencari akun Hutang Dagang dengan kode_akun = '2-1100'
			if ($cabang_column_exists) {
				// Cari akun dengan kode_akun 2-1100 untuk cabang ini atau cabang 0 (default)
				$query_hutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '2-1100' AND (cabang = $invoice_pembelian_cabang OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
			} else {
				$query_hutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '2-1100' LIMIT 1";
			}
			
			$result_hutang = mysqli_query($conn, $query_hutang);
			
			if ($result_hutang && mysqli_num_rows($result_hutang) > 0) {
				// Akun Hutang Dagang sudah ada, update saldo
				$row_hutang = mysqli_fetch_assoc($result_hutang);
				$saldo_hutang_sekarang = floatval($row_hutang['saldo']);
				$saldo_hutang_baru = $saldo_hutang_sekarang + $total_hutang;
				
				// Update saldo
				$update_hutang_query = "UPDATE laba_kategori SET saldo = $saldo_hutang_baru WHERE id = " . intval($row_hutang['id']);
				mysqli_query($conn, $update_hutang_query);
			} else {
				// Akun Hutang Dagang belum ada, buat baru
				// Cari kategori 'pasiva' untuk menentukan kategori yang tepat
				$query_kategori = "SELECT kategori, tipe_akun FROM laba_kategori WHERE kategori = 'pasiva' LIMIT 1";
				$result_kategori = mysqli_query($conn, $query_kategori);
				
				$kategori_hutang = 'pasiva';
				$tipe_akun_hutang = 'kredit';
				
				if ($result_kategori && mysqli_num_rows($result_kategori) > 0) {
					$row_kategori = mysqli_fetch_assoc($result_kategori);
					$tipe_akun_hutang = $row_kategori['tipe_akun'] ?? 'kredit';
				}
				
				// Insert akun Hutang Dagang baru
				if ($cabang_column_exists) {
					$insert_hutang_query = "INSERT INTO laba_kategori (name, kode_akun, kategori, tipe_akun, saldo, cabang) VALUES ('Hutang Dagang', '2-1100', '$kategori_hutang', '$tipe_akun_hutang', $total_hutang, $invoice_pembelian_cabang)";
				} else {
					$insert_hutang_query = "INSERT INTO laba_kategori (name, kode_akun, kategori, tipe_akun, saldo) VALUES ('Hutang Dagang', '2-1100', '$kategori_hutang', '$tipe_akun_hutang', $total_hutang)";
				}
				
				mysqli_query($conn, $insert_hutang_query);
			}
		}
		
		return mysqli_affected_rows($conn);
	}
}

// ======================================== Pembelian Edit ================================ //
function updateQTY2pembelian($data)
{
	global $conn;
	$id = $data["pembelian_id"];
	$bid = $data["barang_id"];

	// ambil data dari tiap elemen dalam form
	$barang_qty      = htmlspecialchars($data['barang_qty']);
	$barang_qty_lama = $data['barang_qty_lama'];

	// retur
	$barang_stock           = $data['barang_stock'];
	$barang_stock_kurang    = $barang_qty_lama - $barang_qty;
	$barang_stock_hasil     = $barang_stock - $barang_stock_kurang;
	// var_dump($barang_stock_hasil); die();

	if ($barang_qty > $barang_qty_lama) {
		echo "
			<script>
				alert('Jika Anda Ingin Menambahkan QTY Barang.. Lakukan Transaksi Invoice Baru !!!');
			</script>
		";
	} else {
		// query update data
		$query = "UPDATE pembelian SET 
					barang_qty       = '$barang_qty'
					WHERE pembelian_id = $id
					";
		$query1 = "UPDATE barang SET 
					barang_stock   = '$barang_stock_hasil'
					WHERE barang_id = $bid
					";
		mysqli_query($conn, $query1);
		mysqli_query($conn, $query);
		return mysqli_affected_rows($conn);
		// $query1 = "INSERT INTO retur VALUES ('', '$retur_barang_id', '$retur_invoice', '$retur_admin_id', '$retur_date', ' ', '$barang_stock')";
		// mysqli_query($conn, $query1);

	}
}

function updateInvoicePembelian($data)
{
	global $conn;
	$id = $data["invoice_pembelian_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_total        = htmlspecialchars($data['invoice_total']);
	$invoice_bayar        = htmlspecialchars($data['angka1']);
	$invoice_kembali      = $invoice_bayar - $invoice_total;
	$invoice_kasir_edit   = $data['invoice_kasir_edit'];
	$invoice_date_edit    = date('Y-m-d');

	// query update data
	$query = "UPDATE invoice_pembelian SET 
					invoice_total      = '$invoice_total',
					invoice_bayar      = '$invoice_bayar',
					invoice_kembali    = '$invoice_kembali',
					invoice_date_edit  = '$invoice_date_edit',
					invoice_kasir_edit = '$invoice_kasir_edit'
					WHERE invoice_pembelian_id = $id
					";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

function hapusPembelianInvoice($id)
{
	global $conn;

	$id = $id;

	$pembelian_invoice_parent = mysqli_query($conn, "select pembelian_invoice_parent, invoice_pembelian_cabang from invoice_pembelian where invoice_pembelian_id = '" . $id . "'");
	$pip = mysqli_fetch_array($pembelian_invoice_parent);
	$pembelian_invoice_parent  = $pip["pembelian_invoice_parent"];
	$invoice_pembelian_cabang  = $pip["invoice_pembelian_cabang"];

	// Menghitung data di tabel HUtang sesuai No. Invoice Parent
	$hutang = mysqli_query($conn, "select * from hutang where hutang_invoice_parent = '" . $pembelian_invoice_parent . "' && hutang_cabang = '" . $invoice_pembelian_cabang . "' ");
	$jmlHutang = mysqli_num_rows($hutang);

	if ($jmlHutang > 0) {
		mysqli_query($conn, "DELETE FROM hutang WHERE hutang_invoice_parent = $pembelian_invoice_parent && hutang_cabang = $invoice_pembelian_cabang");

		mysqli_query($conn, "DELETE FROM pembelian WHERE pembelian_invoice_parent = $pembelian_invoice_parent && pembelian_cabang = $invoice_pembelian_cabang");

		mysqli_query($conn, "DELETE FROM invoice_pembelian WHERE pembelian_invoice_parent = $pembelian_invoice_parent && invoice_pembelian_cabang = $invoice_pembelian_cabang");
	} else {
		mysqli_query($conn, "DELETE FROM pembelian WHERE pembelian_invoice_parent = $pembelian_invoice_parent && pembelian_cabang = $invoice_pembelian_cabang");

		mysqli_query($conn, "DELETE FROM invoice_pembelian WHERE pembelian_invoice_parent = $pembelian_invoice_parent && invoice_pembelian_cabang = $invoice_pembelian_cabang");
	}

	return mysqli_affected_rows($conn);
}

// ===================================== Pindah Cabang ===================================== //
function editLokasiCabang($data)
{
	global $conn;
	$id = $data["user_id"];

	// ambil data dari tiap elemen dalam form
	$user_cabang = htmlspecialchars($data['user_cabang']);

	// query update data
	$query = "UPDATE user SET 
				user_cabang       = '$user_cabang'
				WHERE user_id     = $id
				";
	// var_dump($query); die();
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

// ======================================== Kurir ========================================== //
function editStatusKurir($data)
{
	global $conn;
	$id = $data["invoice_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_status_kurir       = $data['invoice_status_kurir'];
	$invoice_date_selesai_kurir = date("d F Y g:i:s a");

	if ($invoice_status_kurir == 3) {
		// query update data
		$query = "UPDATE invoice SET 
				invoice_status_kurir 		= '$invoice_status_kurir',
				invoice_date_selesai_kurir	= '$invoice_date_selesai_kurir'
				WHERE invoice_id     = $id
		";
	} else {
		// query update data
		$query = "UPDATE invoice SET 
				invoice_status_kurir 		= '$invoice_status_kurir',
				invoice_date_selesai_kurir	= '-'
				WHERE invoice_id     = $id
		";
	}

	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

// ======================================= Piutang ======================================= //
function tambahCicilanPiutang($data)
{
	global $conn;
	$id = $data["invoice_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_bayar_lama			= $data['invoice_bayar'];
	$piutang_nominal			= $data['piutang_nominal'];
	$invoice_bayar         		= $invoice_bayar_lama + $piutang_nominal;
	$invoice_sub_total			= $data['invoice_sub_total'];
	$invoice_kembali            = $invoice_bayar - $invoice_sub_total;

	$piutang_invoice			= $data['piutang_invoice'];
	$piutang_date				= date("Y-m-d");
	$piutang_date_time			= date("d F Y g:i:s a");
	$piutang_kasir				= $data['piutang_kasir'];
	$piutang_tipe_pembayaran	= $data['piutang_tipe_pembayaran'];
	$piutang_cabang				= $data['piutang_cabang'];

	if ($invoice_bayar >= $invoice_sub_total) {
		// query update data
		$query = "UPDATE invoice SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali',
					invoice_piutang        = 0,
					invoice_piutang_lunas  = 1
					WHERE invoice_id = $id
				";
		mysqli_query($conn, $query);

		// Insert Tabel kembalian Piutang Cicilan
		$kembalian_piutang = $invoice_bayar - $invoice_sub_total;
		$query3 = "INSERT INTO piutang_kembalian VALUES ('', '$piutang_invoice', '$piutang_date', '$piutang_date_time', '$kembalian_piutang', '$piutang_cabang')";
		mysqli_query($conn, $query3);
	} else {
		// query update data
		$query = "UPDATE invoice SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali'
					WHERE invoice_id = $id
				";
		mysqli_query($conn, $query);
	}



	// query insert data
	$query2 = "INSERT INTO piutang VALUES ('', '$piutang_invoice', '$piutang_date', '$piutang_date_time', '$piutang_kasir', '$piutang_nominal', '$piutang_tipe_pembayaran', '$piutang_cabang')";
	mysqli_query($conn, $query2);

	// Update saldo laba_kategori: Kurangi Piutang Dagang (1-1300) dan tambah ke akun pembayaran
	$piutang_nominal_float = floatval($piutang_nominal);
	
	// Cek apakah kolom cabang ada di table laba_kategori
	$check_cabang_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
	$cabang_column_result = mysqli_query($conn, $check_cabang_column);
	$cabang_column_exists = ($cabang_column_result && mysqli_num_rows($cabang_column_result) > 0);
	
	// 1. Kurangi saldo Piutang Dagang (1-1300)
	if ($cabang_column_exists) {
		$query_piutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '1-1300' AND (cabang = $piutang_cabang OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
	} else {
		$query_piutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '1-1300' LIMIT 1";
	}
	
	$result_piutang = mysqli_query($conn, $query_piutang);
	if ($result_piutang && mysqli_num_rows($result_piutang) > 0) {
		$row_piutang = mysqli_fetch_assoc($result_piutang);
		$saldo_piutang_sekarang = floatval($row_piutang['saldo']);
		$saldo_piutang_baru = $saldo_piutang_sekarang - $piutang_nominal_float;
		
		// Update saldo Piutang Dagang (kurangi)
		$update_piutang_query = "UPDATE laba_kategori SET saldo = $saldo_piutang_baru WHERE id = " . intval($row_piutang['id']);
		mysqli_query($conn, $update_piutang_query);
	}
	
	// 2. Tambah saldo ke akun pembayaran sesuai tipe
	$kode_akun_pembayaran = '';
	if ($piutang_tipe_pembayaran == 0) {
		// Cash → 1-1100 (Kas Tunai)
		$kode_akun_pembayaran = '1-1100';
	} else if ($piutang_tipe_pembayaran == 1 || $piutang_tipe_pembayaran == 2 || $piutang_tipe_pembayaran == 3) {
		// Transfer (1), Debit (2), Credit Card (3) → 1-1152 (Kas Bank BRI)
		$kode_akun_pembayaran = '1-1152';
	}
	
	if (!empty($kode_akun_pembayaran)) {
		// Cari akun pembayaran
		if ($cabang_column_exists) {
			$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' AND (cabang = $piutang_cabang OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
		} else {
			$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' LIMIT 1";
		}
		
		$result_kas = mysqli_query($conn, $query_kas);
		
		if ($result_kas && mysqli_num_rows($result_kas) > 0) {
			// Akun sudah ada, update saldo
			$row_kas = mysqli_fetch_assoc($result_kas);
			$saldo_kas_sekarang = floatval($row_kas['saldo']);
			$saldo_kas_baru = $saldo_kas_sekarang + $piutang_nominal_float;
			
			// Update saldo akun pembayaran (tambah)
			$update_kas_query = "UPDATE laba_kategori SET saldo = $saldo_kas_baru WHERE id = " . intval($row_kas['id']);
			mysqli_query($conn, $update_kas_query);
		} else {
			// Akun belum ada, buat baru
			$nama_akun = ($kode_akun_pembayaran == '1-1100') ? 'Kas Tunai' : 'Kas Bank BRI';
			$kategori_kas = 'aktiva';
			$tipe_akun_kas = 'debit';
			
			// Cari kategori 'aktiva' untuk menentukan tipe akun yang tepat
			$query_kategori = "SELECT kategori, tipe_akun FROM laba_kategori WHERE kategori = 'aktiva' LIMIT 1";
			$result_kategori = mysqli_query($conn, $query_kategori);
			if ($result_kategori && mysqli_num_rows($result_kategori) > 0) {
				$row_kategori = mysqli_fetch_assoc($result_kategori);
				$tipe_akun_kas = $row_kategori['tipe_akun'] ?? 'debit';
			}
			
			// Insert akun baru
			if ($cabang_column_exists) {
				$insert_kas_query = "INSERT INTO laba_kategori (name, kode_akun, kategori, tipe_akun, saldo, cabang) VALUES ('$nama_akun', '$kode_akun_pembayaran', '$kategori_kas', '$tipe_akun_kas', $piutang_nominal_float, $piutang_cabang)";
			} else {
				$insert_kas_query = "INSERT INTO laba_kategori (name, kode_akun, kategori, tipe_akun, saldo) VALUES ('$nama_akun', '$kode_akun_pembayaran', '$kategori_kas', '$tipe_akun_kas', $piutang_nominal_float)";
			}
			
			mysqli_query($conn, $insert_kas_query);
		}
	}

	return mysqli_affected_rows($conn);
}

function hapusCicilanPiutang($id)
{
	global $conn;


	// Ambil ID produk
	$data_id = $id;

	// Mencari No. Invoice
	$noInvoice = mysqli_query($conn, "select piutang_invoice, piutang_nominal, piutang_cabang from piutang where piutang_id = '" . $data_id . "'");
	$noInvoice = mysqli_fetch_array($noInvoice);
	$piutangInvoice = $noInvoice["piutang_invoice"];
	$nominal 		= $noInvoice["piutang_nominal"];
	$cabangInvoice 	= $noInvoice["piutang_cabang"];

	// Mencari Nilai Bayar di Tabel Invoive
	$bayarInvoice = mysqli_query($conn, "select invoice_id, invoice_bayar, invoice_sub_total from invoice where penjualan_invoice = '" . $piutangInvoice . "' && invoice_cabang = '" . $cabangInvoice . "' ");
	$bayarInvoice = mysqli_fetch_array($bayarInvoice);
	$invoice_id         = $bayarInvoice['invoice_id'];
	$bayar       		= $bayarInvoice['invoice_bayar'];
	$subTotalInvoice 	= $bayarInvoice['invoice_sub_total'];

	// Proses
	$invoice_bayar         		= $bayar - $nominal;
	$invoice_kembali            = $invoice_bayar - $subTotalInvoice;

	if ($invoice_bayar >= $subTotalInvoice) {
		// query update data
		$query2 = "UPDATE invoice SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali',
					invoice_piutang        = 0,
					invoice_piutang_lunas  = 1
					WHERE invoice_id = $invoice_id
				";
	} else {
		// query update data
		$query2 = "UPDATE invoice SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali',
					invoice_piutang        = 1,
					invoice_piutang_lunas  = 0
					WHERE invoice_id = $invoice_id
				";
	}
	mysqli_query($conn, $query2);

	// Ambil data piutang yang akan dihapus untuk mengembalikan saldo
	$query_piutang_data = "SELECT piutang_nominal, piutang_tipe_pembayaran, piutang_cabang FROM piutang WHERE piutang_id = $id";
	$result_piutang_data = mysqli_query($conn, $query_piutang_data);
	
	if ($result_piutang_data && mysqli_num_rows($result_piutang_data) > 0) {
		$piutang_data = mysqli_fetch_assoc($result_piutang_data);
		$piutang_nominal_hapus = floatval($piutang_data['piutang_nominal']);
		$piutang_tipe_pembayaran_hapus = intval($piutang_data['piutang_tipe_pembayaran']);
		$piutang_cabang_hapus = intval($piutang_data['piutang_cabang']);
		
		// Cek apakah kolom cabang ada di table laba_kategori
		$check_cabang_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
		$cabang_column_result = mysqli_query($conn, $check_cabang_column);
		$cabang_column_exists = ($cabang_column_result && mysqli_num_rows($cabang_column_result) > 0);
		
		// 1. Tambah kembali saldo Piutang Dagang (1-1300) karena cicilan dihapus
		if ($cabang_column_exists) {
			$query_piutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '1-1300' AND (cabang = $piutang_cabang_hapus OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
		} else {
			$query_piutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '1-1300' LIMIT 1";
		}
		
		$result_piutang = mysqli_query($conn, $query_piutang);
		if ($result_piutang && mysqli_num_rows($result_piutang) > 0) {
			$row_piutang = mysqli_fetch_assoc($result_piutang);
			$saldo_piutang_sekarang = floatval($row_piutang['saldo']);
			$saldo_piutang_baru = $saldo_piutang_sekarang + $piutang_nominal_hapus;
			
			// Update saldo Piutang Dagang (tambah kembali)
			$update_piutang_query = "UPDATE laba_kategori SET saldo = $saldo_piutang_baru WHERE id = " . intval($row_piutang['id']);
			mysqli_query($conn, $update_piutang_query);
		}
		
		// 2. Kurangi saldo dari akun pembayaran sesuai tipe
		$kode_akun_pembayaran = '';
		if ($piutang_tipe_pembayaran_hapus == 0) {
			// Cash → 1-1100 (Kas Tunai)
			$kode_akun_pembayaran = '1-1100';
		} else if ($piutang_tipe_pembayaran_hapus == 1 || $piutang_tipe_pembayaran_hapus == 2 || $piutang_tipe_pembayaran_hapus == 3) {
			// Transfer (1), Debit (2), Credit Card (3) → 1-1152 (Kas Bank BRI)
			$kode_akun_pembayaran = '1-1152';
		}
		
		if (!empty($kode_akun_pembayaran)) {
			// Cari akun pembayaran
			if ($cabang_column_exists) {
				$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' AND (cabang = $piutang_cabang_hapus OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
			} else {
				$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' LIMIT 1";
			}
			
			$result_kas = mysqli_query($conn, $query_kas);
			
			if ($result_kas && mysqli_num_rows($result_kas) > 0) {
				// Akun sudah ada, update saldo
				$row_kas = mysqli_fetch_assoc($result_kas);
				$saldo_kas_sekarang = floatval($row_kas['saldo']);
				$saldo_kas_baru = $saldo_kas_sekarang - $piutang_nominal_hapus;
				
				// Update saldo akun pembayaran (kurangi)
				$update_kas_query = "UPDATE laba_kategori SET saldo = $saldo_kas_baru WHERE id = " . intval($row_kas['id']);
				mysqli_query($conn, $update_kas_query);
			}
		}
	}

	mysqli_query($conn, "DELETE FROM piutang WHERE piutang_id = $id");

	return mysqli_affected_rows($conn);
}

function updateInvoicePiutang($data)
{
	global $conn;
	$id = $data["invoice_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_total        = htmlspecialchars($data['invoice_total']);
	$invoice_ongkir       = $data['invoice_ongkir'];
	$invoice_sub_total    = $data['invoice_sub_total'];
	$invoice_bayar        = htmlspecialchars($data['angka1']);
	$invoice_kembali      = $invoice_bayar - $invoice_sub_total;
	$invoice_kasir_edit   = $data['invoice_kasir_edit'];
	$invoice_date_edit    = date('Y-m-d');



	if ($invoice_bayar >= $invoice_sub_total) {
		// query update data
		$query = "UPDATE invoice SET 
					invoice_total      		= '$invoice_total',
					invoice_ongkir     		= '$invoice_ongkir',
					invoice_sub_total  		= '$invoice_sub_total',
					invoice_bayar      		= '$invoice_bayar',
					invoice_kembali    		= '$invoice_kembali',
					invoice_date_edit  		= '$invoice_date_edit',
					invoice_kasir_edit 		= '$invoice_kasir_edit',
					invoice_piutang        	= 0,
					invoice_piutang_lunas 	= 1
					WHERE invoice_id = $id
				";
	} else {
		// query update data
		$query = "UPDATE invoice SET 
					invoice_total      		= '$invoice_total',
					invoice_ongkir     		= '$invoice_ongkir',
					invoice_sub_total  		= '$invoice_sub_total',
					invoice_bayar      		= '$invoice_bayar',
					invoice_kembali    		= '$invoice_kembali',
					invoice_date_edit  		= '$invoice_date_edit',
					invoice_kasir_edit 		= '$invoice_kasir_edit',
					invoice_piutang        	= 1,
					invoice_piutang_lunas 	= 0
					WHERE invoice_id = $id
				";
	}
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

// ======================================= Hutang ======================================= //
function tambahCicilanhutang($data)
{
	global $conn;
	$id = $data["invoice_pembelian_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_bayar_lama			= $data['invoice_bayar'];
	$hutang_nominal				= $data['hutang_nominal'];
	$invoice_bayar         		= $invoice_bayar_lama + $hutang_nominal;
	$invoice_total				= $data['invoice_total'];
	$invoice_kembali            = $invoice_bayar - $invoice_total;

	$hutang_invoice				= $data['hutang_invoice'];
	$hutang_invoice_parent		= $data['hutang_invoice_parent'];
	$hutang_date				= date("Y-m-d");
	$hutang_date_time			= date("d F Y g:i:s a");
	$hutang_kasir				= $data['hutang_kasir'];
	$hutang_tipe_pembayaran		= $data['hutang_tipe_pembayaran'];
	$hutang_cabang				= $data['hutang_cabang'];

	if ($invoice_bayar >= $invoice_total) {
		// query update data
		$query = "UPDATE invoice_pembelian SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali',
					invoice_hutang         = 0,
					invoice_hutang_lunas   = 1
					WHERE invoice_pembelian_id = $id
				";
		mysqli_query($conn, $query);

		// Insert Tabel kembalian Piutang Cicilan
		$kembalian_hutang = $invoice_bayar - $invoice_total;
		$query3 = "INSERT INTO hutang_kembalian VALUES ('', '$hutang_invoice', '$hutang_invoice_parent', '$hutang_date', '$hutang_date_time', '$kembalian_hutang', '$hutang_cabang')";
		mysqli_query($conn, $query3);
	} else {
		// query update data
		$query = "UPDATE invoice_pembelian SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali'
					WHERE invoice_pembelian_id = $id
				";
		mysqli_query($conn, $query);
	}



	// query insert data
	$query2 = "INSERT INTO hutang VALUES ('', '$hutang_invoice', '$hutang_invoice_parent', '$hutang_date', '$hutang_date_time', '$hutang_kasir', '$hutang_nominal', '$hutang_tipe_pembayaran', '$hutang_cabang')";
	mysqli_query($conn, $query2);

	// Update saldo laba_kategori: Kurangi akun pembayaran dan kurangi Hutang Dagang (2-1100)
	$hutang_nominal_float = floatval($hutang_nominal);
	
	// Cek apakah kolom cabang ada di table laba_kategori
	$check_cabang_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
	$cabang_column_result = mysqli_query($conn, $check_cabang_column);
	$cabang_column_exists = ($cabang_column_result && mysqli_num_rows($cabang_column_result) > 0);
	
	// 1. Kurangi saldo Hutang Dagang (2-1100)
	if ($cabang_column_exists) {
		$query_hutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '2-1100' AND (cabang = $hutang_cabang OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
	} else {
		$query_hutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '2-1100' LIMIT 1";
	}
	
	$result_hutang = mysqli_query($conn, $query_hutang);
	if ($result_hutang && mysqli_num_rows($result_hutang) > 0) {
		$row_hutang = mysqli_fetch_assoc($result_hutang);
		$saldo_hutang_sekarang = floatval($row_hutang['saldo']);
		$saldo_hutang_baru = $saldo_hutang_sekarang - $hutang_nominal_float;
		
		// Update saldo Hutang Dagang (kurangi)
		$update_hutang_query = "UPDATE laba_kategori SET saldo = $saldo_hutang_baru WHERE id = " . intval($row_hutang['id']);
		mysqli_query($conn, $update_hutang_query);
	}
	
	// 2. Kurangi saldo dari akun pembayaran sesuai tipe
	$kode_akun_pembayaran = '';
	if ($hutang_tipe_pembayaran == 0) {
		// Cash → 1-1100 (Kas Tunai)
		$kode_akun_pembayaran = '1-1100';
	} else if ($hutang_tipe_pembayaran == 1 || $hutang_tipe_pembayaran == 2 || $hutang_tipe_pembayaran == 3) {
		// Transfer (1), Debit (2), Credit Card (3) → 1-1152 (Kas Bank BRI)
		$kode_akun_pembayaran = '1-1152';
	}
	
	if (!empty($kode_akun_pembayaran)) {
		// Cari akun pembayaran
		if ($cabang_column_exists) {
			$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' AND (cabang = $hutang_cabang OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
		} else {
			$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' LIMIT 1";
		}
		
		$result_kas = mysqli_query($conn, $query_kas);
		
		if ($result_kas && mysqli_num_rows($result_kas) > 0) {
			// Akun sudah ada, update saldo
			$row_kas = mysqli_fetch_assoc($result_kas);
			$saldo_kas_sekarang = floatval($row_kas['saldo']);
			$saldo_kas_baru = $saldo_kas_sekarang - $hutang_nominal_float;
			
			// Update saldo akun pembayaran (kurangi)
			$update_kas_query = "UPDATE laba_kategori SET saldo = $saldo_kas_baru WHERE id = " . intval($row_kas['id']);
			mysqli_query($conn, $update_kas_query);
		}
	}

	return mysqli_affected_rows($conn);
}

function hapusCicilanHutang($id)
{
	global $conn;


	// Ambil ID produk
	$data_id = $id;

	// Mencari No. Invoice
	$noInvoice = mysqli_query($conn, "select hutang_invoice_parent, hutang_nominal, hutang_cabang from hutang where hutang_id = '" . $data_id . "'");
	$noInvoice = mysqli_fetch_array($noInvoice);
	$invoiceParent 		 = $noInvoice["hutang_invoice_parent"];
	$nominal 			 = $noInvoice["hutang_nominal"];
	$cabangInvoice 	 	 = $noInvoice["hutang_cabang"];

	// Mencari Nilai Bayar di Tabel Invoive
	$bayarInvoicePembelian = mysqli_query($conn, "select invoice_pembelian_id, invoice_bayar, invoice_total from invoice_pembelian where pembelian_invoice_parent = '" . $invoiceParent . "' && invoice_pembelian_cabang = '" . $cabangInvoice . "' ");
	$bip 				  		  = mysqli_fetch_array($bayarInvoicePembelian);
	$invoice_pembelian_id         = $bip['invoice_pembelian_id'];
	$bayar       				  = $bip['invoice_bayar'];
	$totalInvoice 	              = $bip['invoice_total'];

	// Proses
	$invoice_bayar         		= $bayar - $nominal;
	$invoice_kembali            = $invoice_bayar - $totalInvoice;

	if ($invoice_bayar >= $totalInvoice) {
		// query update data
		$query2 = "UPDATE invoice_pembelian SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali',
					invoice_hutang         = 0,
					invoice_hutang_lunas   = 1
					WHERE invoice_pembelian_id = $invoice_pembelian_id
				";
	} else {
		// query update data
		$query2 = "UPDATE invoice_pembelian SET 
					invoice_bayar          = '$invoice_bayar',
					invoice_kembali        = '$invoice_kembali',
					invoice_hutang         = 1,
					invoice_hutang_lunas   = 0
					WHERE invoice_pembelian_id = $invoice_pembelian_id
				";
	}
	mysqli_query($conn, $query2);

	// Ambil data hutang yang akan dihapus untuk mengembalikan saldo
	$query_hutang_data = "SELECT hutang_nominal, hutang_tipe_pembayaran, hutang_cabang FROM hutang WHERE hutang_id = $id";
	$result_hutang_data = mysqli_query($conn, $query_hutang_data);
	
	if ($result_hutang_data && mysqli_num_rows($result_hutang_data) > 0) {
		$hutang_data = mysqli_fetch_assoc($result_hutang_data);
		$hutang_nominal_hapus = floatval($hutang_data['hutang_nominal']);
		$hutang_tipe_pembayaran_hapus = intval($hutang_data['hutang_tipe_pembayaran']);
		$hutang_cabang_hapus = intval($hutang_data['hutang_cabang']);
		
		// Cek apakah kolom cabang ada di table laba_kategori
		$check_cabang_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
		$cabang_column_result = mysqli_query($conn, $check_cabang_column);
		$cabang_column_exists = ($cabang_column_result && mysqli_num_rows($cabang_column_result) > 0);
		
		// 1. Tambah kembali saldo Hutang Dagang (2-1100) karena cicilan dihapus
		if ($cabang_column_exists) {
			$query_hutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '2-1100' AND (cabang = $hutang_cabang_hapus OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
		} else {
			$query_hutang = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '2-1100' LIMIT 1";
		}
		
		$result_hutang = mysqli_query($conn, $query_hutang);
		if ($result_hutang && mysqli_num_rows($result_hutang) > 0) {
			$row_hutang = mysqli_fetch_assoc($result_hutang);
			$saldo_hutang_sekarang = floatval($row_hutang['saldo']);
			$saldo_hutang_baru = $saldo_hutang_sekarang + $hutang_nominal_hapus;
			
			// Update saldo Hutang Dagang (tambah kembali)
			$update_hutang_query = "UPDATE laba_kategori SET saldo = $saldo_hutang_baru WHERE id = " . intval($row_hutang['id']);
			mysqli_query($conn, $update_hutang_query);
		}
		
		// 2. Tambah kembali saldo ke akun pembayaran sesuai tipe
		$kode_akun_pembayaran = '';
		if ($hutang_tipe_pembayaran_hapus == 0) {
			// Cash → 1-1100 (Kas Tunai)
			$kode_akun_pembayaran = '1-1100';
		} else if ($hutang_tipe_pembayaran_hapus == 1 || $hutang_tipe_pembayaran_hapus == 2 || $hutang_tipe_pembayaran_hapus == 3) {
			// Transfer (1), Debit (2), Credit Card (3) → 1-1152 (Kas Bank BRI)
			$kode_akun_pembayaran = '1-1152';
		}
		
		if (!empty($kode_akun_pembayaran)) {
			// Cari akun pembayaran
			if ($cabang_column_exists) {
				$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' AND (cabang = $hutang_cabang_hapus OR cabang = 0 OR cabang IS NULL) ORDER BY cabang DESC LIMIT 1";
			} else {
				$query_kas = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun_pembayaran' LIMIT 1";
			}
			
			$result_kas = mysqli_query($conn, $query_kas);
			
			if ($result_kas && mysqli_num_rows($result_kas) > 0) {
				// Akun sudah ada, update saldo
				$row_kas = mysqli_fetch_assoc($result_kas);
				$saldo_kas_sekarang = floatval($row_kas['saldo']);
				$saldo_kas_baru = $saldo_kas_sekarang + $hutang_nominal_hapus;
				
				// Update saldo akun pembayaran (tambah kembali)
				$update_kas_query = "UPDATE laba_kategori SET saldo = $saldo_kas_baru WHERE id = " . intval($row_kas['id']);
				mysqli_query($conn, $update_kas_query);
			}
		}
	}

	mysqli_query($conn, "DELETE FROM hutang WHERE hutang_id = $id");

	return mysqli_affected_rows($conn);
}

function updateInvoicePembelianHutang($data)
{
	global $conn;
	$id = $data["invoice_pembelian_id"];

	// ambil data dari tiap elemen dalam form
	$invoice_total        = htmlspecialchars($data['invoice_total']);
	$invoice_bayar        = htmlspecialchars($data['angka1']);
	$invoice_kembali      = $invoice_bayar - $invoice_total;
	$invoice_kasir_edit   = $data['invoice_kasir_edit'];
	$invoice_date_edit    = date('Y-m-d');

	if ($invoice_bayar >= $invoice_total) {
		// query update data
		$query = "UPDATE invoice_pembelian SET 
					invoice_total      = '$invoice_total',
					invoice_bayar      = '$invoice_bayar',
					invoice_kembali    = '$invoice_kembali',
					invoice_date_edit  = '$invoice_date_edit',
					invoice_kasir_edit = '$invoice_kasir_edit',
					invoice_hutang        	= 0,
					invoice_hutang_lunas 	= 1
					WHERE invoice_pembelian_id = $id
				";
	} else {
		// query update data
		$query = "UPDATE invoice_pembelian SET 
					invoice_total      = '$invoice_total',
					invoice_bayar      = '$invoice_bayar',
					invoice_kembali    = '$invoice_kembali',
					invoice_date_edit  = '$invoice_date_edit',
					invoice_kasir_edit = '$invoice_kasir_edit',
					invoice_hutang        	= 1,
					invoice_hutang_lunas 	= 0
					WHERE invoice_pembelian_id = $id
				";
	}

	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

// ================================ Tranfer Stock Cabang =================================== //
function tambahTransferSelectCabang($data)
{
	global $conn;

	// ambil data dari tiap elemen dalam form
	$tsc_cabang_pusat 		= htmlspecialchars($data['tsc_cabang_pusat']);
	$tsc_cabang_penerima 	= htmlspecialchars($data['tsc_cabang_penerima']);
	$tsc_user_id 			= htmlspecialchars($data['tsc_user_id']);
	$tsc_cabang 			= htmlspecialchars($data['tsc_cabang']);


	$count = mysqli_query($conn, "select * from transfer_select_cabang where tsc_user_id = " . $tsc_user_id . " && tsc_cabang = " . $tsc_cabang . " ");
	$count = mysqli_num_rows($count);

	if ($count < 1) {
		// query insert data
		$query = "INSERT INTO transfer_select_cabang VALUES ('', '$tsc_cabang_pusat', '$tsc_cabang_penerima', '$tsc_user_id', '$tsc_cabang')";
		mysqli_query($conn, $query);
	} else {
		mysqli_query($conn, "DELETE FROM transfer_select_cabang WHERE tsc_user_id = $tsc_user_id && tsc_cabang = $tsc_cabang");
	}

	return mysqli_affected_rows($conn);
}

function resetTransferSelectCabang($data)
{
	global $conn;

	// ambil data dari tiap elemen dalam form
	$tsc_user_id 			= htmlspecialchars($data['tsc_user_id']);
	$tsc_cabang 			= htmlspecialchars($data['tsc_cabang']);
	$tsc_cabang_pusat		= htmlspecialchars($data['tsc_cabang_pusat']);

	$keranjang = mysqli_query($conn, "select * from keranjang_transfer where keranjang_transfer_id_kasir = " . $tsc_user_id . " && keranjang_transfer_cabang = " . $tsc_cabang_pusat . " ");
	$jmlkeranjang = mysqli_num_rows($keranjang);


	if ($jmlkeranjang > 0) {
		mysqli_query($conn, "DELETE FROM keranjang_transfer WHERE keranjang_transfer_id_kasir = $tsc_user_id && keranjang_transfer_cabang = $tsc_cabang_pusat");
	}

	mysqli_query($conn, "DELETE FROM transfer_select_cabang WHERE tsc_user_id = $tsc_user_id && tsc_cabang = $tsc_cabang");

	return mysqli_affected_rows($conn);
}

function tambahkeranjangtransfer($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$keranjang_nama     			= $data['keranjang_nama'];
	$barang_id          			= $data['barang_id'];
	$keranjang_qty      			= 1;
	$keranjang_barang_sn_id     	= 0;
	$keranjang_barang_option_sn 	= $data['keranjang_barang_option_sn'];
	$keranjang_sn       			= 0;
	$keranjang_id_kasir 			= $data['keranjang_id_kasir'];
	$keranjang_cabang   			= $data['keranjang_cabang'];

	$keranjang_id_cek   			= $barang_id . $keranjang_id_kasir . $keranjang_cabang;

	$keranjang_cabang_pengirim 		= $data['keranjang_cabang_pengirim'];
	$keranjang_cabang_tujuan		= $data['keranjang_cabang_tujuan'];
	$barang_kode_slug				= $data['barang_kode_slug'];
	$barang_kode 					= $data['barang_kode'];
	$cabang_penerima_stock			= $data['cabang_penerima_stock'];

	// Mencari Data Barang berdasarkan Kode Slug dan cabang
	$barangTujuan 		= mysqli_query($conn, "select * from barang where barang_kode_slug = '" . $barang_kode_slug . "' && barang_cabang = " . $keranjang_cabang_tujuan . " ");
	$jmlBarangTujuan 	= mysqli_num_rows($barangTujuan);

	// Kondisi Jika Cabang Penerima tidak memiliki Produk terkait
	if ($jmlBarangTujuan < 1) {
		echo "
  			<script>
  				alert('Maaf Kode Produk " . $barang_kode . " Tidak Ada di Toko " . $cabang_penerima_stock . " dan Coba Cek Kembali !!');
  			</script>
  		";
	} else {
		// Cek STOCK
		$barang_id_cek = mysqli_num_rows(mysqli_query($conn, "select * from keranjang_transfer where keranjang_id_cek = '$keranjang_id_cek' "));

		if ($barang_id_cek > 0 && $keranjang_barang_option_sn < 1) {
			$keranjangParent = mysqli_query($conn, "select keranjang_transfer_qty from keranjang_transfer where keranjang_id_cek = '" . $keranjang_id_cek . "'");
			$kp = mysqli_fetch_array($keranjangParent);
			$kp = $kp['keranjang_transfer_qty'];
			$kp += $keranjang_qty;

			$query = "UPDATE keranjang_transfer SET 
						keranjang_transfer_qty   = '$kp'
						WHERE keranjang_id_cek = $keranjang_id_cek
						";
			mysqli_query($conn, $query);
			return mysqli_affected_rows($conn);
		} else {
			// query insert data
			$query = "INSERT INTO keranjang_transfer VALUES ('', '$keranjang_nama', '$barang_id', '$barang_kode_slug', '$keranjang_qty', '$keranjang_barang_sn_id', '$keranjang_barang_option_sn', '$keranjang_sn', '$keranjang_id_kasir', '$keranjang_id_cek', '$keranjang_cabang_pengirim', '$keranjang_cabang_tujuan', '$keranjang_cabang')";

			mysqli_query($conn, $query);

			return mysqli_affected_rows($conn);
		}
	}
}

function tambahKeranjangBarcodeTransfer($data)
{
	global $conn;

	$barang_kode 					= htmlspecialchars($data['inputbarcode']);
	$barang_kode_slug   			= str_replace(" ", "-", $barang_kode);
	$keranjang_cabang_pengirim 		= $data['keranjang_cabang_pengirim'];
	$keranjang_cabang_tujuan		= $data['keranjang_cabang_tujuan'];
	$keranjang_id_kasir 			= $data['keranjang_id_kasir'];
	$keranjang_cabang   			= $data['keranjang_cabang'];

	// Ambil Data Barang berdasarkan Kode Barang 
	$barang 	= mysqli_query($conn, "select barang_id, barang_nama, barang_harga, barang_option_sn from barang where barang_kode = '" . $barang_kode . "' && barang_cabang = '" . $keranjang_cabang . "' ");
	$br 		= mysqli_fetch_array($barang);

	$barang_id  				= $br["barang_id"];
	$keranjang_nama  			= $br["barang_nama"];
	$keranjang_barang_option_sn = $br["barang_option_sn"];
	$keranjang_qty      		= 1;
	$keranjang_barang_sn_id     = 0;
	$keranjang_sn       		= 0;
	$keranjang_id_cek   		= $barang_id . $keranjang_id_kasir . $keranjang_cabang;

	// Kondisi jika scan Barcode Tidak sesuai
	if ($barang_id != null) {

		// Cek STOCK
		$barang_id_cek = mysqli_num_rows(mysqli_query($conn, "select * from keranjang_transfer where keranjang_id_cek = '$keranjang_id_cek' "));

		if ($barang_id_cek > 0 && $keranjang_barang_option_sn < 1) {
			$keranjangParent = mysqli_query($conn, "select keranjang_transfer_qty from keranjang_transfer where keranjang_id_cek = '" . $keranjang_id_cek . "'");
			$kp = mysqli_fetch_array($keranjangParent);
			$kp = $kp['keranjang_transfer_qty'];
			$kp += $keranjang_qty;

			$query = "UPDATE keranjang_transfer SET 
						keranjang_transfer_qty   = '$kp'
						WHERE keranjang_id_cek = $keranjang_id_cek
						";
			mysqli_query($conn, $query);
			return mysqli_affected_rows($conn);
		} else {
			// query insert data
			$query = "INSERT INTO keranjang_transfer VALUES ('', '$keranjang_nama', '$barang_id', '$barang_kode_slug', '$keranjang_qty', '$keranjang_barang_sn_id', '$keranjang_barang_option_sn', '$keranjang_sn', '$keranjang_id_kasir', '$keranjang_id_cek', '$keranjang_cabang_pengirim', '$keranjang_cabang_tujuan', '$keranjang_cabang')";

			mysqli_query($conn, $query);

			return mysqli_affected_rows($conn);
		}
	} else {
		echo '
			<script>
				alert("Kode Produk Tidak ada di Data Master Barang dan Coba Cek Kembali !! ");
				document.location.href = "";
			</script>
		';
	}
}

function updateSnTransfer($data)
{
	global $conn;
	$id = $data["keranjang_id"];


	// ambil data dari tiap elemen dalam form
	$barang_sn_id  				= $data["barang_sn_id"];
	$keranjang_transfer_cabang 	= $data['keranjang_transfer_cabang'];


	$barang_sn_desc = mysqli_query($conn, "select barang_sn_desc from barang_sn where barang_sn_id = '" . $barang_sn_id . "'");
	$barang_sn_desc = mysqli_fetch_array($barang_sn_desc);
	$barang_sn_desc = $barang_sn_desc['barang_sn_desc'];

	// Menghitung jumlah No SN berdasarkan cabang jika ada maka di tolak
	$barang_sn_count = mysqli_query($conn, "select * from keranjang_transfer where keranjang_sn = '" . $barang_sn_desc . "' && keranjang_transfer_cabang = '" . $keranjang_transfer_cabang . "' ");
	$barang_sn_count = mysqli_num_rows($barang_sn_count);

	if ($barang_sn_count > 0) {
		echo "
    		<script>
    			alert('Data No.SN " . $barang_sn_desc . " Sudah ada di daftar transfer coba pilih yang lain !!');
    			document.location.href = 'transfer-stock-cabang';
    		</script>
    	";
	} else {
		// query update data
		$query = "UPDATE keranjang_transfer SET 
							keranjang_barang_sn_id  			= '$barang_sn_id',
							keranjang_sn            			= '$barang_sn_desc'
							WHERE keranjang_transfer_id      	= $id
					";

		mysqli_query($conn, $query);
	}

	return mysqli_affected_rows($conn);
}


function updateQtyTransfer($data)
{
	global $conn;
	$id = $data["keranjang_id"];

	// ambil data dari tiap elemen dalam form
	$keranjang_qty 		= htmlspecialchars($data['keranjang_qty']);
	$stock_brg 			= $data['stock_brg'];

	if ($keranjang_qty > $stock_brg) {
		echo "
			<script>
				alert('QTY Melebihi Stock Barang.. Coba Cek Lagi !!!');
				document.location.href = '';
			</script>
		";
	} else {
		// query update data
		$query = "UPDATE keranjang_transfer SET 
					keranjang_transfer_qty   		= '$keranjang_qty'
					WHERE keranjang_transfer_id 	= $id
					";
		mysqli_query($conn, $query);
		return mysqli_affected_rows($conn);
	}
}

function hapusKeranjangTransfer($id)
{
	global $conn;

	mysqli_query($conn, "DELETE FROM keranjang_transfer WHERE keranjang_transfer_id = $id");

	return mysqli_affected_rows($conn);
}

function prosesTransfer($data)
{
	global $conn;

	// Data Input Tabel Transfer
	$transfer_ref 				= htmlspecialchars($data['transfer_ref']);
	$transfer_count				= htmlspecialchars($data['transfer_count']);
	$transfer_date				= date("Y-m-d");
	$transfer_date_time			= date("d F Y g:i:s a");
	$transfer_note				= htmlspecialchars($data['transfer_note']);
	$transfer_pengirim_cabang   = $data['transfer_pengirim_cabang'];
	$transfer_penerima_cabang   = $data['transfer_penerima_cabang'];
	$transfer_id_tipe_keluar    = $data['transfer_id_tipe_keluar'];
	$transfer_id_tipe_masuk		= $data['transfer_id_tipe_masuk'];
	// Status Trnsfer Stock Antar Cabang
	// 1. Proses Kirim
	// 2. Selesai
	// 3. Dibatalkan 
	$transfer_status			= 1;
	$transfer_user				= htmlspecialchars($data['transfer_user']);
	$transfer_cabang 			= $data['transfer_cabang'];

	// ============================================================================= //
	// Data Input Tabel transfer_produk_keluar
	$tpk_transfer_barang_id		= $data['barang_id'];
	$tpk_barang_id				= $data['barang_id'];
	$tpk_kode_slug				= $data['tpk_kode_slug'];
	$tpk_qty					= $data['keranjang_transfer_qty'];
	$tpk_ref 					= $data['tpk_ref'];
	$tpk_date                   = $data['tpk_date'];
	$tpk_date_time              = $data['tpk_date_time'];
	$tpk_barang_option_sn       = $data['tpk_barang_option_sn'];
	$tpk_barang_sn_id           = $data['tpk_barang_sn_id'];
	$tpk_barang_sn_desc         = $data['tpk_barang_sn_desc'];
	$tpk_user                   = $data['keranjang_transfer_id_kasir'];
	$tpk_pengirim_cabang        = $data['tpk_pengirim_cabang'];
	$tpk_penerima_cabang        = $data['tpk_penerima_cabang'];
	$tpk_cabang                 = $data['tpk_cabang'];


	$jumlah = count($tpk_user);

	// query insert invoice
	$query1 = "INSERT INTO transfer VALUES ('', 
							'$transfer_ref', 
							'$transfer_count', 
							'$transfer_date', 
							'$transfer_date_time',
							'',
							'', 
							'$transfer_note', 
							'$transfer_pengirim_cabang', 
							'$transfer_penerima_cabang',
							'$transfer_id_tipe_keluar', 
							'$transfer_id_tipe_masuk', 
							'$transfer_status', 
							'$transfer_user', 
							'',
							'$transfer_cabang')";
	// var_dump($query1); die();
	mysqli_query($conn, $query1);

	for ($x = 0; $x < $jumlah; $x++) {
		$query = "INSERT INTO transfer_produk_keluar VALUES ('', 
										'$tpk_transfer_barang_id[$x]', 
										'$tpk_barang_id[$x]', 
										'$tpk_kode_slug[$x]', 
										'$tpk_qty[$x]', 
										'$tpk_ref[$x]', 
										'$tpk_date[$x]', 
										'$tpk_date_time[$x]', 
										'$tpk_barang_option_sn[$x]', 
										'$tpk_barang_sn_id[$x]', 
										'$tpk_barang_sn_desc[$x]', 
										'$tpk_user[$x]', 
										'$tpk_pengirim_cabang[$x]', 
										'$tpk_penerima_cabang[$x]',
										'$tpk_cabang[$x]')";

		mysqli_query($conn, $query);
	}

	// Mencari banyak barang SN
	$barang_option_sn = mysqli_query($conn, "select tpk_barang_option_sn from transfer_produk_keluar where tpk_ref = '" . $transfer_ref . "' && tpk_barang_option_sn > 0 && tpk_cabang = '" . $transfer_cabang . "' ");
	$barang_option_sn = mysqli_num_rows($barang_option_sn);



	// Mencari ID SN
	if ($barang_option_sn > 0) {
		$barang_sn_id = query("SELECT * FROM transfer_produk_keluar WHERE tpk_ref = $transfer_ref && tpk_barang_option_sn > 0 && tpk_cabang = $transfer_cabang ");

		// var_dump($barang_sn_id); die();
		foreach ($barang_sn_id as $row) :
			$barang_sn_id = $row['tpk_barang_sn_id'];

			$barang = count($barang_sn_id);
			for ($i = 0; $i < $barang; $i++) {
				$query5 = "UPDATE barang_sn SET 
						barang_sn_status     = 5
						WHERE barang_sn_id = $barang_sn_id
				";
			}
			mysqli_query($conn, $query5);
		endforeach;
	}

	mysqli_query($conn, "DELETE FROM keranjang_transfer WHERE keranjang_transfer_id_kasir = $transfer_user");
	mysqli_query($conn, "DELETE FROM transfer_select_cabang WHERE tsc_user_id = $transfer_user && tsc_cabang = $transfer_id_tipe_keluar");

	return mysqli_affected_rows($conn);
}

function hapusTransferStockCabang($id)
{
	global $conn;

	mysqli_query($conn, "DELETE FROM transfer WHERE transfer_ref = $id");
	mysqli_query($conn, "DELETE FROM transfer_produk_keluar WHERE tpk_ref = $id");

	return mysqli_affected_rows($conn);
}

function prosesKonfirmasiTransfer($data)
{
	global $conn;

	// Data Input Tabel Transfer
	$transfer_status 					= htmlspecialchars($data['transfer_status']);
	$transfer_terima_date				= date("Y-m-d");
	$transfer_terima_date_time			= date("d F Y g:i:s a");
	$transfer_ref 						= $data['transfer_ref'];
	$transfer_user_penerima 			= $data['transfer_user_penerima'];
	$transfer_penerima_cabang			= $data['transfer_penerima_cabang'];
	// Status Trnsfer Stock Antar Cabang
	// 1. Proses Kirim
	// 2. Selesai
	// 3. Dibatalkan 

	// ============================================================================= //
	// Data Input Tabel transfer_produk_masuk
	$tpm_kode_slug			= $data['tpm_kode_slug'];
	$tpm_qty				= $data['tpm_qty'];
	$tpm_ref				= $data['tpm_ref'];
	$tpm_date				= $data['tpm_date'];
	$tpm_date_time 			= $data['tpm_date_time'];
	$tpm_barang_option_sn   = $data['tpm_barang_option_sn'];
	$tpm_barang_sn_id       = $data['tpm_barang_sn_id'];
	$tpm_barang_sn_desc     = $data['tpm_barang_sn_desc'];
	$tpm_user           	= $data['tpm_user'];
	$tpm_pengirim_cabang    = $data['tpm_pengirim_cabang'];
	$tpm_penerima_cabang    = $data['tpm_penerima_cabang'];
	$tpm_cabang        		= $data['tpm_cabang'];


	$jumlah = count($tpm_user);

	// Mencari banyak barang SN di tabel transfer_produk_keluar
	$barang_option_sn_produk_keluar = mysqli_query($conn, "select tpk_barang_option_sn from transfer_produk_keluar where tpk_ref = '" . $transfer_ref . "' && tpk_barang_option_sn > 0 && tpk_penerima_cabang = '" . $transfer_penerima_cabang . "' ");
	$barang_option_sn_produk_keluar = mysqli_num_rows($barang_option_sn_produk_keluar);


	if ($barang_option_sn_produk_keluar > 0) {
		if ($transfer_status > 0) {
			// query update data
			$query = "UPDATE transfer SET 
						transfer_terima_date   		= '$transfer_terima_date',
						transfer_terima_date_time   = '$transfer_terima_date_time',
						transfer_status 			= 2,
						transfer_user_penerima      = '$transfer_user_penerima'
						WHERE transfer_ref 			= $transfer_ref
						";
			mysqli_query($conn, $query);

			for ($x = 0; $x < $jumlah; $x++) {
				$query1 = "INSERT INTO transfer_produk_masuk VALUES ('', 
											'$tpm_kode_slug[$x]', 
											'$tpm_qty[$x]', 
											'$tpm_ref[$x]', 
											'$tpm_date[$x]', 
											'$tpm_date_time[$x]', 
											'$tpm_barang_option_sn[$x]', 
											'$tpm_barang_sn_id[$x]', 
											'$tpm_barang_sn_desc[$x]', 
											'$tpm_user[$x]', 
											'$tpm_pengirim_cabang[$x]', 
											'$tpm_penerima_cabang[$x]', 
											'$tpm_cabang[$x]')";
				mysqli_query($conn, $query1);
			}

			// Mencari banyak barang SN
			$barang_option_sn = mysqli_query($conn, "select tpm_barang_option_sn from transfer_produk_masuk where tpm_ref = '" . $transfer_ref . "' && tpm_barang_option_sn > 0 && tpm_penerima_cabang = '" . $transfer_penerima_cabang . "' ");
			$barang_option_sn = mysqli_num_rows($barang_option_sn);


			// Mencari ID SN
			if ($barang_option_sn > 0) {
				$barang_sn_id = query("SELECT * FROM transfer_produk_masuk WHERE tpm_ref = $transfer_ref && tpm_barang_option_sn > 0 && tpm_penerima_cabang = $transfer_penerima_cabang ");

				// var_dump($barang_sn_id); die();
				foreach ($barang_sn_id as $row) :
					$barang_sn_id = $row['tpm_barang_sn_id'];

					$barang = count($barang_sn_id);
					for ($i = 0; $i < $barang; $i++) {
						$query5 = "UPDATE barang_sn SET 
								barang_sn_status     = 1,
								barang_sn_cabang     = '$transfer_penerima_cabang'
								WHERE barang_sn_id = $barang_sn_id
						";
					}
					mysqli_query($conn, $query5);
				endforeach;
			}
		} else {
			// query update data
			$query = "UPDATE transfer SET 
							transfer_terima_date   		= '$transfer_terima_date',
							transfer_terima_date_time   = '$transfer_terima_date_time',
							transfer_status 			= 0,
							transfer_user_penerima      = '$transfer_user_penerima'
							WHERE transfer_ref 			= $transfer_ref
							";
			mysqli_query($conn, $query);
		}
	} else {
		if ($transfer_status > 0) {
			// query update data
			$query = "UPDATE transfer SET 
						transfer_terima_date   		= '$transfer_terima_date',
						transfer_terima_date_time   = '$transfer_terima_date_time',
						transfer_status 			= 2,
						transfer_user_penerima      = '$transfer_user_penerima'
						WHERE transfer_ref 			= $transfer_ref
						";
			mysqli_query($conn, $query);

			for ($x = 0; $x < $jumlah; $x++) {
				$query1 = "INSERT INTO transfer_produk_masuk VALUES ('', 
											'$tpm_kode_slug[$x]', 
											'$tpm_qty[$x]', 
											'$tpm_ref[$x]', 
											'$tpm_date[$x]', 
											'$tpm_date_time[$x]', 
											'$tpm_barang_option_sn[$x]', 
											'$tpm_barang_sn_id[$x]', 
											'$tpm_barang_sn_desc[$x]', 
											'$tpm_user[$x]', 
											'$tpm_pengirim_cabang[$x]', 
											'$tpm_penerima_cabang[$x]', 
											'$tpm_cabang[$x]')";
				mysqli_query($conn, $query1);
			}
		} else {
			// query update data
			$query = "UPDATE transfer SET 
							transfer_terima_date   		= '$transfer_terima_date',
							transfer_terima_date_time   = '$transfer_terima_date_time',
							transfer_status 			= 0,
							transfer_user_penerima      = '$transfer_user_penerima'
							WHERE transfer_ref 			= $transfer_ref
							";
			mysqli_query($conn, $query);
		}
	}

	return mysqli_affected_rows($conn);
}


// ====================================== Laba Bersih ===================================== //
function editLabaBersih($data)
{
	global $conn;
	$id = $data["lb_id"];

	// ambil data dari tiap elemen dalam form
	$lb_pendapatan_lain      			= $data["lb_pendapatan_lain"];
	$lb_pengeluaran_gaji      			= $data["lb_pengeluaran_gaji"];
	$lb_pengeluaran_listrik 			= $data["lb_pengeluaran_listrik"];
	$lb_pengeluaran_tlpn_internet     	= $data["lb_pengeluaran_tlpn_internet"];
	$lb_pengeluaran_perlengkapan_toko   = $data["lb_pengeluaran_perlengkapan_toko"];
	$lb_pengeluaran_biaya_penyusutan    = $data["lb_pengeluaran_biaya_penyusutan"];
	$lb_pengeluaran_bensin     			= $data["lb_pengeluaran_bensin"];
	$lb_pengeluaran_tak_terduga 		= $data["lb_pengeluaran_tak_terduga"];
	$lb_pengeluaran_lain        		= $data["lb_pengeluaran_lain"];
	$lb_cabang 							= $data["lb_cabang"];

	// query update data
	$query = "UPDATE laba_bersih SET 
				lb_pendapatan_lain       			= '$lb_pendapatan_lain',
				lb_pengeluaran_gaji       			= '$lb_pengeluaran_gaji',
				lb_pengeluaran_listrik      		= '$lb_pengeluaran_listrik',
				lb_pengeluaran_tlpn_internet      	= '$lb_pengeluaran_tlpn_internet',
				lb_pengeluaran_perlengkapan_toko    = '$lb_pengeluaran_perlengkapan_toko',
				lb_pengeluaran_biaya_penyusutan     = '$lb_pengeluaran_biaya_penyusutan',
				lb_pengeluaran_bensin  				= '$lb_pengeluaran_bensin',
				lb_pengeluaran_tak_terduga  		= '$lb_pengeluaran_tak_terduga',
				lb_pengeluaran_lain 				= '$lb_pengeluaran_lain'
				WHERE lb_id   = $id && lb_cabang = $lb_cabang
				";

	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}

// ============================= Stock Opname Keseluruhan ================================= //
function tambahStockOpname($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form
	$stock_opname_date_create 		= date("Y-m-d");
	$stock_opname_datetime_create 	= date("d F Y g:i:s a");
	$stock_opname_date_proses 		= htmlspecialchars($data['stock_opname_date_proses']);
	$stock_opname_user_create 		= htmlspecialchars($data['stock_opname_user_create']);
	$stock_opname_user_eksekusi 	= htmlspecialchars($data['stock_opname_user_eksekusi']);
	// Status 0 = Proses || 1 = selesai
	$stock_opname_status 			= 0;
	$stock_opname_tipe 				= htmlspecialchars($data['stock_opname_tipe']);
	$stock_opname_cabang 			= htmlspecialchars($data['stock_opname_cabang']);

	// query insert data
	$query = "INSERT INTO stock_opname VALUES ('', '$stock_opname_date_create', '$stock_opname_datetime_create', '$stock_opname_date_proses', '$stock_opname_user_create', '$stock_opname_user_eksekusi', '$stock_opname_status', '', '', '', '$stock_opname_tipe', '$stock_opname_cabang')";
	mysqli_query($conn, $query);

	return mysqli_affected_rows($conn);
}

function hapusStockOpname($id, $sessionCabang)
{
	global $conn;

	$stock_opname_hasil_count = mysqli_query($conn, "SELECT * FROM stock_opname_hasil WHERE soh_stock_opname_id = $id && soh_barang_cabang = $sessionCabang");
	$stock_opname_hasil_count = mysqli_num_rows($stock_opname_hasil_count);


	if ($stock_opname_hasil_count > 0) {
		mysqli_query($conn, "DELETE FROM stock_opname_hasil WHERE soh_stock_opname_id = $id && soh_barang_cabang = $sessionCabang");
	}

	mysqli_query($conn, "DELETE FROM stock_opname WHERE stock_opname_id = $id");

	return mysqli_affected_rows($conn);
}

function tambahStockOpnamePerProduk($data)
{
	global $conn;
	// ambil data dari tiap elemen dalam form

	$soh_stock_opname_id 		= htmlspecialchars($data['soh_stock_opname_id']);
	$soh_barang_kode 			= htmlspecialchars($data['soh_barang_kode']);
	$soh_stock_fisik 			= htmlspecialchars($data['soh_stock_fisik']);
	$soh_note 					= htmlspecialchars($data['soh_note']);
	$soh_date 					= date("Y-m-d");
	$soh_datetime 				= date("d F Y g:i:s a");
	$soh_tipe 					= htmlspecialchars($data['soh_tipe']);
	$soh_user 					= htmlspecialchars($data['soh_user']);
	$soh_barang_cabang 			= htmlspecialchars($data['soh_barang_cabang']);

	$soh_barang_kode_slug       = str_replace(" ", "-", $soh_barang_kode);

	$barang         = mysqli_query($conn, "SELECT barang_id, barang_stock FROM barang WHERE barang_cabang = $soh_barang_cabang && barang_status = 1 && barang_kode_slug = '" . $soh_barang_kode_slug . "' ");
	$barang         = mysqli_fetch_array($barang);
	$barang_id      = $barang['barang_id'];
	$barang_stock   = $barang['barang_stock'];
	$soh_selisih            	= $soh_stock_fisik - $barang_stock;

	if ($barang_id == null) {
		echo '
            <script>
                alert("Kode Barang/Barcode ' . $soh_barang_kode . ' TIDAK ADA di DATA Barang !! Silahkan Sesuaikan & Cek Kembali dari penulisan Huruf Besar, Kecil, Spasi beserta KODE HARUS SESUAI !!");
                  document.location.href = "";
            </script>
        ';
		exit();
	}

	// query insert data
	$query = "INSERT INTO stock_opname_hasil VALUES ('', 
            '$soh_stock_opname_id',
            '$barang_id', 
            '$soh_barang_kode', 
            '$barang_stock', 
            '$soh_stock_fisik',
            '$soh_selisih', 
            '$soh_note',
            '$soh_date',
            '$soh_datetime',
            '$soh_tipe',
            '$soh_user',
            '$soh_barang_cabang')";
	mysqli_query($conn, $query);

	return mysqli_affected_rows($conn);
}

function editStockOpname($data)
{
	global $conn;
	$id = $data["stock_opname_id"];

	// ambil data dari tiap elemen dalam form
	$stock_opname_user_upload 		= htmlspecialchars($data['stock_opname_user_upload']);
	$stock_opname_status 			= htmlspecialchars($data['stock_opname_status']);
	$stock_opname_date_upload 		= date("Y-m-d");
	$stock_opname_datetime_upload 	= date("d F Y g:i:s a");
	$stock_opname_cabang			= htmlspecialchars($data['stock_opname_cabang']);

	$query = "UPDATE stock_opname SET 
            stock_opname_status           = '$stock_opname_status',
            stock_opname_user_upload      = '$stock_opname_user_upload',
            stock_opname_date_upload      = '$stock_opname_date_upload',
            stock_opname_datetime_upload  = '$stock_opname_datetime_upload'
            WHERE stock_opname_id         = $id && stock_opname_cabang = $stock_opname_cabang;
            ";
	mysqli_query($conn, $query);
	return mysqli_affected_rows($conn);
}


function getRandomColor()
{
	$r = rand(0, 255);
	$g = rand(0, 255);
	$b = rand(0, 255);
	return "rgba($r, $g, $b,";
}

function formatDate($date)
{
	return date('d-m-Y', strtotime($date));
}