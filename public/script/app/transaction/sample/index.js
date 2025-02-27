

$(document).ready(function () {
    let inpData          = $('#data_id');
    let inpDeskripsi     = $('#desc_style');
    let inpStyle         = $('#style');
    let inpBuyer         = $('#select_buyer');
    let inpTglTransaksi  = $('#tgl_sample');
    let inpTglDeadline   = $('#tgl_deadline');
    let inpKetSample     = $('#ket_sample');
    let inpPoWarna1      = $('#po_warna1');
    let inpPoWarna2      = $('#po_warna2');
    let inpPoWarna3      = $('#po_warna3');
    let inpPoWarna4      = $('#po_warna4');
    let inpPoWarna5      = $('#po_warna5');
    let inpPoWarna6      = $('#po_warna6');
    let inpPoWarna7      = $('#po_warna7');
    let inpPoWarna8      = $('#po_warna8');

    let fileSample       = $('#fileSample');
    let fileSampleOld    = $('#fileSampleOld');
    let linkFileSample   = $('#linkFileSample');
    let deskripsiText    = $('#deskripsiText');
    let tglSampleText    = $('#tglSampleText');
    let buyerText        = $('#buyerText');
    let tglDeadlineText  = $('#tglDeadlineText');
    let noSampleText     = $('#noSampleText');
    let fotoText         = $('#fotoText');
    let rowDet           = $("#rowDet")
    let noSample         = $("#no_sample");

    let isModal          = $("#modal-form-add-po");
    let isModalPO        = $("#modal-form-po");
    var idSample         = null
    var idSampleDet      = null
    var status           = null


    // conf function 
    let cellMoney = function(cell, formatterParams){
        const isEditable = cell.getElement().className.indexOf('tabulator-editable') >= 0
        let classN = `text-right tabulator-cell text-end${isEditable ? ' tabulator-editable' : ''}`;
        cell.getElement().className = classN;
    
        let isVal = number_format(cell.getValue(), 2, ',', '.'); 
        return isVal; //return the contents of the cell;
    }

    function number_format (number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
    
    // Fungsi untuk mengambil nilai bottomCalc
    function getBottomCalcValue(table, columnField) {
        // Ambil kolom berdasarkan field
        let column = table.getColumn(columnField);

        if (!column) {
            console.error("Kolom tidak ditemukan:", columnField);
            return null;
        }

        // Ambil nilai hasil bottomCalc
        var calcValue = table.getCalcResults().bottom.qty; // Hasil bottomCalc
        return calcValue;
    }

    let buttonQRAction = function(cell){
       if(cell.getData().id){
           let fmBtnQRCode = "";
           fmBtnQRCode = ` <button type="button" class="btn btn-sm btn-info" title='qr code'><i class="fa fa-print" title='qr code'></i></button>`;
           return fmBtnQRCode;
        }
    }

    let buttonRowAction = function(cell) {
        let fmBtnDelete = "";        
        let fmBtnEdit = "";        

        if (status == 0){
            fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        }
       
        fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;

        return fmBtnEdit + " " + fmBtnDelete;
    };

    let dtList = new Tabulator("#dt-list", {
        columns: [
            {formatter: cardFormatter, hozAlign:"center", widthGrow: 1,headerSort: false},
        ],
        responsiveLayout: true, // Untuk membuat tabel responsif
        layout: "fitColumns",
        locale: 'id',    
        ajaxURL: "/trans/sample/list",
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

    let dtListDetail = new Tabulator("#dt-detail", {
        pagination: true, 
        paginationSize: 10,
        paginationButtonCount: 5,
        columns:[
            {
                headerSort: false,  
                title: 'Aksi', 
                formatter: buttonRowAction,
                width: '15%', align: "center", cssClass: "text-center",
                cellClick: function(e, cell) {
                    let row = cell.getRow();
                    let data_row = row.getData();
                    if (e.target.title === 'delete') {
                        Swal.fire({
                            title: "Apakah anda yakin ingin menghapus data?",
                            icon: 'question',
                            confirmButtonText: 'Hapus',
                            confirmButtonColor: '#dc3545',
                            showCancelButton: true,
                            cancelButtonText: 'Batal',
                            cancelButtonColor: '#6C757D'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteData(data_row.id)
                            }
                        })
                    }else if(e.target.title === 'edit'){
                        getDetailQty(idSample,data_row.id)
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
        ],
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
            {headerSort: false, title:"No",formatter: "rownum",cssClass:'text-center', hozAlign: "center", width:"8%"},
            {headerSort: false, title:"id_ukuran", field:"id_ukuran", cssClass:'text-center', hozAlign:"center",visible:false},
            {headerSort: false, title:"Ukuran", field:"ukuran", cssClass:'text-center', hozAlign:"center",width:"18%"},
            {
                headerSort: false, title:"QTY", field:"qty", cssClass:'text-center', hozAlign:"center",width:"10%",editor: "number",cellEdited: updateTotal,
                bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney
            },
            {
                headerSort: false, title:"Price", field:"harga_satuan",formatter: "money", formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                }, hozAlign:"right",width:"32%",editor: "number",cellEdited: updateTotal,
                bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney
            },
            {
                headerSort: false, title:"Total", field:"harga_total",formatter: "money", formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                }, hozAlign:"right",width:"32%",
                bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney
            },
        ],
     
        locale: 'id',    
        // layout: 'fitColumns',
        height: '300px',
        placeholder: "Tidak ada data",
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
    const inpp_noSample = $("#noSamplePrint");
    const inpp_deskripsi = $("#deskripsiPrint");
    const inpp_tglSample = $("#tglSamplePrint");
    const inpp_tglDeadline = $("#tglDeadlinePrint");
    const inpp_buyer = $("#buyerPrint");    
    const inpp_warna = $("#warnaPrint");
    const inpp_trans = $("#warnaTrans");

    function cardFormatter(cell, formatterParams, onRendered){
        let data = cell.getRow().getData(); // Ambil data row
        // console.log(data)
        let btnAksi = `<button type="button" class="btn btn-sm btn-warning text-dark edit" data-id="${data.id}"> <i class="fa fa-edit"></i> Edit</button>
                        <button type="button" class="btn btn-sm btn-danger delete" data-id="${data.id}"> <i class="fa fa-trash"></i> Hapus</button>
                         <button type="button" hidden  class="btn btn-sm btn-info print" data-id="${data.id}"> <i class="fa fa-print"></i> Cetak</button>`
        let status = ` <i class="fa fa-dot-circle text-muted m-e-6"></i>
                    <span class="f-w-700 text-muted">`+data.status+`</span>`
                    console.log(data.status)
        if(data.status === 'Submit' || data.status === 'Approval'){
            btnAksi = `<button type="button" class="btn btn-sm btn-warning text-dark edit" data-id="${data.id}"> <i class="fa fa-edit"></i> Edit</button>
            <button type="button" hidden class="btn btn-sm btn-danger delete" data-id="${data.id}"> <i class="fa fa-trash"></i> Hapus</button>
            <button type="button"  class="btn btn-sm btn-info print" data-id="${data.id}"> <i class="fa fa-print"></i> Cetak</button>
            `
             status = ` <i class="fa fa-check-circle text-success m-e-6"></i>
                    <span class="f-w-700 text-success">`+data.status+`</span>`
        }
        
        var cardHtml = `<div class="card shadow-sm">
                  <div class="card-header">
                    <div class="row">
                      <div class="col-sm-6 text-start">
                        ${btnAksi}
                      </div>
                      <div class="col-sm-6">
                        <div class="d-flex justify-content-end" style="column-gap: 8px;">
                          
                          <div class="card m-y-0 cursor-pointer">
                            <div class="card-body p-y-4">
                              <div class="d-flex justify-content-start align-items-center f-s-11">
                                ${status}
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
                        <h6 class="f-w-700 m-b-6">${data.kode_sample}</h6>
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
                fileSample.val(null)
                linkFileSample.attr('src', "")
                linkFileSample.addClass("d-none")
                getDetail(data.id)
            });
            
            document.querySelector(`.print[data-id='${data.id}']`).addEventListener('click', ()=>{
                window.open(`${baseUrl}/trans/sample/print/${data.id}`, "_blank");
            });
            document.querySelector(`.delete[data-id='${data.id}']`).addEventListener('click', ()=>{
                Swal.fire({
                    title: "Apakah anda yakin ingin menghapus data?",
                    icon: 'question',
                    confirmButtonText: 'Hapus',
                    confirmButtonColor: '#dc3545',
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    cancelButtonColor: '#6C757D'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.replace(baseUrl + "/trans/sample/delete/list" + data.id);
                    }
                })
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
                            inpp_noSample.html(data.kode_sample)
                            inpp_deskripsi.html(data.deskripsi);
                            inpp_warna.html(data_row.colordasar);
                            inpp_tglSample.html(formatterDate(data.tgl_transaksi))
                            inpp_tglDeadline.html(formatterDate(data.tgl_deadline))
                            inpp_buyer.html(data.nama)

                            setTimeout(() => {
                                // inpp_trans.html(data.id);
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
        linkFileSample.addClass("d-none")
        linkFileSample.attr('src', "")
        fileSampleOld.val("")
        fileSample.val("")
        inpDeskripsi.val("")
        inpBuyer.val("").trigger("change")
        inpTglTransaksi.val(formatterDate(today))
        inpTglDeadline.val("")
        inpKetSample.val("")
        noSample.val("");
        inpStyle.val("");
        rowDet.hide()
        $("#btn-save").hide()
        $("#btn-draft").show()
        isModal.modal("show");
    });

    $("#btn-add-detail").on("click", function(){
        idSample = inpData.val()
        idSampleDet = null
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
        if(dtListDetail.getData().length > 0){
            Swal.fire({
                title: "Apakah anda ingin mensubmit data Sample ?",
                icon: 'question',
                confirmButtonText: 'Simpan',
                confirmButtonColor: '#198754',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                cancelButtonColor: '#6C757D'
            }).then((result) => {
                if (result.isConfirmed) {
                    simpanData(1)
                }
            })
        } else{
            Swal.fire({
                text: "Detail data harus diisi",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });
    $("#btn-draft").on("click", function(e){
        e.preventDefault()
        simpanData(0)
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
            url: `/trans/sample/detail/${id}`,
            type: 'GET',
            dataType: 'json', 
            success: function(data) {
                idSample = id
                status = data.status
                rowDet.show()
                if(data.status == 1){
                    $("#btn-save").hide()
                    $("#btn-draft").hide()
                    $("#btn-add-detail").hide()
                } else{
                    $("#btn-save").show()
                    $("#btn-draft").show()
                    $("#btn-add-detail").show()
                }
                inpData.val(data.id)
                inpDeskripsi.val(data.deskripsi)
                fileSampleOld.val(data.gambar_id)
                inpKetSample.val(data.keterangan)
                inpBuyer.val(data.id_konsumen).trigger('change')
                noSample.val(data.kode_sample);
                inpStyle.val(data.style);
                inpTglDeadline.val(formatterDate(data.tgl_deadline))
                inpTglTransaksi.val(formatterDate(data.tgl_transaksi))
                if(data.file_gambar){
                    fileSampleOld.val(data.gambar_id)
                    linkFileSample.removeClass("d-none")
                    linkFileSample.attr('src', data.file_gambar)
                }
                
                // dtListDetail.setData(data.detail)
                setColumDetailData(data)
                isModal.modal("show");
                
            },
            error: function(xhr, status, error) {
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
                                        getDetailQty(idSample,data_row.id)
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
        dtListDetailQty.setData([])
        $.ajax({
            url: `/trans/sample/detail-qty/${id}/${idDet}`,
            type: 'GET',
            dataType: 'json', 
            success: function(data) {
                isModal.modal("hide")
                if(status == 1){
                    $("#btn-save-detail").hide()
                } else{
                    $("#btn-save-detail").show()
                }
                // console.log(data);
                noSampleText.html(data.kode_sample)
                deskripsiText.html(data.deskripsi)
                tglSampleText.html(`<i class="fa fa-calendar-day f-s-11"></i>&nbsp; ${formatterDate(data.tgl_transaksi)}`)
                buyerText.html(`<i class="fa fa-user f-s-11"></i>&nbsp; ${data.nama}`)
                fotoText.attr("src",data.file_gambar)
                tglDeadlineText.html(`<i class="fa fa-calendar-week f-s-11"></i>&nbsp; <em>Deadline: ${formatterDate(data.tgl_deadline)}</em>`)
                if(data.detail){
                    idSampleDet = data.detail.id
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

                dtListDetailGram.setData(data.detail_gram)
                isModalPO.modal("show");
                setTimeout(() => {
                    dtListDetailQty.redraw(true)
                    dtListDetailGram.redraw(true);

                    let tableColumn = dtListDetailGram.getData()
                    let total_gram = 0;
                    if(tableColumn.length > 0){
                        let index_total = tableColumn.length - 1;
                        total_gram = tableColumn[index_total].value
                    }
                    updateRow([], total_gram)
                }, 500);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    }

    function generateQRCode(data){
        $.ajax({
            type: 'POST',
            url: '/trans/sample/generate',
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
        const dt_noSample = inpp_noSample.html()
        const dt_deskripsi = inpp_deskripsi.html()
        const dt_buyer = inpp_buyer.html()
        const dt_warna = inpp_warna.html()
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
          };
  
          // Buat query string
          let queryString = $.param(params); // Convert objek ke query string
          let fullUrl = `trans/sample/generate?${queryString}`;
  
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

    function deleteData($id) {
        
            $.ajax({
                type: 'POST',
                url: '/trans/sample/delete/detail',
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
                        getDetail(idSample)
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
    
    function simpanData(status) {
        
        let validation = true
        if(inpDeskripsi.val().length == 0) validation = false
        if(inpBuyer.val().length == 0) validation = false
        if(inpTglDeadline.val().length == 0) validation = false
        if(inpTglTransaksi.val().length == 0) validation = false
        if(fileSample[0].files[0] == undefined && fileSampleOld.val().length == 0) validation = false
    
        if(validation){
            var formData = new FormData();
            formData.append("id",inpData.val());
            formData.append("status",status);
            formData.append("style", inpStyle.val());
            formData.append("deskripsi",inpDeskripsi.val());
            formData.append("fileSample",fileSample[0].files[0] == undefined ? null : fileSample[0].files[0] );
            formData.append("fileIdSampleOld",fileSampleOld.val());
            formData.append("idKonsumen",inpBuyer.val());
            formData.append("tglDeadline",formatLocaleDate(inpTglDeadline.val()));
            formData.append("tglTransaksi",formatLocaleDate(inpTglTransaksi.val()));
            formData.append("keterangan",inpKetSample.val());
            let data = dtListDetail.getData()
            if (data){
                const totalHarga = data.reduce((sum, item) => sum + parseFloat(item.harga_satuan), 0);
                const totalQty = data.reduce((sum, item) => sum + parseInt(item.s) +parseInt(item.m)+parseInt(item.l)+parseInt(item.xl)+parseInt(item.xxl)+parseInt(item.xxxl)+parseInt(item.all)     , 0);
                formData.append("qty",totalQty)
                formData.append("hargaTotal",totalHarga)
            }
            $.ajax({
                type: 'POST',
                url: '/trans/sample/save',
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
        
        let dtGram = dtListDetailGram.getData()
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
                url: '/trans/sample/save-detail',
                data: {
                    warna1:inpPoWarna1.val(),
                    warna2:inpPoWarna2.val(),
                    warna3:inpPoWarna3.val(),
                    warna4:inpPoWarna4.val(),
                    warna5:inpPoWarna5.val(),
                    warna6:inpPoWarna6.val(),
                    warna7:inpPoWarna7.val(),
                    warna8:inpPoWarna8.val(),
                    dataGram: dtGram,
                    dataUkuran: dataUkuran,
                    idSample:idSample,
                    idSampleDet:idSampleDet,
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
                        getDetail(idSample)
                        dtList.setData()
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

    $('#style').autocomplete({
        appendTo: '#modal-form-add-po',
		source: function( request, response ) {
            // console.log(inpBuyer.val())
		  if(inpBuyer.val() != ''){
            $.ajax({
                url: "/trans/sample/get-style-konsumen",
                dataType: "json",
                data: {
                  kata_kunci : request.term,
                  konsumen   : inpBuyer.val(),
                },
                type : 'post',
                success: function( res ) {
                    // console.log(res)
                  if(res.status){
                      response(res.slc);
                  }else{
                      console.log(res.msg);
                  }
                }
              });
          }else{
            inpDeskripsi.val('')
            Swal.fire({
                text: 'Pilih Konsumen/Buyer terlebih dahulu !',
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
          }
		},
		minLength: 0,
		select: function( event, ui ) {
            // console.log(ui)
			// addItem(ui.item.data);
		},
		open: function() {
		  $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
		},
		close: function() {
		  $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
		//   $( this ).val("");
		}
	});

     // start table detail warna 
     let detailQty = 0;
     $("#btn-refresh-gram").on("click", function(e) {
        e.preventDefault();
        setGramasi()
     });

     let inpDetailLoss = $("#loss_perc");
     function setGramasi(){
        detailQty = getBottomCalcValue(dtListDetailQty, 'qty');
        loss = inpDetailLoss.val().length > 0 ? inpDetailLoss.val() : 0

        Swal.fire({
            title: "Muat Ulang data Gramasi akan menghilangkan data sebelumnya",
            icon: 'question',
            confirmButtonText: 'Ya',
            confirmButtonColor: '#dc3545',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6C757D'
        }).then((result) => {
            if (!result.isConfirmed) {
                dtListDetailGram.setData([]);
                return false
            }
        })

        let gramData = [];
        let gramIsi = {
            'qty' : detailQty,
            'loss' : loss,
            'id_warna' : '',
            'kode_warna' : '',
            'persen' : 0,
            'gram' : 0,
            'gram_nd' : 0,
            'kg' : 0,
            'kg_loss' : 0,
            'total' : 0,
        }


        let text1 = $("#po_warna1 option:selected").text();
        if(inpPoWarna1.val().length > 0){
            let arrW1 = gramIsi
            arrW1.id_warna = inpPoWarna1.val()
            arrW1.kode_warna = text1

            gramData.push({
                'id_warna' : inpPoWarna1.val(),
                'kode_warna' : text1,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }

        let text2 = $("#po_warna2 option:selected").text();
        if(inpPoWarna2.val().length > 0){
            let arrW2 = gramIsi
            arrW2.id_warna = inpPoWarna2.val()
            arrW2.kode_warna = text2
            gramData.push({
                'id_warna' : inpPoWarna2.val(),
                'kode_warna' : text2,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }

        let text3 = $("#po_warna3 option:selected").text();
        if(inpPoWarna3.val().length > 0){
            let arrW3 = gramIsi
            arrW3.id_warna = inpPoWarna3.val()
            arrW3.kode_warna = text3
            gramData.push({
                'id_warna' : inpPoWarna3.val(),
                'kode_warna' : text3,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }

        let text4 = $("#po_warna4 option:selected").text();
        if(inpPoWarna4.val().length > 0){
            let arrW4 = gramIsi
            arrW4.id_warna = inpPoWarna4.val()
            arrW4.kode_warna = text4
            gramData.push({
                'id_warna' : inpPoWarna4.val(),
                'kode_warna' : text4,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }

        let text5 = $("#po_warna5 option:selected").text();
        if(inpPoWarna5.val().length > 0){
            let arrW5 = gramIsi
            arrW5.id_warna = inpPoWarna5.val()
            arrW5.kode_warna = text5
            gramData.push({
                'id_warna' : inpPoWarna5.val(),
                'kode_warna' : text5,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }

        let text6 = $("#po_warna6 option:selected").text();
        if(inpPoWarna6.val().length > 0){
            let arrW6 = gramIsi
            arrW6.id_warna = inpPoWarna6.val()
            arrW6.kode_warna = text6
            gramData.push({
                'id_warna' : inpPoWarna6.val(),
                'kode_warna' : text6,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }

        let text7 = $("#po_warna7 option:selected").text();
        if(inpPoWarna7.val().length > 0){
            let arrW7 = gramIsi
            arrW7.id_warna = inpPoWarna7.val()
            arrW7.kode_warna = text7
            gramData.push({
                'id_warna' : inpPoWarna7.val(),
                'kode_warna' : text7,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }

        let text8 = $("#po_warna8 option:selected").text();
        if(inpPoWarna8.val().length > 0){
            let arrW8 = gramIsi
            arrW8.id_warna = inpPoWarna8.val()
            arrW8.kode_warna = text8
            gramData.push({
                'id_warna' : inpPoWarna8.val(),
                'kode_warna' : text8,
                'qty' : detailQty,
                'loss' : loss,
                'persen' : 0,
                'gram' : 0,
                'gram_nd' : 0,
                'kg' : 0,
                'kg_loss' : 0,
                'total' : 0,
            })
        }
        // console.log(gramData);
        setTimeout(() => {
            dtListDetailGram.setData(gramData);
        }, 500);
     }

     
     let dtListDetailGram = new Tabulator("#dt-detail-gram", {
         columns: [
                 {
                     title: "Colour", field: "kode_warna",  sorter: "string", headerSort:false, align: "center", cssClass: "text-left",
                     width:"16%"
                 },
                 {
                     title: "%", field: "persen",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                     width:"14%"
                 },
                 {
                     title: "GRAM", field: "gram",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end tabulator-editable",
                     width:"14%", editor: "number", bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney,
                     cellEdited: function (cell) {
 
                         let qty = parseInt(detailQty)
                        //  console.log(qty)
                         // Dapatkan baris data yang telah diedit
                         let rowData = cell.getRow().getData();
                         let tableColumn = cell._cell.column.cells;
                         let total_gram = 0;
                         if(tableColumn.length > 0){
                             let index_total = tableColumn.length - 1;
                             total_gram = tableColumn[index_total].value
                         }
                         updateRow(rowData, total_gram)
                         let val_gram = rowData.gram ? rowData.gram : 0;
                         // let val_persen = total_gram > 0 ? (rowData.gram/total_gram) * 100 : 0;
                         //     val_persen = val_persen > 0 ? val_persen.toFixed(2) : 0;
                         let val_gram_nd = val_gram * qty;
                         let val_kg = val_gram_nd / 1000;
                             // val_kg = val_kg > 0 ? val_kg.toFixed(2) : 0;
                         let val_kg_loss = inpDetailLoss.val().length > 0 ? (val_kg * inpDetailLoss.val())/ 100 : 0
                             // val_kg_loss = val_kg_loss > 0 ? val_kg_loss.toFixed(2) : 0;
 
                         let val_total = parseFloat(val_kg) + parseFloat(val_kg_loss);
                             // val_total = val_total > 0 ? val_total.toFixed(2) : 0
                         let val_kuota = 0;
                             // val_kuota    = val_kuota    > 0 ? val_kuota   .toFixed(2) : 0
                         let val_kuota_tambah =  val_kuota - val_total
             
                         // Set nilai total di baris yang sama
                         cell.getRow().update({ 
                             // persen: val_persen,
                             gram_nd: val_gram_nd,
                             kg: val_kg,
                             kg_loss: val_kg_loss,
                             total: val_total,
                             kuota: val_kuota,
                             kuota_tambah: val_kuota_tambah,
                          });
                     },
                 },
                 {
                     title: "NEEDS<br>(GRAM)", field: "gram_nd",  sorter: "string", headerSort:false,  align: "center", cssClass: "text-end",
                     width:"14%", bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney,
                 },
 
                 {
                     title: "IN KG", field: "kg",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                     width:"14%", bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney,
                 },
 
                 {
                     title: "LOSS<br>(KG)", field: "kg_loss",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                     width:"14%", bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney,
                 },
 
                 {
                     title: "NFP (KG)", field: "total",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                     width:"14%", bottomCalc:"sum", bottomCalcFormatter: cellMoney, formatter: cellMoney,
                 }
             ],
             locale: 'id',
            //  layout: 'fitColumns',
             height: '300px',
             placeholder: "Tidak ada data",
             pagination: false,
             paginationSize: 99,
             paginationButtonCount: 2,
             paginationDataSent: {
                 sorters: "order",
             },
             selectableRows: false
     });

     inpDetailLoss.on("change", function(){
        let val = $(this).val()
        let rows = dtListDetailGram.getRows();
        rows.forEach(row => {
            let rowData = row.getData();
            let val_kg = rowData.kg;

            let val_kg_loss = val.length > 0 ? (val_kg * val) / 100 : 0
                val_kg_loss = val_kg_loss > 0 ? val_kg_loss.toFixed(2) : 0;

            let val_total = parseFloat(val_kg) + parseFloat(val_kg_loss);
                val_total = val_total > 0 ? val_total.toFixed(2) : 0

            let val_kuota = 0;//parseFloat(val_kg) - parseFloat(val_kg_loss);
                val_kuota    = val_kuota    > 0 ? val_kuota   .toFixed(2) : 0

            let val_kuota_tambah = val_kuota - val_total;

            row.update({
                kg_loss: val_kg_loss,
                total: val_total,
                kuota: val_kuota,
                kuota_tambah: val_kuota_tambah,
            });
        });
    });

     function updateRow(data, total){
        let rows = dtListDetailGram.getRows();
        rows.forEach(row => {
            let rowData = row.getData();
            let val_persen = total > 0 ? (rowData.gram/total) * 100 : 0;
                             val_persen = val_persen > 0 ? val_persen.toFixed(2) : 0;
            row.update({ persen: val_persen });
        });
    }   
    
});

function readURL(input,id) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        const imageUrl = e.target.result;
        const imgElement = document.getElementById('linkFileSample');
        imgElement.classList.remove("d-none");
        imgElement.src = imageUrl; // Set src dari <img> ke data URL
      };
      reader.readAsDataURL(input.files[0]);
    }
  }