

$(document).ready(function () {

    $("[data-politespace]").politespace();

    const inpData           = $('#data_id');
    const inpNoSalesOrder   = $('#no_sales_order');
    const inpDeskripsi      = $('#desc_style');
    const inpStyle          = $('#style');
    const inpBuyer          = $('#select_buyer');
    const inpTglTransaksi   = $('#tgl_sales_order');
    const inpTglDeadline    = $('#tgl_deadline');
    const inpKetSalesOrder  = $('#ket_sales_order');
    const inpUangDP         = $('#uang_dp');
    const inpPoWarna1       = $('#po_warna1');
    const inpPoWarna2       = $('#po_warna2');
    const inpPoWarna3       = $('#po_warna3');
    const inpPoWarna4       = $('#po_warna4');
    const inpPoWarna5       = $('#po_warna5');
    const inpPoWarna6       = $('#po_warna6');
    const inpPoWarna7       = $('#po_warna7');
    const inpPoWarna8       = $('#po_warna8');

    const fileSalesOrder     = $('#fileSalesOrder');
    const fileSalesOrderOld  = $('#fileSalesOrderOld');
    const linkFileSalesOrder = $('#linkFileSalesOrder');
    const deskripsiText      = $('#deskripsiText');
    const tglSalesOrderText  = $('#tglSalesOrderText');
    const buyerText          = $('#buyerText');
    const tglDeadlineText    = $('#tglDeadlineText');
    const noSalesOrderText   = $('#noSalesOrderText');
    const fotoText           = $('#fotoText');
    const rowDet             = $("#rowDet")

    const isModal            = $("#modal-form-add-po");
    const isModalPO          = $("#modal-form-po");
    var idSalesOrder       = null
    var idSalesOrderDet    = null

    const btnSend            = $("#btn-send");
    const brcStyle            = $("#style_input");

    let buttonRowAction = function(cell) {
        let fmBtnDelete = "";        
        let fmBtnEdit = "";        
     
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;
        return fmBtnEdit + " " + fmBtnDelete;
    };

    inpBuyer.on("change", function(){
        let  val = $(this).val()
        if(val.length > 0){
            getSample()
        }
    });

    let dtList = new Tabulator("#dt-list", {
        columns: [
            {formatter: cardFormatter, hozAlign:"center", widthGrow: 1,headerSort: false},
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: "/trans/sales-order/list",
        ajaxConfig: "POST",
        sortMode: "remote",
        filterMode: "remote",
        placeholder: "Tidak ada data",
        height: '1200px',
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

    let defColumDet = [
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
                                        deleteData(data_row.id)
                                        // window.location.replace(baseUrl + "/trans/sales-order/delete/detail" + data_row.id);
                                    }
                                }else if(e.target.title === 'edit'){
                                    getDetailQty(idSalesOrder,data_row.id)
                                }   
                            }
                        },
                        {headerSort: false,title:"Colour", field:"colour"},
                        {headerSort: false,title:"Amount", field:"harga_satuan",formatter: "money", formatterParams: {
                            decimal: ",",
                            thousand: ".",
                            symbol: "Rp",  // Simbol mata uang Rupiah
                            precision: 0,   // Tidak ada desimal
                        }, hozAlign:"right", width: '12%'},
                    ]
    let dtListDetail = new Tabulator("#dt-detail", {
        pagination: true, 
        paginationSize: 10,
        paginationButtonCount: 5,
        columns: defColumDet,
        locale: 'id',    
        layout:"fitColumns",
        resizableColumnFit:true,
        placeholder: "Tidak ada data",
	});

    function calculateTotal(row) {
        if(row){
            let qty = row.qty || 0;
            let harga = row.harga_satuan || 0;
            return qty * harga;
        }
    }

    let buttonQRAction = function(cell){
        if(cell.getData().qty != null){
            let fmBtnQRCode = "";
            fmBtnQRCode = ` <button type="button" class="btn btn-sm btn-info" title='qr code'><i class="fa fa-print" title='qr code'></i></button>`;
            return fmBtnQRCode;
         }
     }

    let dtListDetailQty = new Tabulator("#dt-detail-qty", {
        pagination: true, 
        paginationSize: 10,
        paginationButtonCount: 5,
        columns:[
            {title:"ID", field:"id", visible:false},
            // {
            //     headerSort: false,  
            //     title: 'Aksi', 
            //     formatter: buttonQRAction,
            //     width: '10%', align: "center", cssClass: "text-center",
            //     cellClick: function(e, cell) {
            //         let row = cell.getRow();
            //         let data_row = row.getData();
            //         if (e.target.title === 'qr code') {
            //             generateQRCode(data_row)
            //         } 
            //     }
            // },
            {headerSort: false,title:"No",formatter: "rownum",cssClass:'text-center', hozAlign: "center", width:"8%"},
            {headerSort: false,title:"id_ukuran", field:"id_ukuran", cssClass:'text-center', hozAlign:"center",visible:false},
            {headerSort: false,title:"Ukuran", field:"ukuran", cssClass:'text-center', hozAlign:"center",width:"18%"},
            {headerSort: false,title:"QTY", field:"qty", cssClass:'text-center', hozAlign:"center",width:"10%",editor: "number",cellEdited: updateTotal},
            {headerSort: false,title:"Price", field:"harga_satuan",formatter: "money", formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, cssClass:'text-end', hozAlign:"right",width:"32%",editor: "number",cellEdited: updateTotal},
            {headerSort: false,title:"Total", field:"harga_total",formatter: "money", formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, cssClass:'text-end', hozAlign:"right",width:"32%"},
        ],
     
        locale: 'id',    
        // layout: 'fitColumns',
        placeholder: "Tidak ada data",
        height: '300px',
        pagination:false
	});

    function updateTotal(cell) {
        let row = cell.getRow();
        if (row) { 
            let newTotal = calculateTotal(row.getData());
            row.update({ harga_total: newTotal });
        } 
    }

    
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

     // declarre untuk variable print qr
     const mdlPrint = $("#modal-print-barcode");
     const inpp_foto = $("#fotoPrint");
     const inpp_noSo = $("#noSamplePrint");
     const inpp_deskripsi = $("#deskripsiPrint");
     const inpp_tglSample = $("#tglSamplePrint");
     const inpp_tglDeadline = $("#tglDeadlinePrint");
     const inpp_buyer = $("#buyerPrint");    
     const inpp_warna = $("#warnaPrint");
     const inpp_trans = $("#warnaTrans");

    function cardFormatter(cell, formatterParams, onRendered){
        let data = cell.getRow().getData(); // Ambil data row
        let status = '';
        let aksi = '';
        if(data.status == 'Draft'){
         status = ` <i class="fa fa-dot-circle text-muted m-e-6"></i>
                    <span class="f-w-700 text-muted">`+data.status+`</span>`

         aksi = `<button type="button" class="btn btn-sm btn-warning text-dark edit" data-id="${data.id}"> <i class="fa fa-edit"></i> Edit</button>
                 <button type="button" class="btn btn-sm btn-danger delete" data-id="${data.id}"> <i class="fa fa-trash"></i> Hapus</button>
                 <button type="button" hidden  class="btn btn-sm btn-info print" data-id="${data.id}"> <i class="fa fa-print"></i> Cetak</button>`;
        }else{
         status = ` <i class="fa fa-check-circle text-success m-e-6"></i>
                    <span class="f-w-700 text-success">`+data.status+`</span>`


        aksi = `<button type="button" class="btn btn-sm btn-warning text-dark edit" data-id="${data.id}"> <i class="fa fa-edit"></i> Edit</button>
                <button hidden type="button" class="btn btn-sm btn-danger delete" data-id="${data.id}"> <i class="fa fa-trash"></i> Hapus</button>
                <button type="button"   class="btn btn-sm btn-info print" data-id="${data.id}"> <i class="fa fa-print"></i> Cetak</button>`;
        }
        
        // HTML Card Layout
        var cardHtml = `<div class="card shadow-sm">
                  <div class="card-header">
                    <div class="row">
                      <div class="col-sm-6 text-start">
                          ${aksi}
                      </div>
                      <div class="col-sm-6">
                        <div class="d-flex justify-content-end" style="column-gap: 8px;">

                          <div class="card m-y-0 cursor-pointer">
                            <div class="card-body p-y-4">
                              <div class="d-flex justify-content-start align-items-center f-s-11">
                                `
                                    +status+
                                `
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-sm-3 text-start">
                        <h6 class="f-w-700 m-b-6">${data.kode_sales_order}</h6>
                        <p class="f-w-500 m-y-0">${data.style}</p>
                        <p class="f-w-500 m-y-0">${data.deskripsi}</p>
                        <hr class="m-y-8" />
                        <p class="m-y-0"><i class="fa fa-calendar-day f-s-11"></i>&nbsp; ${formatterDate(data.tgl_transaksi)}</p>
                        <p class="m-y-0"><i class="fa fa-calendar-week f-s-11"></i>&nbsp; <em>Deadline: ${formatterDate(data.tgl_deadline)}</em></p>
                        <p class="m-t-8 badge bg-secondary d-inline-block"><i class="fa fa-user f-s-11"></i>&nbsp; ${data.nama}</p>
                        <a class="hover-zoom-rotate" href="${data.file_gambar}" target="_blank"><img class="m-t-0 d-block object-fit-cover rounded" src="${data.file_gambar}" alt="Foto Sample" width="160px" height="90px" /></a>
                      </div>
                      <div class="col-sm-9">
                           <div id="dt-list-detail-${data.id}" class="table-responsive table-striped"></div>
                      </div>
                    </div>
                  </div>
                </div>`;

        // declarre untuk variable print qr
        const print_btn = () => {
            let btn = `<button type="button" class="btn btn-sm btn-info" data-bs-toggle="modalz" title="print-warna"> <i class="fa fa-print" title="print-warna"></i></button>`;
            return btn
        }
    
        onRendered(()=>{
            
            document.querySelector(`.edit[data-id='${data.id}']`).addEventListener('click', ()=>{
                fileSalesOrder.val('')
                linkFileSalesOrder.attr('src', "")
                getDetail(data.id)
            });
            document.querySelector(`.print[data-id='${data.id}']`).addEventListener('click', ()=>{
                window.open(`${baseUrl}/trans/sales-order/print/${data.id}`, "_blank");
            });
            document.querySelector(`.delete[data-id='${data.id}']`).addEventListener('click', ()=>{
                if (confirm("Anda yakin akan menghapus data?")) {
                    window.location.replace(baseUrl + "/trans/sales-order/delete/list" + data.id);
                }
            });
            
            let isColumn = [
                {headerSort: false,title:"No", field:"no",   width: "5%"},
                {headerSort: false,  title:"QR", width:"7%", formatter: print_btn,
                    cellClick: function(e, cell) {
                        let row = cell.getRow();
                        let data_row = row.getData();
                        if (e.target.title === 'print-warna') {
                            // console.log(data_row)

                            const inpp_slcUkuran = $("#print_slc_ukuran");
                            const inpp_qty       = $("#print_qty");
                            const inpp_qtyp      = $("#print_qtyp");

                            // inpp_slcUkuran
                            inpp_qty.val(1)
                            inpp_qtyp.val(1)

                            inpp_foto.attr('src', data.file_gambar);
                            inpp_noSo.html(data.kode_sales_order)
                            inpp_deskripsi.html(data.deskripsi);
                            inpp_warna.html(data_row.colordasar);
                            inpp_tglSample.html(formatterDate(data.tgl_transaksi))
                            inpp_tglDeadline.html(formatterDate(data.tgl_deadline))

                            brcStyle.val(data.style)
                            inpp_buyer.html(data.nama)

                            setTimeout(() => {
                                inpp_trans.html(data.id);
                                mdlPrint.modal("show");
                            }, 500);
                        } 
                    }
                },
                {headerSort: false, cssClass: 'text-start', title:"Colour", field:"colordasar"}
            ]

            for (const el of data.key_ukuran) {
                const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
                isColumn.push( {headerSort: false,  title:el.kode_ukuran, field: isKey, cssClass: "text-center", hozAlign:"center", width:"7%"} )
            }

            isColumn.push(
                {
                    headerSort: false, cssClass: 'text-center', title:"Amount", field:"total_harga",formatter: "money", 
                    formatterParams: {
                        decimal: ",",
                        thousand: ".",
                        symbol: "Rp",  // Simbol mata uang Rupiah
                        precision: 0,   // Tidak ada desimal
                    },
                    hozAlign:"right", cssClass: 'text-end', width:"15%"})

            new Tabulator(`#dt-list-detail-${data.id}`, {
                data: data.detail, 
                layout:"fitColumns",
                resizableColumnFit:true,
                pagination: true, 
                paginationSize: 10,
                paginationButtonCount: 5,
                columns: isColumn,
            });
        });
    
        return cardHtml; // Return HTML Card
    }

    $("#btn-add").on("click", function(){
        let today = new Date().toISOString().split('T')[0];
        inpData.val("")
        linkFileSalesOrder.attr('src', "")
        inpNoSalesOrder.val("")
        fileSalesOrderOld.val("")
        fileSalesOrder.val("")
        inpDeskripsi.val("")
        inpBuyer.val("").trigger("change")
        inpTglTransaksi.val(formatterDate(today))
        inpTglDeadline.val("")
        inpKetSalesOrder.val("")
        inpStyle.val("")
        inpSample.val("").trigger("change")
        setTimeout(() => {
            inpUangDP.val("0").trigger("change");
        }, 1000);
        rowDet.hide()
        btnSend.hide()
        isModal.modal("show");
    });

    $("#btn-add-detail").on("click", function(){
        idSalesOrder = inpData.val()
        idSalesOrderDet = null
        inpPoWarna1.val('').trigger('change');
        inpPoWarna2.val('').trigger('change');
        inpPoWarna3.val('').trigger('change');
        inpPoWarna4.val('').trigger('change');
        inpPoWarna5.val('').trigger('change');
        inpPoWarna6.val('').trigger('change');
        inpPoWarna7.val('').trigger('change');
        inpPoWarna8.val('').trigger('change');
        getDetailQty(inpData.val(),0)
    });

    $("#btn-save").on("click", function(e){
        e.preventDefault()
        simpanData(0)
    });

    $("#btn-send").on("click", function(e){
        e.preventDefault()
        simpanData(1)
    });


    $("#btn-save-detail").on("click", function(e){
        e.preventDefault()
        simpanDataDetail()
    });

    function formatterDate($date){
        let newDate = new Date($date);
        const options = { day: '2-digit', month: 'long', year: 'numeric' };
        const formattedDate = newDate.toLocaleDateString('id-ID', options);
        return formattedDate
    }
    
    function formatLocaleDate(localeDate) {
    
        var months = {
            "Januari": "01",
            "Februari": "02",
            "Maret": "03",
            "April": "04",
            "Mei": "05",
            "Juni": "06",
            "Juli": "07",
            "Agustus": "08",
            "September": "09",
            "Oktober": "10",
            "November": "11",
            "Desember": "12"
        };

        var parts = localeDate.split(" ");
        var day = parts[0].padStart(2, '0'); 
        var month = months[parts[1]]; 
        var year = parts[2];

        return `${year}-${month}-${day}`;
    }

    function getDetail(id) {
        $.ajax({
            url: `/trans/sales-order/detail/${id}`,
            type: 'GET',
            dataType: 'json', 
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
            success: function(data) {
                
                idSalesOrder = id
                rowDet.show()
                inpData.val(data.id)
                inpDeskripsi.val(data.deskripsi)
                inpNoSalesOrder.val(data.kode_sales_order)
                fileSalesOrderOld.val(data.gambar_id)
                inpKetSalesOrder.val(data.keterangan)
                inpStyle.val(data.style)
                
                
                setTimeout(() => {
                    inpBuyer.val(data.id_konsumen).trigger('change')
                    setTimeout(() => {
                        inpTglDeadline.val(formatterDate(data.tgl_deadline))
                        inpTglTransaksi.val(formatterDate(data.tgl_transaksi))
                        Swal.close();
                        setTimeout(() => {
                            inpSample.attr('value', data.id_sample);
                            inpSample.val(data.id_sample).trigger("change");
                            inpUangDP.val(data.uang_dp).trigger("change");
                        }, 1000);
                    }, 2000);
                }, 1000);
                
                if(data.file_gambar){
                    fileSalesOrderOld.val(data.gambar_id)
                    linkFileSalesOrder.removeClass("d-none")
                    linkFileSalesOrder.attr('src', data.file_gambar)
                }
               
                setColumDetailData(data)

                
                if(data.status == 'Draft'){
                    btnSend.show()
                }else{
                    btnSend.hide()
                }

                isModal.modal("show");

                
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error fetching data:', error);
            }
        });
    }

    function setColumDetailData(data){
        let newColumn =  [
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
                                            deleteData(data_row.id)
                                            // window.location.replace(baseUrl + "/trans/sales-order/delete/detail" + data_row.id);
                                        }
                                    }else if(e.target.title === 'edit'){
                                        getDetailQty(idSalesOrder,data_row.id)
                                    }   
                                }
                            },
                            {headerSort: false,title:"Colour", field:"colour"},
                        ]

        const dataCol = data.key_ukuran

        for (const el of dataCol) {
            const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
            newColumn.push( {headerSort: false,  title:el.kode_ukuran, field: isKey, cssClass: "text-center", hozAlign:"center", width:"7%"} )
        }

        // last column 
        newColumn.push(  
                        {
                            headerSort: false,title:"Amount", field:"total_harga",formatter: "money", 
                            formatterParams: {
                                decimal: ",",
                                thousand: ".",
                                symbol: "Rp",  // Simbol mata uang Rupiah
                                precision: 0,   // Tidak ada desimal
                            },
                            hozAlign:"right", width: '15%'
                        }
                    )

        setTimeout(() => {
            dtListDetail.setColumns(newColumn);
            dtListDetail.setData(data.detail)
            dtListDetail.redraw(true)
        }, 500);
    }

    function getDetailQty(id,idDet) {
        $.ajax({
            url: `/trans/sales-order/detail-qty/${id}/${idDet}`,
            type: 'GET',
            dataType: 'json', 
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
            success: function(data) {
                Swal.close();
                isModal.modal("hide")
                noSalesOrderText.html(data.kode_sales_order)
                deskripsiText.html(data.deskripsi)
                tglSalesOrderText.html(`<i class="fa fa-calendar-day f-s-11"></i>&nbsp; ${formatterDate(data.tgl_transaksi)}`)
                buyerText.html(`<i class="fa fa-user f-s-11"></i>&nbsp; ${data.nama}`)
                fotoText.attr("src",data.file_gambar)
                tglDeadlineText.html(`<i class="fa fa-calendar-week f-s-11"></i>&nbsp; <em>Deadline: ${formatterDate(data.tgl_deadline)}</em>`)
                if(data.detail){
                    idSalesOrderDet = data.detail.id
                    inpPoWarna1.val(data.detail.id_warna_1).trigger('change');
                    inpPoWarna2.val(data.detail.id_warna_2).trigger('change');
                    inpPoWarna3.val(data.detail.id_warna_3).trigger('change');
                    inpPoWarna4.val(data.detail.id_warna_4).trigger('change');
                    inpPoWarna5.val(data.detail.id_warna_5).trigger('change');
                    inpPoWarna6.val(data.detail.id_warna_6).trigger('change');
                    inpPoWarna7.val(data.detail.id_warna_7).trigger('change');
                    inpPoWarna8.val(data.detail.id_warna_8).trigger('change');
                }
                
                dtListDetailQty.setData(data.detailUkuran)
                isModalPO.modal("show");
                setTimeout(() => {
                    dtListDetailQty.redraw(true)
                }, 500);
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error fetching data:', error);
            }
        });
    }


    function deleteData($id) {
        
            $.ajax({
                type: 'POST',
                url: '/trans/sales-order/delete/detail',
                data: {id:$id},
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
                        getDetail(idSalesOrder)
                        dtList.setData()
                        Swal.close();
                    
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
        
    }
    
    function simpanData(send) {
        
        let validation = true
        // if(inpNoSalesOrder.val().length == 0) validation = false
        if(inpDeskripsi.val().length == 0) validation = false
        if(inpBuyer.val().length == 0) validation = false
        if(inpTglDeadline.val().length == 0) validation = false
        if(inpTglTransaksi.val().length == 0) validation = false
        // if(fileSalesOrder[0].files[0] == undefined) validation = false
    
        if(validation){
            var formData = new FormData();
            formData.append("id",inpData.val());
            formData.append("noSalesOrder",inpNoSalesOrder.val());
            formData.append("deskripsi",inpDeskripsi.val());
            formData.append("fileSalesOrder",fileSalesOrder[0].files[0]);
            formData.append("fileIdSalesOrderOld",fileSalesOrderOld.val());
            formData.append("idKonsumen",inpBuyer.val());
            formData.append("tglDeadline",formatLocaleDate(inpTglDeadline.val()));
            formData.append("tglTransaksi",formatLocaleDate(inpTglTransaksi.val()));
            formData.append("keterangan",inpKetSalesOrder.val());
            formData.append("samples", inpSample.val())
            formData.append("uang_dp", inpUangDP.val());
            formData.append("style", inpStyle.val());
            formData.append("submit_data", send);
            
            $.ajax({
                type: 'POST',
                url: '/trans/sales-order/save',
                data: formData,
                processData: false,  // Jangan ubah data menjadi string
            contentType: false,  // Agar jQuery tidak mengatur tipe konten
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

    function simpanDataDetail() {
        if(inpPoWarna1.val() == "")
        {
            return Swal.fire({
                text: "Warna 1 Belum terpilih!",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
        }

        let dataUkuran = dtListDetailQty.getData().filter(x => x.qty && x.harga_satuan);
        if(dataUkuran.length ==0)
            {
                return Swal.fire({
                    text: "Ukuran Minimal satu harus diisi",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
    
            $.ajax({
                type: 'POST',
                url: '/trans/sales-order/save-detail',
                data: {
                    warna1:inpPoWarna1.val(),
                    warna2:inpPoWarna2.val(),
                    warna3:inpPoWarna3.val(),
                    warna4:inpPoWarna4.val(),
                    warna5:inpPoWarna5.val(),
                    warna6:inpPoWarna6.val(),
                    warna7:inpPoWarna7.val(),
                    warna8:inpPoWarna8.val(),
                    dataUkuran: dataUkuran,
                    idSalesOrder:idSalesOrder,
                    idSalesOrderDet:idSalesOrderDet,
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
                        Swal.close();
                        getDetail(idSalesOrder)
                        isModalPO.modal("hide");
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
        
    }
    
    var isSampleData = [];
    let inpSample = $("#select_samples")
    function getSample(){
        $.ajax({
            url: '/trans/sales-order/getSample', // point to server-side controller method
            dataType: "json", // what to expect back from the server
            type: "post",
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
            data: { 
                  'buyers': inpBuyer.val(),
              },
            success: function (res) {
              Swal.close();
              inpSample.empty()
              if(res.status){
                isSampleData = res.data
                inpSample.append($("<option></option>").attr("value", 0).text("- Pilih Sample -"));
                $.each(isSampleData, function(key,value) {
                    inpSample.append($("<option></option>").attr("value", value.id).text(value.kode_sample + " : " + value.style));
                });

                if(inpData.val().length == 0){
                    // inpTglTransaksi.datepicker('setDate', isSampleData[0].tgl_transaksi); //.val(changeTgl(isSampleData[0].tgl_transaksi))
                    // inpTglDeadline.datepicker('setDate', isSampleData[0].tgl_deadline); //.val(changeTgl(isSampleData[0].tgl_transaksi))
                    // // inpTglDeadline.val(changeTgl(isSampleData[0].tgl_deadline))
                    // inpKetSalesOrder.val(isSampleData[0].deskripsi)
                    // inpDeskripsi.val(isSampleData[0].deskripsi)
                }else{
                    setTimeout(() => {
                        // console.log(inpSample.attr('value'))
                        inpSample.attr('value') != undefined ? inpSample.val(inpSample.attr('value')).trigger('change') : ''
                    }, 500);
                }
                
                
              }else{
                  console.log(res.message)
              }
            },
            error: function (res) {
              let msg = res.responseJSON.message;
              console.log(res.responseJSON.message)
              Swal.close();
            },
          });
    }

    function changeTgl(tgl){
        let splits = tgl.split('-');

        return splits[2] + '-' + splits[1] + '-' + splits[0]
    }

    inpSample.on("change", function(){
        let val = $(this).val()
        $(this).val(val)
        let isin = isSampleData.filter((isi) => val == isi.id);
        if(isin.length > 0){
            if(inpData.val().length == 0){
               setTimeout(() => {
                    inpTglTransaksi.val("")
                    inpTglDeadline.val("")
                    let tglTr = isin[0].tgl_transaksi.split('-')
                    let tglTransaksi = tglTr['2'] + '-' + tglTr['1']+ '-' + tglTr['0']
                    // inpTglTransaksi.datepicker('setDate', tglTransaksi); //.val(changeTgl(isin[0].tgl_transaksi))
                    let tglD = isin[0].tgl_deadline.split('-')
                    let tglDead = tglD['2'] + '-' + tglD['1']+ '-' + tglD['0']
                    // inpTglDeadline.datepicker('setDate', tglDead); //.val(changeTgl(isin[0].tgl_transaksi))
                    inpStyle.val(isin[0].style)
                    setTimeout(() => {
                        inpTglDeadline.val(formatterDate(isin[0].tgl_deadline))
                        inpTglTransaksi.val(formatterDate(isin[0].tgl_transaksi))
                    }, 600);
                    inpKetSalesOrder.val(isin[0].deskripsi)
                    inpDeskripsi.val(isin[0].deskripsi)
               }, 1000);
            }
        }
    });

    function generateQRCode(data){
        $.ajax({
            type: 'POST',
            url: '/trans/sales-order/generate',
            data:{data:JSON.stringify(data)},
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
                    const blob = base64ToBlob(response.file_base64, 'image/png');
                    const url = URL.createObjectURL(blob);
                    const downloadLink = document.createElement('a');
                    downloadLink.href = url;  
                    downloadLink.download = response.file_name; 

                    downloadLink.click();
                
                    URL.revokeObjectURL(url);
                    Swal.close();
                
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
    }

    $("#btn-cetak-print").on('click', function (e) {
        e.preventDefault()

        //   const inpp_slcWarna = $("#print_slc_warna");
        const inpp_slcUkuran = $("#print_slc_ukuran");
        const inpp_qty       = $("#print_qty");
        const inpp_qtyp      = $("#print_qtyp");

        // mdlPrint
        const dt_noSample = inpp_noSo.html()
        const dt_deskripsi = inpp_deskripsi.html()
        const dt_buyer = inpp_buyer.html()
        const dt_warna = inpp_warna.html()
        const dt_trans = inpp_trans.html()

        const dt_style = brcStyle.val()
        // inpp_trans

        // Query parameters
        let params = {
            ukuran : inpp_slcUkuran.val(),
            qty : inpp_qty.val(),
            qtyp : inpp_qtyp.val(),
            noSample : dt_noSample,
            deskripsi : dt_deskripsi,
            buyer : '',
            warna : dt_warna,
            trans : dt_trans,
            style : dt_style
          };
  
          // Buat query string
          let queryString = $.param(params); // Convert objek ke query string
          let fullUrl = `trans/sales-order/generate?${queryString}`;
  
          // Buka link di tab baru
          window.open(fullUrl, '_blank');
        //   setTimeout(() => {
        //     // inpp_trans.html(data.id);
        //     mdlPrint.modal("hide");
        // }, 500);

    });

    function base64ToBlob(base64, contentType = '', sliceSize = 512) {
        const byteCharacters = atob(base64); // Hapus prefix "data:image/png;base64,"
        const byteArrays = [];
    
        for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            const slice = byteCharacters.slice(offset, offset + sliceSize);
            const byteNumbers = new Array(slice.length);
            
            for (let i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }
    
            const byteArray = new Uint8Array(byteNumbers);
            byteArrays.push(byteArray);
        }
    
        return new Blob(byteArrays, { type: contentType });
    }


});

function readURL(input,id) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        const imageUrl = e.target.result;
        const imgElement = document.getElementById('linkFileSalesOrder');
        imgElement.classList.remove("d-none");
        imgElement.src = imageUrl; // Set src dari <img> ke data URL
      };
      reader.readAsDataURL(input.files[0]);
    }
  }