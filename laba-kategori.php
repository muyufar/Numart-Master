<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';

if ($levelLogin != "admin" && $levelLogin != "super admin") {
  echo "
    <script>
      document.location.href = 'bo';
    </script>
  ";
}

// Get list of cabang for filter and display
$listCabang = query("SELECT * FROM toko ORDER BY toko_nama");
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-9">
          <h1>Data Kategori Laba</h1>
        </div>
        <div class="col-sm-3">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="bo">Home</a></li>
            <li class="breadcrumb-item active">Kategori Laba</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <section class="content">
    <div class="container-fluid">
      <div class="card card-primary">
        <div class="card-header d-flex align-content-center">
          <h3 class="card-title">Data Kategori Laba</h3>
          <button
            id="btn-add-modal"
            class="btn btn-success btn-sm ml-auto d-flex justify-content-around align-content-center"
            data-toggle="modal"
            data-target="#modal-add"
            style="gap: 0.2rem;">
            <i class="bi bi-plus"></i>
            <span>Tambah</span>
          </button>
        </div>
        <div class="card-body">
          <!-- Filter dan Pencarian -->
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="form-group">
                <label>Filter Cabang</label>
                <select class="form-control" id="filter-cabang">
                  <option value="">Semua Cabang</option>
                  <option value="0">PCNU (Default)</option>
                  <?php 
                  foreach ($listCabang as $c) : 
                  ?>
                    <option value="<?= $c['toko_cabang']; ?>"
                      <?= (isset($_SESSION['user_cabang']) && $_SESSION['user_cabang'] == $c['toko_cabang']) ? ' selected' : '' ?>>
                      <?= $c['toko_nama']; ?> - <?= $c['toko_kota']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Filter Kategori</label>
                <select class="form-control" id="filter-kategori">
                  <option value="">Semua Kategori</option>
                  <option value="aktiva">Aktiva</option>
                  <option value="pasiva">Pasiva</option>
                  <option value="modal">Modal</option>
                  <option value="pendapatan">Pendapatan</option>
                  <option value="beban">Beban</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Filter Tipe Akun</label>
                <select class="form-control" id="filter-tipe-akun">
                  <option value="">Semua Tipe</option>
                  <option value="debit">Debit</option>
                  <option value="kredit">Kredit</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Cari</label>
                <input type="text" class="form-control" id="search-input" placeholder="Cari nama atau kode akun...">
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <button type="button" class="btn btn-primary btn-sm" id="btn-apply-filter">
                <i class="fas fa-filter"></i> Terapkan Filter
              </button>
              <button type="button" class="btn btn-secondary btn-sm" id="btn-reset-filter">
                <i class="fas fa-redo"></i> Reset
              </button>
            </div>
          </div>
          
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead class="thead-default">
                <tr>
                  <th class="text-center" style="width: 50px;">No</th>
                  <th>Kode Akun</th>
                  <th>Nama Kategori</th>
                  <th>Kategori</th>
                  <th>Tipe Akun</th>
                  <th>Cabang</th>
                  <th class="text-right">Saldo</th>
                  <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="table-data"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modal-add" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-title">Tambah Kategori Laba</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-add">
          <input type="hidden" id="form-type" value="create">
          <input type="hidden" id="add-id" value="">

          <div class="form-group">
            <label for="add-name">Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" name="name" id="add-name" class="form-control" placeholder="Contoh: KAS, BIAYA LOGISTIK" required>
            <div class="invalid-feedback">
              Nama kategori harus diisi
            </div>
          </div>

          <div class="form-group">
            <label for="add-kode-akun">Kode Akun</label>
            <input type="text" name="kode_akun" id="add-kode-akun" class="form-control" placeholder="Contoh: 1-10001, 5-50001">
            <small class="form-text text-muted">Opsional: Kode akun untuk neraca</small>
          </div>

          <div class="form-group">
            <label for="add-kategori">Kategori <span class="text-danger">*</span></label>
            <select class="form-control" id="add-kategori" required>
              <option value="">Pilih Kategori</option>
              <option value="aktiva">Aktiva</option>
              <option value="pasiva">Pasiva</option>
              <option value="modal">Modal</option>
              <option value="pendapatan">Pendapatan</option>
              <option value="beban">Beban</option>
            </select>
            <div class="invalid-feedback">
              Kategori harus diisi
            </div>
          </div>

          <div class="form-group">
            <label for="add-tipe-akun">Tipe Akun <span class="text-danger">*</span></label>
            <select class="form-control" id="add-tipe-akun" required>
              <option value="">Pilih Tipe Akun</option>
              <option value="debit">Debit</option>
              <option value="kredit">Kredit</option>
            </select>
            <div class="invalid-feedback">
              Tipe akun harus diisi
            </div>
          </div>

          <div class="form-group">
            <label for="add-saldo">Saldo Awal</label>
            <input type="number" step="0.01" name="saldo" id="add-saldo" class="form-control" placeholder="0" value="0">
            <small class="form-text text-muted">Saldo awal untuk neraca</small>
          </div>

          <div class="form-group">
            <label for="add-cabang-kategori">Cabang <span class="text-danger">*</span></label>
            <select class="form-control" id="add-cabang-kategori" required>
              <option value="">Pilih Cabang</option>
              <?php foreach ($listCabang as $c) : ?>
                <option value="<?= $c['toko_cabang']; ?>"
                  <?= $_SESSION['user_cabang'] == $c['toko_cabang'] ? ' selected' : '' ?>>
                  <?= $c['toko_nama']; ?> - <?= $c['toko_kota']; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Akun ini akan hanya tersedia untuk cabang yang dipilih</small>
            <div class="invalid-feedback">
              Cabang harus diisi
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-close" data-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="btn-add">Simpan</button>
      </div>
    </div>
  </div>
