

$(document).ready(function () {
    let inpData       = $('#data_id');
    let inpNama  = $('#nama');
    let inpKeterangan = $('#keterangan');
    let inpUrutan = $('#seq');
    let inpIsRajut = $('#is_rajut');

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
                    let target = $(e.target).closest('button');
                    let action = target.attr('title') || $(e.target).attr('title');

                    if (action === 'delete') {
                        let namaProses = data_row.nama || 'ini';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Konfirmasi Hapus',
                                text: 'Anda yakin ingin menghapus data proses "' + namaProses + '"?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: '<i class="fa fa-trash me-1"></i> Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: baseUrl + "/master-data/proses/delete/" + data_row.id,
                                        type: "GET",
                                        dataType: "json",
                                        success: function(res) {
                                            if (res && res.status) {
                                                Swal.fire({
                                                    title: 'Berhasil!',
                                                    text: res.message || 'Data Proses berhasil dihapus.',
                                                    icon: 'success',
                                                    timer: 1500,
                                                    showConfirmButton: false
                                                });
                                                dtList.setData();
                                            } else {
                                                Swal.fire('Gagal!', (res && res.message) ? res.message : 'Gagal menghapus data.', 'error');
                                            }
                                        },
                                        error: function() {
                                            window.location.href = baseUrl + "/master-data/proses/delete/" + data_row.id;
                                        }
                                    });
                                }
                            });
                        } else {
                            if (confirm("Anda yakin akan menghapus data proses \"" + namaProses + "\"?")) {
                                window.location.replace(baseUrl + "/master-data/proses/delete/" + data_row.id);
                            }
                        }
                    } else if(action === 'edit'){
                        inpData.val(data_row.id);
                        inpNama.val(data_row.nama);
                        inpKeterangan.val(data_row.keterangan);
                        inpUrutan.val(data_row.seq);
                        inpIsRajut.prop('checked', data_row.is_rajut == 1 || data_row.is_rajut === true || data_row.is_rajut === '1');

                        isModal.modal("show");
                    }   
                }
            },
            {
                title: "Urutan Proses", field: "seq", headerSort: false,
                width: "12%", cssClass : 'text-center'
            },
            {
                title: "Nama Proses", field: "nama", headerSort: false,
                width: "25%", cssClass : 'text-start'
            },
            {
                title: "Rajut", field: "is_rajut", headerSort: false,
                width: "12%", cssClass: "text-center",
                formatter: function(cell) {
                    let val = cell.getValue();
                    if (val == 1 || val === true || val === "1") {
                        return '<span class="badge bg-success"><i class="fa fa-check"></i> Ya</span>';
                    }
                    return '<span class="badge bg-light text-muted">Tidak</span>';
                }
            },
            {
                title: "Keterangan", field: "keterangan", formatter: "html", headerSort: false,
                
            }
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: "/master-data/proses/list",
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
        inpData.val("")
        inpNama.val("")
        inpKeterangan.val("")
        inpUrutan.val(0)
        inpIsRajut.prop("checked", false)

        isModal.modal("show");
    });

    $("#btn-save").on("click", function(e){
        e.preventDefault()
        simpanData()
    });

    
    function simpanData() {
        
        let validation = true
        if(inpNama.val().length == 0) validation = false
        if(inpUrutan.val().length == 0) validation = false
    
        if(validation){
            $.ajax({
                type: 'POST',
                url: '/master-data/proses/simpan',
                data: {
                    dataId     : inpData.val(),
                    nama  : inpNama.val(),
                    keterangan : inpKeterangan.val(),
                    seq : inpUrutan.val(),
                    is_rajut : inpIsRajut.is(':checked') ? 1 : 0
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

