
let inpIdKonsumen = $('#id_buyer');
let inpTglReceive = $('#tanggal');
let spanKonsumen = $('#spanKonsumen');
let divNamaKonsumen = $('#divNamaKonsumen');
let inpKonsumen = $('#nama_konsumen');
let inpBarang = $('#namaBarang');
let inpKeterangan = $('#trans_desc');
let inpIdBarang = $('#idBarang');
let inpKodeBarang = $('#kodeBarang');
let inpQtyItem = $('#qty_item');
let inpPrice = $('#price');
let inpUnit = $('#unit');
let inpEdit = $('#edit');
let spanBarang = $('#spanBarang');
let selectGudang = $('#select_warehouse');
let selectPerusahaan = $('#select_perusahaan');
let selectKategori = $('#select_kategori');
let selectProses = $('#select_proses');
let selectOperator = $('#select_operator');
let inpLotNo = $('#lot_no');
let inpPack = $('#pack_id');
let detailData = $("#data-details").val().replace(/&quot;/ig, '"');
let dataSO = $("#data-so").val().replace(/&quot;/ig, '"');
let btnAdd = $('#btn-add');
let btnSimpan = $('#btn-simpan');
let btnApprove = $('#btn-approve');
let btnCancelApprove = $('#btn-cancel-approve');
let btnSimpanDetail = $('#btn-simpan-det');
let btnView = $('#ic_ref_transfer');
let inpNoRefTrf = $('#no_ref_transfer');
const inpIdRefTrf = $("#id_ref_transfer");
let modalDet = $('#modal-detail-item');
let inpStatus = $('#status');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');
const inpRole = $('#role_id');

const divDetail = $(".div_detail, .div_details");
const divRefProduk = $(".div_produksi");

// Backup semua opsi gudang asli saat inisialisasi
const allGudangOptions = [];
selectGudang.find('option').each(function () {
    const val = $(this).val();
    if (val !== "" && val !== null && val !== undefined) {
        allGudangOptions.push({
            id: val,
            text: $(this).text().trim(),
            id_perusahaan: $(this).attr('data-perusahaan') || '',
            selected: $(this).is(':selected')
        });
    }
});

function updateGudangByPerusahaan(idPerusahaan, setDefault = false) {
    if (!selectGudang.length || allGudangOptions.length === 0) return;

    const currentVal = selectGudang.val();
    const isEdit = (inpIdHeader.val() && inpIdHeader.val().length > 0);

    selectGudang.empty();
    selectGudang.append('<option value="" disabled>-- Pilih Warehouse --</option>');

    let defaultGudangId = null;
    if (idPerusahaan == 2) {
        // Citra Knitt -> default CITRA KNITT (ID 60)
        defaultGudangId = "60";
    } else if (idPerusahaan == 1) {
        // PT CJK -> default Gudang Utama (ID 1)
        defaultGudangId = "1";
    }

    let hasMatchCurrent = false;

    allGudangOptions.forEach(opt => {
        const isMatch = (!idPerusahaan || opt.id_perusahaan === "" || opt.id_perusahaan == idPerusahaan);
        if (isMatch) {
            const isSel = (!setDefault && opt.id == currentVal);
            if (isSel) hasMatchCurrent = true;
            selectGudang.append(new Option(opt.text, opt.id, false, isSel));
        }
    });

    if (setDefault || (!hasMatchCurrent && !isEdit)) {
        if (defaultGudangId && selectGudang.find(`option[value="${defaultGudangId}"]`).length > 0) {
            selectGudang.val(defaultGudangId).trigger('change');
        } else {
            const firstValid = selectGudang.find('option:not([disabled])').first().val();
            if (firstValid) {
                selectGudang.val(firstValid).trigger('change');
            }
        }
    } else {
        selectGudang.trigger('change');
    }
}

if (inpIdHeader.val().length == 0) {
    selectKategori.val("").trigger("change");
    if (selectPerusahaan.val()) {
        updateGudangByPerusahaan(selectPerusahaan.val(), true);
    } else {
        selectGudang.val("").trigger("change");
        selectPerusahaan.val("").trigger("change");
    }
} else {
    updateGudangByPerusahaan(selectPerusahaan.val(), false);
}



setTimeout(() => {
    if (selectKategori.val() == 3) {
        divNamaKonsumen.removeClass("d-none")
    } else if (selectKategori.val() == 12 || selectKategori.val() == 1 || selectKategori.val() == 15) {
        dtList.hideColumn('amount');
        divRefProduk.show();
        try {
            let parsedDet = (typeof detailData === 'string') ? JSON.parse(detailData || '[]') : (detailData || []);
            if (Array.isArray(parsedDet) && parsedDet.length > 0) {
                divDetail.show();
            } else {
                divDetail.hide();
            }
        } catch (e) {
            divDetail.hide();
        }
        setTimeout(() => {
            if (typeof dtListProduksi !== 'undefined' && dtListProduksi) {
                dtListProduksi.redraw(true);
            }
        }, 300);
    }
    if (inpNoRefTrf.val() && inpNoRefTrf.val().length > 0) {
        selectProses.prop('disabled', true);
        selectOperator.prop('disabled', true);
    }
}, 500);

inpNoRefTrf.on('input change', function () {
    if (!$(this).val() || $(this).val().trim() === '') {
        selectProses.prop('disabled', false);
        selectOperator.prop('disabled', false);
    } else {
        selectProses.prop('disabled', true);
        selectOperator.prop('disabled', true);
    }
});

const statusVal = inpStatus.val();
const isApproved = (statusVal == 1 || statusVal == "1");

if (!isApproved) {
    btnAdd.show();
    btnSimpan.show();
    btnApprove.show();
    btnCancelApprove.hide();
} else {
    btnAdd.hide();
    btnSimpan.hide();
    btnApprove.hide();
    
    const roleId = parseInt(inpRole.val());
    const isAllowedCancelRole = [1, 2, 4, 5].includes(roleId);
    if (inpIdHeader.val().length > 0 && isAllowedCancelRole) {
        btnCancelApprove.show().removeClass("d-none").removeAttr("hidden");
    } else {
        btnCancelApprove.hide();
    }
}
// const regex = /^[0-9]+(\.[0-9]+)?$/; // Hanya angka dan desimal
const regex = /^[0-9.,]+$/;


let dtListKonsumen = new Tabulator("#dt-list-konsumen", {
    columns: [
        {
            title: "Nama Konsumen", field: "nama", headerSort: false,
            width: "25%"
        },
        {
            title: "Alamat", field: "alamat", formatter: "html", headerSort: false,
            width: "45%"

        },
        {
            title: "Email", field: "email", headerSort: false,
            width: "15%", cssClass: 'text-center'
        },
        {
            title: "No. HP", field: "no_hp", headerSort: false,
            width: "15%", cssClass: 'text-center'
        },
    ],
    locale: 'id',
    ajaxURL: "/master-data/konsumen/list",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
    },
    ajaxResponse: function (url, params, response) {
        let pageSize = dtListKonsumen.getPageSize();
        let pageNo = dtListKonsumen.getPage();
        let startRow = (pageSize * (pageNo - 1)) + 1;
        let endRow = response.data.length + startRow - 1;
        if (response.data.length === 0) {
            startRow = 0; endRow = 0;
        }
        let recordsFiltered = parseInt(response.recordsFiltered);
        let recordsTotal = parseInt(response.recordsTotal);

        $("#table-footer3 .tabulator-startrow").text(startRow);
        $("#table-footer3 .tabulator-endrow").text(endRow);
        $("#table-footer3 .tabulator-totalrow").text(recordsFiltered);

        let elTotalFilteredRow = $("#table-footer3 .tabulator-totalfilteredrow");
        elTotalFilteredRow.text("");
        if (recordsTotal > recordsFiltered) {
            elTotalFilteredRow.text(" (disaring dari " + recordsTotal
                + " entri keseluruhan)");
        }
        return response;
    },
    footerElement: '<div id="table-footer3" class="pull-left tabulator-info">'
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


let searchThreadKonsumen = null;
let elSearchKonsumen = $("#tb-search3");
if (elSearchKonsumen != null) {
    elSearchKonsumen.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThreadKonsumen);
        searchThreadKonsumen = setTimeout(function () {
            dtListKonsumen.setFilter("", "like", elSearchKonsumen.val());
        }, 600);
    });
}

dtListKonsumen.on("rowClick", function (e, row) {
    var namaKonsumen = row._row.data.nama.replace(/<[^>]*>/g, '');
    var idKonsumen = row._row.data.id;
    inpKonsumen.val(namaKonsumen)
    inpIdKonsumen.val(idKonsumen)
    $("#modal-konsumen").modal("hide");
})


let dtListBarang = new Tabulator("#dt-list-barang", {
    columns: [
        {
            title: "ID Barang", field: "id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "Kode Barang", field: "kode_barang", headerSort: false,
            width: "20%"
        },
        {
            title: "Nama Barang", field: "nama_barang", headerSort: false,
            width: "30%"
        },
        {
            title: "Jenis Barang", field: "nama_jenis_barang", headerSort: false,
            width: "25%"
        },
        {
            title: "Satuan", field: "nama_satuan", headerSort: false,
            width: "10%"
        },
    ],
    locale: 'id',
    ajaxURL: "/master-data/barang/list",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    selectableRows: true,
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
    },
    ajaxResponse: function (url, params, response) {
        let pageSize = dtListBarang.getPageSize();
        let pageNo = dtListBarang.getPage();
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
    selectable: false,
});


let searchThreadBarang = null;
let elSearchBarang = $("#tb-search-barang");
if (elSearchBarang != null) {
    elSearchBarang.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThreadBarang);
        searchThreadBarang = setTimeout(function () {
            dtListBarang.setFilter("", "like", elSearchBarang.val());
        }, 600);
    });
}


