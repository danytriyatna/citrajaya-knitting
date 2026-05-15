

$(document).ready(function () {
    $("[data-politespace]").politespace();
    let inpData         = $('#data_id');
    let inpNip = $('#nip');
    let inpNama = $('#nama_konsumen');
    let inpEmail        = $('#email');
    let inpAlamat       = $('#alamat');
    let inpNoHP         = $('#no_hp');

    let inpPosisi        = $('#posisi');
    let inpTglBergabung  = $('#tgl_bergabung');
    let inpJenisKelamin  = $('#jenis_kelamin');
    let inpBank    = $('#nama_bank');
    let inpRekening    = $('#no_rekening');
    let inpUpahHarian    = $('#upah_harian');
    let inpUpahLembur    = $('#upah_lembur');
    let inpUpahLemburWe  = $('#upah_lembur_we');
    let inpUpahPerjam    = $('#upah_jam');
    let inpPremiKehadiran    = $('#premi_kehadiran');
    let selectTipe = $('#tipe');
    let selectCMT = $('#id_cmt');
    let selectPerusahaan = $('#id_perusahaan');
    let divCMT = $('#div-cmt');

    const fileKaryawan     = $('#fileKaryawan');
    const fileKaryawanOld  = $('#fileKaryawanOld');
    const linkFileKaryawan = $('#linkFileKaryawan');

    let isModal       = $("#modal-form-add-po");


    $('#modal-form-add-po').on('hidden.bs.modal', function () {
        var $modal = $(this);

        // Reset input biasa
        $modal.find('input[type="text"], input[type="number"], input[type="email"], textarea').val('');

        // Reset checkbox & radio
        $modal.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);

        // Reset select ke opsi pertama
        $modal.find('select').prop('selectedIndex', 0).trigger('change');

        // Reset file input
        $modal.find('input[type="file"]').val('');
    });

    let buttonRowAction = function(cell) {
        let fmBtnDelete = "";        
        let fmBtnEdit = "";        
        fmBtnDelete = "<button class='btn btn-danger btn-xs open_form' type='button' title='delete'><i class='fa fa-trash' title='delete'></i></button>";
        fmBtnEdit = "<button class='btn btn-warning btn-xs open_form' type='button' title='edit'><i class='fa fa-edit' title='edit'></i></button>";
        return fmBtnEdit + " " + fmBtnDelete;
    };

    $("#filter_perusahaan").on("change", function () {
        dtList.setPage(1); // balik ke halaman 1, otomatis trigger ajax baru
    });

    let dtList = new Tabulator("#dt-list", {
        columns: [
            {
                headerSort: false,  
                title: 'Aksi', 
                formatter: buttonRowAction,
                width: 100, align: "center", cssClass: "text-center",
                cellClick: function(e, cell) {
                    let row = cell.getRow();
                    let data_row = row.getData();
                    if (e.target.title === 'delete') {
                        if (confirm("Anda yakin akan menghapus data?")) {
                            window.location.replace(baseUrl + "/master-data/karyawan/delete/" + data_row.id);
                        }
                    }else if(e.target.title === 'edit'){
                        resetInput()

                        inpData.val(data_row.id)
                        
                        // inpNamaKonsumen.val(data_row.nama)
                        // inpAlamat.val(data_row.alamat)
                        // inpNoHP.val(data_row.no_hp)
                        // inpEmail.val(data_row.email)
                        
                        inpNip.val(data_row.nip)
                        inpNama.val(data_row.full_name)
                        inpEmail.val(data_row.email)
                        inpAlamat.val(data_row.alamat)
                        inpNoHP.val(data_row.no_hp)
                        inpPosisi.val(data_row.posisi)
                        inpTglBergabung.val(data_row.tgl_bergabung)
                        inpJenisKelamin.val(data_row.jenis_kelamin).trigger('change');
                        selectTipe.val(data_row.type).trigger("change")
                        selectPerusahaan.val(data_row.id_perusahaan).trigger("change")
                        if(data_row.type == 1){
                            divCMT.addClass("d-none")
                            selectCMT.val("").trigger("change")
                        } else{
                            divCMT.removeClass("d-none")
                            selectCMT.val(data_row.id_operator).trigger("change")
                        } 

                        if(data_row.file_gambar){
                            fileKaryawanOld.val(data_row.gambar_id)
                            linkFileKaryawan.removeClass("d-none")
                            linkFileKaryawan.attr('src', data_row.file_gambar)
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
                }
            },
            {
                title: "NIP", field: "nip", headerSort: false,
                width: "10%"
            },
            {
                title: "Nama", field: "full_name", headerSort: false,
                width: "20%",
            },
            {
                title: "Posisi", field: "posisi", headerSort: false,
                width: "17%"
            },
            // {
            //     title: "Alamat", field: "alamat", formatter: "html", headerSort: false,
                
            // },
            {
                title: "Email", field: "email", headerSort: false,
                width: "16%", cssClass : 'text-center'
            },
            {
                title: "No. HP", field: "no_hp", headerSort: false,
                width: "15%", cssClass : 'text-center'
            },
            {
                title: "Perusahaan", field: "nama_perusahaan", headerSort: false,
                width: "15%"
            },
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: "/master-data/karyawan/list",
        ajaxConfig: "POST",
        sortMode: "remote",
        filterMode: "remote",
        placeholder: "Tidak ada data",
        ajaxParams: function () {
            return {
                id_perusahaan: $("#filter_perusahaan").val() === "all" ? "" : $("#filter_perusahaan").val()
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
       
        resetInput()
        isModal.modal("show");
    });

    $("#btn-save").on("click", function(e){
        e.preventDefault()
        simpanData()
    });

    function resetInput(){
        inpData.val("")
        inpNip.val("")
        inpNama.val("")
        inpEmail.val("")
        inpAlamat.val("")
        inpNoHP.val("")
        inpPosisi.val("")
        inpTglBergabung.val("")
        inpJenisKelamin.val("")
        fileKaryawanOld.val("")
        fileKaryawan.val("")
        linkFileKaryawan.addClass("d-none");
        linkFileKaryawan.attr('src', '');

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

    
    function simpanData() {
        
        let validation = true

        if(inpNip.val().length == 0) validation = false
        console.log("masuk nip", inpNip.val())
        if(inpNama.val().length == 0) validation = false
        console.log("masuk inpNama", inpNama.val())
        if(inpEmail.val().length == 0) validation = false
        console.log("masuk inpEmail", inpEmail.val())
        if(inpAlamat.val().length == 0) validation = false
        console.log("masuk inpAlamat", inpAlamat.val())
        if(inpNoHP.val().length == 0) validation = false
        console.log("masuk inpNoHP", inpNoHP.val())
        if(inpPosisi.val().length == 0) validation = false
        console.log("masuk inpPosisi", inpPosisi.val())
        if(inpTglBergabung.val().length == 0) validation = false
        console.log("masuk inpTglBergabung", inpTglBergabung.val())
        if(inpJenisKelamin.val().length == 0) validation = false
        console.log("masuk inpJenisKelamin", inpJenisKelamin.val())
        if(inpUpahHarian.val().length == 0) validation = false
        console.log("masuk inpUpahHarian", inpUpahHarian.val())
        if(inpUpahLembur.val().length == 0) validation = false
        console.log("masuk inpUpahLembur", inpUpahLembur.val())
        if(inpUpahLemburWe.val().length == 0) validation = false
        console.log("masuk inpUpahLemburWe", inpUpahLemburWe.val())
        if(inpUpahPerjam.val().length == 0) validation = false
        console.log("masuk inpUpahPerjam", inpUpahPerjam.val())
        if(inpPremiKehadiran.val().length == 0) validation = false
        console.log("masuk inpPremiKehadiran", inpPremiKehadiran.val())
        if(selectTipe.val().length == 0) validation = false
        console.log("masuk selectTipe", selectTipe.val())
        if(selectPerusahaan.val().length == 0) validation = false
        console.log("masuk selectPerusahaan", selectPerusahaan.val())

        if(validation){
            var formData = new FormData();
            formData.append("dataId",inpData.val());
            formData.append("nip",inpNip.val());
            formData.append("full_name",inpNama.val());
            formData.append("email",inpEmail.val());
            formData.append("posisi",inpPosisi.val());
            formData.append("alamat",inpAlamat.val());
            formData.append("fileKaryawan",fileKaryawan[0].files[0]);
            formData.append("fileKaryawanOld",fileKaryawanOld.val());
            formData.append("tgl_bergabung",inpTglBergabung.val());
            // formData.append("tgl_bergabung",formatLocaleDate(inpTglBergabung.val()));
            formData.append("jenis_kelamin",inpJenisKelamin.val());
            formData.append("no_hp",inpNoHP.val());
            formData.append("nama_bank", inpBank.val())
            formData.append("no_rekening", inpRekening.val())
            formData.append("upah_harian", inpUpahHarian.val())
            formData.append("upah_lembur", inpUpahLembur.val());
            formData.append("upah_lembur_we", inpUpahLemburWe.val());
            formData.append("upah_jam", inpUpahPerjam.val());
            formData.append("premi_kehadiran", inpPremiKehadiran.val());
            formData.append("type", selectTipe.val());
            formData.append("id_perusahaan", selectPerusahaan.val());
            formData.append("id_operator", selectCMT.val());
            $.ajax({
                type: 'POST',
                url: '/master-data/karyawan/simpan',

                data: formData,
                processData: false,  // Jangan ubah data menjadi string
                contentType: false,
                beforeSend: function () {
                    Swal.fire({
                        title: 'Loading...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        onBeforeOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function (response) {
        
                    if(response.status == true){
                        Swal.fire({
                            text: response.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        dtList.setData()
                        Swal.close();
                        isModal.modal("hide");
                        resetInput()
                    }else{
                        Swal.fire({
                            text: response.message,
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                },
                error: function (e) {
                    let msg = e.responseJSON.message;
                    Swal.close();
        
                    Swal.fire({
                        text: msg,
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
            });
        }else{
            Swal.fire({
                text: "Lengkapi isian pada form !",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
        }
    }    

    selectTipe.on("change",function(e){
        var nilai = e.target.value;

        selectCMT.val("").trigger("change")
        if(nilai == 1){
            divCMT.addClass("d-none")
        } else{
            divCMT.removeClass("d-none")
        } 
        
    })
});
