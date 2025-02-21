$(document).ready(function () {
    let dtList = new Tabulator("#dt-list", {
        columns: [
            {
                title: " ", field: "aksi", headerSort: false, formatter: "html",
                width: "10%"
            },
            
			{
				title: 'PP NO.', field: 'pay_no',hozAlign:"left", headerSort:false, sorter: 'string',
				width: "12%", formatter : "html"
			}, 
				
			{
				title: 'PP DATE', field: 'pay_date',hozAlign:"left", headerSort:false, sorter: 'string',
				width: "10%",
			}, 
				
			{
				title: 'VENDOR NAME', field: 'nama_vendor',hozAlign:"left", headerSort:false, sorter: 'string',
				width: "20%",
			}, 

            {
				title: 'PO AMOUNT', field: 'hutang', headerSort:false, sorter: 'string', hozAlign:"right",
				width: "10%", formatter : "money",
                formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                },
			},

            {
				title: 'DISCOUNT', field: 'diskon', headerSort:false, sorter: 'string', hozAlign:"right",
				width: "10%", formatter : "money",
                formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                },
			},

            {
				title: 'PAYMENT', field: 'total_bayar', headerSort:false, sorter: 'string', hozAlign:"right",
				width: "10%", formatter : "money",
                formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                },
			},


            {
				title: 'REMAIN AMOUNT', field: 'sisa_bayar', headerSort:false, sorter: 'string', hozAlign:"right",
				width: "13%", formatter : "money",
                formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                },
			},

            {
				title: 'PAYMENT STATUS', field: 'status', formatter : "html", align: "center", headerSort:false,
                width: "15%",hozAlign:"center",
			},
				
        ],
        // layout: 'fitColumns',
        ajaxURL: "/purchasing/purchase-payment/list",
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
});