$(document).ready(function () {

    setTimeout(() => {
     $('.tabulator-headers').addClass('d-none');
   }, 1000);


    // declarre untuk variable print qr

    function cardFormatter(cell, formatterParams, onRendered){
        let data = cell.getRow().getData(); // Ambil data row
        console.log("data", data);
        let status = '';
        let aksi = '';
        if(data.status == 'Draft'){
         status = ` <i class="fa fa-dot-circle text-muted m-e-6"></i>
                    <span class="f-w-700 text-muted">`+data.status+`</span>`

         aksi = `<a href="${data.url_edit}" type="button" class="btn btn-sm btn-warning text-dark edit"> <i class="fa fa-edit"></i> Edit</a>`;
        } else if (data.status == 'Closed') {
         status = `<i class="fa fa-lock text-dark m-e-6"></i>
                   <span class="f-w-700 text-dark">Closed</span>`;

         aksi = `<button type="button" class="btn btn-sm btn-secondary text-white" disabled><i class="fa fa-lock"></i> Closed</button>`;
        } else {
         status = ` <i class="fa fa-check-circle text-success m-e-6"></i>
                    <span class="f-w-700 text-success">Approved</span>`

         aksi = `<a href="${data.url_edit}" type="button" class="btn btn-sm btn-warning text-dark edit"> <i class="fa fa-edit"></i> Edit</a>`;
        }
        
        let headerActionHtml = `<div class="d-flex align-items-center gap-1 my-1">${aksi}</div>`;
        let badgesHtml = '';

        if (data.laba_kotor) {
            let lk = data.laba_kotor;
            let isUntung = (lk.laba_kotor >= 0);
            let badgeLabaBg = isUntung ? '#16a34a' : '#dc2626';

            headerActionHtml = `
              <div class="d-flex align-items-center gap-1 my-1">
                  ${aksi}
                  <button type="button" class="btn btn-sm text-white fw-bold btn-detail-laba text-nowrap py-1 px-2 f-s-12 shadow-sm" style="background-color: #0284c7; border: 1px solid #0369a1;" data-id="${data.id}" title="Lihat Rincian Laba Kotor">
                    <i class="fa fa-chart-line me-1"></i> Rincian Laba
                  </button>
              </div>`;

            let qtySoBadge = lk.f_qty_so ? ` <span class="badge bg-white text-dark px-1 py-0 ms-1 fw-bold" style="font-size: 10px;">${lk.f_qty_so}</span>` : '';

            badgesHtml = `
              <div class="d-flex flex-wrap justify-content-end align-items-center gap-1 my-1">
                <span class="badge py-1 px-2 text-nowrap shadow-sm" style="background: #1e40af; border: 1px solid #1d4ed8 !important; color: #ffffff; font-weight: 700;">
                  <span class="opacity-75 f-s-10">SO:</span> <span class="f-s-11">${lk.f_nilai_so}</span>${qtySoBadge}
                </span>
                <span class="badge py-1 px-2 text-nowrap shadow-sm" style="background: #0284c7; border: 1px solid #0369a1 !important; color: #ffffff; font-weight: 700;">
                  <span class="opacity-75 f-s-10">Masuk:</span> <span class="f-s-11">${lk.f_nilai_pendapatan}</span>
                </span>
                <span class="badge py-1 px-2 text-nowrap shadow-sm" style="background: #d97706; border: 1px solid #b45309 !important; color: #ffffff; font-weight: 700;">
                  <span class="opacity-75 f-s-10">Bahan:</span> <span class="f-s-11">${lk.f_nilai_bahan}</span>
                </span>
                <span class="badge py-1 px-2 text-nowrap shadow-sm" style="background: #dc2626; border: 1px solid #b91c1c !important; color: #ffffff; font-weight: 700;">
                  <span class="opacity-75 f-s-10">Upah:</span> <span class="f-s-11">${lk.f_nilai_tk}</span>
                </span>
                <span class="badge py-1 px-2 text-nowrap shadow-sm" style="background: ${badgeLabaBg}; border: 1px solid ${badgeLabaBg} !important; color: #ffffff; font-weight: 700;">
                  <span class="opacity-75 f-s-10">Laba:</span> <span class="f-s-11">${lk.f_laba_kotor}</span> <span class="opacity-75 f-s-10">(${lk.margin_persen}%)</span>
                </span>
              </div>`;
        }
        
        // HTML Card Layout
        var cardHtml = `<div class="card shadow-sm mb-3 border">
                  <div class="card-header py-1 px-3" style="background-color: #f8fafc; border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                      ${headerActionHtml}
                      ${badgesHtml}
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-sm-3 text-start">
                        <h6 class="f-w-700 m-b-6">${data.kode_prod}</h6>
                        <p class="f-w-500 m-y-0">${data.ref_kode}</p>
                        <p class="f-w-500 m-y-0">${data.kode_walkorder_ref}</p>
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
            
            let isColumn = [
                {headerSort: false,title:"No", field:"no",   width: "5%"},
                // {headerSort: false,  title:"QR", width:"7%", formatter: print_btn,
                //     cellClick: function(e, cell) {
                //         let row = cell.getRow();
                //         let data_row = row.getData();
                //         if (e.target.title === 'print-warna') {
                //             // console.log(data_row)
                //             const xdata = data.ref_data;
                //             const inpp_slcUkuran = $("#print_slc_ukuran");
                //             const inpp_qty       = $("#print_qty");
                //             const inpp_qtyp      = $("#print_qtyp");

                //             // inpp_slcUkuran
                //             inpp_qty.val(1)
                //             inpp_qtyp.val(1)

                //             inpp_foto.attr('src', data.file_name);
                //             inpp_noSo.html(data.ref_kode)
                //             inpp_deskripsi.html(xdata.deskripsi);
                //             inpp_warna.html(data_row.colordasar);
                //             inpp_tglSample.html(formatterDate(data.tgl_transaksi))
                //             inpp_tglDeadline.html(formatterDate(data.tgl_deadline))

                //             brcStyle.val(xdata.style)
                //             inpp_buyer.html(data.konsumen_nama)

                //             // console.log("kolom print", data_row)

                //             setTimeout(() => {
                //                 inpp_trans.html(data_row.id);
                //                 mdlPrint.modal("show");
                //             }, 500);
                //         } 
                //     }
                // },
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
        ajaxURL: "trans/production/list",
        ajaxConfig: "POST",
        sortMode: "remote",
        filterMode: "remote",
        placeholder: "Tidak ada data",
        height: '1200px',
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
            params.filter_trans = $("#filter_status").val();
            params.tahun = $("#filter_tahun").length ? $("#filter_tahun").val() : "";
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

            if (response.summary_global) {
                let s = response.summary_global;
                $('#sum-glob-so').text(s.f_total_nilai_so);
                $('#sum-glob-pendapatan').text(s.f_total_nilai_pendapatan);
                $('#sum-glob-bahan').text(s.f_total_nilai_bahan);
                $('#sum-glob-tk').text(s.f_total_nilai_tk);
                $('#sum-glob-laba').text(s.f_total_laba_kotor);
                $('#sum-glob-margin').text('Margin: ' + s.total_margin_persen + '%');

                if (s.total_laba_kotor < 0) {
                    $('#card-glob-laba').css({'border-color': '#dc2626'});
                    $('#lbl-glob-laba-title').css({'color': '#b91c1c'});
                    $('#sum-glob-laba').css({'color': '#dc2626'});
                    $('#sum-glob-margin').removeClass('bg-success').addClass('bg-danger');
                } else {
                    $('#card-glob-laba').css({'border-color': '#16a34a'});
                    $('#lbl-glob-laba-title').css({'color': '#15803d'});
                    $('#sum-glob-laba').css({'color': '#16a34a'});
                    $('#sum-glob-margin').removeClass('bg-danger').addClass('bg-success');
                }
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
	// 			title: 'No. Production', field: 'kode_prod', headerSort:false, sorter: 'string',
	// 			width: 160, formatter : "html"
	// 		}, 
				
	// 		{
	// 			title: 'Tgl Production', field: 'tgl_transaksi', headerSort:false, sorter: 'string',
	// 			width: 140
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
	// 			width: 200, formatter : "html"
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
	// 			title: 'Proses Prod', field: 'qty_prod', formatter : "html", headerSort:false, sorter: 'string',
	// 			width: 120
	// 		} ,

    //         {
	// 			title: 'Hasil Prod', field: 'qty_hasil', formatter : "html", headerSort:false, sorter: 'string',
	// 			width: 120
	// 		} ,
            
    //         {
	// 			title: 'Sisa Prod', field: 'qty_remain', headerSort:false, sorter: 'string', align: "center",
    //             width: 120
	// 		} ,

    //         {
	// 			title: 'Prod Status', field: 'status', formatter : "html", headerSort:false, sorter: 'string',
	// 			width: 125, visible: false
	// 		} ,

    //         {
	// 			title: 'No. Referensi', field: 'kode_walkorder_ref', headerSort:false, sorter: 'string',
	// 			width: 140, 
	// 		}, 

    //         {
	// 			title: 'No. Referensi WO', field: 'ref_kode', headerSort:false, sorter: 'string',
	// 			width: 140, 
	// 		}, 
				
    //     ],
    //     // layout: 'fitColumns',
    //     ajaxURL: "/trans/production/list",
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

    $("#filter_tahun").on("change", function(){
        dtList.setData();
    });

    // Detail Analisis Laba Kotor Modal
    $(document).on("click", ".btn-detail-laba", function(e) {
        e.preventDefault();
        let prodId = $(this).data("id");
        if (!prodId) return;

        $("#modal-laba-detail").modal("show");
        
        // Reset modal state
        $("#tbl-mdl-rekap-bahan tbody").html('<tr><td colspan="8" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data rekapitulasi bahan baku...</td></tr>');
        $("#mdl-rekap-badge-kebutuhan").text("Kebutuhan: 0 Kg");
        $("#mdl-rekap-badge-pakai").text("Pemakaian: 0 Kg");
        $("#mdl-rekap-badge-nilai").text("Nilai Pemakaian: Rp 0");
        $("#mdl-tot-rekap-kebutuhan").text("0.00");
        $("#mdl-tot-rekap-trf").text("0.00");
        $("#mdl-tot-rekap-kembali").text("0.00");
        $("#mdl-tot-rekap-pakai").text("0.00");
        $("#mdl-tot-rekap-sisa").text("0.00");
        $("#mdl-tot-rekap-nilai").text("Rp 0");
        $("#tbl-mdl-tk tbody").html('<tr><td colspan="8" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data tenaga kerja...</td></tr>');
        $("#tbl-mdl-bahan tbody").html('<tr><td colspan="6" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data bahan baku...</td></tr>');

        $.ajax({
            url: baseUrl + "/trans/production/get_laba_detail",
            type: "POST",
            data: { id: prodId },
            dataType: "json",
            success: function(res) {
                if (!res.status) {
                    alert(res.message || "Gagal mengambil data rincian laba kotor");
                    return;
                }

                let l = res.laba_data;
                let info = res.info || {};
                let qtySo = info.qty_so || l.qty_so || 0;
                let fQtySo = info.f_qty_so || l.f_qty_so || (Number(qtySo).toLocaleString("id-ID") + " Pcs");

                $("#mdl-lbl-prod").text(info.kode_prod || "-");
                $("#mdl-lbl-wo").text(info.kode_walkorder || "-");
                $("#mdl-lbl-so").text(info.ref_kode || "SO");
                $("#mdl-lbl-konsumen").text(info.konsumen_nama || "-");
                $("#mdl-lbl-qty-so").text("Qty SO: " + fQtySo);
                $("#mdl-lbl-style").text(info.keterangan_style || "-");

                $("#mdl-val-so").text(l.f_nilai_so);
                $("#mdl-val-qty-so").text("Target: " + fQtySo);
                $("#mdl-val-pendapatan").text(l.f_nilai_pendapatan);
                $("#mdl-val-bahan").text(l.f_nilai_bahan);
                $("#mdl-val-tk").text(l.f_nilai_tk);
                $("#mdl-val-laba").text(l.f_laba_kotor);
                $("#mdl-val-margin").text("Margin: " + l.margin_persen + "%");

                if (l.laba_kotor < 0) {
                    $("#mdl-badge-laba").removeClass("bg-success").addClass("bg-danger").text("Laba Kotor: " + l.f_laba_kotor + " (Defisit)");
                    $("#mdl-card-laba").css({"border-color": "#dc2626"});
                    $("#mdl-lbl-laba-title").css({"color": "#b91c1c"});
                    $("#mdl-val-laba").css({"color": "#dc2626"});
                } else {
                    $("#mdl-badge-laba").removeClass("bg-danger").addClass("bg-success").text("Laba Kotor: " + l.f_laba_kotor + " (Profit)");
                    $("#mdl-card-laba").css({"border-color": "#16a34a"});
                    $("#mdl-lbl-laba-title").css({"color": "#15803d"});
                    $("#mdl-val-laba").css({"color": "#16a34a"});
                }

                // Render Rekapitulasi Pemakaian Bahan Baku
                let htmlRekapBahan = "";
                let totKebutuhan = 0;
                let totTrf = 0;
                let totKembali = 0;
                let totPakai = 0;
                let totSisa = 0;
                let totNilaiPakai = 0;

                if (res.rekap_bahan && res.rekap_bahan.length > 0) {
                    let no = 1;
                    res.rekap_bahan.forEach(function(item) {
                        let keb = parseFloat(item.total || item.kebutuhan || 0);
                        let trf = parseFloat(item.total_qty_trf || 0);
                        let kbl = parseFloat(item.total_qty_kembali || 0);
                        let pki = parseFloat(item.total_qty_pakai || 0);
                        let sisa = parseFloat(item.sisa || 0);
                        let nilaiPakai = parseFloat(item.total_nilai_pakai || 0);

                        totKebutuhan += keb;
                        totTrf += trf;
                        totKembali += kbl;
                        totPakai += pki;
                        totSisa += sisa;
                        totNilaiPakai += nilaiPakai;

                        let sisaColor = sisa < 0 ? "#dc2626" : "#16a34a";
                        let sisaWeight = sisa < 0 ? "700" : "600";
                        let namaWarna = item.kode_warna || item.nama_barang || "-";

                        htmlRekapBahan += `<tr>
                            <td class="text-center">${no++}</td>
                            <td class="fw-bold text-dark">${namaWarna}</td>
                            <td class="text-end fw-bold" style="color: #1e40af;">${keb.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end fw-bold" style="color: #0284c7;">${trf.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end fw-bold" style="color: #64748b;">${kbl.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end fw-bold" style="color: #d97706;">${pki.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end" style="color: ${sisaColor}; font-weight: ${sisaWeight};">${sisa.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end fw-bold" style="color: #15803d;">Rp ${nilaiPakai.toLocaleString("id-ID", {minimumFractionDigits: 0, maximumFractionDigits: 0})}</td>
                        </tr>`;
                    });
                } else {
                    htmlRekapBahan = '<tr><td colspan="8" class="text-center text-muted py-3">Belum ada data rekapitulasi kebutuhan &amp; pemakaian bahan baku untuk nomor WO ini</td></tr>';
                }
                $("#tbl-mdl-rekap-bahan tbody").html(htmlRekapBahan);

                // Fallback to res.laba_data.nilai_bahan if totNilaiPakai is 0 but l.nilai_bahan > 0
                if (totNilaiPakai === 0 && l && l.nilai_bahan) {
                    totNilaiPakai = parseFloat(l.nilai_bahan || 0);
                }

                $("#mdl-rekap-badge-kebutuhan").text("Kebutuhan: " + totKebutuhan.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}) + " Kg");
                $("#mdl-rekap-badge-pakai").text("Pemakaian: " + totPakai.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}) + " Kg");
                $("#mdl-rekap-badge-nilai").text("Nilai Pemakaian: Rp " + totNilaiPakai.toLocaleString("id-ID", {minimumFractionDigits: 0, maximumFractionDigits: 0}));

                $("#mdl-tot-rekap-kebutuhan").text(totKebutuhan.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $("#mdl-tot-rekap-trf").text(totTrf.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $("#mdl-tot-rekap-kembali").text(totKembali.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $("#mdl-tot-rekap-pakai").text(totPakai.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                let sisaTotalColor = totSisa < 0 ? "#dc2626" : "#16a34a";
                $("#mdl-tot-rekap-sisa").css({"color": sisaTotalColor, "font-weight": "700"}).text(totSisa.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $("#mdl-tot-rekap-nilai").text("Rp " + totNilaiPakai.toLocaleString("id-ID", {minimumFractionDigits: 0, maximumFractionDigits: 0}));

                // Render Breakdown Tenaga Kerja Operator (Split Prod & Perbaikan)
                let htmlTk = "";
                let totQtySo = 0;
                let totQtyProd = 0;
                let totQtyFix = 0;
                let totQtyAll = 0;

                if (res.breakdown && res.breakdown.tenaga_kerja && res.breakdown.tenaga_kerja.length > 0) {
                    let no = 1;
                    res.breakdown.tenaga_kerja.forEach(function(item) {
                        let rowQtySo = Number(item.qty_so || qtySo || 0);
                        let rowQtyProd = Number(item.qty_prod || 0);
                        let rowQtyFix = Number(item.qty_fix || 0);
                        let rowQtyTotal = Number(item.total_qty !== undefined ? item.total_qty : rowQtyProd);

                        totQtySo += rowQtySo;
                        totQtyProd += rowQtyProd;
                        totQtyFix += rowQtyFix;
                        totQtyAll += rowQtyTotal;

                        htmlTk += `<tr>
                            <td class="text-center">${no++}</td>
                            <td class="fw-bold text-dark">${item.nama_proses}</td>
                            <td class="text-end fw-bold" style="color: #1e40af;">${rowQtySo.toLocaleString("id-ID")}</td>
                            <td class="text-end fw-bold" style="color: #16a34a;">${rowQtyProd.toLocaleString("id-ID")}</td>
                            <td class="text-end fw-bold" style="color: #d97706;">${rowQtyFix.toLocaleString("id-ID")}</td>
                            <td class="text-end fw-bold text-dark">${rowQtyTotal.toLocaleString("id-ID")}</td>
                            <td class="text-end">Rp ${Number(item.avg_harga).toLocaleString("id-ID")}</td>
                            <td class="text-end fw-bold text-danger">Rp ${Number(item.subtotal_ongkos).toLocaleString("id-ID")}</td>
                        </tr>`;
                    });
                } else {
                    htmlTk = '<tr><td colspan="8" class="text-center text-muted py-3">Belum ada data pengerjaan operator pada transaksi produksi ini</td></tr>';
                }
                $("#tbl-mdl-tk tbody").html(htmlTk);
                $("#mdl-total-qty-so").text(Number(qtySo).toLocaleString("id-ID"));
                $("#mdl-total-qty-prod").text(totQtyProd.toLocaleString("id-ID"));
                $("#mdl-total-qty-fix").text(totQtyFix.toLocaleString("id-ID"));
                $("#mdl-total-qty-all").text(totQtyAll.toLocaleString("id-ID"));
                $("#mdl-total-tk").text(l.f_nilai_tk);

                // Render Breakdown Bahan Baku
                let htmlBahan = "";
                let totBahanQty = 0;
                if (res.breakdown && res.breakdown.bahan_baku && res.breakdown.bahan_baku.length > 0) {
                    let no = 1;
                    res.breakdown.bahan_baku.forEach(function(item) {
                        let warna = (item.kode_warna ? item.kode_warna : "") + (item.nama_warna ? " - " + item.nama_warna : "");
                        let bQty = Number(item.qty_pakai || 0);
                        totBahanQty += bQty;
                        htmlBahan += `<tr>
                            <td class="text-center">${no++}</td>
                            <td class="fw-bold text-dark">${item.kode_barang} - ${item.nama_barang}</td>
                            <td>${warna || "-"}</td>
                            <td class="text-end fw-bold text-dark">${bQty.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end">Rp ${Number(item.harga_satuan).toLocaleString("id-ID")}</td>
                            <td class="text-end fw-bold" style="color: #d97706;">Rp ${Number(item.subtotal_bahan).toLocaleString("id-ID")}</td>
                        </tr>`;
                    });
                } else {
                    htmlBahan = '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada data pemakaian bahan baku (BTK) untuk nomor WO ini</td></tr>';
                }
                $("#tbl-mdl-bahan tbody").html(htmlBahan);
                $("#mdl-total-bahan-qty").text(totBahanQty.toLocaleString("id-ID", {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $("#mdl-total-bahan").text(l.f_nilai_bahan);
            },
            error: function() {
                alert("Gagal menghubungi server untuk memuat rincian laba kotor");
            }
        });
    });
});