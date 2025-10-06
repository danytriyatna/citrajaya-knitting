

$(document).ready(function () {
    let inpData         = $('#data_id');
    let inpNamaKonsumen = $('#nama_konsumen');
    let inpAlamat       = $('#alamat');
    let inpNoHP         = $('#no_hp');
    let inpEmail        = $('#email');
    let inpNpwp         = $('#npwp');
    let inpPIC        = $('#pic');

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
                            window.location.replace(baseUrl + "/master-data/konsumen/delete/" + data_row.id);
                        }
                    }else if(e.target.title === 'edit'){
                        inpData.val(data_row.id)
                        inpNamaKonsumen.val(data_row.nama)
                        inpAlamat.val(data_row.alamat)
                        inpNoHP.val(data_row.no_hp)
                        inpEmail.val(data_row.email)
                        inpPIC.val(data_row.pic)
                        setTimeout(() => {
                            getKonsumenStyle()
                            dtListStyle.redraw(true)
                        }, 400);
                        isModal.modal("show");
                    }   
                }
            },
            {
                title: "Nama Konsumen", field: "nama", headerSort: false,
                width: "20%"
            },
            {
                title: "Alamat", field: "alamat", formatter: "html", headerSort: false,
                
            },
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
        ajaxURL: "/master-data/konsumen/list",
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
        inpNamaKonsumen.val("")
        inpAlamat.val("")
        inpNoHP.val("")
        inpEmail.val("")

        isModal.modal("show");
    });

    $("#btn-save").on("click", function(e){
        e.preventDefault()
        simpanData()
    });

    
    function simpanData() {
        
        let validation = true
        if(inpNamaKonsumen.val().length == 0) validation = false
        if(inpNoHP.val().length == 0) validation = false
        if(inpEmail.val().length == 0) validation = false
        if(inpAlamat.val().length == 0) validation = false
        if(inpPIC.val().length == 0) validation = false
    
        if(validation){
            $.ajax({
                type: 'POST',
                url: '/master-data/konsumen/simpan',
                data: {
                    dataId : inpData.val(),
                    nama   : inpNamaKonsumen.val(),
                    alamat : inpAlamat.val(),
                    email  : inpEmail.val(),
                    no_hp  : inpNoHP.val(),
                    npwp   : inpNpwp.val(),
                    pic  : inpPIC.val(),
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


    let buttonRowActionStyle = function(cell) {
        let fmBtnEdit = "";        
        fmBtnEdit = "<button class='btn btn-warning btn-xs open_form' type='button' title='proses'><i class='fa fa-clone' title='proses'></i></button>";
        return fmBtnEdit;
    };


    // set tanle style konsumen yang didapat dari order dan sample 
    let inpStyle = $("#style_id")
    let isModalHrg = $("#modal-form-add-harga");
    let dtListStyle = new Tabulator("#dt-detail-style", {
        columns: [
            {
                title: "No.", formatter: "rownum",  sorter: "string", headerSort:false, align: "center", cssClass: "text-left",
                width:"10%"
            },
            {
                title: "Style", field: "keterangan_style",  sorter: "string", headerSort:false, align: "center", 
            },
            {
                headerSort: false,  
                title: 'Proses', 
                formatter: buttonRowActionStyle,
                width: 100, align: "center", cssClass: "text-center",
                cellClick: function(e, cell) {
                    let row = cell.getRow();
                    let data_row = row.getData();
                    if(e.target.title === 'proses'){
                        inpStyle.val(data_row.id)
                        
                        setTimeout(() => {
                            getKonsumenStyleHarga()
                            dtListStyleProses.redraw(true)
                        }, 400);
                        isModalHrg.modal("show");
                    }   
                }
            },
        ],
        layout: 'fitColumns',
        locale: 'id',
        placeholder: "Tidak ada data",
        pagination: false,
        paginationSize: 99,
        paginationButtonCount: 2,
        paginationDataSent: {
            sorters: "order",
        },
        selectable: false
	});

    function getKonsumenStyle(){
        dtListStyle.clearData();
        $.ajax({
            type: 'POST',
            url: '/master-data/konsumen/get_data_style',
            data: {
                konsumen : inpData.val(),
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
                Swal.close();
                if(response.status == true){
                    // Swal.fire({
                    //     text: response.message,
                    //     icon: 'success',
                    //     showConfirmButton: false,
                    //     timer: 2000
                    // });
                    dtListStyle.setData(response.data)
                }else{
                    // Swal.fire({
                    //     text: response.message,
                    //     icon: 'error',
                    //     showConfirmButton: false,
                    //     timer: 2000
                    // });
                    console.log(response.message)
                }
            },
            error: function (e) {
                let msg = e.responseJSON.message;
                Swal.close();
                console.log(msg);
                // Swal.fire({
                //     text: msg,
                //     icon: 'error',
                //     showConfirmButton: false,
                //     timer: 2000
                // });
            },
        });
    }
    

    // style get list harga 
    let dtListStyleProses = new Tabulator("#dt-detail-harga", {
        columns: [
            {
                title: "No.", formatter: "rownum",  sorter: "string", headerSort:false, align: "center", cssClass: "text-left",
                width:"40"
            },
            {
                title: "Proses", field: "nama_proses",  sorter: "string", headerSort:false, align: "center", 
            },
            {
				title: 'Harga', field: 'harga_borongan', headerSort:false, sorter: 'string', align: "center", editor:"number", 
                width: 220, formatter:"money", cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalc: 'sum'
			} ,
        ],
        layout: 'fitColumns',
        locale: 'id',
        placeholder: "Tidak ada data",
        pagination: false,
        paginationSize: 99,
        paginationButtonCount: 2,
        paginationDataSent: {
            sorters: "order",
        },
        selectable: false
	});

    function getKonsumenStyleHarga(){
        dtListStyleProses.clearData();
        $.ajax({
            type: 'POST',
            url: '/master-data/konsumen/get_data_proses',
            data: {
                konsumen : inpData.val(),
                style : inpStyle.val(),
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
                Swal.close();
                if(response.status == true){
                    dtListStyleProses.setData(response.data)
                }else{
                    console.log(response.message)
                }
            },
            error: function (e) {
                let msg = e.responseJSON.message;
                Swal.close();
                console.log(msg);
            },
        });
    }


    $("#btn-save-harga").on("click", function(e){
        e.preventDefault()

        let listData = JSON.stringify(dtListStyleProses.getData());
        $.ajax({
            type: 'POST',
            url: '/master-data/konsumen/simpanHarga',
            data: {
                konsumen : inpData.val(),
                style : inpStyle.val(),
                list_proses : listData,
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
                    // dtList.setData()
                    Swal.close();
                    isModalHrg.modal("hide");
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
    });
});