dtListBarang.on("rowClick", function (e, row) {

    if (selectKategori.val() == null) {
        return Swal.fire({
            text: "Kategori harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if (selectGudang.val() == 0) {
        return Swal.fire({
            text: "Gudang Tujuan harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if (selectPerusahaan.val() == 0) {
        return Swal.fire({
            text: "Perusahaan harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (dtListDetail.getData().some(x => x.id_barang == idBarang)) {
        return Swal.fire({
            text: `Barang ${namaBarang} telah dipilih`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    var idBarang = row._row.data.id;
    var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    var namaSatuan = row._row.data.nama_satuan.replace(/<[^>]*>/g, '');
    inpIdBarang.val(idBarang)
    inpBarang.val(`${namaBarang}`)
    inpKodeBarang.val(`${kodeBarang}`)
    inpUnit.val(namaSatuan)
    inpLotNo.val("")
    $("#modal-barang").modal("hide");
})

let dtListSO = new Tabulator("#dt-list-sample", {
    columns: [
        { title: "ID", field: "id", width: "20%", visible: false },
        {
            title: 'TRANSACTION NO.', field: 'kode_transaksi', headerSort: false, sorter: 'string',
            width: "15%", formatter: "html"
        },

        {
            title: 'DATE', field: 'tanggal', headerSort: false, sorter: 'string',
            width: "15%"
        },

        {
            title: 'TRANSFER FORM', field: 'gudang_asal', formatter: "html", align: "center", cssClass: "text-center", headerSort: false,
            width: "20%",
        },
        {
            title: 'TRANSFER TO', field: 'gudang_tujuan', formatter: "html", align: "center", cssClass: "text-center", headerSort: false,
            width: "20%",
        },
        {
            title: 'CMT', field: 'nama_operator', formatter: "html", align: "center", cssClass: "text-center", headerSort: false,
            width: "20%",
        },
        {
            title: 'STATUS', field: 'status', formatter: "html", align: "center", headerSort: false,
            width: "15%", hozAlign: "center",
        },
    ],

    locale: 'id',
    ajaxURL: "/trans/item-transfer/list-ref",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    placeholder: "Tidak ada data",
    selectableRows: true,
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.isApprove = true;
        params.kategori = selectKategori.val();
        params.id_perusahaan = selectPerusahaan.val();
        params.id_gudang = selectGudang.val();
    },
    ajaxResponse: function (url, params, response) {
        let pageSize = dtListSO.getPageSize();
        let pageNo = dtListSO.getPage();
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
});

let searchThreadSO = null;
let elSearchSO = $("#tb-search-so");
if (elSearchSO != null) {
    elSearchSO.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThreadSO);
        searchThreadSO = setTimeout(function () {
            dtListSO.setFilter("", "like", elSearchSO.val());
        }, 600);
    });
}

let searchThreadPO = null;
let elSearchPO = $("#tb-search-po");

if (elSearchPO.length > 0) {
    elSearchPO.on("keyup", function (e) {
        let keyword = $(this).val().trim().toLowerCase();

        clearTimeout(searchThreadPO);
        searchThreadPO = setTimeout(function () {
            // Jika kosong, clear filter (tampilkan semua data)
            if (keyword === "") {
                dtListProduksiRef.clearFilter();
                return;
            }

            // Kalau kurang dari 3 karakter dan bukan Enter, jangan cari
            if (keyword.length < 3 && e.keyCode !== 13) {
                return;
            }

            // Jalankan filter multi-kolom
            dtListProduksiRef.setFilter(function (data) {
                return (
                    String(data.kode_sales_order).toLowerCase().includes(keyword) ||
                    String(data.style).toLowerCase().includes(keyword) ||
                    String(data.deskripsi).toLowerCase().includes(keyword) ||
                    String(data.buyer).toLowerCase().includes(keyword) ||
                    String(data.color).toLowerCase().includes(keyword) ||
                    String(data.qty_kirim).toLowerCase().includes(keyword) ||
                    String(data.kode_ukuran).toLowerCase().includes(keyword)
                );
            });
        }, 600);
    });
}


const inpProses = $("#proses");
const inpIdproses = $("#id_proses");

const inpOperator = $("#nama_operator");
const inpIdCmt = $("#id_cmt");


let refData = [];
let xrefData = [];
dtListSO.on("rowClick", function (e, row) {
    const xdata = row.getData();

    // console.log("data", xdata)

    if (inpNoRefTrf.val().length > 0) {
        if (xdata.kode_transaksi != inpNoRefTrf.val()) {
            Swal.fire({
                title: "Apakah Anda yakin ingin mengubah Referensi transaksi?",
                text: "Data produksi yang sudah ada akan dihapus.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, ubah",
                cancelButtonText: "Batal",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6C757D"
            }).then((result) => {
                if (result.isConfirmed) {
                    dtListProduksi.setData([]);
                    dtList.setData([]);
                } else {
                    return false;
                }
            });
        }

    }

    inpNoRefTrf.val(xdata.kode_transaksi);
    inpIdRefTrf.val(xdata.id);

    // Sinkronkan dan pilih Proses
    if (xdata.id_proses) {
        if (selectProses.find(`option[value="${xdata.id_proses}"]`).length === 0 && xdata.proses) {
            selectProses.append(new Option(xdata.proses, xdata.id_proses, true, true));
        }
        selectProses.val(xdata.id_proses).trigger('change.select2');
    }

    // Sinkronkan dan pilih CMT
    if (xdata.id_cmt) {
        if (selectOperator.find(`option[value="${xdata.id_cmt}"]`).length === 0 && xdata.nama_operator) {
            selectOperator.append(new Option(xdata.nama_operator, xdata.id_cmt, true, true));
        }
        selectOperator.val(xdata.id_cmt).trigger('change.select2');
    }

    // Lock Proses dan CMT agar tidak bisa dirubah saat menggunakan No. Transfer
    selectProses.prop('disabled', true);
    selectOperator.prop('disabled', true);

    loadDataSo();

    $("#modal-so").modal("hide");
})


// load data som
function loadDataSo(callback = null) {
    const kode_transaksi = inpNoRefTrf.val();
    let url_dis = null;
    if (kode_transaksi != null && kode_transaksi != undefined && kode_transaksi != "") {
        url_dis = `/trans/item-transfer/data-so?noSO=${encodeURIComponent(kode_transaksi)}`;
    }
    else if (selectProses.val() != null && selectOperator.val() != null) {
        let idPrsh = selectPerusahaan.val();
        const whPrsh = selectGudang.find('option:selected').attr('data-perusahaan');
        if (whPrsh) {
            idPrsh = whPrsh;
        } else if (!idPrsh && isPerusahaanCitraKnitt()) {
            idPrsh = 2;
        }
        const params = new URLSearchParams({
            id_proses: selectProses.val(),
            id_cmt: selectOperator.val(),
            id_perusahaan: idPrsh || '',
        });
        url_dis = `/trans/item-transfer/data-so?${params.toString()}`;
    }

    if (url_dis != null) {
        $(".preloader").css("opacity", "0.7").show();
        $.ajax({
            url: url_dis,
            type: 'GET',
            dataType: 'json',
            success: function (data) {

                if (data.status) {
                    const isProduksi = (selectKategori.val() == 12 || selectKategori.val() == 1 || selectKategori.val() == 15);
                    const hasSO = (data.dataSO && data.dataSO.length > 0);
                    const hasSODet = (data.dataSODet && data.dataSODet.length > 0);
                    const isTrfDoc = (inpNoRefTrf.val() && inpNoRefTrf.val().length > 0);

                    if (isProduksi) {
                        if (isTrfDoc) {
                            // Skenario dokumen transfer dipilih:
                            refData = data.dataSO || [];
                            xrefData = data.dataSO || [];

                            // 1. Jika membawa data SPK Produksi, tampilkan dan langsung isi tabel produksi
                            if (hasSO) {
                                divRefProduk.show();
                                const now = new Date();
                                const yyyy = now.getFullYear();
                                const mm = String(now.getMonth() + 1).padStart(2, '0');
                                const dd = String(now.getDate()).padStart(2, '0');
                                const defaultDate = `${yyyy}-${mm}-${dd}`;

                                const idProses = selectProses.val();
                                const idCmt = selectOperator.val();

                                const soData = (data.dataSO || []).map(item => {
                                    const q = (item.qty !== undefined && item.qty !== null && item.qty !== '') ? parseFloat(item.qty) : (parseFloat(item.qty_sisa) || 0);
                                    const h = parseFloat(item.harga) || 0;
                                    item.qty = q;
                                    item.harga = h;
                                    item.amount = (q > 0 && h > 0) ? (q * h) : (parseFloat(item.amount) || 0);
                                    if (!item.tgl_transaksi || item.tgl_transaksi === '') {
                                        item.tgl_transaksi = defaultDate;
                                    }
                                    return item;
                                });
                                dtListProduksi.setData(soData);
                                setTimeout(() => {
                                    if (typeof dtListProduksi !== 'undefined' && dtListProduksi) {
                                        dtListProduksi.redraw(true);
                                    }
                                }, 200);

                                if (isPerusahaanCitraKnitt() && idProses && idCmt) {
                                    const pricePromises = soData.map(item => {
                                        if ((!item.harga || parseFloat(item.harga) === 0) && item.style) {
                                            return $.ajax({
                                                url: baseUrl + '/trans/incoming-goods/last-price',
                                                type: 'GET',
                                                data: {
                                                    id_proses: idProses,
                                                    id_cmt: idCmt,
                                                    style: item.style,
                                                    id_perusahaan: selectPerusahaan.val()
                                                }
                                            }).then(res => {
                                                if (res && res.status && parseFloat(res.harga) > 0) {
                                                    item.harga = parseFloat(res.harga);
                                                    item.amount = (parseFloat(item.qty) || 0) * item.harga;
                                                }
                                                return item;
                                            }).catch(() => item);
                                        }
                                        return Promise.resolve(item);
                                    });

                                    Promise.all(pricePromises).then(updatedSoData => {
                                        dtListProduksi.setData(updatedSoData);
                                        setTimeout(() => {
                                            if (typeof dtListProduksi !== 'undefined' && dtListProduksi) {
                                                dtListProduksi.redraw(true);
                                            }
                                        }, 200);
                                    });
                                }
                            } else {
                                divRefProduk.hide();
                                dtListProduksi.setData([]);
                            }

                            // 2. Jika membawa detail benang/bahan baku, tampilkan dan isi tabel benang
                            if (hasSODet) {
                                divDetail.show();
                                btnAdd.show();
                                if (typeof dtListDetail !== 'undefined' && dtListDetail) {
                                    dtListDetail.setData(data.dataSODet);
                                }
                            } else {
                                divDetail.hide();
                                btnAdd.hide();
                                if (typeof dtListDetail !== 'undefined' && dtListDetail) {
                                    dtListDetail.setData([]);
                                }
                            }
                        } else {
                            // Skenario input manual Hasil Produksi (tanpa no transfer):
                            divDetail.hide();
                            divRefProduk.show();
                            btnAdd.hide();
                            refData = data.dataSO || [];
                            xrefData = data.dataSO || [];
                            if (typeof dtListDetail !== 'undefined' && dtListDetail) {
                                dtListDetail.setData([]);
                            }
                        }
                    } else {
                        divDetail.show();
                        divRefProduk.hide();
                        btnAdd.show();
                        refData = [];
                        xrefData = [];
                        dtList.setData(data.dataSO || []);
                        dtListProduksi.setData(data.dataSO || []);

                        // Jika transfer non-produksi membawa detail barang/benang (dataSODet), masukkan ke dtListDetail
                        if (hasSODet && typeof dtListDetail !== 'undefined') {
                            dtListDetail.setData(data.dataSODet);
                        } else if (typeof dtListDetail !== 'undefined') {
                            dtListDetail.setData([]);
                        }
                    }
                }
                $(".preloader").hide().css("opacity", "1");
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function (xhr, status, error) {
                $(".preloader").hide().css("opacity", "1");
                console.error('Error fetching data:', error);
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    } else {
        if (typeof callback === 'function') {
            callback();
        }
    }
}

selectProses.on('change', function () {
    if (!inpNoRefTrf.val()) {
        if ((selectOperator.val() != null && selectOperator.val() != undefined && selectOperator.val() != "") && (selectProses.val() != null && selectProses.val() != undefined && selectProses.val() != "")) {
            loadDataSo();
        }
        else {
            refData = [];
        }
    }
});

selectOperator.on('change', function () {
    if (!inpNoRefTrf.val()) {
        if ((selectOperator.val() != null && selectOperator.val() != undefined && selectOperator.val() != "") && (selectProses.val() != null && selectProses.val() != undefined && selectProses.val() != "")) {
            loadDataSo();
        }
        else {
            refData = [];
        }
    }
});

selectPerusahaan.on('change', function () {
    const idPrsh = $(this).val();
    updateGudangByPerusahaan(idPrsh, inpIdHeader.val().length === 0);

    if (typeof dtListSO !== 'undefined' && dtListSO) {
        dtListSO.setData();
    }

    if (!inpNoRefTrf.val()) {
        if ((selectOperator.val() != null && selectOperator.val() != undefined && selectOperator.val() != "") && (selectProses.val() != null && selectProses.val() != undefined && selectProses.val() != "")) {
            loadDataSo();
        }
        else {
            refData = [];
        }
    }
});

setTimeout(() => {
    if (selectKategori.val() == 12 || selectKategori.val() == 1 || selectKategori.val() == 15) {
        if (selectProses.val() && selectOperator.val()) {
            loadDataSo();
        } else if (!inpIdHeader.val() || inpIdHeader.val().length === 0) {
            loadDataSo();
        }
    }
    else {
        if (!inpIdHeader.val() || inpIdHeader.val().length === 0) {
            selectProses.val(null).trigger('change');
            selectOperator.val(null).trigger('change');
        }
    }
}, 500);


let buttonRowAction = function (cell) {
    let fmBtnDelete = "";
    let fmBtnEdit = "";

    if (inpStatus.val() == 0) {
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;
    }


    return fmBtnEdit + " " + fmBtnDelete;
};

let buttonRowSOAction = function (cell) {
    let fmBtnDelete = "";
    let fmBtnEdit = "";

    if (inpStatus.val() == 0) {
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
    }


    return fmBtnEdit + " " + fmBtnDelete;
};

let dtListDetail = new Tabulator("#dt-list-detail", {
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        { field: "id", visible: false },
        { field: "isEdit", visible: false },
        { field: "id_barang", visible: false },
        { field: "id_header", visible: false },
        { field: "qty_receive", visible: false },
        { field: "nama_unit", visible: false },
        {
            headerSort: false,
            title: '#',
            formatter: buttonRowAction,
            width: '10%', align: "center", cssClass: "text-center",
            cellClick: function (e, cell) {
                let row = cell.getRow();
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
                            row.delete();
                        }
                    })
                } else if (e.target.title === 'edit') {

                    openModalDetail(row)

                }

            }
        },
        { title: "ITEM CODE", field: "kode_barang", hozAlign: "left", width: "15%" },
        { title: "ITEM DESCRIPTION", field: "nama_barang", hozAlign: "left", width: "25%" },
        {
            title: "QTY", field: "qty", hozAlign: "center", width: "10%", bottomCalc: "sum",
            bottomCalcFormatter: function (cell) {
                var value = cell.getValue();
                return Number(value).toFixed(2);
            }
        },
        { title: "UNIT", field: "nama_unit", hozAlign: "center", width: "15%" },
        {
            title: "PRICE", field: "price", formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, width: "10%"
        },
        { title: "LOT NO", width: "15%", field: "lot_no", hozAlign: "left" },
        { title: "PACK", width: "15%", field: "pack_name", hozAlign: "left" },
    ],
    locale: 'id',
    // layout: 'fitColumns',
    placeholder: "Tidak ada data",
});

if (detailData.length > 0) {
    setTimeout(() => {
        try {
            let isdata = JSON.parse(detailData);
            dtListDetail.setData(isdata);
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
}


// Hitung lebar minimum kolom otomatis berdasarkan teks judul kolom (header title)
// Memastikan teks judul + icon sort + padding tidak terpotong (truncated)
function calcHeaderTitleMinWidth(titleText, hasSort = true, extraBuffer = 26) {
    if (!titleText || titleText === '#') return 45;
    const cleanText = titleText.replace(/<[^>]*>/g, ' ').trim();
    let textWidth = 0;
    try {
        const canvas = calcHeaderTitleMinWidth.canvas || (calcHeaderTitleMinWidth.canvas = document.createElement("canvas"));
        const ctx = canvas.getContext("2d");
        ctx.font = "bold 13px -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
        textWidth = Math.ceil(ctx.measureText(cleanText).width);
    } catch (e) {
        textWidth = cleanText.length * 8.5;
    }
    const sortWidth = hasSort ? 25 : 0;
    return Math.max(textWidth + sortWidth + extraBuffer, 75);
}

let dtList = new Tabulator("#dt-list-so", {
    layout: "fitDataFill",
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        { title: "ID", field: "id_konsumen", visible: false },
        { title: "No.SO", field: "kode_sales_order", minWidth: Math.max(calcHeaderTitleMinWidth("No.SO"), 125) },
        { title: "Style", field: "style", minWidth: Math.max(calcHeaderTitleMinWidth("Style"), 100) },
        { title: "Deskripsi", field: "deskripsi", minWidth: Math.max(calcHeaderTitleMinWidth("Deskripsi"), 130) },
        { title: "Buyer", field: "buyer", minWidth: Math.max(calcHeaderTitleMinWidth("Buyer"), 110) },
        { title: "Colour", field: "color", minWidth: Math.max(calcHeaderTitleMinWidth("Colour"), 180) },
        { title: "Qty", field: "qty", minWidth: Math.max(calcHeaderTitleMinWidth("Qty"), 90) },
        { title: "Ukuran", field: "kode_ukuran", minWidth: Math.max(calcHeaderTitleMinWidth("Ukuran"), 85) },
        {
            title: "Amount", field: "amount", minWidth: Math.max(calcHeaderTitleMinWidth("Amount"), 120), formatter: "money", formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }
        },
    ],
    placeholder: "Tidak ada data",
});

let buttonRowActionRef = function (cell) {
    let fmBtnDelete = "";
    let fmBtnEdit = "";

    if (inpStatus.val() == 0) {
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        return fmBtnEdit + " " + fmBtnDelete;
    } else {
        let row = cell.getRow();
        let index = row.getPosition(true);
        return `<span class="fw-bold text-muted">${index}</span>`;
    }
};

let dtListProduksi = new Tabulator("#dt-list-so-produksi", {
    layout: "fitDataFill",
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        { title: "ID", field: "id_konsumen", visible: false },
        { title: "ID_MP", field: "id_mp", visible: false },
        {
            headerSort: false,
            title: '#',
            formatter: buttonRowActionRef,
            width: 45,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center",
            cssClass: "text-center",
            cellClick: function (e, cell) {
                let row = cell.getRow();
                if (e.target.title === 'delete' || $(e.target).closest('button').attr('title') === 'delete') {
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
                            row.delete();
                        }
                    });
                }
            }
        },
        {
            title: "Tipe",
            field: "print_type",
            minWidth: Math.max(calcHeaderTitleMinWidth("Tipe"), 105),
            hozAlign: "center",
            headerHozAlign: "center",
            formatter: function (cell) {
                let val = cell.getValue();
                if (val == 2 || val == "2") {
                    return `<span class="badge bg-warning text-dark font-weight-bold" style="font-size: 11px; padding: 4px 8px;">PERBAIKAN</span>`;
                }
                return `<span class="badge bg-success text-white font-weight-bold" style="font-size: 11px; padding: 4px 8px;">PRODUKSI</span>`;
            },
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            },
            editor: "list",
            editorParams: {
                values: {
                    "1": "PRODUKSI",
                    "2": "PERBAIKAN"
                }
            },
            mutator: function (value) {
                if (value == 2 || value == "2") return 2;
                return 1;
            }
        },
        { title: "No.SO", field: "kode_sales_order", minWidth: Math.max(calcHeaderTitleMinWidth("No.SO"), 125), hozAlign: "left", headerHozAlign: "left" },
        { 
            title: "Style", 
            field: "style", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Style"), 100), 
            hozAlign: "left", 
            headerHozAlign: "left",
            formatter: function (cell) {
                let val = cell.getValue();
                return `<div style="white-space: normal; line-height: 1.3;">${val ? val : '-'}</div>`;
            }
        },
        { 
            title: "Deskripsi", 
            field: "deskripsi", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Deskripsi"), 130), 
            hozAlign: "left", 
            headerHozAlign: "left",
            formatter: function (cell) {
                let val = cell.getValue();
                return `<div style="white-space: normal; line-height: 1.3;">${val ? val : '-'}</div>`;
            }
        },
        { 
            title: "Buyer", 
            field: "buyer", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Buyer"), 110), 
            hozAlign: "left", 
            headerHozAlign: "left",
            formatter: function (cell) {
                let val = cell.getValue();
                return `<div style="white-space: normal; line-height: 1.3;">${val ? val : '-'}</div>`;
            }
        },
        { 
            title: "Colour", 
            field: "color", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Colour"), 180), 
            hozAlign: "left", 
            headerHozAlign: "left",
            formatter: function (cell) {
                let val = cell.getValue();
                return `<div style="white-space: normal; line-height: 1.3;">${val ? val : '-'}</div>`;
            }
        },
        {
            title: "Tgl Transaksi",
            field: "tgl_transaksi",
            minWidth: Math.max(calcHeaderTitleMinWidth("Tgl Transaksi"), 135),
            hozAlign: "center",
            headerHozAlign: "center",
            sorter: "date",
            sorterParams: {
                format: "dd-MM-yyyy",
                alignEmptyValues: "top",
            },
            editor: "date",
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            }
        },
        { 
            title: "Qty Kirim", 
            field: "qty_kirim", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Qty Kirim"), 110),
            hozAlign: "right",
            headerHozAlign: "right",
            formatter: function (cell) {
                let val = parseFloat(cell.getValue() || 0);
                return val < 0 ? 0 : val.toFixed(0);
            }
        },
        {
            title: "Qty BTM",
            field: "qty",
            minWidth: Math.max(calcHeaderTitleMinWidth("Qty BTM"), 110),
            hozAlign: "right",
            headerHozAlign: "right",
            editor: "number",
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            },
            bottomCalc: "sum",
            validator: ["required", "numeric"],
            formatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toFixed(0);
            },
            bottomCalcFormatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toFixed(0);
            },
            cellEdited: function (cell) {
                const row = cell.getRow();
                const qty = parseFloat(cell.getValue()) || 0;
                const harga = parseFloat(row.getCell("harga").getValue()) || 0;

                row.update({
                    qty: qty,
                    qty_qc: qty,
                    amount: qty * harga
                });
            }
        },
        {
            title: "Berat (Kg)",
            field: "berat",
            minWidth: Math.max(calcHeaderTitleMinWidth("Berat (Kg)"), 120),
            hozAlign: "right",
            headerHozAlign: "right",
            editor: "input",
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            },
            bottomCalc: "sum",
            validator: ["numeric"],
            formatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toFixed(2);
            },
            bottomCalcFormatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toFixed(2);
            },
            editorParams: {
                elementAttributes: {
                    type: "number",
                    step: "0.01",
                    min: "0"
                }
            },
            cellEdited: function (cell) {
                const row = cell.getRow();
                const berat = parseFloat(cell.getValue()) || 0;

                row.update({
                    berat: berat,
                    berat_qc: berat
                });
            }
        },
        { title: "Ukuran", field: "kode_ukuran", minWidth: Math.max(calcHeaderTitleMinWidth("Ukuran"), 90), hozAlign: "center", headerHozAlign: "center" },
        {
            title: "Harga",
            field: "harga",
            minWidth: Math.max(calcHeaderTitleMinWidth("Harga"), 105),
            hozAlign: "right",
            headerHozAlign: "right",
            formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",
                precision: 0,
            },
            editor: "number",
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            },
            cellEdited: function (cell) {
                const row = cell.getRow();
                const harga = parseFloat(cell.getValue()) || 0;
                const qty = parseFloat(row.getCell("qty").getValue()) || 0;

                row.update({
                    harga: harga,
                    amount: qty * harga
                });
            }
        },
        {
            title: "Amount",
            field: "amount",
            minWidth: Math.max(calcHeaderTitleMinWidth("Amount"), 125),
            hozAlign: "right",
            headerHozAlign: "right",
            mutator: function (value, data) {
                const qty = (typeof data.qty !== 'undefined' && data.qty !== '' && data.qty !== null) ? parseFloat(data.qty) : 0;
                const harga = parseFloat(data.harga) || 0;
                const amt = parseFloat(value) || 0;
                if (amt === 0 && qty > 0 && harga > 0) {
                    return qty * harga;
                }
                return amt;
            },
            formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",
                precision: 0,
            }
        },
        { 
            title: "Keterangan", 
            field: "keterangan", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Keterangan"), 120),
            hozAlign: "left",
            headerHozAlign: "left",
            editor: "input",
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            }
        },
        { title: "Total Scanned", field: "total_scanned", visible: false },
        { title: "Tgl<br>Scan", field: "tgl_scan", visible: false },
    ],
    placeholder: "Tidak ada data",
});


