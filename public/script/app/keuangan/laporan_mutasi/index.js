$(document).ready(function () {

    let dtList = null;

    // 1. Column Definition for 12-Month Matrix
    const columnsMatrix = [
        {
            title: 'Nomor Akun', field: 'coa_kode', headerSort: false, sorter: 'string',
            formatter: 'html', visible: false
        },
        {
            title: 'Akun', field: 'coa_nama', headerSort: false, sorter: 'string',
            formatter: 'html', frozen: true
        },
        {
            title: 'Januari', field: 'bln1', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Februari', field: 'bln2', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Maret', field: 'bln3', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'April', field: 'bln4', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Mei', field: 'bln5', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Juni', field: 'bln6', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Juli', field: 'bln7', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Agustus', field: 'bln8', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'September', field: 'bln9', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Oktober', field: 'bln10', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'November', field: 'bln11', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Desember', field: 'bln12', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        }
    ];

    // 2. Column Definition for Beban Biaya Preview
    const columnsBeban = [
        {
            title: 'Tipe Bayar / Sumber', field: 'tipe_bayar', headerSort: false, sorter: 'string'
        },
        {
            title: 'Tanggal', field: 'trans_akun_date', headerSort: false, sorter: 'string',
            align: "center", cssClass: "text-center"
        },
        {
            title: 'No. Transaksi', field: 'trans_akun_kode', headerSort: false, sorter: 'string',
            align: "center", cssClass: "text-center"
        },
        {
            title: 'Kode Akun', field: 'kode', headerSort: false, sorter: 'string',
            align: "center", cssClass: "text-center"
        },
        {
            title: 'Nama Akun', field: 'nama', headerSort: false, sorter: 'string'
        },
        {
            title: 'Keterangan', field: 'keterangan', headerSort: false, sorter: 'string'
        },
        {
            title: 'Jumlah', field: 'jumlah', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        }
    ];

    // 3. Column Definition for Mutasi Biaya Preview
    const columnsMutasi = [
        {
            title: 'Tipe Bayar', field: 'tipe_bayar', headerSort: false, sorter: 'string'
        },
        {
            title: 'Tanggal', field: 'tgl_transaksi', headerSort: false, sorter: 'string',
            align: "center", cssClass: "text-center"
        },
        {
            title: 'Transaksi', field: 'transaksi', headerSort: false, sorter: 'string',
            formatter: "html", align: "center", cssClass: "text-center"
        },
        {
            title: 'No. Ref', field: 'kode_transaksi', headerSort: false, sorter: 'string',
            align: "center", cssClass: "text-center"
        },
        {
            title: 'Kode', field: 'kode', headerSort: false, sorter: 'string',
            align: "center", cssClass: "text-center"
        },
        {
            title: 'Nama Akun', field: 'nama', headerSort: false, sorter: 'string'
        },
        {
            title: 'Keterangan', field: 'keterangan', headerSort: false, sorter: 'string'
        },
        {
            title: 'Masuk', field: 'masuk', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Keluar', field: 'keluar', headerSort: false, sorter: 'number',
            formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-end"
        },
        {
            title: 'Saldo', field: 'saldo', headerSort: false, sorter: 'number',
            formatter: "money", align: "right", cssClass: "text-end"
        }
    ];

    function applyCurrentFilter() {
        let query = $("#tb-search").val();
        if (dtList) {
            if (query && query.trim() !== "") {
                dtList.setFilter(customFilter, { query: query });
            } else {
                dtList.clearFilter();
            }
        }
    }

    function initMatrixTable() {
        if (dtList) {
            dtList.destroy();
        }
        dtList = new Tabulator("#dt-list", {
            columns: columnsMatrix,
            layout: 'fitDataFill',
            ajaxURL: "/keuangan/laporan_mutasi/list",
            placeholder: "Tidak ada data",
            ajaxConfig: "POST",
            ajaxSorting: true,
            ajaxFiltering: true,
            ajaxRequesting: function (url, params) {
                params.tahun = $("#slc_tahun").val() || new Date().getFullYear();
            },
            ajaxResponse: function (url, params, response) {
                return response.data;
            },
            dataTree: true,
            dataTreeChildField: "children",
            dataTreeStartExpanded: true,
            dataTreeChildIndent: 15,
            selectableRows: false,
        });

        dtList.on("dataLoaded", function () {
            applyCurrentFilter();
        });
    }

    function initFlatTable(cols, data) {
        if (dtList) {
            dtList.destroy();
        }
        dtList = new Tabulator("#dt-list", {
            data: data,
            columns: cols,
            layout: 'fitDataFill',
            placeholder: "Tidak ada data",
            selectableRows: false,
        });

        applyCurrentFilter();
    }

    // Custom search filter function (hanya mencari kode akun dan nama akun)
    function customFilter(data, filterParams) {
        let query = filterParams.query ? filterParams.query.toLowerCase().trim() : "";
        if (!query) return true;

        if (data.kode && String(data.kode).toLowerCase().includes(query)) return true;
        if (data.coa_kode && String(data.coa_kode).toLowerCase().includes(query)) return true;
        if (data.nama && String(data.nama).toLowerCase().includes(query)) return true;
        if (data.coa_nama && String(data.coa_nama).toLowerCase().includes(query)) return true;

        return false;
    }

    $("#tb-search").on("keyup search", function () {
        applyCurrentFilter();
    });

    // Initial load
    initMatrixTable();

    let btnCari = $("#btn_cari");
    btnCari.on("click", function () {
        let select_type_export = $("#select_type_export").val();

        if (select_type_export == "0") {
            // Mode Beban Biaya
            let from_date = $("#from_date").val();
            let to_date = $("#to_date").val();
            let select_payment_type = $("#select_payment_type").val();

            if (!from_date) {
                Swal.fire({ text: "From Date Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!to_date) {
                Swal.fire({ text: "To Date Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!select_payment_type) {
                Swal.fire({ text: "Payment Type Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (parseDate(from_date) > parseDate(to_date)) {
                Swal.fire({ text: "From Date tidak boleh lebih besar dari To Date!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }

            $.ajax({
                url: '/keuangan/laporan_mutasi/preview_beban',
                type: 'POST',
                data: {
                    from_date: from_date,
                    to_date: to_date,
                    select_payment_type: select_payment_type
                },
                dataType: 'json',
                success: function (res) {
                    if (res && res.status && res.data) {
                        initFlatTable(columnsBeban, res.data);
                    } else {
                        initFlatTable(columnsBeban, []);
                    }
                },
                error: function (xhr) {
                    console.error("Preview Beban error:", xhr.responseText);
                    initFlatTable(columnsBeban, []);
                }
            });

        } else if (select_type_export == "1") {
            // Mode Mutasi Biaya
            let filter_bulan = $("#filter_bulan").val();
            let filter_tahun = $("#filter_tahun").val();
            let select_payment_type_one = $("#select_payment_type_one").val();
            let id_perusahaan = $("#filter_perusahaan").val();

            if (!filter_bulan) {
                Swal.fire({ text: "Bulan Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!filter_tahun) {
                Swal.fire({ text: "Tahun Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!select_payment_type_one) {
                Swal.fire({ text: "Payment Type Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }

            $.ajax({
                url: '/keuangan/laporan_mutasi/preview_mutasi',
                type: 'POST',
                data: {
                    filter_bulan: filter_bulan,
                    filter_tahun: filter_tahun,
                    select_payment_type_one: select_payment_type_one,
                    id_perusahaan: id_perusahaan
                },
                dataType: 'json',
                success: function (res) {
                    if (res && res.status && res.data) {
                        initFlatTable(columnsMutasi, res.data);
                    } else {
                        initFlatTable(columnsMutasi, []);
                    }
                },
                error: function (xhr) {
                    console.error("Preview Mutasi error:", xhr.responseText);
                    initFlatTable(columnsMutasi, []);
                }
            });

        } else {
            // Default: Matriks 12 Bulan
            initMatrixTable();
        }
    });

    let btnExcel = $("#btn_excel");
    btnExcel.on("click", function () {
        let select_type_export = $("#select_type_export").val();
        let from_date = $("#from_date").val();
        let to_date = $("#to_date").val();
        let select_payment_type = $("#select_payment_type").val();
        let filter_bulan = $("#filter_bulan").val();
        let filter_tahun = $("#filter_tahun").val();
        let select_payment_type_one = $("#select_payment_type_one").val();
        let select_payment_type_one_text = $("#select_payment_type_one").find(':selected').text();
        let id_perusahaan = $("#filter_perusahaan").val();

        if (select_type_export == 0 && select_type_export !== "") {
            if (!from_date) {
                Swal.fire({ text: "From Date Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!to_date) {
                Swal.fire({ text: "To Date Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!select_payment_type) {
                Swal.fire({ text: "Payment Type Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (parseDate(from_date) > parseDate(to_date)) {
                Swal.fire({ text: "From Date tidak boleh lebih besar dari To Date!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }

            let url = "/keuangan/laporan_mutasi/getExcelNew/" + from_date + "/" + to_date + "/" + select_payment_type;
            window.open(url, '_blank');

        } else if (select_type_export == 1) {
            if (!filter_bulan) {
                Swal.fire({ text: "Bulan Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!filter_tahun) {
                Swal.fire({ text: "Tahun Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }
            if (!select_payment_type_one) {
                Swal.fire({ text: "Payment Type Export Harus Diisi!", icon: 'warning', showConfirmButton: false, timer: 2000 });
                return false;
            }

            let url = "/keuangan/laporan_mutasi/getExcelAll/" + filter_bulan + "/" + filter_tahun + "/" + select_payment_type_one + "/" + select_payment_type_one_text + "?id_perusahaan=" + id_perusahaan;
            window.open(url, '_blank');

        } else {
            // Default matrix excel export
            let periode = $("#slc_tahun").val() || new Date().getFullYear();
            let url = "/keuangan/laporan_mutasi/getExcel/" + periode;
            window.open(url, '_blank');
        }
    });

    function parseDate(str) {
        if (!str) return new Date();
        let parts = str.split("-");
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    $('#select_type_export').on('change', function () {
        let val = $(this).val();
        if (val == "0") {
            $('.type-export-beban').removeAttr('hidden');
            $('.type-export-mutasi').attr('hidden', true);
        } else if (val == "1") {
            $('.type-export-beban').attr('hidden', true);
            $('.type-export-mutasi').removeAttr('hidden');
        } else {
            $('.type-export-beban').attr('hidden', true);
            $('.type-export-mutasi').attr('hidden', true);
        }
    });

});