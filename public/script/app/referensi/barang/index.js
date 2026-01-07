

$(document).ready(function () {
    let inpData         = $('#data_id');
    let inpNamaBarang = $('#nama_barang');
    let inpKeterangan       = $('#keterangan');
    let inpHargaSatuan       = $('#harga_satuan');
    let inpIdSatuan       = $('#id_satuan');
    let inpIdWarna       = $('#id_warna');
    let inpIdJenisBarang       = $('#id_jenis_barang');
    let inpStokMinimum       = $('#stok_minimum');

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
                            window.location.replace(baseUrl + "/master-data/barang/delete/" + data_row.id);
                        }
                    }else if(e.target.title === 'edit'){
                        inpData.val(data_row.id)
                        inpNamaBarang.val(data_row.nama_barang)
                        inpKeterangan.val(data_row.keterangan)
                        inpHargaSatuan.val(data_row.harga_satuan).trigger('change');
                        inpStokMinimum.val(data_row.stok_minimum)
                        inpIdSatuan.val(data_row.id_satuan).trigger('change');
                        inpIdWarna.val(data_row.id_warna).trigger('change');
                        
                        inpIdJenisBarang.val(data_row.id_jenis_barang).trigger('change');
                        inpKeterangan.val(data_row.keterangan)

                        isModal.modal("show");
                    }   
                }
            },
            {
                title: "Kode Barang", field: "kode_barang", headerSort: false,
                width: "10%"
            },
            {
                title: "Nama Barang", field: "nama_barang", headerSort: false,
                width: "20%"
            },
            {
                title: "Jenis Barang", field: "nama_jenis_barang", headerSort: false,
                width: "15%"
            },
            {
                title: "Warna", field: "kode_warna", headerSort: false,
                width: "15%"
            },
            {
                title: "Stok Minimum", field: "stok_minimum", headerSort: false,
                width: "10%"
            },
            {
                title: "Satuan", field: "nama_satuan", headerSort: false,
                width: "10%"
            },
            {
                title: "Harga Satuan", field: "harga_satuan", headerSort: false,
                width: "12%",formatter: "money", formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                }, hozAlign:"right",
            },
           
            {
                title: "Keterangan", field: "keterangan", formatter: "html", headerSort: false,
                width:"15%"
            },
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: "/master-data/barang/list",
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
        selectable: false,
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
        inpNamaBarang.val("")
        inpKeterangan.val("")
        inpHargaSatuan.val(0).trigger('change');
        inpStokMinimum.val("")
        inpIdSatuan.val("").trigger('change');
        inpIdWarna.val("").trigger('change');
        inpIdJenisBarang.val("").trigger('change');
        isModal.modal("show");
    });

    $("#btn-save").on("click", function(e){
        e.preventDefault()
        simpanData()
    });

    
    function simpanData() {
        
        let validation = true

        if(inpNamaBarang.val().length == 0) validation = false
        if(inpHargaSatuan.val().length == 0) validation = false
        if(inpIdJenisBarang.val() == null) validation = false
        if(inpIdSatuan.val() == null) validation = false
        if(inpIdWarna.val() == null) validation = false
        if(inpStokMinimum.val().length == 0) validation = false
    
        if(validation){
            $.ajax({
                type: 'POST',
                url: '/master-data/barang/simpan',
                data: {
                    dataId : inpData.val(),
                    nama_barang   : inpNamaBarang.val(),
                    harga_satuan   : inpHargaSatuan.val(),
                    id_satuan   : inpIdSatuan.val(),
                    id_warna   : inpIdWarna.val(),
                    id_jenis_barang   : inpIdJenisBarang.val(),
                    stok_minimum   : inpStokMinimum.val(),
                    keterangan : inpKeterangan.val(),
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
