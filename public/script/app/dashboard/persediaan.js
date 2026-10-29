$(document).ready(function () {

    let selGudang = $("#filter_persediaan_gudang");
    let selJenis = $("#filter_persediaan_jenis");
    let selBulan = $("#filter_persediaan_bulan");
    let selTahun = $("#filter_persediaan_tahun");
    let btnUpdate = $("#btn_update_persediaan");
    let inpSearch = $("#inp-persediaan-search");
    let panelDetail = $("#panel-persediaan-detail");

    let cellMoney2 = function (cell) {
        let val = parseFloat(cell.getValue() || 0);
        return val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    let cellRupiah = function (cell) {
        let val = parseFloat(cell.getValue() || 0);
        return val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    // ==== Tabel ringkasan (KODE WARNA / NAMA BARANG / UNIT / QTY AWAL / QTY MASUK / QTY KELUAR / QTY AKHIR / DETAIL) ====
    let dtPersediaan = new Tabulator("#tbl-persediaan-ringkasan", {
        layout: "fitColumns",
        placeholder: "Tidak ada data",
        locale: 'id',
        initialSort: [
            { column: "qty_keluar", dir: "desc" }
        ],
        height: "420px", // tampilkan ~10 baris, sisanya scroll ke bawah (bukan paging)
        columns: [
            { title: "KODE WARNA", field: "kode_warna", headerSort: false, width: "12%" },
            { title: "NAMA BARANG", field: "nama_barang", headerSort: false, width: "20%" },
            { title: "UNIT", field: "unit", headerSort: false, width: "6%" },
            {
                title: "QTY AWAL", field: "qty_awal", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                sorter: "number", width: "8%", bottomCalc: "sum", formatter: cellMoney2, bottomCalcFormatter: cellMoney2
            },
            {
                title: "QTY MASUK", field: "qty_masuk", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                sorter: "number", width: "8%", bottomCalc: "sum", formatter: cellMoney2, bottomCalcFormatter: cellMoney2
            },
            {
                title: "QTY KELUAR", field: "qty_keluar", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                sorter: "number", width: "9%", bottomCalc: "sum", formatter: cellMoney2, bottomCalcFormatter: cellMoney2
            },
            {
                title: "QTY AKHIR", field: "qty_akhir", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                sorter: "number", width: "9%", bottomCalc: "sum", formatter: function (cell) {
                    let val = parseFloat(cell.getValue() || 0);
                    if (val < 0) cell.getElement().style.color = "#dc3545";
                    return cellMoney2(cell);
                }, bottomCalcFormatter: cellMoney2
            },
            {
                title: "HARGA", field: "harga", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                sorter: "number", width: "10%", formatter: cellRupiah
            },
            {
                title: "NILAI", field: "nilai", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                sorter: "number", width: "10%", bottomCalc: "sum", formatter: cellRupiah, bottomCalcFormatter: cellRupiah
            },
            {
                title: "DETAIL", headerSort: false, hozAlign: "center", width: "8%",
                formatter: function () {
                    return "<button type='button' class='btn btn-sm btn-outline-warning btn-persediaan-detail'>Detail</button>";
                },
                cellClick: function (e, cell) {
                    if ($(e.target).hasClass('btn-persediaan-detail') || $(e.target).closest('.btn-persediaan-detail').length) {
                        openPersediaanDetail(cell.getRow().getData());
                    }
                }
            }
        ]
    });

    function loadPersediaanRingkasan(forceUpdate) {
        let baseUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/');
        let endpoint = baseUrl + (forceUpdate ? "dashboard/persediaan_update" : "dashboard/persediaan_ringkasan");
        $(".preloader").css("opacity", "0.7").show();
        $.ajax({
            url: endpoint,
            type: "GET",
            dataType: "json",
            data: {
                id_gudang: selGudang.val(),
                id_jenis_barang: selJenis.val(),
                bulan: selBulan.val(),
                tahun: selTahun.val(),
                search: inpSearch.val()
            },
            success: function (res) {
                let rows = res.status ? (res.data || []) : [];
                dtPersediaan.setData(rows).then(function () {
                    // Paksa Tabulator hitung ulang lebar kolom (fitColumns) supaya
                    // tabel benar-benar mengisi penuh lebar card, tidak menyisakan
                    // ruang kosong (bisa terjadi kalau lebar container awal salah terukur).
                    dtPersediaan.redraw(true);
                });
                $(".preloader").hide().css("opacity", "1");
            },
            error: function () {
                $(".preloader").hide().css("opacity", "1");
                if (typeof toastr !== 'undefined') {
                    toastr.error("Gagal memuat data ringkasan persediaan", "Error", { positionClass: "toast-top-right" });
                }
            }
        });
    }

    // Variabel untuk menyimpan instance Tabulator detail
    let dtPersediaanDetail = null;

    function openPersediaanDetail(rowData) {
        $(".preloader").css("opacity", "0.7").show();
        $.ajax({
            url: (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + "dashboard/persediaan_detail",
            type: "GET",
            dataType: "json",
            data: {
                id_gudang: selGudang.val(),
                id_jenis_barang: selJenis.val(),
                bulan: selBulan.val(),
                tahun: selTahun.val(),
                id_barang: rowData.id_barang
            },
            success: function (res) {
                $(".preloader").hide().css("opacity", "1");
                if (!res.status) return;

                $("#txt-persediaan-detail-title").text("Detail Stock Card - " + rowData.kode_warna);
                $("#txt-persediaan-detail-sub").text(rowData.nama_barang + " (" + rowData.unit + ")");
                $("#txt-persediaan-total-awal").text(parseFloat(res.total_awal || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $("#txt-persediaan-total-akhir").text(parseFloat(res.total_akhir || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                panelDetail.show(); // Tampilkan kontainer DULU agar ukurannya terbaca browser

                // Tambahkan jeda waktu agar CSS display:block benar-benar teraplikasi di browser
                setTimeout(function() {
                    // Hancurkan tabel yang lama jika sudah pernah dibuat
                    if (dtPersediaanDetail) {
                        dtPersediaanDetail.destroy();
                    }
                    
                    // Buat (inisialisasi) tabel BARU SAAT panel sudah terbuka dan membesar
                    dtPersediaanDetail = new Tabulator("#tbl-persediaan-detail", {
                        layout: "fitColumns",
                        data: res.detail || [],
                        placeholder: "Tidak ada data",
                        locale: 'id',
                        columns: [
                            { title: "Tanggal", field: "tanggal", headerSort: false, width: "8%" },
                            { title: "Transaksi", field: "transaksi", headerSort: false, width: "12%" },
                            { title: "No. Transaksi", field: "no_transaksi", headerSort: false, width: "10%" },
                            { title: "Unit", field: "unit", headerSort: false, width: "6%" },
                            { title: "Lot", field: "lot", headerSort: false, hozAlign: "right", headerHozAlign: "right", width: "6%" },
                            { title: "Pack", field: "pack", headerSort: false, hozAlign: "right", headerHozAlign: "right", width: "6%" },
                            {
                                title: "Qty Awal", field: "qty_awal", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                                width: "8%", formatter: cellMoney2
                            },
                            {
                                title: "Qty Masuk", field: "qty_masuk", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                                width: "8%", bottomCalc: "sum", formatter: cellMoney2, bottomCalcFormatter: cellMoney2
                            },
                            {
                                title: "Qty Keluar", field: "qty_keluar", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                                width: "8%", bottomCalc: "sum", formatter: cellMoney2, bottomCalcFormatter: cellMoney2
                            },
                            {
                                title: "Qty Akhir", field: "qty_akhir", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                                width: "8%", formatter: cellMoney2
                            },
                            {
                                title: "Harga", field: "harga", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                                width: "10%", formatter: cellRupiah
                            },
                            {
                                title: "Nilai", field: "nilai", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                                width: "10%", formatter: cellRupiah
                            },
                            {
                                title: "Jumlah", field: "jumlah", headerSort: false, hozAlign: "right", headerHozAlign: "right",
                                visible: false, bottomCalc: "sum", formatter: function (cell) {
                                    let v = cell.getValue();
                                    return (v === null || v === undefined) ? "" : cellRupiah(cell);
                                }, bottomCalcFormatter: cellRupiah
                            }
                        ]
                    });

                    $('html, body').animate({ scrollTop: panelDetail.offset().top - 100 }, 300);
                }, 100);
            },
            error: function () {
                $(".preloader").hide().css("opacity", "1");
                if (typeof toastr !== 'undefined') {
                    toastr.error("Gagal memuat detail stock card", "Error", { positionClass: "toast-top-right" });
                }
            }
        });
    }

    $("#btn-close-persediaan-detail").on("click", function () {
        panelDetail.hide();
    });

    // ==== event handlers ====
    // Tombol "Update Data": recompute snapshot trans_barang_history dulu (bisa agak lambat),
    // baru tampilkan ringkasan terbaru - beda dengan ganti filter yang cuma baca cache lama.
    btnUpdate.on("click", function () {
        panelDetail.hide();
        loadPersediaanRingkasan(true);
    });

    selGudang.on("change", function () { panelDetail.hide(); loadPersediaanRingkasan(); });
    selJenis.on("change", function () { panelDetail.hide(); loadPersediaanRingkasan(); });
    selBulan.on("change", function () { panelDetail.hide(); loadPersediaanRingkasan(); });
    selTahun.on("change", function () { panelDetail.hide(); loadPersediaanRingkasan(); });

    let searchTimer = null;
    inpSearch.on("input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            loadPersediaanRingkasan();
        }, 400);
    });

    // load pertama kali sesuai default (Gudang Utama, Benang Rajut, bulan & tahun berjalan)
    loadPersediaanRingkasan();
});
