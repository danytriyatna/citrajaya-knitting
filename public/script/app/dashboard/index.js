$(document).ready(function () {

    // table tracking order 
    let dtListTracking = new Tabulator("#tbl-tracking", {
        columns: [
            {
                title: "NO. TRANSAKSI", field: "btnOrder", headerSort: false, formatter: "html",
                width: 130, hozAlign: 'center', cssClass: 'text-center'
            },

            {
                title: "TIPE", field: "tipe", headerSort: false, formatter: "html",
                width: 120, 
            },

            
			{
				title: 'TGL TRANSAKSI', field: 'tgl_transaksi', headerSort:false, sorter: 'string',
				width: 130, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 
				
			{
				title: 'BUYER', field: 'nama', headerSort:false, sorter: 'string',
				width: 180
			}, 

            {
				title: 'STYLE', field: 'keterangan', headerSort:false, sorter: 'string',
				formatter : "html", width: 180
			},

            {
				title: 'TGL DEADLINE', field: 'tgl_deadline', headerSort:false, sorter: 'string',
				width: 120, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 

            {
				title: 'QTY', field: 'qty', headerSort:false, sorter: 'string',
				width: 100, formatter : "html", hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
                title: "NO. PRODUKSI", field: "btnProd", headerSort: false, formatter: "html",
                width: 120, hozAlign: 'center', cssClass: 'text-center'
            },

            {
				title: 'QTY<br>PRODUKSI', field: 'qty_prod', headerSort:false, sorter: 'string',
				width: 100, hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
                title: "NO. PENGIRIMAN", field: "btnDev", headerSort: false, formatter: "html",
                width: 150, hozAlign: 'center', cssClass: 'text-center'
            },

            {
				title: 'QTY BELUM<br>PRODUKSI', field: 'qty_sisa', headerSort:false, sorter: 'string',
				width: 100, hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
				title: 'QTY<br>PENGIRIMAN', field: 'qty_kirim', headerSort:false, sorter: 'string',
				width: 110, hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
				title: 'QTY BELUM<br>DIKIRIM ', field: 'qty_sisa_kirim', headerSort:false, sorter: 'string',
				width: 100, hozAlign: 'right', cssClass: 'text-end'
			}, 

            
        ],
        layout: 'fitColumns',
        ajaxURL: "/dashboard/list_order",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        minHeight: 300,
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
        },
        ajaxResponse: function (url, params, response) {
            let pageSize = dtListTracking.getPageSize();
            let pageNo = dtListTracking.getPage();
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
    let elSearch = $("#inp-tracking");
    if (elSearch != null) {
        elSearch.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThread);
            searchThread = setTimeout(function () {
                dtListTracking.setFilter("", "like", elSearch.val());
            }, 600);
        });
    }

    // end table tracking order 


    // table invoice
    let dtListInvoice = new Tabulator("#tbl-invoice", {
        columns: [
            {
                title: "NO. INVOICE", field: "btnInv", headerSort: false, formatter: "html",
                width: 110, hozAlign: 'center', cssClass: 'text-center'
            }, 

			{
				title: 'TGL<br>INVOICE', field: 'tgl_invoice', headerSort:false, sorter: 'string',
				width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 
				
			{
				title: 'BUYER', field: 'nama', headerSort:false, sorter: 'string',
				width: 120
			}, 

            {
				title: 'TGL<br>JATUH<br>TEMPO', field: 'tgl_jatuh_tempo', headerSort:false, sorter: 'string',
				width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 

            {
				title: 'NILAI<br>INVOICE', field: 'total_invoice', headerSort:false, sorter: 'string',
				width: 100, formatter : "money", hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
				title: 'PEMBAYARAN', field: 'pembayaran', headerSort:false, sorter: 'string',
				width: 100, formatter : "money", hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
				title: 'SISA', field: 'sisa_bayar', headerSort:false, sorter: 'string',
				width: 100, formatter : "money", hozAlign: 'right', cssClass: 'text-end'
			}, 

            
        ],
        layout: 'fitColumns',
        ajaxURL: "/dashboard/list_invoice",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        minHeight: 300,
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
        },
        ajaxResponse: function (url, params, response) {
            let pageSize = dtListInvoice.getPageSize();
            let pageNo = dtListInvoice.getPage();
            let startRow = (pageSize * (pageNo - 1)) + 1;
            let endRow = response.data.length + startRow - 1;
            if (response.data.length === 0) {
                startRow = 0; endRow = 0;
            }
            let recordsFiltered = parseInt(response.recordsFiltered);
            let recordsTotal = parseInt(response.recordsTotal);

            $("#table-footer-inv .tabulator-startrow").text(startRow);
            $("#table-footer-inv .tabulator-endrow").text(endRow);
            $("#table-footer-inv .tabulator-totalrow").text(recordsFiltered);

            let elTotalFilteredRow = $("#table-footer-inv .tabulator-totalfilteredrow");
            elTotalFilteredRow.text("");
            if (recordsTotal > recordsFiltered) {
                elTotalFilteredRow.text(" (disaring dari " + recordsTotal
                    + " entri keseluruhan)");
            }
            return response;
        },
        footerElement: '<div id="table-footer-inv" class="pull-left tabulator-info">'
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

    let searchThreadInv = null;
    let elSearchInv = $("#inp-invoice");
    if (elSearchInv != null) {
        elSearchInv.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThreadInv);
            searchThreadInv = setTimeout(function () {
                dtListInvoice.setFilter("", "like", elSearchInv.val());
            }, 600);
        });
    }
    // end table invoice

    // table PO
    let dtListPo = new Tabulator("#tbl-po", {
        columns: [
            {
                title: "NO. PO", field: "btnPo", headerSort: false, formatter: "html",
                width: 110, hozAlign: 'center', cssClass: 'text-center'
            }, 

			{
				title: 'TGL<br>PO', field: 'po_date', headerSort:false, sorter: 'string',
				width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 
				
			{
				title: 'BUYER', field: 'nama', headerSort:false, sorter: 'string',
				width: 120
			}, 

            // {
			// 	title: 'TGL<br>JATUH<br>TEMPO', field: 'date_exc', headerSort:false, sorter: 'string',
			// 	width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			// }, 
            {
				title: 'TGL<br>JATUH<br>TEMPO', field: 'due_date', headerSort:false, sorter: 'string',
				width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 

            {
				title: 'NILAI<br>PO', field: 'total_bayar', headerSort:false, sorter: 'string',
				width: 100, formatter : "money", hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
				title: 'PEMBAYARAN', field: 'dibayar', headerSort:false, sorter: 'string',
				width: 100, formatter : "money", hozAlign: 'right', cssClass: 'text-end'
			}, 

            {
				title: 'SISA', field: 'sisa_bayar', headerSort:false, sorter: 'string',
				width: 100, formatter : "money", hozAlign: 'right', cssClass: 'text-end'
			}, 

            
        ],
        layout: 'fitColumns',
        ajaxURL: "/dashboard/list_po",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        minHeight: 300,
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
        },
        ajaxResponse: function (url, params, response) {
            let pageSize = dtListPo.getPageSize();
            let pageNo = dtListPo.getPage();
            let startRow = (pageSize * (pageNo - 1)) + 1;
            let endRow = response.data.length + startRow - 1;
            if (response.data.length === 0) {
                startRow = 0; endRow = 0;
            }
            let recordsFiltered = parseInt(response.recordsFiltered);
            let recordsTotal = parseInt(response.recordsTotal);

            $("#table-footer-po .tabulator-startrow").text(startRow);
            $("#table-footer-po .tabulator-endrow").text(endRow);
            $("#table-footer-po .tabulator-totalrow").text(recordsFiltered);

            let elTotalFilteredRow = $("#table-footer-po .tabulator-totalfilteredrow");
            elTotalFilteredRow.text("");
            if (recordsTotal > recordsFiltered) {
                elTotalFilteredRow.text(" (disaring dari " + recordsTotal
                    + " entri keseluruhan)");
            }
            return response;
        },
        footerElement: '<div id="table-footer-po" class="pull-left tabulator-info">'
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

    let searchThreadPo = null;
    let elSearchPo = $("#inp-po");
    if (elSearchPo != null) {
        elSearchPo.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThreadPo);
            searchThreadPo = setTimeout(function () {
                dtListPo.setFilter("", "like", elSearchPo.val());
            }, 600);
        });
    }
    // end table PO

    $('#filter_lap_bulan, #filter_lap_tahun').on('change', fetchData);

    const bulanSekarang = new Date().getMonth() + 1;
    const tahunSekarang = new Date().getFullYear();

    $('#filter_lap_bulan').val(bulanSekarang);
    $('#filter_lap_tahun').val(tahunSekarang);

    fetchData(); // Panggil fungsi fetchData saat halaman dimuat

    function fetchData() {
        const val1 = $('#filter_lap_bulan').val();
        const val2 = $('#filter_lap_tahun').val();

        if (!val1 || !val2) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Kedua dropdown harus dipilih terlebih dahulu.'
            });
            return;
        }
        
        // Hanya panggil jika keduanya terisi (atau sesuai logikamu)
        if (val1 && val2) {
            
          $.ajax({
            url: '/dashboard/list_laba',
            method: 'GET',
            data: {
              month: val1,
              year: val2
            },
            success: function (data) {
                const penjualan = data.penjualan;
                const pemakaian = data.pemakaian;
                const laba_kotor = data.laba_kotor;
                const total_biaya = data.total_biaya;
                const laba_bersih = data.laba_bersih;
                const nama_biaya = data.nama_biaya;
                const harga_per_biaya = data.harga_per_biaya;

                const targetRow = $("#total_operasional").closest('tr');

                $(".biaya-operasional-row").remove();
        
                $('#penjualan').text(penjualan);     
                $('#total_penjualan').text(penjualan); 
                $('#pemakaian').text(pemakaian);      
                $('#total_pemakaian').text(pemakaian); 
                $('#laba_kotor').text(laba_kotor); 
                $('#total_operasional').text(total_biaya); 
                $('#laba_bersih').text(laba_bersih); 

                // Pastikan kedua array punya panjang sama
                for (let i = 0; i < nama_biaya.length; i++) {
                    const rowHtml = `
                        <tr class="biaya-operasional-row">
                        <td class="p-s-24">- ${nama_biaya[i]}</td>
                        <td>${harga_per_biaya[i]}</td>
                        <td></td>
                        </tr>
                    `;
                    targetRow.before(rowHtml);
                }
            },
            error: function () {
              $('#result').html('Gagal memuat data.');
            }
          });
        }
      }
});