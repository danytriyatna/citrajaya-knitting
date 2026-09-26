$(document).ready(function () {
    const DASH_BASE = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/');
    let slBulanFrom = $("#bulan_from");
    let slBulanTo   = $("#bulan_to");
    let slTahunFrom = $("select[name='slc_tahun']").first();
    let slTahunTo   = $("#tahun_to");

    let idMoneyParams = {
        decimal: ",",
        thousand: ".",
        symbol: "",
        precision: 2
    };

    function formatRupiah(num, withDecimals = false) {
        let isNegative = num < 0;
        let absNum = Math.abs(num);
        let digits = withDecimals ? 2 : 0;
        let formatted = Number(absNum).toLocaleString('id-ID', { minimumFractionDigits: digits, maximumFractionDigits: digits });
        return (isNegative ? "-Rp " : "Rp ") + formatted;
    }

    // 1. Tabulator Penjualan
    let dtPenjualan = new Tabulator("#dt-list-penjualan", {
        layout: 'fitDataFill',
        placeholder: "Tidak ada data penjualan",
        pagination: false,
        selectableRows: false,
        columns: [
            {
                title: "TIPE BAYAR", field: "tipe_bayar", headerSort: true, hozAlign: "left",
                bottomCalc: function () { return "TOTAL PENJUALAN"; }
            },
            { title: "TANGGAL", field: "tgl_transaksi", headerSort: true, hozAlign: "center" },
            { title: "JENIS", field: "transaksi", headerSort: true, formatter: "html", hozAlign: "center" },
            { title: "NO. REFERENSI", field: "kode_transaksi", headerSort: true, hozAlign: "center" },
            { title: "KETERANGAN / KONSUMEN", field: "keterangan", headerSort: true, hozAlign: "left" },
            {
                title: "NOMINAL (MASUK)", field: "nominal", headerSort: true, hozAlign: "right",
                formatter: "money", formatterParams: idMoneyParams,
                bottomCalc: "sum", bottomCalcFormatter: "money", bottomCalcFormatterParams: idMoneyParams
            }
        ]
    });

    // 2. Tabulator Pemakaian Bahan Baku (Barang Keluar Kategori 5)
    let dtPembelian = new Tabulator("#dt-list-pembelian", {
        layout: 'fitDataFill',
        placeholder: "Tidak ada data pemakaian bahan",
        pagination: false,
        selectableRows: false,
        columns: [
            {
                title: "GUDANG ASAL", field: "tipe_bayar", headerSort: true, hozAlign: "left",
                bottomCalc: function () { return "TOTAL PEMAKAIAN BAHAN"; }
            },
            { title: "TANGGAL", field: "tgl_transaksi", headerSort: true, hozAlign: "center" },
            { title: "NO. TRANSAKSI", field: "kode_transaksi", headerSort: true, hozAlign: "center" },
            { title: "NO. WO", field: "no_ref_wo", headerSort: true, hozAlign: "center" },
            { title: "BARANG / BENANG", field: "nama_barang", headerSort: true, hozAlign: "left" },
            { title: "LOT NO", field: "lot_no", headerSort: true, hozAlign: "center" },
            {
                title: "QTY (KG)", field: "qty", headerSort: true, hozAlign: "right",
                formatter: function (cell) { return parseFloat(cell.getValue() || 0).toFixed(2); },
                bottomCalc: "sum", bottomCalcFormatter: function (cell) { return parseFloat(cell.getValue() || 0).toFixed(2) + " Kg"; }
            },
            {
                title: "HARGA SATUAN", field: "harga", headerSort: true, hozAlign: "right",
                formatter: "money", formatterParams: idMoneyParams
            },
            {
                title: "TOTAL NILAI (KELUAR)", field: "nominal", headerSort: true, hozAlign: "right",
                formatter: "money", formatterParams: idMoneyParams,
                bottomCalc: "sum", bottomCalcFormatter: "money", bottomCalcFormatterParams: idMoneyParams
            }
        ]
    });

    // 3. Tabulator Operasional
    let dtOperasional = new Tabulator("#dt-list-operasional", {
        layout: 'fitDataFill',
        placeholder: "Tidak ada data operasional",
        pagination: false,
        selectableRows: false,
        columns: [
            {
                title: "TIPE BAYAR", field: "tipe_bayar", headerSort: true, hozAlign: "left",
                bottomCalc: function () { return "TOTAL BEBAN OPERASIONAL"; }
            },
            { title: "TANGGAL", field: "tgl_transaksi", headerSort: true, hozAlign: "center" },
            { title: "NO. TRANSAKSI", field: "kode_transaksi", headerSort: true, hozAlign: "center" },
            { title: "KODE AKUN", field: "kode", headerSort: true, hozAlign: "center" },
            { title: "NAMA AKUN", field: "nama", headerSort: true, hozAlign: "left" },
            { title: "KETERANGAN", field: "keterangan", headerSort: true, hozAlign: "left" },
            {
                title: "JUMLAH (KELUAR)", field: "nominal", headerSort: true, hozAlign: "right",
                formatter: "money", formatterParams: idMoneyParams,
                bottomCalc: "sum", bottomCalcFormatter: "money", bottomCalcFormatterParams: idMoneyParams
            }
        ]
    });

    // Search filters per Tab
    $(".tb-search-tab").on("keyup search", function () {
        let target = $(this).data("target");
        let val = $(this).val().toLowerCase().trim();

        if (target === "penjualan") {
            if (!val) { dtPenjualan.clearFilter(); return; }
            dtPenjualan.setFilter(function (data) {
                return (data.kode_transaksi && data.kode_transaksi.toLowerCase().includes(val)) ||
                       (data.keterangan && data.keterangan.toLowerCase().includes(val)) ||
                       (data.tipe_bayar && data.tipe_bayar.toLowerCase().includes(val));
            });
        } else if (target === "pembelian") {
            if (!val) { dtPembelian.clearFilter(); return; }
            dtPembelian.setFilter(function (data) {
                return (data.kode_transaksi && data.kode_transaksi.toLowerCase().includes(val)) ||
                       (data.no_ref_wo && data.no_ref_wo.toLowerCase().includes(val)) ||
                       (data.nama_barang && data.nama_barang.toLowerCase().includes(val)) ||
                       (data.lot_no && data.lot_no.toLowerCase().includes(val));
            });
        } else if (target === "operasional") {
            if (!val) { dtOperasional.clearFilter(); return; }
            dtOperasional.setFilter(function (data) {
                return (data.kode && String(data.kode).toLowerCase().includes(val)) ||
                       (data.nama && data.nama.toLowerCase().includes(val)) ||
                       (data.kode_transaksi && data.kode_transaksi.toLowerCase().includes(val)) ||
                       (data.keterangan && data.keterangan.toLowerCase().includes(val));
            });
        }
    });

    // Redraw tabulator on tab switch to fix column widths
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        let targetId = $(e.target).attr("data-bs-target");
        if (targetId === "#tab-penjualan") {
            dtPenjualan.redraw(true);
        } else if (targetId === "#tab-pembelian") {
            dtPembelian.redraw(true);
        } else if (targetId === "#tab-operasional") {
            dtOperasional.redraw(true);
        }
    });

    function loadPreview() {
        let b_from = slBulanFrom.val();
        let t_from = slTahunFrom.val();
        let b_to   = slBulanTo.val();
        let t_to   = slTahunTo.val();

        if (!b_from || !t_from || !b_to || !t_to) {
            Swal.fire({ icon: 'warning', text: 'Pilih semua filter periode terlebih dahulu.' });
            return;
        }

        let periodStart = parseInt(t_from + b_from);
        let periodEnd   = parseInt(t_to + b_to);
        if (periodEnd < periodStart) {
            Swal.fire({ icon: 'error', text: 'Periode "Sampai" tidak boleh lebih kecil dari periode "Dari"!' });
            return;
        }

        $(".preloader").css("opacity", "0.7").show();

        $.ajax({
            url: DASH_BASE + 'keuangan/laporan_laba/preview',
            type: 'POST',
            data: {
                bulan_from: b_from,
                tahun_from: t_from,
                bulan_to:   b_to,
                tahun_to:   t_to
            },
            dataType: 'json',
            success: function (res) {
                $(".preloader").hide().css("opacity", "1");
                if (res && res.status) {
                    // 1. Update KPI Summary Cards (tanpa koma desimal, rata kanan)
                    $("#kpi_penjualan").text(formatRupiah(res.total_penjualan || 0, false));
                    $("#kpi_pembelian").text(formatRupiah(res.total_pembelian || 0, false));
                    if (res.label_biaya_produksi) {
                        $("#kpi_pembelian_title").text(res.label_biaya_produksi);
                        $("#ikhtisar_pb_title").text("- " + res.label_biaya_produksi);
                        $("#ikhtisar_total_pb_title").text("TOTAL " + res.label_biaya_produksi.toUpperCase());
                    }
                    $("#kpi_labakotor").text(formatRupiah(res.laba_kotor || 0, false));
                    $("#kpi_operasional").text(formatRupiah(res.total_operasional || 0, false));
                    $("#kpi_lababersih").text(formatRupiah(res.laba_bersih || 0, false));

                    // 2. Update Ikhtisar Table
                    $("#ikhtisar_so").text(formatRupiah(res.total_so || 0));
                    $("#ikhtisar_cr").text(formatRupiah(res.total_cr || 0));
                    $("#ikhtisar_total_penjualan").text(formatRupiah(res.total_penjualan || 0));

                    $("#ikhtisar_pb").text(formatRupiah(res.total_pembelian || 0));
                    $("#ikhtisar_total_pembelian").text(formatRupiah(res.total_pembelian || 0));

                    $("#ikhtisar_laba_kotor").text(formatRupiah(res.laba_kotor || 0));

                    // Render dynamic beban operasional COA rows
                    let htmlBeban = "";
                    if (res.rekap_beban && res.rekap_beban.length > 0) {
                        res.rekap_beban.forEach(function (rb) {
                            htmlBeban += `<tr>
                                <td class="ps-4"><span class="badge bg-secondary me-2">${rb.kode}</span> ${rb.nama} <small class="text-muted">(${rb.count} trx)</small></td>
                                <td class="text-end font-weight-bold">${formatRupiah(rb.total)}</td>
                            </tr>`;
                        });
                    } else {
                        htmlBeban = `<tr><td colspan="2" class="text-center text-muted ps-4">Tidak ada beban operasional pada periode ini</td></tr>`;
                    }
                    $("#ikhtisar_body_beban").html(htmlBeban);

                    $("#ikhtisar_total_operasional").text(formatRupiah(res.total_operasional || 0));

                    let elLabaBersihRow = $("#row_laba_bersih");
                    if ((res.laba_bersih || 0) >= 0) {
                        elLabaBersihRow.removeClass("row-highlight-bersih-rugi").addClass("row-highlight-bersih");
                        $("#ikhtisar_laba_bersih").html(`<b>${formatRupiah(res.laba_bersih || 0)}</b>`);
                    } else {
                        elLabaBersihRow.removeClass("row-highlight-bersih").addClass("row-highlight-bersih-rugi");
                        $("#ikhtisar_laba_bersih").html(`<b class="text-danger">${formatRupiah(res.laba_bersih || 0)} (RUGI)</b>`);
                    }

                    // 3. Update Detail Tabs
                    let dataPenjualan = res.list_penjualan || [];
                    let dataPemakaian = res.list_pembelian || [];
                    let dataOperasional = res.list_operasional || [];

                    dtPenjualan.setData(dataPenjualan);
                    dtPembelian.setData(dataPemakaian);
                    dtOperasional.setData(dataOperasional);

                    $("#badge-cnt-penjualan").text(dataPenjualan.length);
                    $("#badge-cnt-pembelian").text(dataPemakaian.length);
                    $("#badge-cnt-operasional").text(dataOperasional.length);
                }
            },
            error: function (xhr, status, error) {
                $(".preloader").hide().css("opacity", "1");
                console.error("Error load preview:", error, xhr.responseText);
                Swal.fire({ icon: 'error', text: 'Gagal memuat data preview laba rugi.' });
            }
        });
    }

    $("#btn_cari").on("click", function () {
        loadPreview();
    });

    $("#btn_excel").on("click", function () {
        let b_from = slBulanFrom.val();
        let t_from = slTahunFrom.val();
        let b_to   = slBulanTo.val();
        let t_to   = slTahunTo.val();

        if (!b_from || !t_from || !b_to || !t_to) {
            Swal.fire({ icon: 'warning', text: 'Pilih semua filter periode terlebih dahulu.' });
            return;
        }

        let periodStart = parseInt(t_from + b_from);
        let periodEnd   = parseInt(t_to + b_to);
        if (periodEnd < periodStart) {
            Swal.fire({ icon: 'error', text: 'Periode "Sampai" tidak boleh lebih kecil dari periode "Dari"!' });
            return;
        }

        let url = DASH_BASE + "keuangan/laporan_laba/getExcel/" + b_from + "/" + t_from + "/" + b_to + "/" + t_to;
        window.open(url, '_blank');
    });

    // Auto load on initial load
    loadPreview();
});