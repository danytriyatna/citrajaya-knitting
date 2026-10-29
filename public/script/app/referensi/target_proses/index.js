$(document).ready(function () {
    const inpData         = $('#data_id');
    const inpKode         = $('#kode');
    const inpNama         = $('#nama');
    const inpTargetHarian = $('#target_harian');
    const inpSatuan       = $('#satuan');
    const inpWarna        = $('#warna_badge');
    const inpPicker       = $('#warna_badge_picker');
    const inpKeterangan   = $('#keterangan');
    const inpSeq          = $('#seq');
    const isModal         = $("#modal-form-target-proses");
    const modalTitle      = $("#modal-title-text");

    // Sync color picker with text input
    inpPicker.on('input change', function() {
        inpWarna.val($(this).val());
    });
    inpWarna.on('input change', function() {
        if (/^#[0-9A-F]{6}$/i.test($(this).val())) {
            inpPicker.val($(this).val());
        }
    });

    const buttonRowAction = function(cell) {
        const fmBtnDelete = "<button class='btn btn-danger btn-xs ms-1' type='button' title='delete'><i class='fa fa-trash'></i></button>";
        const fmBtnEdit   = "<button class='btn btn-warning btn-xs' type='button' title='edit'><i class='fa fa-edit'></i></button>";
        return fmBtnEdit + " " + fmBtnDelete;
    };

    const badgeFormatter = function(cell) {
        const row = cell.getRow().getData();
        const color = row.warna_badge || '#007bff';
        const kode = cell.getValue() || '-';
        return `<span class="badge px-2 py-1 text-white font-weight-bold" style="background-color: ${color}; font-size: 13px;">${kode}</span>`;
    };

    const dtList = new Tabulator("#dt-list", {
        columns: [
            {
                headerSort: false,  
                title: 'Aksi', 
                formatter: buttonRowAction,
                width: 90, 
                hozAlign: "center", 
                cssClass: "text-center",
                cellClick: function(e, cell) {
                    const row = cell.getRow();
                    const data = row.getData();
                    const target = $(e.target).closest('button');

                    if (target.attr('title') === 'delete') {
                        Swal.fire({
                            title: 'Konfirmasi Hapus',
                            text: `Hapus proses '${data.nama}' (${data.kode})?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = baseUrl + "/master-data/target-proses/delete/" + data.id_encrypted;
                            }
                        });
                    } else if (target.attr('title') === 'edit') {
                        inpData.val(data.id_encrypted);
                        inpKode.val(data.kode);
                        inpNama.val(data.nama);
                        inpTargetHarian.val(data.target_harian > 0 ? parseFloat(data.target_harian) : '');
                        inpSatuan.val(data.satuan || 'Pcs');
                        inpWarna.val(data.warna_badge || '#007bff');
                        inpPicker.val(data.warna_badge || '#007bff');
                        inpKeterangan.val(data.keterangan || '');
                        inpSeq.val(data.seq || 1);

                        modalTitle.text("Edit Proses Target Harian");
                        isModal.modal("show");
                    }   
                }
            },
            {
                title: "Kode Singkatan", 
                field: "kode", 
                width: 140,
                hozAlign: "center",
                formatter: badgeFormatter,
                headerSort: true
            },
            {
                title: "Nama Pekerjaan / Proses", 
                field: "nama", 
                headerSort: true,
                cssClass: "font-weight-bold"
            },
            {
                title: "Standar Target Harian", 
                field: "target_harian", 
                width: 170,
                hozAlign: "right",
                headerSort: true,
                formatter: function(cell) {
                    const val = parseFloat(cell.getValue()) || 0;
                    return val > 0 ? `<span class="font-weight-bold text-primary">${val.toLocaleString()}</span>` : '<span class="text-muted">-</span>';
                }
            },
            {
                title: "Satuan", 
                field: "satuan", 
                width: 100,
                hozAlign: "center",
                headerSort: false
            },
            {
                title: "Keterangan", 
                field: "keterangan", 
                headerSort: false,
                formatter: function(cell) {
                    return cell.getValue() || '<span class="text-muted">-</span>';
                }
            },
            {
                title: "Urutan", 
                field: "seq", 
                width: 90, 
                hozAlign: "center",
                headerSort: true
            }
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: baseUrl + "/master-data/target-proses/list",
        ajaxConfig: "POST",
        sortMode: "remote",
        filterMode: "remote",
        placeholder: "Tidak ada data proses target harian",
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
        },
        ajaxResponse: function (url, params, response) {
            return response;
        },
        pagination: true,
        paginationMode: "remote",
        paginationSize: 25,
        paginationButtonCount: 5,
        selectable: false
    });

    $("#btn-add").click(function () {
        inpData.val('');
        inpKode.val('');
        inpNama.val('');
        inpTargetHarian.val('');
        inpSatuan.val('Pcs');
        inpWarna.val('#007bff');
        inpPicker.val('#007bff');
        inpKeterangan.val('');
        inpSeq.val(1);

        modalTitle.text("Tambah Proses Target Harian");
        isModal.modal("show");
    });

    $("#form-target-proses").submit(function (e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const btnSave = $("#btn-save");
        btnSave.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: baseUrl + "/master-data/target-proses/simpan",
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                btnSave.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan');
                if (res.status) {
                    isModal.modal("hide");
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    dtList.replaceData();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Terjadi kesalahan saat menyimpan data.'
                    });
                }
            },
            error: function (xhr) {
                btnSave.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan');
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat terhubung ke server.'
                });
            }
        });
    });

    let searchThread = null;
    $("#tb-search").keyup(function () {
        const val = $(this).val();
        clearTimeout(searchThread);
        searchThread = setTimeout(function () {
            dtList.setFilter("nama", "like", val);
        }, 400);
    });
});
