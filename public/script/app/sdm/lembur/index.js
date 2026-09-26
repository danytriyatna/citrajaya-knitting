$(document).ready(function () {
    let dtList = new Tabulator("#dt-list", {
        columns: [
            {
                title: "Aksi", field: "aksi", headerSort: false, formatter: "html",
                width: "8%", hozAlign: "center", headerHozAlign: "center"
            },
            {
                title: 'No. Lembur', field: 'kode_lembur', headerSort: false, sorter: 'string',
                width: "11%", formatter: function(cell) {
                    let val = cell.getValue();
                    let rowData = cell.getRow().getData();
                    return `<a href="/sdm/lembur/form/${rowData.id}" class="text-primary font-weight-bold">${val}</a>`;
                }
            },
            {
                title: 'Tanggal', field: 'tanggal', headerSort: false, sorter: 'string',
                width: "10%"
            },
            {
                title: 'Perusahaan', field: 'nama_perusahaan', headerSort: false, sorter: 'string',
                width: "13%"
            },
            {
                title: 'Keterangan', field: 'keterangan', headerSort: false, sorter: 'string',
                width: "14%"
            },
            {
                title: 'Karyawan', field: 'total_karyawan', headerSort: false,
                width: "8%", hozAlign: "center", headerHozAlign: "center"
            },
            {
                title: 'Tot Target', field: 'total_target', headerSort: false,
                width: "8%", hozAlign: "right", headerHozAlign: "right"
            },
            {
                title: 'Tot Realisasi', field: 'total_realisasi', headerSort: false,
                width: "8%", hozAlign: "right", headerHozAlign: "right"
            },
            {
                title: '% Capai', field: 'pencapaian_rata', headerSort: false,
                width: "7%", hozAlign: "center", headerHozAlign: "center",
                formatter: function(cell) {
                    let val = parseFloat(cell.getValue()) || 0;
                    let color = val >= 100 ? 'text-success font-weight-bold' : (val >= 80 ? 'text-info' : 'text-danger font-weight-bold');
                    return `<span class="${color}">${cell.getValue()}</span>`;
                }
            },
            {
                title: 'Jam Absen', field: 'total_jam_absen', headerSort: false,
                width: "8%", hozAlign: "right", headerHozAlign: "right"
            },
            {
                title: 'Jam Dihitung', field: 'total_jam_dihitung', headerSort: false,
                width: "9%", hozAlign: "right", headerHozAlign: "right",
                formatter: function(cell) {
                    return `<span class="badge bg-light-primary text-primary font-weight-bold">${cell.getValue()}</span>`;
                }
            },
            {
                title: 'Status', field: 'status_badge', headerSort: false, formatter: "html",
                width: "12%", hozAlign: 'center', headerHozAlign: "center"
            }
        ],
        layout: 'fitColumns',
        ajaxURL: "/sdm/lembur/list",
        placeholder: "Tidak ada data lembur karyawan",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        minHeight: 320,
        ajaxParams: function () {
            return {
                id_perusahaan: $("#filter_perusahaan").val() === "all" ? "" : $("#filter_perusahaan").val(),
                status: $("#filter_status").val(),
                tgl_awal: $("#filter_tgl_awal").val(),
                tgl_akhir: $("#filter_tgl_akhir").val()
            };
        },
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
            if ($("#tb-search").val()) {
                params.filter = [{ field: 'all', value: $("#tb-search").val() }];
            }
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
                elTotalFilteredRow.text(" (disaring dari " + recordsTotal + " entri keseluruhan)");
            }
            return response;
        },
        footerElement: '<div id="table-footer" class="pull-left tabulator-info">'
            + 'Menampilkan <span class="tabulator-startrow"></span> - <span class="tabulator-endrow"></span> dari '
            + '<span class="tabulator-totalrow"></span> entri<span class="tabulator-totalfilteredrow"></span></div>',
        pagination: true,
        paginationMode: "remote",
        paginationSize: 20,
        paginationButtonCount: 10,
        selectableRows: false,
    });

    $("#filter_perusahaan, #filter_status, #filter_tgl_awal, #filter_tgl_akhir").on("change", function () {
        dtList.setPage(1);
    });

    let searchTimeout;
    $("#tb-search").on("keyup", function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            dtList.setPage(1);
        }, 400);
    });
});