</div>

<script src="./dist/js/utils.js"></script>
<script>
  const base_url = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '')
  const cabangList = <?php echo json_encode($listCabang); ?>;

  const deleteKategori = (id) => {
    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!"
    }).then((result) => {
      if (result.value) {
        $.ajax({
          url: `${base_url}/api/laba-kategori.php?id=${id}`,
          type: 'DELETE',
          success: function(response) {
            if (response.success) {
              Swal.fire({
                title: "Deleted!",
                text: response.message || "Data berhasil dihapus",
                icon: "success"
              });
              getData();
            } else {
              Swal.fire({
                title: "Error!",
                text: response.message || "Gagal menghapus data",
                icon: "error"
              });
            }
          },
          error: function(xhr, status, error) {
            let errorMsg = "Terjadi kesalahan";
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) {
              errorMsg = xhr.responseText || errorMsg;
            }
            Swal.fire({
              title: "Error!",
              text: errorMsg,
              icon: "error"
            });
          }
        });
      }
    });
  }

  const editKategori = (item) => {
    $('#form-type').val('edit')
    $('#modal-title').text('Edit Kategori Laba')
    $('#add-id').val(item.id)
    $('#add-name').val(item.name)
    $('#add-kode-akun').val(item.kode_akun || '')
    $('#add-kategori').val(item.kategori)
    $('#add-tipe-akun').val(item.tipe_akun)
    $('#add-saldo').val(item.saldo || 0)
    $('#add-cabang-kategori').val(item.cabang || '')
    $('#modal-add').modal('show')
  }

  const getData = () => {
    // Get filter values
    const filterCabang = $('#filter-cabang').val() || '';
    const filterKategori = $('#filter-kategori').val() || '';
    const filterTipeAkun = $('#filter-tipe-akun').val() || '';
    const search = $('#search-input').val() || '';
    
    // Build query parameters
    const params = new URLSearchParams();
    
    // IMPORTANT: Only send cabang parameter if explicitly selected
    // If "Semua Cabang" is selected (empty string), don't send cabang parameter
    // This allows API to return all accounts (including cabang 0 and NULL)
    // Note: filterCabang can be '0' (string) for PCNU, which is a valid selection
    if (filterCabang !== '') {
      // User explicitly selected a cabang (including 0 for PCNU)
      params.append('cabang', filterCabang);
    }
    // If filterCabang is empty (Semua Cabang), don't send cabang parameter
    // This will make API return all accounts without cabang filter
    
    if (filterKategori) params.append('kategori', filterKategori);
    if (filterTipeAkun) params.append('tipe_akun', filterTipeAkun);
    if (search) params.append('search', search);
    
    const queryString = params.toString();
    const url = base_url + '/api/laba-kategori.php' + (queryString ? '?' + queryString : '');
    
    $.ajax({
      url: url,
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
      dataType: 'json',
      beforeSend: () => {
        $('#table-data').html('<tr><td class="text-center" colspan="8">Loading...</td></tr>')
      },
      success: (res) => {
        let html = ''
        if (res.success && res.data && res.data.length > 0) {
          res.data.forEach((item, index) => {
            const kategoriBadge = {
              'aktiva': 'badge-info',
              'pasiva': 'badge-warning',
              'modal': 'badge-success',
              'pendapatan': 'badge-primary',
              'beban': 'badge-danger'
            }
            const tipeBadge = {
              'debit': 'badge-primary',
              'kredit': 'badge-success'
            }
            // Get cabang name
            let cabangName = '-';
            if (item.cabang === 0 || item.cabang === '0') {
              cabangName = 'PCNU (Default)';
            } else if (item.cabang) {
              // Try to get cabang name from list
              const cabangItem = cabangList.find(c => c.toko_cabang == item.cabang);
              if (cabangItem) {
                cabangName = cabangItem.toko_nama + ' - ' + cabangItem.toko_kota;
              } else {
                cabangName = 'Cabang ' + item.cabang;
              }
            }
            
            // Format saldo
            const saldo = parseFloat(item.saldo || 0)
            const saldoFormatted = saldo.toLocaleString('id-ID', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            })
            const saldoClass = saldo >= 0 ? 'text-success' : 'text-danger'
            
            html += `
              <tr>
                <td class="text-center">${index + 1}</td>
                <td>${item.kode_akun || '-'}</td>
                <td>${item.name}</td>
                <td><span class="badge ${kategoriBadge[item.kategori] || 'badge-secondary'}">${item.kategori ? item.kategori.toUpperCase() : '-'}</span></td>
                <td><span class="badge ${tipeBadge[item.tipe_akun] || 'badge-secondary'}">${item.tipe_akun ? item.tipe_akun.toUpperCase() : '-'}</span></td>
                <td>${cabangName}</td>
                <td class="text-right ${saldoClass}"><strong>Rp ${saldoFormatted}</strong></td>
                <td class="text-center">
                  <button class="btn btn-warning btn-sm" onclick='editKategori(${JSON.stringify(item)})'><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-danger btn-sm" onclick="deleteKategori('${item.id}')"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
            `
          })
        } else {
          html = '<tr><td class="text-center" colspan="8">Tidak ada data</td></tr>'
        }
        $('#table-data').html(html)
      },
      error: (err) => {
        console.log('Error loading data:', err)
        let errorMsg = 'Terjadi kesalahan saat memuat data'
        try {
          if (err.responseJSON && err.responseJSON.message) {
            errorMsg = err.responseJSON.message
          } else if (err.responseText) {
            const response = JSON.parse(err.responseText)
            errorMsg = response.message || errorMsg
          }
        } catch (e) {
          console.log('Error parsing response:', e)
        }
        $('#table-data').html(`<tr><td class="text-center" colspan="8">${errorMsg}</td></tr>`)
      }
    })
  }

  $('#btn-add-modal').click(() => {
    $('#form-type').val('create')
    $('#modal-title').text('Tambah Kategori Laba')
    $('#form-add')[0].reset()
    $('#add-id').val('')
    $('#add-saldo').val('0')
    $('#add-name').removeClass('is-invalid')
    $('#add-kategori').removeClass('is-invalid')
    $('#add-tipe-akun').removeClass('is-invalid')
  })

  // Apply filter
  $('#btn-apply-filter').on('click', function() {
    getData()
  })

  // Reset filter
  $('#btn-reset-filter').on('click', function() {
    $('#filter-cabang').val('')
    $('#filter-kategori').val('')
    $('#filter-tipe-akun').val('')
    $('#search-input').val('')
    getData()
  })

  // Search on enter key
  $('#search-input').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
      getData()
    }
  })

  // Auto search with delay (debounce)
  let searchTimeout
  $('#search-input').on('keyup', function() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      getData()
    }, 500) // Wait 500ms after user stops typing
  })

  // Filter change triggers reload
  $('#filter-cabang, #filter-kategori, #filter-tipe-akun').on('change', function() {
    getData()
  })

  $(document).ready(function() {
    getData()

    // Button click triggers form submission
    $('#btn-add').on('click', function() {
      const name = $('#add-name')
      const kategori = $('#add-kategori')
      const tipe_akun = $('#add-tipe-akun')

      // Reset validation
      name.removeClass('is-invalid')
      kategori.removeClass('is-invalid')
      tipe_akun.removeClass('is-invalid')

      // Validation
      let isValid = true
      if (!name.val() || name.val().trim() === '') {
        name.addClass('is-invalid')
        isValid = false
      }
      if (!kategori.val() || kategori.val() === '') {
        kategori.addClass('is-invalid')
        isValid = false
      }
      if (!tipe_akun.val() || tipe_akun.val() === '') {
        tipe_akun.addClass('is-invalid')
        isValid = false
      }

      if (!isValid) {
        alert('Periksa kembali isian, field yang bertanda * wajib diisi')
        return
      }

      const cabangKategori = $('#add-cabang-kategori')
      if (!cabangKategori.val() || cabangKategori.val() === '') {
        cabangKategori.addClass('is-invalid')
        isValid = false
      }

      if (!isValid) {
        alert('Periksa kembali isian, field yang bertanda * wajib diisi')
        return
      }

      const formData = {
        name: name.val().trim(),
        kode_akun: $('#add-kode-akun').val().trim(),
        kategori: kategori.val(),
        tipe_akun: tipe_akun.val(),
        saldo: parseFloat($('#add-saldo').val()) || 0,
        cabang: cabangKategori.val()
      }

      if ($('#form-type').val() == 'create') {
        return createKategori(formData)
      } else if ($('#form-type').val() == 'edit') {
        formData.id = $('#add-id').val()
        return updateKategori(formData)
      } else {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Terjadi kesalahan - tipe pengiriman tidak ditemukan !",
        });
      }
    })

    $('#btn-close').on('click', function() {
      $('#add-name').removeClass('is-invalid')
      $('#add-kategori').removeClass('is-invalid')
      $('#add-tipe-akun').removeClass('is-invalid')
      $('#form-add')[0].reset()
      $('#btn-add').prop('disabled', false).html('Simpan')
    })
  })

  function createKategori(formData) {
    $.ajax({
      url: base_url + '/api/laba-kategori.php',
      method: 'POST',
      data: JSON.stringify(formData),
      contentType: 'application/json',
      headers: {
        'Accept': 'application/json',
      },
      dataType: 'json',
      beforeSend: () => {
        $('#btn-add').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...')
      },
      success: (res) => {
        console.log(res)
        if (res.success) {
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: res.message || "Data berhasil disimpan",
          });
          getData()
          $('#add-name').removeClass('is-invalid')
          $('#add-kategori').removeClass('is-invalid')
          $('#add-tipe-akun').removeClass('is-invalid')
          $('#form-add')[0].reset()
          $('#btn-close').click()
        } else {
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: res.message || 'Terjadi Kesalahan'
          });
        }
        $('#btn-add').prop('disabled', false).html('Simpan')
      },
      error: (err) => {
        console.log('Error:', err)
        $('#btn-add').prop('disabled', false).html('Simpan')
        let errorMsg = "Terjadi kesalahan";
        let errorDetails = "";
        try {
          const response = JSON.parse(err.responseText);
          errorMsg = response.message || errorMsg;
          if (response.errors && Array.isArray(response.errors)) {
            errorDetails = "\n\nDetail:\n" + response.errors.join("\n");
          }
        } catch (e) {
          errorMsg = err.responseText || errorMsg;
        }
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: errorMsg + errorDetails
        });
      }
    });
  }

  function updateKategori(formData) {
    $.ajax({
      url: base_url + '/api/laba-kategori.php',
      method: 'PUT',
      data: JSON.stringify(formData),
      contentType: 'application/json',
      headers: {
        'Accept': 'application/json',
      },
      dataType: 'json',
      beforeSend: () => {
        $('#btn-add').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...')
      },
      success: (res) => {
        console.log(res)
        if (res.success) {
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: res.message || "Data berhasil diupdate",
          });
          getData()
          $('#add-name').removeClass('is-invalid')
          $('#add-kategori').removeClass('is-invalid')
          $('#add-tipe-akun').removeClass('is-invalid')
          $('#form-add')[0].reset()
          $('#btn-close').click()
        } else {
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: res.message || 'Terjadi Kesalahan'
          });
        }
        $('#btn-add').prop('disabled', false).html('Simpan')
      },
      error: (err) => {
        console.log('Error:', err)
        $('#btn-add').prop('disabled', false).html('Simpan')
        let errorMsg = "Terjadi kesalahan";
        let errorDetails = "";
        try {
          const response = JSON.parse(err.responseText);
          errorMsg = response.message || errorMsg;
          if (response.errors && Array.isArray(response.errors)) {
            errorDetails = "\n\nDetail:\n" + response.errors.join("\n");
          }
        } catch (e) {
          errorMsg = err.responseText || errorMsg;
        }
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: errorMsg + errorDetails
        });
      }
    });
  }
</script>

<?php include '_footer.php'; ?>