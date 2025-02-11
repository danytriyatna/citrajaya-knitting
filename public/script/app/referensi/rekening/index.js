

$(document).ready(function () {
    let inpData         = $('#data_id');
    let inpRekeningNo = $('#rekening_no');
    let inpRekeningNama = $('#rekening_an');
    let inpRekeningAn = $('#rekening_bank');
    let inpKeterangan       = $('#keterangan');

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
                            window.location.replace(baseUrl + "/master-data/rekening/delete/" + data_row.id);
                        }
                    }else if(e.target.title === 'edit'){
                        inpData.val(data_row.id)
                        inpRekeningNo.val(data_row.rekening_no)
                        inpRekeningNama.val(data_row.rekening_bank)
                        inpRekeningAn.val(data_row.rekening_an)
                        inpKeterangan.val(data_row.keterangan)

                        isModal.modal("show");
                    }   
                }
            },
            {
                title: "No Rekening", field: "rekening_no", headerSort: false,
                width: "20%"
            },
            {
                title: "Nama Rekening", field: "rekening_no", headerSort: false,
                width: "20%"
            },
            {
                title: "Atasnama", field: "rekening_an", headerSort: false,
                width: "20%"
            },
           
            {
                title: "Keterangan", field: "keterangan", formatter: "html", headerSort: false,
                
            },
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: "/master-data/rekening/list",
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
        inpRekeningNama.val("")
        inpRekeningNo.val("")
        inpRekeningAn.val("")
        inpKeterangan.val("")

        isModal.modal("show");
    });

    $("#btn-save").on("click", function(e){
        e.preventDefault()
        simpanData()
    });

    
    function simpanData() {
        
        let validation = true
        if(inpRekeningNo.val().length == 0) validation = false
        if(inpRekeningNama.val().length == 0) validation = false
        if(inpRekeningAn.val().length == 0) validation = false
    
        if(validation){
            $.ajax({
                type: 'POST',
                url: '/master-data/rekening/simpan',
                data: {
                    dataId : inpData.val(),
                    rekening_no   : inpRekeningNo.val(),
                    rekening_an   : inpRekeningNama.val(),
                    rekening_bank   : inpRekeningAn.val(),
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
