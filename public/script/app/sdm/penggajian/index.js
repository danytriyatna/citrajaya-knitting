$(document).ready(function () {
    let dtList = new Tabulator("#dt-list", {
        columns: [
            {
                title: " ", field: "aksi", headerSort: false, formatter: "html",
                width: "7%"
            },
            
			{
				title: 'Kode', field: 'kode_gaji', headerSort:false, sorter: 'string',
				width: "10%", formatter : "html"
			}, 
				
			{
				title: 'Periode Awal', field: 'periode_awal', headerSort:false, sorter: 'string',
				width: "10%"
			}, 

            {
				title: 'Periode Akhir', field: 'periode_akhir', headerSort:false, sorter: 'string',
				width: "10%"
			}, 

            {
				title: 'Tipe', field: 'type', headerSort:false, sorter: 'string',
				width: "8%"
			}, 
            {
				title: 'Perusahaan', field: 'nama_perusahaan', headerSort:false, sorter: 'string',
				width: "15%"
			},

            {
				title: 'Keterangan', field: 'keterangan', headerSort:false, sorter: 'string',
				width: "30%", formatter : "html"
			},

            {
				title: 'Status', field: 'status', headerSort:false, sorter: 'string',
				width: "10%", align:'center',
			},
				
        ],
        layout: 'fitColumns',
        ajaxURL: "/sdm/penggajian/list",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        minHeight: 300,
        ajaxParams: function () {
            return {
                id_perusahaan: $("#filter_perusahaan").val() === "all" ? "" : $("#filter_perusahaan").val()
            };
        },
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

    $("#filter_perusahaan").on("change", function () {
        dtList.setPage(1); // balik ke halaman 1, otomatis trigger ajax baru
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