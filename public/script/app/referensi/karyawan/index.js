$(document).ready(function () {
    $("[data-politespace]").politespace();
    let inpData         = $('#data_id');
    let inpNip          = $('#nip');
    let inpNama         = $('#nama_konsumen');
    let inpEmail        = $('#email');
    let inpAlamat       = $('#alamat');
    let inpNoHP         = $('#no_hp');
    let inpTempatLahir  = $('#tempat_lahir');
    let inpTglLahir     = $('#tgl_lahir');

    let inpPosisi        = $('#posisi');
    let selectKategoriPosisi = $('#kategori_posisi');
    let selectShift      = $('#id_shift');
    let inpTglBergabung  = $('#tgl_bergabung');
    let inpJenisKelamin  = $('#jenis_kelamin');
    let inpBank          = $('#nama_bank');
    let inpRekening      = $('#no_rekening');
    let inpUpahHarian    = $('#upah_harian');
    let inpUpahLembur    = $('#upah_lembur');
    let inpUpahLemburWe  = $('#upah_lembur_we');
    let inpUpahPerjam    = $('#upah_jam');
    let inpPremiKehadiran= $('#premi_kehadiran');
    let selectTipe       = $('#tipe');
    let selectCMT        = $('#id_cmt');
    let selectPerusahaan = $('#id_perusahaan');
    let divCMT           = $('#div-cmt');

    const fileKaryawan     = $('#fileKaryawan');
    const fileKaryawanOld  = $('#fileKaryawanOld');
    const linkFileKaryawan = $('#linkFileKaryawan');

    let isModal       = $("#modal-form-add-po");
    let modalKontrak  = $("#modal-kelola-kontrak");
    let currentKontrakRow = null;

    $('#modal-form-add-po').on('hidden.bs.modal', function () {
        var $modal = $(this);
        $modal.find('input[type="text"], input[type="number"], input[type="email"], input[type="date"], textarea').val('');
        $modal.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
        $modal.find('select').prop('selectedIndex', 0).trigger('change');
        $modal.find('input[type="file"]').val('');
    });

    let buttonRowAction = function(cell) {
        let fmBtnDelete = "<button class='btn btn-danger btn-xs open_form' type='button' title='delete'><i class='fa fa-trash' title='delete'></i></button>";
        let fmBtnEdit = "<button class='btn btn-warning btn-xs open_form' type='button' title='edit'><i class='fa fa-edit' title='edit'></i></button>";
        return fmBtnEdit + " " + fmBtnDelete;
    };

    $("#filter_perusahaan, #filter_tipe").on("change", function () {
        dtList.setPage(1);
    });

    function openEditKaryawan(data_row) {
        resetInput();

        inpData.val(data_row.id);
        inpNip.val(data_row.nip);
        inpNama.val(data_row.full_name);
        inpEmail.val(data_row.email);
        inpAlamat.val(data_row.alamat);
        inpNoHP.val(data_row.no_hp);
        inpPosisi.val(data_row.posisi);
        inpTempatLahir.val(data_row.tempat_lahir || '');
        inpTglLahir.val(data_row.tgl_lahir_raw || '');
        selectKategoriPosisi.val(data_row.kategori_posisi ? data_row.kategori_posisi : '1').trigger('change');
        selectShift.val(data_row.id_shift ? data_row.id_shift : '1').trigger('change');
        inpTglBergabung.val(data_row.tgl_bergabung);
        inpJenisKelamin.val(data_row.jenis_kelamin).trigger('change');
        selectTipe.val(data_row.type).trigger("change");
        selectPerusahaan.val(data_row.id_perusahaan).trigger("change");

        if (data_row.type == 1) {
            divCMT.addClass("d-none");
            selectCMT.val("").trigger("change");
            $('#section-kontrak-karyawan').removeClass('d-none');
            $('#form_tgl_mulai_kontrak').val(data_row.tgl_mulai_kontrak || '');
            $('#form_durasi_kontrak').val(data_row.durasi_kontrak_bulan || '3');
            updateFormTglSelesai();

            let st = parseInt(data_row.status_kontrak) || 0;
            let badgeEl = $('#form-kontrak-status-badge');
            if (st === 2) {
                badgeEl.attr('class', 'badge bg-success p-2').html('<i class="fa fa-check-circle me-1"></i> Ditandatangani');
            } else if (st === 1) {
                badgeEl.attr('class', 'badge bg-warning text-dark p-2').html('<i class="fa fa-file-word-o me-1"></i> Draf Terbit');
            } else {
                badgeEl.attr('class', 'badge bg-secondary p-2').html('<i class="fa fa-file-o me-1"></i> Belum Dibuat');
            }

            let p1Name = data_row.pihak1_nama || 'Eva Fiantina';
            let p1Role = data_row.pihak1_jabatan || 'HRD Citra Jaya Knitting';
            let p3Name = data_row.mengetahui_nama || 'Edwin Ferdiansyah';
            let p3Role = data_row.mengetahui_jabatan || 'Pimpinan CV. Citra Jaya Knitting';

            $('#form_ttd_pihak1_nama').text(p1Name).attr('title', p1Name);
            $('#form_ttd_pihak1_jabatan').text(p1Role);
            $('#form_ttd_mengetahui_nama').text(p3Name).attr('title', p3Name);
            $('#form_ttd_mengetahui_jabatan').text(p3Role);

            $('#form_ttd_pihak2_nama').text(data_row.full_name || '-');
            $('#form_ttd_pihak2_jabatan').text(data_row.posisi || 'Karyawan');

            let sig1 = data_row.ttd_pihak1_url ? { url: data_row.ttd_pihak1_url, tgl: data_row.ttd_pihak1_tgl } : null;
            let sig2 = data_row.ttd_pihak2_url ? { url: data_row.ttd_pihak2_url, tgl: data_row.ttd_pihak2_tgl } : null;
            let sig3 = data_row.ttd_mengetahui_url ? { url: data_row.ttd_mengetahui_url, tgl: data_row.ttd_mengetahui_tgl } : null;

            updateSingleSigBox('form', 1, sig1, p1Name, p1Role);
            updateSingleSigBox('form', 2, sig2, data_row.full_name || 'Karyawan', data_row.posisi || 'Karyawan');
            updateSingleSigBox('form', 3, sig3, p3Name, p3Role);

            if (data_row.file_kontrak_url) {
                $('#btn-form-view-signed-file').removeClass('d-none').attr('href', data_row.file_kontrak_url);
            } else {
                $('#btn-form-view-signed-file').addClass('d-none').attr('href', '#');
            }

            if (data_row.all_signed || st === 2) {
                $('#btn-form-generate-docx').removeClass('btn-primary').addClass('btn-success').html('<i class="fa fa-download me-1"></i> Unduh Dokumen Kontrak Sah (.docx)');
                $('#form-kontrak-action-info').html('<span class="text-success fw-bold"><i class="fa fa-check-circle me-1"></i> Seluruh pihak sudah bertandatangan digital. Berkas siap diunduh!</span>');
            } else {
                $('#btn-form-generate-docx').removeClass('btn-success').addClass('btn-primary').html('<i class="fa fa-download me-1"></i> Unduh Dokumen Kontrak (.docx)');
                $('#form-kontrak-action-info').text('Unduh berkas kontrak kerja Word (.docx) lengkap dengan tanda tangan digital para pihak.');
            }
        } else {
            divCMT.removeClass("d-none");
            selectCMT.val(data_row.id_operator).trigger("change");
            $('#section-kontrak-karyawan').addClass('d-none');
        }

        if (data_row.file_gambar) {
            fileKaryawanOld.val(data_row.gambar_id);
            linkFileKaryawan.removeClass("d-none");
            linkFileKaryawan.attr('src', data_row.file_gambar);
        }

        setTimeout(() => {
            inpBank.val(data_row.nama_bank).trigger('change');
            inpRekening.val(data_row.no_rekening).trigger('change');
            inpUpahHarian.val(data_row.upah_harian).trigger('change');
            inpUpahLembur.val(data_row.upah_lembur).trigger('change');
            inpUpahLemburWe.val(data_row.upah_lembur_we).trigger('change');
            inpUpahPerjam.val(data_row.upah_jam).trigger('change');
            inpPremiKehadiran.val(data_row.premi_kehadiran).trigger('change');
        }, 500);

        isModal.modal("show");
    }

    let dtList = new Tabulator("#dt-list", {
        columns: [
            {
                headerSort: false,  
                title: 'Aksi', 
                formatter: buttonRowAction,
                width: 75, hozAlign: "center", cssClass: "text-center",
                cellClick: function(e, cell) {
                    let row = cell.getRow();
                    let data_row = row.getData();
                    if (e.target.title === 'delete') {
                        if (confirm("Anda yakin akan menghapus data?")) {
                            window.location.replace(baseUrl + "/master-data/karyawan/delete/" + data_row.id);
                        }
                    } else if (e.target.title === 'edit') {
                        openEditKaryawan(data_row);
                    }   
                }
            },
            {
                title: "NIP", field: "nip", headerSort: false,
                width: 105
            },
            {
                title: "Nama", field: "full_name", headerSort: false,
                minWidth: 160
            },
            {
                title: "Posisi", field: "posisi", headerSort: false,
                width: 135
            },
            {
                title: "Kategori", field: "kategori_posisi_text", headerSort: false,
                width: 105, align: "center", cssClass: "text-center",
                formatter: function (cell) {
                    let val = cell.getValue();
                    if (val === 'Mesin') {
                        return "<span class='badge bg-info'>Mesin</span>";
                    } else if (val === 'Non Mesin') {
                        return "<span class='badge bg-secondary'>Non Mesin</span>";
                    } else if (val === 'Staff Khusus' || val === 'Staf Khusus') {
                        return "<span class='badge bg-warning text-dark'>Staff Khusus</span>";
                    }
                    return val || '-';
                }
            },
            {
                title: "Shift", field: "nama_shift", headerSort: false,
                width: 95, align: "center", cssClass: "text-center",
                formatter: function (cell) {
                    let val = cell.getValue();
                    if (!val || val.toUpperCase() === 'NORMAL') {
                        return "<span class='badge bg-light text-dark border'>Normal</span>";
                    } else if (val.toLowerCase().indexOf('pagi') !== -1 || val.indexOf('1') !== -1) {
                        return "<span class='badge bg-info text-dark'>" + val + "</span>";
                    } else if (val.toLowerCase().indexOf('malam') !== -1 || val.indexOf('2') !== -1) {
                        return "<span class='badge bg-dark'>" + val + "</span>";
                    }
                    return "<span class='badge bg-light text-dark border'>" + val + "</span>";
                }
            },
            {
                title: "Tipe", field: "type", headerSort: false,
                width: 120, hozAlign: "center", cssClass: "text-center",
                formatter: function (cell) {
                    let row = cell.getData();
                    if (row.type == 1) {
                        return "<span class='badge bg-primary'><i class='fa fa-building-o me-1'></i> Internal</span>";
                    } else {
                        let cmt = row.nama_operator ? (" " + row.nama_operator) : "";
                        return "<span class='badge bg-secondary' title='CMT" + cmt + "'><i class='fa fa-handshake-o me-1'></i> CMT" + (cmt ? (" " + row.nama_operator) : "") + "</span>";
                    }
                }
            },
            {
                title: "Kontrak", field: "status_kontrak", headerSort: false,
                width: 125, hozAlign: "center", cssClass: "text-center",
                formatter: function (cell) {
                    let row = cell.getData();
                    // Hanya untuk karyawan internal / NON CMT (type == 1)
                    if (row.type != 1) {
                        return "<span class='text-muted small'>Non-Internal</span>";
                    }
                    let st = parseInt(cell.getValue()) || 0;
                    let btnHtml = "";
                    if (st === 2) {
                        btnHtml = "<button type='button' class='btn btn-success btn-xs px-2 btn-kontrak' title='Kontrak Ditandatangani'><i class='fa fa-check-circle me-1'></i> Signed</button>";
                    } else if (st === 1) {
                        btnHtml = "<button type='button' class='btn btn-warning btn-xs px-2 text-dark btn-kontrak' title='Draf Kontrak Terbit'><i class='fa fa-file-word-o me-1'></i> Draf</button>";
                    } else {
                        btnHtml = "<button type='button' class='btn btn-outline-secondary btn-xs px-2 btn-kontrak' title='Belum Ada Kontrak'><i class='fa fa-file-text-o me-1'></i> Belum</button>";
                    }

                    if (row.tgl_selesai_kontrak_formatted) {
                        btnHtml += "<div class='text-muted' style='font-size: 10px; line-height: 1.1; margin-top: 3px;'>s/d " + row.tgl_selesai_kontrak_formatted + "</div>";
                    }
                    return btnHtml;
                },
                cellClick: function (e, cell) {
                    let row = cell.getData();
                    if (row.type != 1) return;
                    openModalKontrak(row);
                }
            },
            {
                title: "No. WhatsApp", field: "no_hp", headerSort: false,
                width: 135, cssClass : 'text-center',
                formatter: function (cell) {
                    let val = cell.getValue();
                    if (!val) return "<span class='text-muted'>-</span>";
                    let cleanPhone = String(val).replace(/[^0-9]/g, '');
                    if (cleanPhone.startsWith('0')) {
                        cleanPhone = '62' + cleanPhone.substring(1);
                    }
                    return "<a href='https://wa.me/" + cleanPhone + "' target='_blank' class='text-decoration-none text-dark fw-semibold' title='Chat WhatsApp: " + val + "'>"
                        + "<i class='fa fa-whatsapp text-success me-1 fs-6'></i>" + val + "</a>";
                }
            },
            {
                title: "Status", field: "active", headerSort: false,
                width: 90, hozAlign: "center", cssClass: "text-center",
                formatter: function (cell) {
                    let isActive = cell.getValue() == 1;
                    return isActive
                        ? "<span class='badge bg-success' style='cursor:pointer' title='Aktif - klik untuk nonaktifkan'><i class='fa fa-check-circle'></i> Aktif</span>"
                        : "<span class='badge bg-secondary' style='cursor:pointer' title='Nonaktif - klik untuk aktifkan'><i class='fa fa-times-circle'></i> Nonaktif</span>";
                },
                cellClick: function (e, cell) {
                    let data_row = cell.getData();
                    let isActive = data_row.active == 1;
                    let msg = isActive
                        ? "Nonaktifkan karyawan ini?"
                        : "Aktifkan karyawan ini?";
                    let url = isActive
                        ? "/master-data/karyawan/deactivate/"
                        : "/master-data/karyawan/activate/";
                    if (confirm(msg)) {
                        window.location.replace(baseUrl + url + data_row.id);
                    }
                }
            },
        ],
        layout: 'fitColumns',
        ajaxURL: "/master-data/karyawan/list",
        ajaxConfig: "POST",
        sortMode: "remote",
        filterMode: "remote",
        placeholder: "Tidak ada data",
        ajaxParams: function () {
            return {
                id_perusahaan: $("#filter_perusahaan").val() === "all" ? "" : $("#filter_perusahaan").val(),
                type: $("#filter_tipe").val() === "all" ? "" : $("#filter_tipe").val()
            };
        },
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
        },
        ajaxResponse: function (url, params, response) {
            let pageSize = dtList.getPageSize();
            let pageNo = dtList.getPage();
            let startRow = (pageSize * (pageNo - 1)) + 1;
            let endRow = response.data.length + startRow - 1;
            if (response.data.length === 0) {
                startRow = 0; endRow = 0;
            }
            let recordsFiltered = parseInt(response.recordsFiltered);
            let recordsTotal = parseInt(response.recordsTotal);

            $("#table-footer .tabulator-startrow").text(startRow);
            $("#table-footer .tabulator-endrow").text(endRow);
            $("#table-footer .tabulator-totalrow").text(recordsFiltered);

            let elTotalFilteredRow = $("#table-footer .tabulator-totalfilteredrow");
            elTotalFilteredRow.text("");
            if (recordsTotal > recordsFiltered) {
                elTotalFilteredRow.text(" (disaring dari " + recordsTotal
                    + " entri keseluruhan)");
            }
            return response;
        },
        footerElement: '<div id="table-footer" class="pull-left tabulator-info">'
            + 'Menampilkan <span class="tabulator-startrow"></span> - <span class="tabulator-endrow"></span> dari '
            + '<span class="tabulator-totalrow"></span> entri<span class="tabulator-totalfilteredrow"></span></div>',
        pagination: true,
        paginationMode: "remote",
        paginationSize: 25,
        paginationButtonCount: 10,
        dataSendParams: {
            sorters: "order"
        },
        selectableRows: false,
	});

    let searchThread = null;
    let elSearch = $("#tb-search");
    if (elSearch != null) {
        elSearch.on("keyup", function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThread);
            searchThread = setTimeout(function () {
                dtList.setFilter("", "like", elSearch.val());
            }, 600);
        });
    }

    $("#btn-add").on("click", function(){
        resetInput();
        isModal.modal("show");
    });

    $("#btn-save").on("click", function(e){
        e.preventDefault();
        simpanData();
    });

    function resetInput(){
        inpData.val("");
        inpNip.val("");
        inpNama.val("");
        inpEmail.val("");
        inpAlamat.val("");
        inpNoHP.val("");
        inpTempatLahir.val("");
        inpTglLahir.val("");
        inpPosisi.val("");
        selectKategoriPosisi.val("1").trigger("change");
        selectShift.val("1").trigger("change");
        inpTglBergabung.val("");
        inpJenisKelamin.val("");
        fileKaryawanOld.val("");
        fileKaryawan.val("");
        linkFileKaryawan.addClass("d-none");
        linkFileKaryawan.attr('src', '');

        $('#form_tgl_mulai_kontrak').val('');
        $('#form_durasi_kontrak').val('3');
        $('#form_display_tgl_selesai').val('');
        $('#form-kontrak-status-badge').attr('class', 'badge bg-secondary p-2').html('<i class="fa fa-file-o me-1"></i> Belum Dibuat');
        $('#form_ttd_pihak2_nama').text('-');
        $('#form_ttd_pihak2_jabatan').text('Karyawan');

        updateSingleSigBox('form', 1, null, 'Eva Fiantina', 'HRD Citra Jaya Knitting');
        updateSingleSigBox('form', 2, null, '-', 'Karyawan');
        updateSingleSigBox('form', 3, null, 'Edwin Ferdiansyah', 'Pimpinan CV. Citra Jaya Knitting');

        $('#btn-form-view-signed-file').addClass('d-none').attr('href', '#');
        $('#btn-form-generate-docx').removeClass('btn-success').addClass('btn-primary').html('<i class="fa fa-download me-1"></i> Unduh Dokumen Kontrak (.docx)');
        $('#form-kontrak-action-info').text('Unduh berkas kontrak kerja Word (.docx) lengkap dengan tanda tangan digital para pihak.');

        setTimeout(() => {
            inpBank.val("").trigger('change');
            inpRekening.val("").trigger('change');
            inpUpahHarian.val("").trigger('change');
            inpUpahLembur.val("").trigger('change');
            inpUpahLemburWe.val("").trigger('change');
            inpUpahPerjam.val("").trigger('change');
            inpPremiKehadiran.val("").trigger('change');
        }, 500);
    }

    function simpanData(closeOnSuccess = true, callback = null) {
        let validation = true;

        if (inpNip.val().length == 0) validation = false;
        if (inpNama.val().length == 0) validation = false;
        if (inpEmail.val().length == 0) validation = false;
        if (inpAlamat.val().length == 0) validation = false;
        if (inpNoHP.val().length == 0) validation = false;
        if (inpPosisi.val().length == 0) validation = false;
        if (!selectKategoriPosisi.val() || selectKategoriPosisi.val().length == 0) validation = false;
        if (!selectShift.val() || selectShift.val().length == 0) validation = false;
        if (inpTglBergabung.val().length == 0) validation = false;
        if (inpJenisKelamin.val().length == 0) validation = false;
        if (inpUpahHarian.val().length == 0) validation = false;
        if (inpUpahLembur.val().length == 0) validation = false;
        if (inpUpahLemburWe.val().length == 0) validation = false;
        if (inpUpahPerjam.val().length == 0) validation = false;
        if (inpPremiKehadiran.val().length == 0) validation = false;
        if (selectTipe.val().length == 0) validation = false;
        if (selectPerusahaan.val().length == 0) validation = false;
        if (!inpTempatLahir.val() || inpTempatLahir.val().trim().length == 0) validation = false;
        if (!inpTglLahir.val() || inpTglLahir.val().trim().length == 0) validation = false;

        if (validation) {
            var formData = new FormData();
            formData.append("dataId", inpData.val());
            formData.append("nip", inpNip.val());
            formData.append("full_name", inpNama.val());
            formData.append("email", inpEmail.val());
            formData.append("posisi", inpPosisi.val());
            formData.append("kategori_posisi", selectKategoriPosisi.val());
            formData.append("id_shift", selectShift.val());
            formData.append("alamat", inpAlamat.val());
            formData.append("tempat_lahir", inpTempatLahir.val());
            formData.append("tgl_lahir", inpTglLahir.val());
            if (fileKaryawan[0].files.length > 0) {
                formData.append("fileKaryawan", fileKaryawan[0].files[0]);
            }
            formData.append("fileKaryawanOld", fileKaryawanOld.val());
            formData.append("tgl_bergabung", inpTglBergabung.val());
            formData.append("jenis_kelamin", inpJenisKelamin.val());
            formData.append("no_hp", inpNoHP.val());
            formData.append("nama_bank", inpBank.val());
            formData.append("no_rekening", inpRekening.val());
            formData.append("upah_harian", inpUpahHarian.val());
            formData.append("upah_lembur", inpUpahLembur.val());
            formData.append("upah_lembur_we", inpUpahLemburWe.val());
            formData.append("upah_jam", inpUpahPerjam.val());
            formData.append("premi_kehadiran", inpPremiKehadiran.val());
            formData.append("type", selectTipe.val());
            formData.append("id_perusahaan", selectPerusahaan.val());
            formData.append("id_operator", selectCMT.val());
            formData.append("tgl_mulai_kontrak", $('#form_tgl_mulai_kontrak').val() || "");
            formData.append("durasi_kontrak_bulan", $('#form_durasi_kontrak').val() || "");

            $.ajax({
                type: 'POST',
                url: '/master-data/karyawan/simpan',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        onBeforeOpen: () => { Swal.showLoading(); }
                    });
                },
                success: function (response) {
                    if (response.status == true) {
                        dtList.setData();
                        if (typeof callback === 'function') {
                            callback(response);
                        } else {
                            Swal.fire({
                                text: response.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            if (closeOnSuccess) {
                                isModal.modal("hide");
                                resetInput();
                            }
                        }
                    } else {
                        Swal.fire({
                            text: response.message,
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                },
                error: function (e) {
                    let msg = (e.responseJSON && e.responseJSON.message) ? e.responseJSON.message : "Terjadi kesalahan sistem";
                    Swal.fire({
                        text: msg,
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 2500
                    });
                },
            });
        } else {
            Swal.fire({
                text: "Lengkapi semua isian wajib (*) pada form termasuk Tempat & Tanggal Lahir!",
                icon: 'warning',
                showConfirmButton: true
            });
        }
    }    

    selectTipe.on("change", function(e){
        var nilai = e.target.value;
        selectCMT.val("").trigger("change");
        if (nilai == 1) {
            divCMT.addClass("d-none");
            $('#section-kontrak-karyawan').removeClass('d-none');
        } else {
            divCMT.removeClass("d-none");
            $('#section-kontrak-karyawan').addClass('d-none');
        } 
    });

    // Kalkulasi Tanggal Akhir di Form Utama
    function updateFormTglSelesai() {
        let tglMulai = $('#form_tgl_mulai_kontrak').val();
        let durasi = parseInt($('#form_durasi_kontrak').val()) || 1;
        if (!tglMulai) {
            $('#form_display_tgl_selesai').val('');
            return;
        }
        let parts = tglMulai.split('-');
        let d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        d.setMonth(d.getMonth() + durasi);
        d.setDate(d.getDate() - 1);
        let y = d.getFullYear();
        let m = String(d.getMonth() + 1).padStart(2, '0');
        let day = String(d.getDate()).padStart(2, '0');
        $('#form_display_tgl_selesai').val(y + '-' + m + '-' + day);
    }

    $('#form_tgl_mulai_kontrak, #form_durasi_kontrak').on('change input', function () {
        updateFormTglSelesai();
    });

    inpNama.on('input change', function () {
        let val = $(this).val().trim();
        $('#form_ttd_pihak2_nama').text(val ? val : '-');
    });

    inpPosisi.on('input change', function () {
        let val = $(this).val().trim();
        $('#form_ttd_pihak2_jabatan').text(val ? val : 'Karyawan');
    });

    // ==========================================
    // KELOLA KONTRAK KERJA KARYAWAN
    // ==========================================

    function calculateTglSelesaiKontrak() {
        let tglMulai = $('#input_tgl_mulai_kontrak').val();
        let durasi = parseInt($('#select_durasi_kontrak').val()) || 1;
        if (!tglMulai) {
            $('#display_tgl_selesai_kontrak').val('');
            return;
        }
        let parts = tglMulai.split('-');
        let d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        d.setMonth(d.getMonth() + durasi);
        d.setDate(d.getDate() - 1);
        let y = d.getFullYear();
        let m = String(d.getMonth() + 1).padStart(2, '0');
        let day = String(d.getDate()).padStart(2, '0');
        $('#display_tgl_selesai_kontrak').val(y + '-' + m + '-' + day);
    }

    $('#input_tgl_mulai_kontrak, #select_durasi_kontrak').on('change input', function () {
        calculateTglSelesaiKontrak();
    });

    function openModalKontrak(row) {
        currentKontrakRow = row;
        $('#kontrak-nip').text('NIP: ' + (row.nip || '-'));
        $('#kontrak-nama').text(row.full_name || '-');
        $('#kontrak-posisi').text(row.posisi || '-');
        $('#kontrak_karyawan_id').val(row.id);
        $('#kelola-ttd-pihak2-nama').text(row.full_name || '-');
        $('#kelola-ttd-pihak2-jabatan').text(row.posisi || 'Karyawan');

        $('#kontrak-alert-warning').addClass('d-none');
        $('#kontrak-missing-list').empty();
        $('#file_scan_kontrak').val('');

        Swal.fire({
            title: 'Memeriksa Data Kontrak...',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: 'POST',
            url: '/master-data/karyawan/check-kontrak',
            data: { id: row.id },
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (!res.status && !res.data) {
                    Swal.fire({ text: res.message || 'Gagal memeriksa data karyawan', icon: 'error' });
                    return;
                }

                let d = res.data;

                // Status badge
                let st = parseInt(d.status_kontrak) || 0;
                let badgeEl = $('#kontrak-status-badge');
                if (st === 2) {
                    badgeEl.attr('class', 'badge bg-success p-2').html('<i class="fa fa-check-circle me-1"></i> Ditandatangani');
                } else if (st === 1) {
                    badgeEl.attr('class', 'badge bg-warning text-dark p-2').html('<i class="fa fa-file-word-o me-1"></i> Draf Terbit');
                } else {
                    badgeEl.attr('class', 'badge bg-secondary p-2').html('<i class="fa fa-file-o me-1"></i> Belum Dibuat');
                }

                if (!res.is_complete) {
                    // Ada data profil yang kosong
                    $('#kontrak-alert-warning').removeClass('d-none');
                    $('#kontrak-panel-complete').addClass('d-none');
                    let listEl = $('#kontrak-missing-list');
                    listEl.empty();
                    (res.missing_fields || []).forEach(function (f) {
                        listEl.append('<span class="badge bg-danger p-2"><i class="fa fa-times me-1"></i>' + f + '</span>');
                    });
                } else {
                    $('#kontrak-alert-warning').addClass('d-none');
                    $('#kontrak-panel-complete').removeClass('d-none');

                    // Ringkasan Identitas
                    $('#kontrak-preview-ttl').text((d.tempat_lahir || '-') + ', ' + (d.tgl_lahir_ind || '-'));
                    $('#kontrak-preview-gender').text(d.jenis_kelamin_text || '-');
                    $('#kontrak-preview-alamat').text(d.alamat || '-');
                    $('#kontrak-preview-posisi').text(d.posisi || '-');
                    $('#kontrak-preview-upah').text('Rp. ' + (d.upah_harian_formatted || '0') + ' / hari');
                    $('#kelola-ttd-pihak2-nama').text(d.full_name || '-');
                    $('#kelola-ttd-pihak2-jabatan').text(d.posisi || 'Karyawan');

                    // Parameter Kontrak
                    $('#input_tgl_mulai_kontrak').val(d.tgl_mulai_kontrak || '');
                    $('#select_durasi_kontrak').val(d.durasi_kontrak_bulan || '3');
                    calculateTglSelesaiKontrak();

                    // File signed
                    if (st === 2 && d.file_kontrak_url) {
                        $('#kontrak-existing-file').removeClass('d-none');
                        $('#kontrak-file-name').text(d.file_kontrak_origin || d.file_kontrak_name || 'Berkas Kontrak');
                        $('#link-download-signed-kontrak').attr('href', d.file_kontrak_url);
                    } else {
                        $('#kontrak-existing-file').addClass('d-none');
                    }

                    let p1Name = d.pihak1_nama || 'Eva Fiantina';
                    let p1Role = d.pihak1_jabatan || 'HRD Citra Jaya Knitting';
                    let p3Name = d.mengetahui_nama || 'Edwin Ferdiansyah';
                    let p3Role = d.mengetahui_jabatan || 'Pimpinan CV. Citra Jaya Knitting';

                    $('#kelola-ttd-pihak1-nama').text(p1Name).attr('title', p1Name);
                    $('#kelola-ttd-pihak1-jabatan').text(p1Role);
                    $('#kelola-ttd-mengetahui-nama').text(p3Name).attr('title', p3Name);
                    $('#kelola-ttd-mengetahui-jabatan').text(p3Role);

                    // Tanda Tangan Digital 3 Pihak
                    let sig1 = d.ttd_pihak1_url ? { url: d.ttd_pihak1_url, tgl: d.ttd_pihak1_tgl } : null;
                    let sig2 = d.ttd_pihak2_url ? { url: d.ttd_pihak2_url, tgl: d.ttd_pihak2_tgl } : null;
                    let sig3 = d.ttd_mengetahui_url ? { url: d.ttd_mengetahui_url, tgl: d.ttd_mengetahui_tgl } : null;

                    updateSingleSigBox('kelola', 1, sig1, p1Name, p1Role);
                    updateSingleSigBox('kelola', 2, sig2, d.full_name || 'Karyawan', d.posisi || 'Karyawan');
                    updateSingleSigBox('kelola', 3, sig3, p3Name, p3Role);

                    if (d.all_signed) {
                        $('#kelola-sig-all-signed-banner').removeClass('d-none');
                    } else {
                        $('#kelola-sig-all-signed-banner').addClass('d-none');
                    }
                }

                modalKontrak.modal('show');
            },
            error: function () {
                Swal.close();
                Swal.fire({ text: 'Terjadi kesalahan saat memeriksa data kontrak.', icon: 'error' });
            }
        });
    }

    $('#btn-edit-dari-kontrak').on('click', function () {
        modalKontrak.modal('hide');
        if (currentKontrakRow) {
            openEditKaryawan(currentKontrakRow);
        }
    });

    $('#btn-do-generate-kontrak').on('click', function () {
        let id = $('#kontrak_karyawan_id').val();
        let tglMulai = $('#input_tgl_mulai_kontrak').val();
        let durasi = $('#select_durasi_kontrak').val();

        if (!tglMulai) {
            Swal.fire({ text: 'Tanggal mulai kontrak wajib diisi!', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Memproses Draf Kontrak...',
            text: 'Harap tunggu, berkas Word sedang disiapkan',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: 'POST',
            url: '/master-data/karyawan/generate-kontrak',
            data: {
                id: id,
                tgl_mulai_kontrak: tglMulai,
                durasi_kontrak_bulan: durasi
            },
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (res.status) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message || 'Dokumen kontrak kerja (PDF) berhasil disiapkan.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Buka dokumen PDF di tab baru
                    window.open(res.download_url, '_blank');
                    // Update badge
                    $('#kontrak-status-badge').attr('class', 'badge bg-warning text-dark p-2').html('<i class="fa fa-file-pdf-o me-1"></i> Draf Terbit');
                    dtList.setData();
                } else {
                    Swal.fire({ text: res.message || 'Gagal menghasilkan kontrak', icon: 'error' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ text: 'Terjadi kesalahan sistem saat generate kontrak.', icon: 'error' });
            }
        });
    });

    $('#btn-form-generate-docx').on('click', function () {
        let karyawanId = inpData.val();
        if (!karyawanId) {
            Swal.fire({
                text: 'Data profil karyawan belum tersimpan. Silakan simpan data karyawan terlebih dahulu sebelum mengunduh dokumen kontrak!',
                icon: 'warning'
            });
            return;
        }

        let tglMulai = $('#form_tgl_mulai_kontrak').val();
        let durasi = $('#form_durasi_kontrak').val();

        if (!tglMulai) {
            Swal.fire({ text: 'Tanggal mulai kontrak wajib diisi terlebih dahulu!', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Menyiapkan Dokumen Kontrak...',
            text: 'Harap tunggu, berkas PDF sedang disiapkan lengkap dengan tanda tangan digital',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: 'POST',
            url: '/master-data/karyawan/generate-kontrak',
            data: {
                id: karyawanId,
                tgl_mulai_kontrak: tglMulai,
                durasi_kontrak_bulan: durasi
            },
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (res.status) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Dokumen kontrak kerja (PDF) berhasil disiapkan dan dibuka.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.open(res.download_url, '_blank');
                    $('#form_display_tgl_selesai').val(res.tgl_selesai_kontrak || '');
                    if (res.status_kontrak == 2) {
                        $('#form-kontrak-status-badge').attr('class', 'badge bg-success p-2').html('<i class="fa fa-check-circle me-1"></i> Ditandatangani');
                    } else {
                        $('#form-kontrak-status-badge').attr('class', 'badge bg-warning text-dark p-2').html('<i class="fa fa-file-pdf-o me-1"></i> Draf Terbit');
                    }
                    dtList.setData();
                } else {
                    Swal.fire({ text: res.message || 'Gagal menghasilkan kontrak', icon: 'error' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ text: 'Terjadi kesalahan sistem saat memproses kontrak.', icon: 'error' });
            }
        });
    });

    $(document).on('click', '#btn-quick-download-signed', function () {
        $('#btn-do-generate-kontrak').trigger('click');
    });

    $('#btn-do-upload-kontrak').on('click', function () {
        let id = $('#kontrak_karyawan_id').val();
        let fileInput = $('#file_scan_kontrak')[0];

        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire({ text: 'Pilih berkas scan kontrak bertandatangan terlebih dahulu!', icon: 'warning' });
            return;
        }

        let formData = new FormData();
        formData.append('id', id);
        formData.append('file_kontrak', fileInput.files[0]);

        Swal.fire({
            title: 'Mengunggah Berkas...',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: 'POST',
            url: '/master-data/karyawan/upload-kontrak',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (res.status) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 2500,
                        showConfirmButton: false
                    });
                    $('#file_scan_kontrak').val('');
                    $('#kontrak-existing-file').removeClass('d-none');
                    $('#kontrak-file-name').text(res.file_name_origin || res.file_name);
                    $('#link-download-signed-kontrak').attr('href', res.file_url);
                    $('#kontrak-status-badge').attr('class', 'badge bg-success p-2').html('<i class="fa fa-check-circle me-1"></i> Ditandatangani');
                    dtList.setData();
                } else {
                    Swal.fire({ text: res.message || 'Gagal mengunggah berkas kontrak', icon: 'error' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ text: 'Terjadi kesalahan sistem saat mengunggah berkas.', icon: 'error' });
            }
        });
    });

    // ==========================================
    // TANDA TANGAN DIGITAL (CANVAS SIGNATURE PAD)
    // ==========================================
    function updateSingleSigBox(prefix, pihak, sigInfo, name, role) {
        let box = $('#' + prefix + '-sig-box-' + pihak);
        if (!box.length) return;

        let color = (pihak == 1 ? 'primary' : (pihak == 2 ? 'success' : 'dark'));

        if (sigInfo && sigInfo.url) {
            box.html(`
                <div class="mb-1 bg-white border rounded p-1 shadow-sm d-flex align-items-center justify-content-center" style="height: 55px;">
                    <img src="${sigInfo.url}" alt="TTD" style="max-height: 48px; max-width: 100%; object-fit: contain;">
                </div>
                <div class="d-flex align-items-center justify-content-between px-1">
                    <span class="text-success small fw-bold text-truncate" style="font-size: 10px;" title="${sigInfo.tgl || 'Sudah TTD'}">
                        <i class="fa fa-check-circle me-1"></i> ${sigInfo.tgl || 'Sudah TTD'}
                    </span>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-1 btn-open-sig" style="font-size: 10px;" data-pihak="${pihak}" data-nama="${name}" data-role="${role}" title="Ganti Tanda Tangan">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger p-0 btn-delete-sig" style="font-size: 11px; text-decoration: none;" title="Hapus TTD" data-pihak="${pihak}" data-nama="${name}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `);
        } else {
            box.html(`
                <div class="text-muted small fst-italic mb-1" style="font-size: 10px;"><i class="fa fa-clock-o me-1"></i> Belum TTD</div>
                <button type="button" class="btn btn-outline-${color} btn-xs py-1 px-2 w-100 btn-open-sig" data-pihak="${pihak}" data-nama="${name}" data-role="${role}">
                    <i class="fa fa-pencil me-1"></i> Tanda Tangan
                </button>
            `);
        }
    }

    let sigCanvas = document.getElementById('canvas-signature');
    let sigCtx = sigCanvas ? sigCanvas.getContext('2d') : null;
    let isDrawing = false;
    let hasDrawn = false;
    let lastX = 0;
    let lastY = 0;

    function initSignatureCanvas() {
        if (!sigCanvas) {
            sigCanvas = document.getElementById('canvas-signature');
            if (!sigCanvas) return;
            sigCtx = sigCanvas.getContext('2d');
        }

        sigCanvas.width = 480;
        sigCanvas.height = 200;

        sigCtx.lineWidth = 2.5;
        sigCtx.lineCap = 'round';
        sigCtx.lineJoin = 'round';
        sigCtx.strokeStyle = '#111827';
    }

    function getCanvasCoords(e) {
        let r = sigCanvas.getBoundingClientRect();
        let clientX = e.clientX;
        let clientY = e.clientY;
        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }
        let scaleX = sigCanvas.width / r.width;
        let scaleY = sigCanvas.height / r.height;
        return {
            x: (clientX - r.left) * scaleX,
            y: (clientY - r.top) * scaleY
        };
    }

    function startDraw(e) {
        if (!sigCtx) return;
        e.preventDefault();
        isDrawing = true;
        let coords = getCanvasCoords(e);
        lastX = coords.x;
        lastY = coords.y;
        sigCtx.beginPath();
        sigCtx.moveTo(lastX, lastY);
    }

    function moveDraw(e) {
        if (!isDrawing || !sigCtx) return;
        e.preventDefault();
        let coords = getCanvasCoords(e);
        sigCtx.lineTo(coords.x, coords.y);
        sigCtx.stroke();
        lastX = coords.x;
        lastY = coords.y;
        hasDrawn = true;
    }

    function stopDraw(e) {
        if (!isDrawing || !sigCtx) return;
        isDrawing = false;
        sigCtx.closePath();
    }

    if (sigCanvas) {
        sigCanvas.addEventListener('mousedown', startDraw);
        sigCanvas.addEventListener('mousemove', moveDraw);
        window.addEventListener('mouseup', stopDraw);

        sigCanvas.addEventListener('touchstart', startDraw, { passive: false });
        sigCanvas.addEventListener('touchmove', moveDraw, { passive: false });
        window.addEventListener('touchend', stopDraw);
    }

    function clearSignatureCanvas() {
        if (!sigCanvas || !sigCtx) return;
        sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
        hasDrawn = false;
    }

    $('#modal-signature-pad').on('shown.bs.modal', function () {
        initSignatureCanvas();
        clearSignatureCanvas();
    });

    $('#btn-sig-clear').on('click', function () {
        clearSignatureCanvas();
    });

    $(document).on('click', '.btn-open-sig', function () {
        let pihak = $(this).data('pihak');
        let nama = $(this).data('nama');
        let role = $(this).data('role');

        let karyawanId = '';
        if ($('#modal-kelola-kontrak').is(':visible')) {
            karyawanId = $('#kontrak_karyawan_id').val();
            if (pihak == 2) {
                nama = $('#kelola-ttd-pihak2-nama').text();
                role = $('#kelola-ttd-pihak2-jabatan').text();
            }
        } else if ($('#modal-form-add-po').is(':visible')) {
            karyawanId = inpData.val();
            if (pihak == 2) {
                nama = inpNama.val() || 'Karyawan';
                role = inpPosisi.val() || 'Karyawan';
            }
        }

        if (!karyawanId) {
            Swal.fire({
                text: 'Data karyawan belum tersimpan di sistem. Silakan simpan data profil karyawan terlebih dahulu sebelum membubuhkan tanda tangan digital!',
                icon: 'warning'
            });
            return;
        }

        $('#sig-target-karyawan-id').val(karyawanId);
        $('#sig-target-pihak').val(pihak);
        $('#sig-target-name').text(nama || '-');
        $('#sig-target-role').text(role || '-');

        let badgeText = (pihak == 1 ? 'PIHAK PERTAMA' : (pihak == 2 ? 'PIHAK KEDUA' : 'MENGETAHUI'));
        let badgeClass = (pihak == 1 ? 'badge bg-primary px-2 py-1 mb-1' : (pihak == 2 ? 'badge bg-success px-2 py-1 mb-1' : 'badge bg-dark px-2 py-1 mb-1'));
        $('#sig-target-badge').attr('class', badgeClass).text(badgeText);

        $('#modal-signature-pad').modal('show');
    });

    $('#btn-sig-save').on('click', function () {
        if (!hasDrawn) {
            Swal.fire({
                text: 'Silakan bubuhkan tanda tangan terlebih dahulu pada kanvas sebelum menyimpan!',
                icon: 'warning'
            });
            return;
        }

        let karyawanId = $('#sig-target-karyawan-id').val();
        let pihak = $('#sig-target-pihak').val();
        let sigBase64 = sigCanvas.toDataURL('image/png');

        Swal.fire({
            title: 'Menyimpan Tanda Tangan...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: 'POST',
            url: '/master-data/karyawan/simpan-ttd-digital',
            data: {
                id: karyawanId,
                pihak: pihak,
                signature: sigBase64,
                nama_penandatangan: $('#sig-target-name').text(),
                jabatan_penandatangan: $('#sig-target-role').text()
            },
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (res.status) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $('#modal-signature-pad').modal('hide');

                    let sigData = {
                        file: res.file_name,
                        url: res.file_url,
                        tgl: res.tgl
                    };

                    let defaultName = $('#sig-target-name').text();
                    let defaultRole = $('#sig-target-role').text();

                    updateSingleSigBox('kelola', pihak, sigData, defaultName, defaultRole);
                    updateSingleSigBox('form', pihak, sigData, defaultName, defaultRole);

                    if (res.all_signed) {
                        $('#kelola-sig-all-signed-banner').removeClass('d-none');
                        $('#kontrak-status-badge, #form-kontrak-status-badge')
                            .attr('class', 'badge bg-success p-2')
                            .html('<i class="fa fa-check-circle me-1"></i> Ditandatangani');
                        $('#btn-form-generate-docx').removeClass('btn-primary').addClass('btn-success').html('<i class="fa fa-download me-1"></i> Unduh Dokumen Kontrak Sah (.docx)');
                        $('#form-kontrak-action-info').html('<span class="text-success fw-bold"><i class="fa fa-check-circle me-1"></i> Seluruh pihak sudah bertandatangan digital. Berkas siap diunduh!</span>');
                    }

                    dtList.setData();
                } else {
                    Swal.fire({ text: res.message || 'Gagal menyimpan tanda tangan.', icon: 'error' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ text: 'Terjadi kesalahan sistem saat menyimpan tanda tangan.', icon: 'error' });
            }
        });
    });

    $(document).on('click', '.btn-delete-sig', function () {
        let pihak = $(this).data('pihak');
        let nama = $(this).data('nama') || 'pihak terkait';

        let karyawanId = '';
        if ($('#modal-kelola-kontrak').is(':visible')) {
            karyawanId = $('#kontrak_karyawan_id').val();
        } else if ($('#modal-form-add-po').is(':visible')) {
            karyawanId = inpData.val();
        }

        if (!karyawanId) return;

        Swal.fire({
            title: 'Hapus Tanda Tangan?',
            text: 'Tanda tangan digital untuk ' + nama + ' akan dihapus dan perlu ditandatangani ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus TTD',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: 'Mereset Tanda Tangan...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    type: 'POST',
                    url: '/master-data/karyawan/hapus-ttd-digital',
                    data: {
                        id: karyawanId,
                        pihak: pihak
                    },
                    dataType: 'json',
                    success: function (res) {
                        Swal.close();
                        if (res.status) {
                            Swal.fire({
                                title: 'Direset!',
                                text: res.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            let defaultName = (pihak == 1 ? ($('#kelola-ttd-pihak1-nama').text() || $('#form_ttd_pihak1_nama').text() || 'Eva Fiantina') : (pihak == 2 ? ($('#nama_konsumen').val() || $('#kelola-ttd-pihak2-nama').text() || $('#form_ttd_pihak2_nama').text()) : ($('#kelola-ttd-mengetahui-nama').text() || $('#form_ttd_mengetahui_nama').text() || 'Edwin Ferdiansyah')));
                            let defaultRole = (pihak == 1 ? ($('#kelola-ttd-pihak1-jabatan').text() || $('#form_ttd_pihak1_jabatan').text() || 'HRD Citra Jaya Knitting') : (pihak == 2 ? ($('#posisi').val() || $('#kelola-ttd-pihak2-jabatan').text() || $('#form_ttd_pihak2_jabatan').text()) : ($('#kelola-ttd-mengetahui-jabatan').text() || $('#form_ttd_mengetahui_jabatan').text() || 'Pimpinan CV. Citra Jaya Knitting')));

                            updateSingleSigBox('kelola', pihak, null, defaultName, defaultRole);
                            updateSingleSigBox('form', pihak, null, defaultName, defaultRole);

                            $('#kelola-sig-all-signed-banner').addClass('d-none');
                            if (res.status_kontrak === 1) {
                                $('#kontrak-status-badge, #form-kontrak-status-badge')
                                    .attr('class', 'badge bg-warning text-dark p-2')
                                    .html('<i class="fa fa-file-word-o me-1"></i> Draf Terbit');
                            }

                            dtList.setData();
                        } else {
                            Swal.fire({ text: res.message || 'Gagal mereset tanda tangan.', icon: 'error' });
                        }
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire({ text: 'Terjadi kesalahan sistem saat mereset tanda tangan.', icon: 'error' });
                    }
                });
            }
        });
    });
});