const modalRefpo = $("#modal-ref-po");
const btnRefPo = $("#btn-ref-po");

const dtListProduksiRef = new Tabulator("#dt-list-refpo", {
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        { title: "No.SO", field: "kode_sales_order", width: "15%" },
        { title: "Style", field: "style", width: "15%" },
        { title: "Deskripsi", field: "deskripsi", width: "20%" },
        { title: "Buyer", field: "buyer", width: "20%" },
        { title: "Colour", field: "color", width: "20%" },
        { title: "Qty", field: "qty_kirim", width: "10%" },
        { title: "Ukuran", field: "kode_ukuran", width: "15%" },
        { title: "Harga", field: "harga", width: "15%", visible: false },
    ],
    placeholder: "Tidak ada data",
});


btnRefPo.on("click", function (e) {
    e.preventDefault();

    const idProses = selectProses.val();
    const idCmt = selectOperator.val();

    if (!idProses || !idCmt) {
        return Swal.fire({
            text: "Silahkan pilih referensi transfer terlebih dahulu atau pilih PROSES dan CMT.",
            icon: 'warning',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (!refData || refData.length === 0) {
        $(".preloader").css("opacity", "0.7").show();
        loadDataSo(function () {
            $(".preloader").hide().css("opacity", "1");
            if (!refData || refData.length === 0) {
                return Swal.fire({
                    text: "Data Tidak Ditemukan!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
            dtListProduksiRef.setData(refData);
            modalRefpo.modal("show");
        });
        return;
    }

    dtListProduksiRef.setData(refData);
    modalRefpo.modal("show");
});

modalRefpo.on('shown.bs.modal', function () {
    if (typeof dtListProduksiRef !== 'undefined' && dtListProduksiRef) {
        dtListProduksiRef.redraw(true);
    }
});

let isRowClicked = false;
dtListProduksiRef.on("rowClick", function (e, row) {
    if (isRowClicked) return; // cegah eksekusi dobel

    isRowClicked = true;

    const data = row._row.data
    let produksi_data = dtListProduksi.getData();

    let list_verif = produksi_data.filter(item => item.color === data.color && item.kode_ukuran === data.kode_ukuran);


    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    data.tgl_transaksi = `${yyyy}-${mm}-${dd}`;

    // if (list_verif.length > 0) {
    //     return Swal.fire({
    //         text: `Data dengan warna ${data.color} dan ukuran ${data.kode_ukuran} sudah ada.`,
    //         icon: 'error',
    //         showConfirmButton: false,
    //         timer: 2000
    //     });
    // }
    data.amount = (parseFloat(data.qty) || 0) * (parseFloat(data.harga) || 0);
    // dtListProduksi.addRow(data);
    addItem(data, 'click');
    modalRefpo.modal("hide");

    setTimeout(() => {
        isRowClicked = false;
    }, 500); // 0.5 detik misalnya
})


if (dataSO.length > 0) {
    setTimeout(() => {
        try {
            let isdata = JSON.parse(dataSO);
            dtList.setData(isdata);
            dtListProduksi.setData(isdata);
            setTimeout(() => {
                if (typeof dtListProduksi !== 'undefined' && dtListProduksi) {
                    dtListProduksi.redraw(true);
                }
            }, 300);
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
}

// inpLotNo.keyup(function (e){
//     let lotNo = inpLotNo.val()
//     if(dtListDetail.getData().some(x => x.lot_no == inpLotNo.val() && inpEdit.val() != inpLotNo.val())){
//         e.target.value = ""
//         return Swal.fire({
//             text: `Lot No ${lotNo} sudah ada`,
//             icon: 'error',
//             showConfirmButton: false,
//             timer: 2000
//         });
//     } 

//     // checkLotNo(e.target.value)
// })


inpPrice.on("input", function (e) {
    e.target.value = formatRupiah(e.target.value)
})

function formatRupiah(value) {
    value = value.replace(/[^\d]/g, '').toString();
    // Pisahkan angka menjadi ribuan
    let split = value.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/g);

    // Tambahkan titik jika ada ribuan
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    // Gabungkan dengan bagian desimal, jika ada
    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

    return rupiah ? 'Rp ' + rupiah : '';
}

inpPrice.keyup(function (e) {

    if (inpBarang.val().length === 0) {
        e.target.value = ""
        return Swal.fire({
            text: `Barang harus dipilih dahulu.`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    // if(!regex.test(e.target.value)){
    //     e.target.value = ""
    //     return Swal.fire({
    //         text: "Price harus berupa angka",
    //         icon: 'error',
    //         showConfirmButton: false,
    //         timer: 2000
    //     });
    // }

})


inpQtyItem.keyup(function (e) {

    if (inpBarang.val().length === 0) {
        e.target.value = ""
        return Swal.fire({
            text: `Barang harus dipilih dahulu.`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (!regex.test(e.target.value)) {
        e.target.value = ""
        return Swal.fire({
            text: "Quantity harus berupa angka",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
})

spanKonsumen.click(function () {
    setTimeout(() => {
        dtListKonsumen.redraw(true)
    }, 500);
    $("#modal-konsumen").modal("show");
    dtListKonsumen.deselectRow();
});
spanBarang.click(function () {
    setTimeout(() => {
        dtListBarang.redraw(true)
    }, 500);
    $("#modal-barang").modal("show");
    dtListBarang.deselectRow();
});
divRefProduk.hide();
selectKategori.on("change", function (e) {
    const nilai = e.target.value;

    divNamaKonsumen.addClass("d-none");
    divDetail.show();
    divRefProduk.hide();
    btnAdd.show();
    if (nilai == 3) {
        divNamaKonsumen.removeClass("d-none");
    } else if (nilai == 9) {
        divNamaKonsumen.addClass("d-none");
    } else if (nilai == 12 || nilai == 1 || nilai == 15) {
        divDetail.hide();
        divRefProduk.show();
        btnAdd.hide();
        if (typeof dtListDetail !== 'undefined' && dtListDetail) {
            dtListDetail.setData([]);
        }
        setTimeout(() => {
            if (typeof dtListProduksi !== 'undefined' && dtListProduksi) {
                dtListProduksi.redraw(true);
            }
        }, 200);
    }

    dtListSO.setData();
});


function submitData(status, message) {

    if (selectKategori.val() == null) {
        return Swal.fire({
            text: "Tipe harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (selectGudang.val() == null) {
        return Swal.fire({
            text: "Warehouse harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if (selectPerusahaan.val() == null) {
        return Swal.fire({
            text: "Perusahaan harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if (inpIdKonsumen.val().length == 0 && selectKategori.val() == 3) {
        return Swal.fire({
            text: "Buyer harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (inpTglReceive.val().length == 0) {
        return Swal.fire({
            text: "Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    const tglHeaderFormatted = formatLocaleDate(inpTglReceive.val());
    const nowCheck = new Date();
    const todayStr = `${nowCheck.getFullYear()}-${String(nowCheck.getMonth() + 1).padStart(2, '0')}-${String(nowCheck.getDate()).padStart(2, '0')}`;
    if (tglHeaderFormatted && tglHeaderFormatted > todayStr) {
        return Swal.fire({
            text: "Tanggal transaksi tidak boleh melebihi hari ini!",
            icon: 'warning',
            confirmButtonColor: '#3085d6'
        });
    }




    const hasDetail = (typeof dtListDetail !== 'undefined' && dtListDetail && dtListDetail.getData().length > 0);
    const hasProduksi = (typeof dtListProduksi !== 'undefined' && dtListProduksi && dtListProduksi.getData().length > 0);

    if (!hasDetail && !hasProduksi) {
        if (selectKategori.val() == 12 || selectKategori.val() == 1 || selectKategori.val() == 15) {
            return Swal.fire({
                text: "Data barang / produksi tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            return Swal.fire({
                text: "Data detail tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    }

    // Validasi Qty Terima tidak boleh melebihi Sisa Qty Transfer saat Approve (status == 1) untuk produksi
    if (hasProduksi && status === 1) {
        let dataProd = dtListProduksi.getData();
        let roleId = $('#role_id').val();
        if (selectPerusahaan.val() != 2 && roleId != 5) {
            for (let i = 0; i < dataProd.length; i++) {
                let r = dataProd[i];
                let valQty = parseFloat(r.qty) || 0;
                let valKirim = parseFloat(r.qty_kirim) || 0;
                if (valQty > valKirim) {
                    return Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengapprove!',
                        text: `Terdapat item (${r.kode_sales_order || ''} - ${r.style || ''} - ${r.color || ''}) dengan Qty Terima (${valQty}) yang melebihi Sisa Qty Transfer (${valKirim})! Harap sesuaikan Qty atau simpan sebagai Draft.`,
                    });
                }
            }
        }
    }

    Swal.fire({
        title: `Apakah anda ingin ${message} data Barang Masuk?`,
        icon: 'question',
        confirmButtonText: 'Simpan',
        confirmButtonColor: '#198754',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#6C757D'
    }).then((result) => {
        if (result.isConfirmed) {
            simpanData(status)
        }
    })
}


btnSimpan.on("click", function (e) {
    e.preventDefault()
    submitData(0, "menyimpan draft")
})

btnApprove.on("click", function (e) {
    e.preventDefault()
    submitData(1, "mengapprove")
})

// TAMBAHAN (2026-08-09): "Batalkan Approve" - membalik semua efek approve
// (stok trans_lots/trans_barang, dan utk Tipe Penerimaan Hasil Produksi/
// Sample-Perbaikan juga trans_barang_masuk_produksi/trans_produksi_operator/
// qty_prod WO) & kembalikan dokumen ke status Draft. Lihat
// BarangMasukModel::cancelApprove().
btnCancelApprove.on("click", function (e) {
    e.preventDefault()

    Swal.fire({
        title: 'Batalkan Approve dokumen ini?',
        text: 'Semua efek approve (perubahan stok, dan kalau Tipe Penerimaan Hasil Produksi/Sample juga data produksi & qty_kirim terkait) akan dibalik, dan dokumen kembali ke status Draft.',
        icon: 'warning',
        confirmButtonText: 'Ya, batalkan approve',
        confirmButtonColor: '#dc3545',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#6C757D'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $(".preloader").css("opacity", "0.7").show();
        $.ajax({
            type: 'POST',
            url: '/trans/incoming-goods/cancel-approve',
            data: {
                id: inpIdHeader.val(),
            },
            dataType: "json",
            success: function (response) {
                $(".preloader").hide().css("opacity", "1");
                if (response.status == true) {
                    Swal.fire({
                        text: response.message,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    Swal.fire({
                        text: response.message,
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 2500
                    });
                }
            },
            error: function (xhr) {
                $(".preloader").hide().css("opacity", "1");
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Gagal membatalkan approve.";
                Swal.fire({
                    text: msg,
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2500
                });
            }
        });
    })
})


btnAdd.click(function () {
    openModalDetail()
})

btnView.click(function () {

    const openTransferModal = () => {
        if (typeof dtListSO !== 'undefined' && dtListSO) {
            dtListSO.setData();
        }
        setTimeout(() => {
            dtListSO.redraw(true);
        }, 500);
        $("#modal-so").modal("show");
        dtListSO.deselectRow();
    };

    const dtProduksi = dtListProduksi.getData().length;
    if (dtProduksi > 0) {
        Swal.fire({
            title: "Apakah Anda yakin ingin mengubah Referensi transaksi Transfer?",
            text: "Data produksi yang sudah ada akan dihapus.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, ubah",
            cancelButtonText: "Batal",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6C757D"
        }).then((result) => {
            if (result.isConfirmed) {
                dtListProduksi.setData([]);
                dtList.setData([]);
                openTransferModal();
            } else {
                return false;
            }
        });
    } else {
        openTransferModal();
    }
});

function checkLotNo(value) {
    $.ajax({
        url: `/purchasing/receive-item/check-lot?id_barang=${inpIdBarang.val()}&lot_no=${value}`,
        type: 'GET',
        dataType: 'json',
        success: function (data) {

            if (data.status) {
                value = ''
                return Swal.fire({
                    text: data.message,
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
            }

        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

function openModalDetail(row = null) {
    if (row) {
        let data = row.getData()
        inpBarang.val(data.nama_barang)
        inpIdBarang.val(data.id_barang)
        inpUnit.val(data.nama_unit)
        inpQtyItem.val(data.qty)
        inpPrice.val(formatRupiah(data.price.toString()))
        inpLotNo.val(data.lot_no)
        inpEdit.val(data.lot_no)
        inpPack.val(data.pack_id).trigger("change")
    } else {
        inpBarang.val("")
        inpIdBarang.val("")
        inpUnit.val("")
        inpQtyItem.val("")
        inpPrice.val("")
        inpLotNo.val("")
        inpEdit.val("")
        inpPack.val("").trigger("change")
    }
    modalDet.modal("show")

    btnSimpanDetail.off("click").on("click", function () {
        let price = parseFloat(inpPrice.val().replace(/[^0-9.,]/g, '').replace(/\./g, '').replace(',', '.'));
        if (inpBarang.val().length == 0) {
            return Swal.fire({
                text: "Barang harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (inpQtyItem.val().length == 0 || inpQtyItem.val() <= 0) {
            return Swal.fire({
                text: "Quantity tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }


        if (!regex.test(inpQtyItem.val())) {
            return Swal.fire({
                text: "Quantity harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (!regex.test(price)) {
            return Swal.fire({
                text: "Price harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (selectGudang.val() == null) {
            return Swal.fire({
                text: "Warehouse harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        if (selectPerusahaan.val() == null) {
            return Swal.fire({
                text: "Perusahaan harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (row) {
            row.update({
                id: inpIdDetail.val(),
                nama_barang: inpBarang.val(),
                kode_barang: inpKodeBarang.val(),
                id_barang: inpIdBarang.val(),
                qty: inpQtyItem.val(),
                price: price,
                lot_no: inpLotNo.val(),
                nama_unit: inpUnit.val(),
                pack_id: inpPack.val(),
                pack_name: inpPack.find('option:selected').text(),
            });
        } else {

            dtListDetail.addRow({
                id: null,
                nama_barang: inpBarang.val(),
                kode_barang: inpKodeBarang.val(),
                id_barang: inpIdBarang.val(),
                price: price,
                qty: inpQtyItem.val(),
                lot_no: inpLotNo.val(),
                nama_unit: inpUnit.val(),
                pack_id: inpPack.val(),
                pack_name: inpPack.find('option:selected').text(),
            });
        }
        modalDet.modal("hide")

    })

}


const inpJamMesin = $("#jam_mesin");
const inpNomorMesin = $("#nomor_mesin");
const inpNilaiMesin = $("#nilai_mesin");

function simpanData(status) {
    $(".preloader").css("opacity", "0.7").show();

    $.ajax({
        type: 'POST',
        url: '/trans/incoming-goods/save',
        data: {
            id: inpIdHeader.val(),
            id_gudang: selectGudang.val(),
            id_perusahaan: selectPerusahaan.val(),
            tanggal: formatLocaleDate(inpTglReceive.val()),
            id_kategori: selectKategori.val(),
            nama: inpKonsumen.val(),
            id_buyer: inpIdKonsumen.val(),
            no_ref_trf: inpNoRefTrf.val(),
            data: dtListDetail.getData(),
            dataProduksi: dtListProduksi.getData(),
            keterangan: inpKeterangan.val(),
            status: status,
            // id_proses: inpIdproses.val(),
            // id_cmt: inpIdCmt.val(),
            id_proses: selectProses.val(),
            id_cmt: selectOperator.val(),
            nomor_mesin: inpNomorMesin.val(),
            jam_mesin: inpJamMesin.val(),
            nilai_mesin: inpNilaiMesin.val(),
        },
        dataType: "json",
        beforeSend: function () {
            // Cegah submit ganda (klik dobel/network retry) selama request masih
            // berjalan - salah satu sumber potensi data dobel di trans_produksi_operator
            // selain bug delete-before-insert yang sudah diperbaiki di backend.
            btnSimpan.prop("disabled", true);
            btnApprove.prop("disabled", true);
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

            if (response.status == true) {
                Swal.fire({
                    text: response.message,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000
                });
                Swal.close();
                window.location.href = 'trans/incoming-goods'

            } else {
                Swal.fire({
                    text: response.message,
                    icon: 'error',
                    showConfirmButton: false,
                    // timer: 2000
                });
            }
            btnSimpan.prop("disabled", false);
            btnApprove.prop("disabled", false);
            $(".preloader").hide().css("opacity", "1");
        },
        error: function (e) {
            let msg = e.responseJSON.message;
            Swal.close();

            Swal.fire({
                text: msg,
                icon: 'error',
                showConfirmButton: false,
                // timer: 2000
            });
            btnSimpan.prop("disabled", false);
            btnApprove.prop("disabled", false);
            $(".preloader").hide().css("opacity", "1");
        },
    });

}

// fungsi barcode dan autocomplete 
$("#text_barcode").autocomplete({
    source: function (request, response) {

        // dtListProduksiRef.setData(refData);
        if (refData == null || refData.length == 0) {
            Swal.fire({
                text: "Data Tidak Ditemukan!",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
        }

        else if (refData.length > 0) {
            const kataKunci = request.term

            const data = refData;
            const results = [];
            const lowerKataKunci = kataKunci.toLowerCase();

            data.forEach(item => {
                const colorStr = item.color ? item.color : '-';
                const labelx = item.kode_sales_order + " - (" + colorStr + ") " + (item.kode_ukuran || '');
                for (const key in item) {
                    if (
                        item.hasOwnProperty(key) &&
                        item[key] != null &&
                        item[key].toString().toLowerCase().includes(lowerKataKunci)
                    ) {
                        results.push({
                            label: labelx,
                            value: item.kode_sales_order + " - " + colorStr + " - " + (item.kode_ukuran || ''),
                            data: item
                        });
                        break; // stop after first match in this item
                    }
                }
            });

            // Batasi hasil autocomplete maksimal 30 entri agar tidak lagging dan mudah dipilih
            response(results.slice(0, 30));
        }
        else {
            Swal.fire({
                text: "Silahkan pilih referensi transfer terlebih dahulu atau pilih PROSES dan CMT.",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
        }

    },
    minLength: 3,
    select: function (event, ui) {
        addItem(ui.item.data, 'search');
    },
    open: function () {
        $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
    },
    close: function () {
        $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
        $("#text_barcode").val("");
    }
});

$("#select_perusahaan").on("change", function () {
    let id_perusahaan = $(this).val();

    if (id_perusahaan == 2) {
        dtListProduksi.hideColumn("qty_kirim");
    } else {
        dtListProduksi.showColumn("qty_kirim");
    }

    const isEdit = (inpIdHeader.val() && inpIdHeader.val().length > 0);
    const hasTrf = (inpNoRefTrf.val() && inpNoRefTrf.val().length > 0);

    // Jika form Baru tanpa No. Transfer, reset operator
    if (!isEdit && !hasTrf) {
        $("#select_operator").val(null).trigger("change");
    }

    // Jika memiliki No. Transfer terpilih, jangan timpa operator dari ajax perusahaan
    if (hasTrf) {
        $("#select_operator").prop("disabled", true);
        return;
    }

    $(".preloader").css("opacity", "0.7").show();

    $.ajax({
        url: baseUrl + "/master-data/operator/get_operator",
        type: "POST",
        data: {
            id_perusahaan: id_perusahaan
        },
        beforeSend: function () {
            $("#select_operator").prop("disabled", true);
        },
        success: function (res) {
            let selectedId = $("#select_operator").data("selected") || $("#select_operator").val() || "";
            let options = '<option value="">-- Pilih CMT --</option>';

            if (res.status && res.data.length > 0) {
                $.each(res.data, function (i, item) {
                    let isSelected = (item.id == selectedId) ? "selected" : "";
                    options += `<option value="${item.id}" ${isSelected}>${item.nama_operator}</option>`;
                });
            }

            $("#select_operator").html(options);
            if (selectedId) {
                $("#select_operator").val(selectedId).trigger("change.select2");
            } else {
                $("#select_operator").trigger("change");
            }
            $(".preloader").hide().css("opacity", "1");
        },
        error: function () {
            alert("Gagal memuat data operator.");
            $(".preloader").hide().css("opacity", "1");
        },
        complete: function () {
            if (hasTrf) {
                $("#select_operator").prop("disabled", true);
            } else {
                $("#select_operator").prop("disabled", false);
            }
        }
    });
});


// Khusus perusahaan "Citra Knitt": nama perusahaan dicek dari teks dropdown atau gudang
// (bukan hardcode ID) supaya tidak tergantung urutan/ID ref_perusahaan di DB.
// Untuk perusahaan lain (mis. PT Citra Jaya Knitting) validasi harga TIDAK berubah.
function isPerusahaanCitraKnitt() {
    const text = selectPerusahaan.find('option:selected').text() || '';
    const val = selectPerusahaan.val();
    const gudangText = selectGudang.find('option:selected').text() || '';
    const gudangPrsh = selectGudang.find('option:selected').attr('data-perusahaan');
    return text.trim().toLowerCase() === 'citra knitt' || val == 2 || gudangPrsh == 2 || gudangText.toUpperCase().includes('CITRA KNITT');
}

selectGudang.on('change', function () {
    const whPrsh = $(this).find('option:selected').attr('data-perusahaan');
    if (whPrsh && selectPerusahaan.length && selectPerusahaan.val() != whPrsh) {
        selectPerusahaan.val(whPrsh);
        if (typeof dtListSO !== 'undefined' && dtListSO) {
            dtListSO.setData();
        }
        if (!inpNoRefTrf.val() && selectOperator.val() && selectProses.val()) {
            loadDataSo();
        }
    }
});

function isCurrentProcessRajut() {
    let opt = selectProses.find('option:selected');
    let isRajutAttr = opt.attr('data-is-rajut');
    if (isRajutAttr === '1' || isRajutAttr === 1) return true;
    let txt = opt.text() || '';
    return txt.toUpperCase().includes('RAJUT');
}

function finalizeAddItem(data, isScan) {
    if (isScan) {
        data.total_scanned = 1;
    }
    if (!data.print_type) {
        data.print_type = 1;
    }
    data.qty_qc = data.qty || 0;
    data.berat_qc = data.berat || 0;
    data.amount = (parseFloat(data.qty) || 0) * (parseFloat(data.harga) || 0);

    console.log("data yang ditambahkan", data);

    dtListProduksi.addRow(data).then(function (row) {
        if (typeof dtListProduksi !== 'undefined' && dtListProduksi.deselectRow) {
            dtListProduksi.deselectRow();
        }
        row.select();
        activeTargetRow = row;
        // Tidak menyalin berat dari pembacaan lama - berat hanya terisi saat barang diletakkan di timbangan secara realtime
    });
}

function addItem(data, typeAction = null, isScan = false) {
    let produksi_data = dtListProduksi.getData();
    let index = -1;

    if (produksi_data.some(x => x.kode_sales_order == data.kode_sales_order && x.color == data.color && x.kode_ukuran == data.kode_ukuran)) {
        index = produksi_data.findIndex(x =>

            x.kode_sales_order == data.kode_sales_order &&
            (x.color ? x.color.split('~')[0] : '') == (data.color ? data.color.split('~')[0] : '') && (x.color ? x.color.split('~')[1] : '') == (data.color ? data.color.split('~')[1] : '') && x.kode_ukuran == data.kode_ukuran
        );
    }
    if (index !== -1 && selectPerusahaan.val() != 2) {
        if (isScan) {
            produksi_data[index].total_scanned = parseFloat(produksi_data[index].total_scanned || 0) + 1;
            // produksi_data[index].keterangan = (parseFloat(produksi_data[index].total_scanned || 0)).toString() + ' Ikat';
        }
        let oldQty = parseFloat(produksi_data[index].qty || 0);
        let addedQty = parseFloat(data.qty || 0);
        let newQty = oldQty + addedQty;

        produksi_data[index].qty = newQty;
        produksi_data[index].qty_qc = newQty;
        produksi_data[index].amount = newQty * parseFloat(produksi_data[index].harga || 0);
        dtListProduksi.setData(produksi_data);
        $("#text_barcode").val("");
    } else {
        data = structuredClone(data);
        const matchingItems = produksi_data.filter(x =>
            x.kode_sales_order == data.kode_sales_order &&
            x.kode_ukuran == data.kode_ukuran &&
            (x.color ? x.color.split('~')[0] : '') == (data.color ? data.color.split('~')[0] : '')
        );

        let totalQty = 0;
        let totalAmount = 0;

        if (typeAction == 'scan') {
            matchingItems.forEach(item => {
                totalQty += parseFloat(item.qty) || 0;
                totalAmount += parseFloat(item.amount) || 0;
            });
        }
        else if (typeAction == 'search') {
            totalQty = parseFloat(data.qty_kirim) || 0;
            totalAmount = parseFloat(data.amount) || 0;
            data.qty = totalQty || 0;
        }
        else {
            totalQty = 1;
            data.qty = 1;
        }

        const hargaRata = totalQty > 0 ? Math.floor(totalAmount / totalQty) : 0;

        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        data.tgl_transaksi = `${yyyy}-${mm}-${dd}`;

        const hargaFix = (parseFloat(data.harga) || 0) > 0 ? parseFloat(data.harga) : hargaRata;

        $("#text_barcode").val("");

        const idProses = selectProses.val();
        const idCmt = selectOperator.val();

        // Validasi otomatis harga khusus perusahaan "Citra Knitt": cek transaksi Barang
        // Masuk terakhir dengan kombinasi Proses + CMT (header) + Style (detail) yang sama.
        if (isPerusahaanCitraKnitt() && idProses && idCmt && data.style) {
            $.ajax({
                url: baseUrl + '/trans/incoming-goods/last-price',
                type: 'GET',
                dataType: 'json',
                data: {
                    id_proses: idProses,
                    id_cmt: idCmt,
                    style: data.style,
                    id_perusahaan: selectPerusahaan.val(),
                },
                success: function (res) {
                    const hargaTerakhir = (res && res.status) ? (parseFloat(res.harga) || 0) : 0;
                    data.harga = hargaTerakhir > 0 ? hargaTerakhir : hargaFix;
                    data.amount = (parseFloat(data.qty) || 0) * data.harga;
                    finalizeAddItem(data, isScan);
                },
                error: function () {
                    data.harga = hargaFix;
                    data.amount = (parseFloat(data.qty) || 0) * hargaFix;
                    finalizeAddItem(data, isScan);
                }
            });
        } else {
            data.harga = hargaFix;
            data.amount = (parseFloat(data.qty) || 0) * hargaFix;
            finalizeAddItem(data, isScan);
        }
    }
}



$("#text_barcode").on("keypress", function (e) {
    let key = e.which;
    if (key == 13) {
        // $.ajax({
        // 	url: "trans/item-transfer/src_produk",
        // 	dataType: "json",
        // 	data: {
        // 	  kata_kunci   : $( "#text_barcode" ).val(),
        // 	},
        // 	type : 'post',
        // 	success: function( es ) {
        //         // console.log(es)
        // 	  if(es.status){
        // 		// response(data.slc);
        // 		if(es.data.length > 0){
        // 			addItem(es.data[0]);
        // 		}else{
        // 			alert("Produk tidak ditemukan !");
        // 		}
        // 	  }else{
        // 		  console.log(es.msg);
        // 	  }
        // 	}
        //   });


        const kataKunci = $("#text_barcode").val();
        if (!kataKunci || kataKunci.trim() === '') {
            return;
        }

        let isRetryingSoFetch = false;

        function showNotFoundError(arrKunci) {
            const trfInfo = inpNoRefTrf.val() ? ` pada No. Transfer ${inpNoRefTrf.val()}` : '';
            const soInfo = (arrKunci && arrKunci[0]) ? ` (${arrKunci[0]})` : '';
            Swal.fire({
                title: "Produk Tidak Ditemukan!",
                text: `Barang${soInfo} tidak terdaftar${trfInfo}. Mohon periksa kembali fisik barang atau surat transfernya.`,
                icon: 'error',
                confirmButtonText: 'Tutup'
            });
        }

        function showEmptyRefError() {
            const trfMsg = inpNoRefTrf.val()
                ? `Tidak ada data barang pada No. Transfer ${inpNoRefTrf.val()} (Pastikan transfer sudah di-Approve).`
                : "Silahkan pilih referensi transfer terlebih dahulu atau pilih PROSES dan CMT.";
            Swal.fire({
                title: "Data Tidak Ditemukan",
                text: trfMsg,
                icon: 'warning',
                confirmButtonText: 'Tutup'
            });
        }

        function fetchSoCitraKnittOnDemand(kodeSO, cb) {
            $(".preloader").css("opacity", "0.7").show();
            const params = new URLSearchParams({
                id_perusahaan: selectPerusahaan.val() || 2,
                id_proses: selectProses.val() || '',
                id_cmt: selectOperator.val() || '',
                kode_so: kodeSO
            });
            $.ajax({
                url: `/trans/item-transfer/data-so?${params.toString()}`,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    $(".preloader").hide().css("opacity", "1");
                    if (res && res.status && res.dataSO && res.dataSO.length > 0) {
                        if (!refData) refData = [];
                        if (!xrefData) xrefData = [];
                        res.dataSO.forEach(newItem => {
                            if (!refData.some(x => x.kode_sales_order === newItem.kode_sales_order && x.kode_ukuran === newItem.kode_ukuran && x.color === newItem.color)) {
                                refData.push(newItem);
                                xrefData.push(newItem);
                            }
                        });
                        if (typeof cb === 'function') cb(true);
                    } else {
                        if (typeof cb === 'function') cb(false);
                    }
                },
                error: function () {
                    $(".preloader").hide().css("opacity", "1");
                    if (typeof cb === 'function') cb(false);
                }
            });
        }

        function executeBarcodeMatching() {
            const arrKunci = kataKunci.split(";");

            if (refData && refData.length > 0) {
                let hasil = refData.map(item => ({ ...item }));

                const normStr = str => str ? str.toString().trim().toLowerCase() : '';
                const normSize = str => str ? str.toString().toLowerCase().replace(/[^a-z0-9]/g, '') : '';
                const isTimestampToken = str => {
                    if (!str) return false;
                    const s = str.toString().trim();
                    return /\d{4}\s+\d{2}\s+\d{2}/.test(s) ||
                           /\d{4}-\d{2}-\d{2}/.test(s) ||
                           /\d{2}-\d{2}-\d{2}-\d+/.test(s) ||
                           /\(\d+\)$/.test(s);
                };
                const cleanColor = c => (!c || c.trim() === '' || c.trim() === '-' || isTimestampToken(c)) ? null : c.replace(/\s+/g, ' ').trim().toUpperCase();

                // 1. Filter No SO / Reference / Style
                if (arrKunci[0] != undefined && arrKunci[0] !== '') {
                    const targetSO = normStr(arrKunci[0]);
                    hasil = hasil.filter(item => normStr(item.kode_sales_order) === targetSO || normStr(item.kode_transaksi) === targetSO || normStr(item.style) === targetSO);
                }

                // 2. Filter Ukuran (cocokkan key_ukuran ATAU kode_ukuran setelah dinormalisasi)
                if (arrKunci[1] != undefined && arrKunci[1] !== '') {
                    const targetUkuran = normSize(arrKunci[1]);
                    hasil = hasil.filter(item => normSize(item.key_ukuran) === targetUkuran || normSize(item.kode_ukuran) === targetUkuran);
                }

                // 3. Filter Warna
                if (arrKunci[2] != undefined && arrKunci[2] !== '') {
                    let inputColor1, inputColor2, inputColor3;
                    if (arrKunci[2].includes('~')) {
                        const splitC = arrKunci[2].split('~').map(c => cleanColor(c)).filter(Boolean);
                        inputColor1 = splitC[0] || null;
                        inputColor2 = splitC[1] || null;
                        inputColor3 = splitC[2] || null;
                    } else {
                        inputColor1 = cleanColor(arrKunci[2]);
                        inputColor2 = (arrKunci[4] && !isTimestampToken(arrKunci[4])) ? cleanColor(arrKunci[4]) : null;
                        inputColor3 = (arrKunci[5] && !isTimestampToken(arrKunci[5])) ? cleanColor(arrKunci[5]) : null;
                    }

                    hasil = hasil.filter(item => {
                        if (item.color) {
                            const colorParts = item.color.toString().split('~').map(c => cleanColor(c)).filter(Boolean);
                            const warna1 = colorParts[0] || null;
                            const warna2 = colorParts[1] || null;
                            const warna3 = colorParts[2] || null;

                            // 3 warna pada barcode
                            if (inputColor3) {
                                return warna1 === inputColor1 && warna2 === inputColor2 && warna3 === inputColor3;
                            }
                            // 2 warna pada barcode
                            else if (inputColor2) {
                                return warna1 === inputColor1 && warna2 === inputColor2;
                            }
                            // 1 warna pada barcode
                            else {
                                return warna1 === inputColor1;
                            }
                        }
                        // Fallback: jika item di master/SO tidak memiliki warna, jangan tolak jika SO & Ukuran cocok
                        return true;
                    });
                } else {
                    return Swal.fire({
                        text: "Scan Kosong!",
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }

                if (hasil.length > 0) {
                    // qty tetap di [3], tgl_scan ambil index terakhir atau token timestamp
                    hasil[0].qty = arrKunci[3] ? parseFloat(arrKunci[3]) : 1;
                    let tglScanVal = null;
                    for (let k = arrKunci.length - 1; k >= 0; k--) {
                        if (isTimestampToken(arrKunci[k])) {
                            tglScanVal = arrKunci[k].trim();
                            break;
                        }
                    }
                    if (!tglScanVal && arrKunci.length > 4) {
                        tglScanVal = arrKunci[arrKunci.length - 1];
                    }
                    hasil[0].tgl_scan = tglScanVal;
                    if (tglScanVal) {
                        let matchKurung = tglScanVal.match(/\((\d+)\)\s*$/);
                        if (matchKurung && matchKurung[1]) {
                            hasil[0].print_type = parseInt(matchKurung[1]);
                        } else {
                            hasil[0].print_type = 1;
                        }
                    } else {
                        hasil[0].print_type = 1;
                    }
                    addItem(hasil[0], 'scan', true);
                    $("#text_barcode").val("");
                    refData = xrefData;
                } else {
                    if (isPerusahaanCitraKnitt() && !inpNoRefTrf.val() && arrKunci[0] && !isRetryingSoFetch) {
                        fetchSoCitraKnittOnDemand(arrKunci[0], function (found) {
                            if (found) {
                                isRetryingSoFetch = true;
                                executeBarcodeMatching();
                                isRetryingSoFetch = false;
                            } else {
                                showNotFoundError(arrKunci);
                            }
                        });
                        return;
                    }
                    showNotFoundError(arrKunci);
                }
            } else {
                if (isPerusahaanCitraKnitt() && !inpNoRefTrf.val() && arrKunci[0] && !isRetryingSoFetch) {
                    fetchSoCitraKnittOnDemand(arrKunci[0], function (found) {
                        if (found) {
                            isRetryingSoFetch = true;
                            executeBarcodeMatching();
                            isRetryingSoFetch = false;
                        } else {
                            showEmptyRefError();
                        }
                    });
                    return;
                }
                showEmptyRefError();
            }
        }

        if (!refData || refData.length === 0) {
            if (inpNoRefTrf.val() || (selectProses.val() && selectOperator.val())) {
                loadDataSo(function () {
                    executeBarcodeMatching();
                });
                return;
            } else if (isPerusahaanCitraKnitt() && !inpNoRefTrf.val() && kataKunci.split(";")[0]) {
                fetchSoCitraKnittOnDemand(kataKunci.split(";")[0], function () {
                    executeBarcodeMatching();
                });
                return;
            }
        }

        executeBarcodeMatching();
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const idPerusahaan = $(selectPerusahaan).val();
    if (idPerusahaan == 2) {
        dtListProduksi.hideColumn("qty_kirim");
    }
});

// ==========================================
// KONEKSI TIMBANGAN DIGITAL SERIAL (COM PORT) VIA WEB SERIAL API
// MODE AUTOMATIC REAL-TIME (AUTO READ ON FOCUS / ROW CLICK)
// ==========================================
let serialScalePort = null;
let serialScaleReader = null;
let latestScaleWeight = 0;
let isScaleAutoReading = false;
let activeTargetRow = null;

// Parser khusus Indikator Timbangan Yaohua YH-T3 / TM-G
function parseYaohuaYHT3(rawBuffer) {
    if (!rawBuffer) return 0;
    const equalMatches = rawBuffer.match(/=[0-9\.\s\-+]+=/g);
    if (equalMatches && equalMatches.length > 0) {
        const lastMatch = equalMatches[equalMatches.length - 1].replace(/=/g, '').trim();
        const reversedStr = lastMatch.split('').reverse().join('');
        const numDirect = parseFloat(lastMatch);
        const numReversed = parseFloat(reversedStr);

        if (!isNaN(numDirect) && numDirect > 0 && numDirect < 2000) return numDirect;
        if (!isNaN(numReversed) && numReversed > 0 && numReversed < 2000) return numReversed;
    }
    const matches = rawBuffer.match(/[-+]?\d*\.?\d+/g);
    if (matches && matches.length > 0) {
        for (let i = matches.length - 1; i >= 0; i--) {
            let val = parseFloat(matches[i]);
            if (!isNaN(val) && val > 0 && val < 5000) {
                return val;
            }
        }
    }
    return 0;
}

async function startContinuousScaleStream() {
    if (!("serial" in navigator)) {
        let isHttp = window.location.protocol === 'http:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
        let msgHtml = 'Fitur Timbangan Digital (Web Serial API) memerlukan browser berbasis Chromium (Google Chrome / Edge).';
        if (isHttp) {
            msgHtml = `
                <div class="text-start">
                    <p class="text-danger fw-bold mb-2">Akses Web HTTP Terdeteksi!</p>
                    <p class="mb-2">Google Chrome & MS Edge mewajibkan koneksi <b>HTTPS</b> atau izin khusus untuk mengakses Timbangan Digital COM Port.</p>
                    <hr class="my-2">
                    <p class="fw-bold mb-1">Cara Aktifkan di Chrome (Cukup 1x saja):</p>
                    <ol class="ps-3 mb-0 small">
                        <li>Buka tab baru di Chrome, ketik: <br><code class="user-select-all text-dark bg-light px-1">chrome://flags/#unsafely-treat-insecure-origin-as-secure</code></li>
                        <li>Ubah pilihan dari <i>Disabled/Default</i> menjadi <b>Enabled</b>.</li>
                        <li>Ketik alamat web di kotaknya: <br><code class="user-select-all text-primary bg-light px-1">${window.location.origin}</code></li>
                        <li>Klik tombol <b>Relaunch</b> di pojok kanan bawah Chrome.</li>
                    </ol>
                </div>
            `;
        }
        Swal.fire({
            icon: 'warning',
            title: 'Izin Akses Timbangan',
            html: msgHtml,
            confirmButtonText: 'Mengerti'
        });
        return false;
    }

    try {
        if (!serialScalePort) {
            serialScalePort = await navigator.serial.requestPort();
            await serialScalePort.open({
                baudRate: 9600,
                dataBits: 8,
                stopBits: 1,
                parity: "none"
            });
        }

        isScaleAutoReading = true;
        updateScaleButtonStatus(true);

        const textDecoder = new TextDecoderStream();
        const readableStreamClosed = serialScalePort.readable.pipeTo(textDecoder.writable);
        serialScaleReader = textDecoder.readable.getReader();

        let rawBuffer = "";

        while (isScaleAutoReading) {
            const { value, done } = await serialScaleReader.read();
            if (done) break;
            if (value) {
                rawBuffer += value;
                if (rawBuffer.length > 200) rawBuffer = rawBuffer.substring(rawBuffer.length - 100);

                const weightVal = parseYaohuaYHT3(rawBuffer);
                latestScaleWeight = weightVal;

                // Realtime update: langsung isi target baris/input aktif saat ada beban live di timbangan
                if (weightVal > 0) {
                    applyAutoWeightToActiveTarget(weightVal);
                }
            }
        }
    } catch (err) {
        console.error("Scale Stream Error:", err);
        isScaleAutoReading = false;
        serialScalePort = null;
        updateScaleButtonStatus(false, err.message);
    }
}

function updateScaleButtonStatus(isActive, errorMsg) {
    const btnScale1 = $('#btn-read-scale');
    const btnScale2 = $('#btn-read-scale-prod');
    const statusText1 = $('#scale-status-text');
    const statusText2 = $('#scale-status-text-prod');

    if (isActive) {
        btnScale1.removeClass("btn-primary btn-warning").addClass("btn-success").html('<i class="fa fa-circle text-white"></i> 🟢 Timbangan REALTIME');
        btnScale2.removeClass("btn-primary btn-warning").addClass("btn-success").html('<i class="fa fa-circle text-white"></i> 🟢 Timbangan REALTIME');
        statusText1.html('<span class="text-success fw-bold"><i class="fa fa-check-circle"></i> Mode Realtime Aktif! Saat barang diletakkan di timbangan, berat Kg langsung terisi realtime.</span>');
        statusText2.html('<span class="text-success fw-bold"><i class="fa fa-check-circle"></i> Mode Realtime Aktif! Saat barang diletakkan di timbangan, berat Kg langsung terisi realtime.</span>');
    } else {
        btnScale1.removeClass("btn-success btn-warning").addClass("btn-primary").html('<i class="fa fa-balance-scale"></i> ⚖️ Timbangan');
        btnScale2.removeClass("btn-success btn-warning").addClass("btn-primary").html('<i class="fa fa-balance-scale"></i> ⚖️ Timbangan');
        if (errorMsg) {
            statusText1.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Gagal: ' + errorMsg + '</span>');
            statusText2.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Gagal: ' + errorMsg + '</span>');
        } else {
            statusText1.html('Mendukung Timbangan Yaohua YH-T3 / TM-G Serial COM Port (Chrome / Edge)');
            statusText2.html('Mendukung Timbangan Yaohua YH-T3 / TM-G Serial COM Port (Chrome / Edge)');
        }
    }
}

function applyAutoWeightToActiveTarget(weightVal) {
    if (!weightVal || weightVal <= 0) return;

    // 1. Jika modal detail item terbuka dan input #qty_item difokuskan / aktif
    if ($('#modal-detail-item').hasClass('show') || $('#qty_item').is(':focus')) {
        $('#qty_item').val(weightVal).trigger('change');
    }

    // 2. Jika baris di Tabel 1 (dtListProduksi) sedang dipilih / aktif dan proses adalah Rajut
    if (typeof dtListProduksi !== 'undefined' && dtListProduksi && isCurrentProcessRajut()) {
        let selectedRows = dtListProduksi.getSelectedRows();
        let targetRow = null;
        if (selectedRows.length > 0) {
            targetRow = selectedRows[0];
        } else if (activeTargetRow) {
            targetRow = activeTargetRow;
        }

        if (targetRow) {
            targetRow.update({
                berat: weightVal,
                berat_qc: weightVal
            });
        }
    }
}

// Handler klik baris/cell pada dtListProduksi untuk menangkap baris target aktif
if (typeof dtListProduksi !== 'undefined' && dtListProduksi) {
    dtListProduksi.on("rowClick", function (e, row) {
        activeTargetRow = row;
        // Hanya set target aktif, tidak menimpa dengan angka lama dari memory
    });

    dtListProduksi.on("cellClick", function (e, cell) {
        let row = cell.getRow();
        activeTargetRow = row;
        // Jika user klik cell berat saat mode timbangan aktif & timbangan sedang membaca beban realtime
        let fieldName = cell.getField();
        if (fieldName === 'berat' && isScaleAutoReading && isCurrentProcessRajut() && latestScaleWeight > 0) {
            row.update({
                berat: latestScaleWeight,
                berat_qc: latestScaleWeight
            });
        }
    });
}

$(document).on('focus', '#qty_item', function () {
    if (isScaleAutoReading && latestScaleWeight > 0) {
        $(this).val(latestScaleWeight).trigger('change');
    }
});

$(document).on('click', '#btn-read-scale, #btn-read-scale-prod', function () {
    if (!isScaleAutoReading) {
        startContinuousScaleStream();
    } else {
        if (latestScaleWeight > 0) {
            applyAutoWeightToActiveTarget(latestScaleWeight);
            Swal.fire({
                icon: 'success',
                title: 'Berat Realtime Terisi!',
                text: `Berat saat ini di timbangan: ${latestScaleWeight} Kg`,
                timer: 1200,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Timbangan Realtime Aktif',
                text: 'Timbangan siap. Letakkan barang di atas timbangan untuk mengisi berat secara realtime.',
                timer: 1800,
                showConfirmButton: false
            });
        }
    }
});