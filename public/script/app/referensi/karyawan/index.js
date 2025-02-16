

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
    let inpUpahHarian    = $('#upah_harian');
    let inpUpahLembur    = $('#upah_lembur');
    let inpUpahLemburWe  = $('#upah_lembur_we');
    let inpUpahPerjam    = $('#upah_jam')

    let isModal       = $("#modal-form-add-po");

    let buttonRowAction = function(cell) {
        let fmBtnDelete = "";        
        let fmBtnEdit = "";        
        fmBtnDelete = "<button class='btn btn-danger btn-xs open_form' type='button' title='delete'><i class='fa fa-trash' title='delete'></i></button>";
        fmBtnEdit = "<button class='btn btn-warning btn-xs open_form' type='button' title='edit'><i class='fa fa-edit' title='edit'></i></button>";
        return fmBtnEdit + " " + fmBtnDelete;
    };

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
                        



                        setTimeout(() => {
                            inpUpahHarian.val(data_row.upah_harian).trigger('change');
                            inpUpahLembur.val(data_row.upah_lembur).trigger('change');
                            inpUpahLemburWe.val(data_row.upah_lembur_we).trigger('change');
                            inpUpahPerjam.val(data_row.upah_jam).trigger('change');

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
                
            },
            {
                title: "Posisi", field: "posisi", headerSort: false,
                width: "20%"
            },
            // {
            //     title: "Alamat", field: "alamat", formatter: "html", headerSort: false,
                
            // },
            {
                title: "Email", field: "email", headerSort: false,
                width: "20%", cssClass : 'text-center'
            },
            {
                title: "No. HP", field: "no_hp", headerSort: false,
                width: "20%", cssClass : 'text-center'
            },
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: "/master-data/karyawan/list",
        ajaxConfig: "POST",
        sortMode: "remote",
        filterMode: "remote",
        placeholder: "Tidak ada data",
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

        setTimeout(() => {
            inpUpahHarian.val("").trigger('change');
            inpUpahLembur.val("").trigger('change');
            inpUpahLemburWe.val("").trigger('change');
            inpUpahPerjam.val("").trigger('change');
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

        if(validation){
            $.ajax({
                type: 'POST',
                url: '/master-data/karyawan/simpan',
                data: {
                    dataId : inpData.val(),

                    nip : inpNip.val(),
                    full_name : inpNama.val(),
                    email : inpEmail.val(),
                    posisi : inpPosisi.val(),
                    alamat : inpAlamat.val(),
                    tgl_bergabung : inpTglBergabung.val(),
                    jenis_kelamin : inpJenisKelamin.val(),
                    no_hp : inpNoHP.val(),
                    upah_lembur : inpUpahHarian.val(),
                    upah_harian : inpUpahLembur.val(),
                    upah_lembur_we : inpUpahLemburWe.val(),
                    upah_jam : inpUpahPerjam.val()
                },
                dataType: "json",
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
});
