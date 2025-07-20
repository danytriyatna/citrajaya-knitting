$(document).ready(function () {
   setTimeout(() => {
     $('.tabulator-headers').addClass('d-none');
   }, 1000);


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
     const brcStyle            = $("#style_input");
    function cardFormatter(cell, formatterParams, onRendered){
        let data = cell.getRow().getData(); // Ambil data row
        console.log("data", data);
        let status = '';
        let aksi = '';
        if(data.status == 'Draft'){
         status = ` <i class="fa fa-dot-circle text-muted m-e-6"></i>
                    <span class="f-w-700 text-muted">`+data.status+`</span>`

         aksi = `<a href="${data.url_edit}" type="button" class="btn btn-sm btn-warning text-dark edit"> <i class="fa fa-edit"></i> Edit</a>
                <button type="button" class="btn btn-sm btn-info print" data-id="${data.id}"> <i class="fa fa-print"></i> Cetak</button>`;
        }else{
         status = ` <i class="fa fa-check-circle text-success m-e-6"></i>
                    <span class="f-w-700 text-success">`+data.status+`</span>`


        aksi = `<a href="${data.url_edit}" type="button" class="btn btn-sm btn-warning text-dark edit"> <i class="fa fa-edit"></i> Edit</a>
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
                        <h6 class="f-w-700 m-b-6">${data.kode_walkorder}</h6>
                        <p class="f-w-500 m-y-0">${data.ref_kode}</p>
                        <p class="f-w-500 m-y-0">${data.tipe}</p>
                        <p class="f-w-500 m-y-0">${data.keterangan_style}</p>
                        <hr class="m-y-8" />
                        <p class="m-y-0"><i class="fa fa-calendar-day f-s-11"></i>&nbsp; ${formatterDate(data.tgl_transaksi)}</p>
                        <p class="m-y-0"><i class="fa fa-calendar-week f-s-11"></i>&nbsp; <em>Deadline: ${formatterDate(data.tgl_deadline)}</em></p>
                        <p class="m-t-8 badge bg-secondary d-inline-block"><i class="fa fa-user f-s-11"></i>&nbsp; ${data.konsumen_nama}</p>
                        <a class="hover-zoom-rotate" href="${data.file_name}" target="_blank"><img class="m-t-0 d-block object-fit-cover rounded" src="${data.file_name}" alt="Foto Sample" width="160px" height="90px" /></a>
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
            
            // document.querySelector(`.edit[data-id='${data.id}']`).addEventListener('click', ()=>{
            //     fileSalesOrder.val('')
            //     linkFileSalesOrder.attr('src', "")
            //     getDetail(data.id)
            // });
            // document.querySelector(`.print[data-id='${data.id}']`).addEventListener('click', ()=>{
            //     window.open(`${baseUrl}/trans/sales-order/print/${data.id}`, "_blank");
            // });
            // document.querySelector(`.delete[data-id='${data.id}']`).addEventListener('click', ()=>{
            //     if (confirm("Anda yakin akan menghapus data?")) {
            //         window.location.replace(baseUrl + "/" + data.url_delete);
            //     }
            // });
            document.querySelector(`.print[data-id='${data.id}']`).addEventListener('click', ()=>{
                window.open(`${baseUrl}/trans/work-order/print/${data.id}`, "_blank");
            });
            
            let isColumn = [
                {headerSort: false,title:"No", field:"no",   width: "5%"},
                {headerSort: false,  title:"QR", width:"7%", formatter: print_btn,
                    cellClick: function(e, cell) {
                        let row = cell.getRow();
                        let data_row = row.getData();
                        if (e.target.title === 'print-warna') {
                            // console.log(data_row)
                            const xdata = data.ref_data;
                            const inpp_slcUkuran = $("#print_slc_ukuran");
                            const inpp_qty       = $("#print_qty");
                            const inpp_qtyp      = $("#print_qtyp");

                            // inpp_slcUkuran
                            inpp_qty.val(1)
                            inpp_qtyp.val(1)

                            inpp_foto.attr('src', data.file_name);
                            inpp_noSo.html(data.ref_kode)
                            inpp_deskripsi.html(xdata.deskripsi);
                            inpp_warna.html(data_row.colordasar);
                            inpp_tglSample.html(formatterDate(data.tgl_transaksi))
                            inpp_tglDeadline.html(formatterDate(data.tgl_deadline))

                            brcStyle.val(xdata.style)
                            inpp_buyer.html(data.konsumen_nama)

                            // console.log("kolom print", data_row)

                            setTimeout(() => {
                                inpp_trans.html(data_row.id);
                                mdlPrint.modal("show");
                            }, 500);
                        } 
                    }
                },
                // {headerSort: false, cssClass: 'text-start', title:"Colour", field:"colordasar"}
                {headerSort: false, cssClass: 'text-start', title:"Colour", field:"colour"}
            ]

            let total = 0;
            for (const el of data.key_ukuran) {
                const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
                // total += isKey;
                console.log(isKey + " || " + el.kode_ukuran);
                isColumn.push({ 
                    headerSort: false,  
                    title: el.kode_ukuran, 
                    field: isKey, 
                    cssClass: "text-center", 
                    hozAlign: "center", 
                    width: "7%", 
                    // bottomCalc: "sum", // Menambahkan kalkulasi sum di bagian bawah kolom
                    // bottomCalcFormatter: "money", // Format hasil kalkulasi sebagai uang
                    // bottomCalcFormatterParams: {
                    //     decimal: ",",
                    //     thousand: ".",
                    //     symbol: "", // Simbol mata uang Rupiah
                    //     precision: 0 // Tidak ada desimal
                    // }
                });
             }

             // Simpan list key_ukuran global
            let ukuranKeys = data.key_ukuran; // global

            isColumn.push({
                headerSort: false,
                cssClass: "text-center", 
                title: "Total",
                field: "total",
                hozAlign: "center",
                 width: "7%", 
                mutator: function(value, data, type, params, component){
                    let total = 0;
                    for (const el of ukuranKeys) {
                        const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran;
                        total += parseFloat(data[isKey] || 0);
                    }
                    return total;
                },
                bottomCalc: function(values, data, calcParams){
                    return values.reduce((sum, val) => sum + parseFloat(val || 0), 0);
                },
                bottomCalcFormatter: "money",
                bottomCalcFormatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "",
                    precision: 0
                }
            });

            isColumn.push(
                {
                    headerSort: false, cssClass: 'text-center', title:"Amount", field:"total_harga",formatter: "money", 
                    formatterParams: {
                        decimal: ",",
                        thousand: ".",
                        symbol: "Rp",  // Simbol mata uang Rupiah
                        precision: 0,   // Tidak ada desimal
                    },
                    bottomCalc: "sum", // Menambahkan kalkulasi sum di bagian bawah kolom
                    bottomCalcFormatter: "money", // Format hasil kalkulasi sebagai uang
                    bottomCalcFormatterParams: {
                        decimal: ",",
                        thousand: ".",
                        symbol: "Rp", // Simbol mata uang Rupiah
                        precision: 0 // Tidak ada desimal
                    },
                    hozAlign:"right", cssClass: 'text-end', width:"15%"})

            new Tabulator(`#dt-list-detail-${data.id}`, {
                data: data.detail, 
                layout:"fitColumns",
                resizableColumnFit:true,
                // pagination: true, 
                // paginationSize: 10,
                // paginationButtonCount: 5,
                columns: isColumn,
            });
        });
    
        return cardHtml; // Return HTML Card
    }

    let dtList = new Tabulator("#dt-list", {
        columns: [
            {formatter: cardFormatter, hozAlign:"center", widthGrow: 1,headerSort: false},
        ],
        locale: 'id',    
        layout: 'fitColumns',
        ajaxURL: "trans/work-order/list",
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

    // let dtList = new Tabulator("#dt-list", {
    //     columns: [
    //         {
    //             title: " ", field: "aksi", headerSort: false, formatter: "html",
    //             width: 100
    //         },
            
	// 		{
	// 			title: 'No. Work Order', field: 'kode_walkorder', headerSort:false, sorter: 'string',
	// 			width: 160, formatter : "html"
	// 		}, 
				
	// 		{
	// 			title: 'Tgl Work Order', field: 'tgl_transaksi', headerSort:false, sorter: 'string',
	// 			width: 140
	// 		}, 
				
	// 		{
	// 			title: 'No. Referensi', field: 'ref_kode', headerSort:false, sorter: 'string',
	// 			width: 140, 
	// 		}, 

    //         {
	// 			title: 'Tipe', field: 'tipe', formatter : "html", align: "center", cssClass: "text-center", headerSort:false,
    //             width: 120, 
	// 		} ,

    //         {
	// 			title: 'Deadline', field: 'tgl_deadline', formatter : "html", align: "center", cssClass: "text-center", headerSort:false,
    //             width: 120, 
	// 		},

    //         {
	// 			title: 'Buyer', field: 'konsumen_nama', headerSort:false, sorter: 'string',
	// 			width: 250, formatter : "html"
	// 		},

    //         {
	// 			title: 'Style', field: 'keterangan_style', headerSort:false, sorter: 'string',
	// 			width: 220, formatter : "html"
	// 		},

    //         {
	// 			title: 'Qty', field: 'qty', headerSort:false, sorter: 'string',
	// 			width: 120, formatter : "html"
	// 		},

    //         {
	// 			title: 'Prod Result', field: 'qty_prod', formatter : "html", headerSort:false, sorter: 'string',
	// 			width: 120
	// 		} ,

            
    //         {
	// 			title: 'Remain Qty', field: 'qty_remain', headerSort:false, sorter: 'string', align: "center",
    //             width: 120
	// 		} ,

    //         {
	// 			title: 'Prod Status', field: 'status', formatter : "html", headerSort:false, sorter: 'string',
	// 			width: 125
	// 		} ,
				
    //     ],
    //     // layout: 'fitColumns',
    //     ajaxURL: "/trans/work-order/list",
    //     placeholder: "Tidak ada data",
    //     ajaxConfig: "POST",
    //     ajaxSorting: true,
    //     ajaxFiltering: false,
    //     sortMode: "remote",
    //     filterMode: "remote",
    //     minHeight: 300,
    //     ajaxRequesting: function (url, params) {
    //         params.start = params.size * (params.page - 1);
    //         params.length = params.size;
    //         params.filter_trans = $("#filter_status").val()
    //     },
    //     ajaxResponse: function (url, params, response) {
    //         let pageSize = dtList.getPageSize();
    //         let pageNo = dtList.getPage();
    //         let startRow = (pageSize * (pageNo - 1)) + 1;
    //         let endRow = response.data.length + startRow - 1;
    //         if (response.data.length === 0) {
    //             startRow = 0; endRow = 0;
    //         }
    //         let recordsFiltered = parseInt(response.recordsFiltered);
    //         let recordsTotal = parseInt(response.recordsTotal);

    //         $("#table-footer .tabulator-startrow").text(startRow);
    //         $("#table-footer .tabulator-endrow").text(endRow);
    //         $("#table-footer .tabulator-totalrow").text(recordsFiltered);

    //         let elTotalFilteredRow = $("#table-footer .tabulator-totalfilteredrow");
    //         elTotalFilteredRow.text("");
    //         if (recordsTotal > recordsFiltered) {
    //             elTotalFilteredRow.text(" (disaring dari " + recordsTotal
    //                 + " entri keseluruhan)");
    //         }
    //         return response;
    //     },
    //     footerElement: '<div id="table-footer" class="pull-left tabulator-info">'
    //         + 'Menampilkan <span class="tabulator-startrow"></span> - <span class="tabulator-endrow"></span> dari '
    //         + '<span class="tabulator-totalrow"></span> entri<span class="tabulator-totalfilteredrow"></span></div>',
    //     pagination: true,
    //     paginationMode: "remote",
    //     paginationSize: 25,
    //     paginationButtonCount: 10,
    //     dataSendParams: {
    //         sorters: "order"
    //     },
    //     selectableRows: false,
    // });

    let searchThread = null;
    let elSearch = $("#tb-search");
    if (elSearch != null) {
        elSearch.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThread);
            searchThread = setTimeout(function () {
                dtList.setFilter("", "like", elSearch.val());
            }, 600);
        });
    }

    $("#filter_status").on("change",  function(){
        const val = $(this).val()
        dtList.setData();
    });


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
});