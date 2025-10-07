$(document).ready(function () {

    let tahun_transaksi = $("#tahun_trans");
    let bulan_transaksi = $("#slc_bulan");

    bulan_transaksi.on("change", function(){
        dtList.setData();
    });

    tahun_transaksi.on("change", function(){
        dtList.setData();
    });

    let dtList = new Tabulator("#dt-list", {
        columns: [

            {
                title: " ", field: "aksi", headerSort: false, formatter: "html",
                width: "8%"
            },
            {
				title: 'No. Transaksi', field: 'trans_akun_kode', headerSort:false, sorter: 'string',
				width: "12%", formatter : "html", cssClass:"text-center"
			}, 
            {
				title: 'Tanggal', field: 'trans_akun_date', headerSort:false, sorter: 'string',
				width: "10%"
			},
			{
				title: 'Akun', field: 'ref_rekening', headerSort:false, sorter: 'string',
				width: "20%"
			}, 
            {
				title: 'Keterangan', field: 'keterangan', headerSort:false, sorter: 'string',
				width: "30%",
			},
            {
				title: 'Jumlah', field: 'total', headerSort:false, sorter: 'string',
				width: "10%", formatter : "money", cssClass:"text-end"
			}, 
            {
				title: 'Status', field: 'status', headerSort:false, sorter: 'string', formatter: "html",
				width: "10%", 
			}, 
			
           
        ],
        layout: 'fitColumns',
        ajaxURL: "/keuangan/transaksi_akun/list",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: true,
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

    $("#excel_download").on("click", function(e){
        e.preventDefault();
        let bulan = bulan_transaksi.val();
        let tahun = tahun_transaksi.val();
        let trans_url = '/keuangan/transaksi_akun/get_reportx/' + bulan + '/' + tahun;

        window.open(trans_url, '_blank'); 
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