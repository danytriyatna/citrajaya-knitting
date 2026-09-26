
const appUrl = (path) => {
    let base = (typeof baseUrl !== 'undefined' && baseUrl) ? baseUrl : ((typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '/');
    if (!base.endsWith('/')) base += '/';
    return base + path.replace(/^\//, '');
};

let inpIdVendor = $('#id_vendor');
let inpVendor = $('#nama_vendor');
let inpTglReceive = $('#tanggal');
let divNamaVendor = $('#divNamaVendor');
let divWO = $('#divWO');
let divTransfer = $('#divTransfer');
let spanVendor = $('#spanVendor');
let inpBarang = $('#namaBarang');
let inpKeterangan = $('#trans_desc');
let inpIdBarang = $('#idBarang');
let inpKodeBarang = $('#kodeBarang');
let inpQtyItem = $('#qty_item');
let inpQtyExist = $('#qty_exist');
let inpPrice = $('#price');
let inpUnit = $('#unit');
let inpEdit = $('#edit');
let spanBarang = $('#spanBarang');
let selectGudang = $('#select_warehouse');
let selectKategori = $('#select_kategori');
let inpLotNo = $('#lot_no');
let inpIdLot = $('#id_lot');
let inpPackName = $('#pack_name');
let inpPackId = $('#id_lot');
let detailData = $("#data-details").val().replace(/&quot;/ig, '"');
let dataSO = $("#data-so").val().replace(/&quot;/ig, '"');
let btnAdd = $('#btn-add');
let divBtnAdd = $('#divBtnAdd');
let divTotalPemakaian = $('#divTotalPemakaian');
let inpTotalPemakaian = $('#total_pemakaian');
let inpBtmKg = $('#btm_kg');
let btnSimpan = $('#btn-simpan');
let btnApprove = $('#btn-approve');
let btnCancelApprove = $('#btn-cancel-approve');
let inpRole = $('#role_id');
let inpNoRefTrf = $('#no_ref_transfer');
let inpNoRefWO = $('#no_ref_wo');
let btnSimpanDetail = $('#btn-simpan-det');
let btnView = $('#ic_ref_transfer');
let btnViewWO = $('#ic_ref_wo');
let modalDet = $('#modal-detail-item');
let inpStatus = $('#status');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');

if (inpIdHeader.val().length == 0) {
    // Default Warehouse ke "Gudang Utama" utk transaksi baru (sebelumnya dikosongkan).
    let optGudangUtama = selectGudang.find('option').filter(function () {
        return $(this).val() == "1" || $(this).text().trim().toLowerCase() === "gudang utama";
    }).first().val();
    selectGudang.val(optGudangUtama || "1").trigger("change")
    selectKategori.val("").trigger("change")
}

if (selectKategori.val() == 8) {
    divNamaVendor.removeClass("d-none")
}

if (selectKategori.val() == 5) {
    divWO.removeClass("d-none")
    divBtnAdd.addClass("d-none")
    divTotalPemakaian.removeClass("d-none")
}

if (inpStatus.val() == 0) {
    btnAdd.show()
    btnSimpan.show()
    btnApprove.show()
    btnCancelApprove.hide()
} else {
    btnAdd.hide()
    btnSimpan.hide()
    btnApprove.hide()
    // TAMBAHAN (2026-08-09): tombol "Batalkan Approve" hanya muncul kalau
    // dokumen sudah Approved (status == 1), sedang mengedit dokumen yg
    // sudah tersimpan (ada id_header - bukan form transaksi baru), DAN role
    // user termasuk Superadmin (1)/Admin Aplikasi (2)/Admin Keuangan (4) -
    // dicek lagi di sini (bukan cuma atribut "hidden" dari PHP) supaya tidak
    // ke-override oleh .show() di atas kalau role-nya tidak diizinkan.
    const roleId = parseInt(inpRole.val());
    const isAllowedCancelRole = [1, 2, 4].includes(roleId);
    if (inpIdHeader.val().length > 0 && isAllowedCancelRole) {
        btnCancelApprove.show()
    } else {
        btnCancelApprove.hide()
    }
}
// const regex = /^[0-9]+(\.[0-9]+)?$/; // Hanya angka dan desimal
const regex = /^[0-9.,]+$/;



let dtListVendor = new Tabulator("#dt-list-vendor", {
    columns: [

        {
            title: "Nama Vendor", field: "nama", headerSort: false,
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
    ajaxURL: "/master-data/vendor/list",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
    },
    ajaxResponse: function (url, params, response) {
        let pageSize = dtListVendor.getPageSize();
        let pageNo = dtListVendor.getPage();
        let startRow = (pageSize * (pageNo - 1)) + 1;
        let endRow = response.data.length + startRow - 1;
        if (response.data.length === 0) {
            startRow = 0; endRow = 0;
        }
        let recordsFiltered = parseInt(response.recordsFiltered);
        let recordsTotal = parseInt(response.recordsTotal);

        $("#table-footer2 .tabulator-startrow").text(startRow);
        $("#table-footer2 .tabulator-endrow").text(endRow);
        $("#table-footer2 .tabulator-totalrow").text(recordsFiltered);

        let elTotalFilteredRow = $("#table-footer2 .tabulator-totalfilteredrow");
        elTotalFilteredRow.text("");
        if (recordsTotal > recordsFiltered) {
            elTotalFilteredRow.text(" (disaring dari " + recordsTotal
                + " entri keseluruhan)");
        }
        return response;
    },
    footerElement: '<div id="table-footer2" class="pull-left tabulator-info">'
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


let searchThreadVendor = null;
let elSearchVendor = $("#tb-search2");
if (elSearchVendor != null) {
    elSearchVendor.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThreadVendor);
        searchThreadVendor = setTimeout(function () {
            dtListVendor.setFilter("", "like", elSearchVendor.val());
        }, 600);
    });
}

dtListVendor.on("rowClick", function (e, row) {
    var namaVendor = row._row.data.nama.replace(/<[^>]*>/g, '');
    var idVendor = row._row.data.id;
    inpVendor.val(namaVendor)
    inpIdVendor.val(idVendor)
    $("#modal-vendor").modal("hide");
})


let dtListBarang = new Tabulator("#dt-list-barang", {
    columns: [
        {
            title: "ID Barang", field: "id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "ID Lots", field: "lot_id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "ID Packs", field: "pack_id", headerSort: false,
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
            title: "Satuan", field: "nama_satuan", headerSort: false,
            width: "10%"
        },
        {
            title: "Lot No.", field: "lot_no", headerSort: false,
            width: "15%"
        },
        {
            title: "Pack", field: "pack_name", headerSort: false,
            width: "15%"
        },
        {
            title: "Qty", field: "qty", headerSort: false,
            width: "25%"
        },
        {
            title: "Harga Satuan", field: "harga_satuan", headerSort: false,

        },
        {
            title: "Total Harga", field: "total_harga", headerSort: false,

        },
    ],
    locale: 'id',
    // ajaxURL: "/trans/outgoing-goods/list-barang",
    ajaxURL: appUrl("trans/item-transfer/list-barang"),
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    selectableRows: true,
    groupBy: "nama_barang",
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.idGudang = selectGudang.val();
        params.kode_walkorder = inpNoRefWO.val()
        params.type = 'BTK'
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
let elSearchBarang = $("#tb-search3");
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
            text: "Warehouse harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }


    var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    var idBarang = row._row.data.id;
    var idLot = row._row.data.lot_id;
    var qty = row._row.data.qty;
    var harga_satuan = row._row.data.harga_satuan;
    var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    var namaSatuan = row._row.data.nama_satuan.replace(/<[^>]*>/g, '');
    var lotNo = row._row.data.lot_no.replace(/<[^>]*>/g, '');
    // if(dtListDetail.getData().some(x=>x.id_barang == idBarang)){
    //     return Swal.fire({
    //         text: `Barang ${namaBarang} telah dipilih`,
    //         icon: 'error',
    //         showConfirmButton: false,
    //         timer: 2000
    //     });
    // }
    if (dtListDetail.getData().some(x => x.id_barang == idBarang && x.lot_id == idLot)) {
        return Swal.fire({
            text: `Barang ${namaBarang} dengan lot ${lotNo} telah dipilih`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    inpIdBarang.val(idBarang)
    inpBarang.val(`${namaBarang}`)
    inpKodeBarang.val(`${kodeBarang}`)
    inpUnit.val(namaSatuan)
    inpLotNo.val(lotNo)
    inpQtyExist.val(qty)
    inpPrice.val(harga_satuan)
    inpIdLot.val(idLot)
    inpPackName.val(row._row.data.pack_name)
    inpPackId.val(row._row.data.pack_id)
    $("#modal-barang").modal("hide");
})


let dtListDetail = new Tabulator("#dt-list-detail", {
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    layout: "fitColumns",
    columns: [
        { field: "id", visible: false },
        { field: "isEdit", visible: false },
        { field: "id_barang", visible: false },
        { field: "id_header", visible: false },
        { field: "lot_id", visible: false },
        { field: "pack_id", visible: false },
        { field: "qty_exist", visible: false },
        {
            title: "Aksi", headerSort: false, hozAlign: "center", width: "7%",
            formatter: function () {
                // Tombol Edit/Hapus per baris detail - hanya tampil selagi dokumen
                // masih Draft (status 0), mengikuti perilaku tombol Simpan/Approve/Tambah.
                if (inpStatus.val() != 0) {
                    return "";
                }
                return "<button type='button' class='btn btn-warning btn-xs btn-edit-detail' title='edit'><i class='fa fa-edit'></i></button> "
                     + "<button type='button' class='btn btn-danger btn-xs btn-delete-detail' title='delete'><i class='fa fa-trash'></i></button>";
            },
            cellClick: function (e, cell) {
                let row = cell.getRow();
                if ($(e.target).closest('.btn-edit-detail').length) {
                    openModalDetail(row);
                } else if ($(e.target).closest('.btn-delete-detail').length) {
                    if (confirm("Anda yakin akan menghapus baris ini?")) {
                        row.delete();
                    }
                }
            }
        },
        { title: "NO WORKORDER", field: "kode_walkorder", hozAlign: "left", headerHozAlign: "left", width: "10%" },
        { title: "ITEM CODE", field: "kode_barang", hozAlign: "left", headerHozAlign: "left", width: "8%" },
        { title: "ITEM DESCRIPTION", field: "nama_barang", hozAlign: "left", headerHozAlign: "left", width: "15%" },
        { title: "QTY TERIMA", field: "qty_terima", hozAlign: "right", headerHozAlign: "right", width: "8%", bottomCalc: "sum", bottomCalcParams: { precision: 2 } },
        { title: "QTY RETUR", field: "qty_retur", hozAlign: "right", headerHozAlign: "right", width: "8%", bottomCalc: "sum", bottomCalcParams: { precision: 2 } },
        { title: "PEMAKAIAN SEBELUMNYA", field: "qty_pemakaian_sebelumnya", hozAlign: "right", headerHozAlign: "right", width: "10%", bottomCalc: "sum", bottomCalcParams: { precision: 2 } },
        {
            title: "QTY SISA", field: "qty_sisa", hozAlign: "right", headerHozAlign: "right", width: "7%",
            formatter: function (cell) {
                let val = parseFloat(cell.getValue()) || 0;
                if (val < 0) val = 0;
                return val.toFixed(2);
            },
            bottomCalc: "sum", bottomCalcParams: { precision: 2 }
        },
        { title: "QTY", field: "qty", hozAlign: "right", headerHozAlign: "right", width: "7%", bottomCalc: "sum", bottomCalcParams: { precision: 2 } },
        { title: "UNIT", field: "nama_unit", hozAlign: "left", headerHozAlign: "left", width: "6%" },
        {
            title: "PRICE", field: "price", formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, width: "9%", hozAlign: "right", headerHozAlign: "right"
        },
        { title: "LOT NO", width: "7%", field: "lot_no", hozAlign: "left", headerHozAlign: "left" },
        { title: "PACK NAME", width: "7%", field: "pack_name", hozAlign: "left", headerHozAlign: "left" },
    ],
    locale: 'id',
    placeholder: "Tidak ada data",
});

if (detailData.length > 0) {
    setTimeout(() => {
        try {
            let isdata = JSON.parse(detailData);
            dtListDetail.setData(isdata).then(function () {
                if (selectKategori.val() == 5) {
                    let totalPemakaianAwal = isdata.reduce(function (sum, r) {
                        return sum + (parseFloat(r.qty) || 0);
                    }, 0);
                    if (totalPemakaianAwal > 0 && (!inpTotalPemakaian.val() || inpTotalPemakaian.val() == '')) {
                        inpTotalPemakaian.val(totalPemakaianAwal.toFixed(2));
                    }
                }
            });
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
}

let dtList = new Tabulator("#dt-list-so", {
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        { title: "ID", field: "id_konsumen", width: "20%", visible: false },
        { title: "No.SO", field: "kode_sales_order", width: "20%" },
        { title: "Style", field: "style", width: "20%" },
        { title: "Deskripsi", field: "deskripsi", width: "20%" },
        { title: "Buyer", field: "buyer", width: "20%" },
        { title: "Colour", field: "color", width: "20%" },
        { title: "Qty", field: "qty", width: "20%" },
        {
            title: "Amount", field: "amount", width: "20%", formatter: "money", formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }
        },
    ],
    placeholder: "Tidak ada data",
});

if (dataSO.length > 0) {
    setTimeout(() => {
        try {
            let isdata = JSON.parse(dataSO);
            dtList.setData(isdata)
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
}

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
        {
            title: 'ID Gudang Tujuan', field: 'id_gudang_tujuan', formatter: "html", align: "center", headerSort: false,
            width: "15%", hozAlign: "center", visible: false
        },
    ],

    locale: 'id',
    ajaxURL: appUrl("trans/item-transfer/list"),
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    placeholder: "Tidak ada data",
    selectableRows: true,
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.isApprove = true;
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

let dtListWO = new Tabulator("#dt-list-wo", {
    columns: [
        { title: "ID", field: "id", visible: false },
        {
            title: 'WO NO.', field: 'kode_walkorder', headerSort: false, sorter: 'string',
            width: "25%", formatter: "html"
        },
        {
            title: 'SAMPLE/SO NO.', field: 'ref_kode', headerSort: false, sorter: 'string',
            width: "25%", formatter: "html"
        },
        {
            title: 'STYLE', field: 'style', formatter: "html", align: "center", cssClass: "text-center", headerSort: false,
            width: "20%",
        },
        {
            title: 'BUYER', field: 'konsumen_nama', formatter: "html", align: "center", cssClass: "text-center", headerSort: false,
            width: "30%",
        },
    ],

    locale: 'id',
    ajaxURL: appUrl("trans/work-order/list"),
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    placeholder: "Tidak ada data",
    selectableRows: true,
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.isApprove = true;
    },
    ajaxResponse: function (url, params, response) {
        let pageSize = dtListWO.getPageSize();
        let pageNo = dtListWO.getPage();
        let startRow = (pageSize * (pageNo - 1)) + 1;
        let endRow = response.data.length + startRow - 1;
        if (response.data.length === 0) {
            startRow = 0; endRow = 0;
        }
        let recordsFiltered = parseInt(response.recordsFiltered);
        let recordsTotal = parseInt(response.recordsTotal);

        $("#table-footer-wo .tabulator-startrow").text(startRow);
        $("#table-footer-wo .tabulator-endrow").text(endRow);
        $("#table-footer-wo .tabulator-totalrow").text(recordsFiltered);

        let elTotalFilteredRow = $("#table-footer-wo .tabulator-totalfilteredrow");
        elTotalFilteredRow.text("");
        if (recordsTotal > recordsFiltered) {
            elTotalFilteredRow.text(" (disaring dari " + recordsTotal
                + " entri keseluruhan)");
        }
        return response;
    },
    footerElement: '<div id="table-footer-wo" class="pull-left tabulator-info">'
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
    elSearchSO.keyup(function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThreadSO);
        searchThreadSO = setTimeout(function () {
            dtListSO.setFilter("", "like", elSearchSO.val());
        }, 600);
    });
}

let searchThreadWO = null;
let elSearchWO = $("#tb-search-wo");
if (elSearchWO != null) {
    elSearchWO.keyup(function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThreadWO);
        searchThreadWO = setTimeout(function () {
            dtListWO.setFilter("", "like", elSearchWO.val());
        }, 600);
    });
}

dtListSO.on("rowClick", function (e, row) {
    inpNoRefTrf.val(row.getData().kode_transaksi)
    $.ajax({
        url: appUrl(`trans/item-transfer/data-so?noSO=${row.getData().kode_transaksi}`),
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            selectGudang.val(row.getData().id_gudang_tujuan).trigger("change");
            if (data.status) {
                dtList.setData(data.dataSO)
                dtListDetail.setData(data.dataSODet)
            }

        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });

    $("#modal-so").modal("hide");
})

dtListWO.on("rowClick", function (e, row) {
    if (selectGudang.val() == null || selectGudang.val() == "") {
        return Swal.fire({
            text: "Warehouse harus dipilih dahulu.",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    var kodeWalkorder = row.getData().kode_walkorder;
    inpNoRefWO.val(kodeWalkorder)

    $.ajax({
        url: appUrl(`trans/item-transfer/data-so?noWO=${kodeWalkorder}&idGudang=${selectGudang.val()}&idHeader=${inpIdHeader.val()}`),
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                dtList.setData(data.dataSO || [])
                dtListDetail.setData(data.dataSODet || []).then(function () {
                    recalcAlokasiPemakaian();
                });
                inpBtmKg.val(parseFloat(data.btm_kg || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });

    $("#modal-wo").modal("hide");
})

/**
 * Alokasi proporsional Total Pemakaian Barang ke tiap baris detail (per lot/pack),
 * berdasarkan Qty Sisa (Qty Terima - Qty Retur - Total Pemakaian Sebelumnya)
 * masing-masing baris terhadap total Qty Sisa seluruh baris:
 *
 *   Qty (baris) = Total Pemakaian * (Qty Sisa baris) / (total Qty Sisa semua baris)
 *
 * Baris dengan Qty Sisa <= 0 dikecualikan dari alokasi (Qty diset 0), karena
 * baris tsb sudah habis terpakai di transfer/transaksi Barang Keluar lain.
 */
function recalcAlokasiPemakaian() {
    let totalPemakaian = parseFloat((inpTotalPemakaian.val() || '0').toString().replace(',', '.')) || 0;
    let rows = dtListDetail.getData();

    if (rows.length == 0) {
        return;
    }

    let sisaArr = rows.map(function (r) {
        return parseFloat(r.qty_sisa) || 0;
    });

    let totalSisa = sisaArr.reduce(function (sum, sisa) {
        return sisa > 0 ? sum + sisa : sum;
    }, 0);

    if (totalSisa > 0 && totalPemakaian > totalSisa) {
        toastr.warning('Total Pemakaian Barang (' + totalPemakaian + ') melebihi total Qty Sisa yang tersedia (' + totalSisa + ')', "Warning", {
            positionClass: "toast-top-right"
        });
    }

    rows.forEach(function (r, idx) {
        let sisa = sisaArr[idx];
        let alokasi = 0;
        if (sisa > 0 && totalSisa > 0) {
            alokasi = totalPemakaian * (sisa / totalSisa);
            alokasi = Math.round(alokasi * 100) / 100;
        }
        r.qty = alokasi;
    });

    dtListDetail.setData(rows);
}

inpTotalPemakaian.on("input", function (e) {
    e.target.value = e.target.value.replace(",", ".");
    recalcAlokasiPemakaian();
})

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

inpQtyItem.on("input", function (e) {
    e.target.value = e.target.value.replace(",", ".");
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

    if (e.target.value > parseFloat(inpQtyExist.val())) {
        e.target.value = ""
        return Swal.fire({
            text: "Quantity tidak boleh lebih dari stok gudang",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

})

spanVendor.click(function () {
    setTimeout(() => {
        dtListVendor.redraw(true)
    }, 500);
    $("#modal-vendor").modal("show");
    dtListVendor.deselectRow();
});
spanBarang.click(function () {
    Swal.showLoading();
    dtListBarang.replaceData()

    if (selectGudang.val() == null) {
        return Swal.fire({
            text: "Warehouse belum dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    dtListBarang.on("dataLoaded", function (data) {
        Swal.close();
        setTimeout(() => {
            dtListBarang.redraw(true)
        }, 500);
        $("#modal-barang").modal("show")
    });

    dtListBarang.deselectRow();
});

selectKategori.on("change", function (e) {
    var nilai = e.target.value;
    if (nilai == 8) {
        divNamaVendor.removeClass("d-none")
        divWO.addClass("d-none")
        divTransfer.removeClass("d-none")
        divBtnAdd.removeClass("d-none")
        divTotalPemakaian.addClass("d-none")
        inpTotalPemakaian.val("")
        inpBtmKg.val("")
    }
    else if (nilai == 5) {
        divNamaVendor.addClass("d-none")
        divWO.removeClass("d-none")
        divTransfer.addClass("d-none")
        inpNoRefTrf.val("")
        divBtnAdd.addClass("d-none")
        divTotalPemakaian.removeClass("d-none")
    } else {
        divNamaVendor.addClass("d-none")
        divWO.addClass("d-none")
        divTransfer.removeClass("d-none")
        divBtnAdd.removeClass("d-none")
        divTotalPemakaian.addClass("d-none")
        inpTotalPemakaian.val("")
        inpBtmKg.val("")
    }
})

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
    if (inpIdVendor.val().length == 0 && selectKategori.val() == 3) {
        return Swal.fire({
            text: "Vendor harus dipilih",
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

    if (selectKategori.val() == 5) {
        if ((inpNoRefWO.val() == null || inpNoRefWO.val() == '' || inpNoRefWO.val() == undefined)) {
            return toastr.warning('Apabila kategori: <strong style="color: cyan;">' + selectKategori.select2('data')[0].text + '</strong> Maka <strong style="color: black;">No. WO</strong> Wajib Diisi!', "Warning", {
                positionClass: "toast-top-right"
            });
        }

        let detailRowsValidasi = dtListDetail.getData();
        let totalQtyDariDetail = detailRowsValidasi.reduce(function (sum, r) {
            return sum + (parseFloat(r.qty) || 0);
        }, 0);
        let totalQtyDariInput = parseFloat((inpTotalPemakaian.val() || '0').toString().replace(',', '.')) || 0;
        let totalQtyValidasi = Math.max(totalQtyDariDetail, totalQtyDariInput);

        let totalQtySisaValidasi = detailRowsValidasi.reduce(function (sum, r) {
            return sum + (parseFloat(r.qty_sisa) || 0);
        }, 0);

        if (totalQtyValidasi <= 0) {
            return toastr.warning('Apabila kategori: <strong style="color: cyan;">' + selectKategori.select2('data')[0].text + '</strong> Maka <strong style="color: black;">Total Pemakaian Barang</strong> Wajib Diisi!', "Warning", {
                positionClass: "toast-top-right"
            });
        }

        // Total Pemakaian (baik input maupun akumulasi detail) tidak boleh
        // melebihi Total Qty Sisa yang tersedia (dari data transfer per WO).
        let toleransiSisa = 0.01;
        if (totalQtyValidasi > totalQtySisaValidasi + toleransiSisa) {
            return Swal.fire({
                text: 'Total Pemakaian (' + totalQtyValidasi.toFixed(2) + ') tidak boleh melebihi Total Qty Sisa (' + totalQtySisaValidasi.toFixed(2) + ')!',
                icon: 'error',
                showConfirmButton: false,
                timer: 3500
            });
        }

        // Pengecekan per baris detail: pastikan tidak ada baris yang qty-nya melebihi qty_sisa
        for (let i = 0; i < detailRowsValidasi.length; i++) {
            let r = detailRowsValidasi[i];
            let qBaris = parseFloat(r.qty) || 0;
            let sBaris = parseFloat(r.qty_sisa) || 0;
            if (sBaris > 0 && qBaris > (sBaris + toleransiSisa)) {
                return Swal.fire({
                    text: 'Qty pada baris ' + (r.nama_barang || ('ke-' + (i + 1))) + ' (' + qBaris.toFixed(2) + ') melebihi Qty Sisa (' + sBaris.toFixed(2) + ')!',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 3500
                });
            }
        }
    }


    if (dtListDetail.getData().length == 0) {
        return Swal.fire({
            text: "Data detail tidak boleh kosong",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    Swal.fire({
        title: `Apakah anda ingin ${message} data Barang Keluar?`,
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
// (stok trans_lots/trans_barang) & kembalikan dokumen ke status Draft.
// Lihat BarangKeluarModel::cancelApprove().
btnCancelApprove.on("click", function (e) {
    e.preventDefault()

    Swal.fire({
        title: 'Batalkan Approve dokumen ini?',
        text: 'Semua efek approve (perubahan stok) akan dibalik, dan dokumen kembali ke status Draft.',
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
            url: appUrl('trans/outgoing-goods/cancel-approve'),
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
    if (selectKategori.val() == 0 || selectKategori.val() == null) {
        return toastr.warning('<strong style="color: black;">Kategori</strong> Wajib Diisi!', "Warning", {
            positionClass: "toast-top-right"
        });
    }
    if (selectGudang.val() == 0 || selectGudang.val() == null) {
        return toastr.warning('<strong style="color: black;">Warehouse</strong> Wajib Diisi!', "Warning", {
            positionClass: "toast-top-right"
        });
    }
    if (selectKategori.val() == 5 && (inpNoRefWO.val() == null || inpNoRefWO.val() == '' || inpNoRefWO.val() == undefined)) {
        return toastr.warning('Apabila kategori: <strong style="color: cyan;">' + selectKategori.select2('data')[0].text + '</strong> Maka <strong style="color: black;">No. WO</strong> Wajib Diisi!', "Warning", {
            positionClass: "toast-top-right"
        });
    }
    openModalDetail()
})

btnView.click(function () {
    setTimeout(() => {
        dtListSO.redraw(true)
    }, 500);
    $("#modal-so").modal("show")
    dtListSO.deselectRow();

})

btnViewWO.click(function () {
    setTimeout(() => {
        dtListWO.redraw(true)
    }, 500);
    $("#modal-wo").modal("show")
    dtListWO.deselectRow();

})

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
        if (data.price != null && data.price != undefined) {
            inpPrice.val(formatRupiah(data.price.toString()))
        } else {
            inpPrice.val(formatRupiah("0"))
        }
        inpLotNo.val(data.lot_no)
        inpEdit.val(data.lot_no)
        inpIdLot.val(data.lot_id)
        inpPackName.val(data.pack_name)
        inpPackId.val(data.pack_id)
        let remainVal = (data.qty_sisa !== undefined && data.qty_sisa !== null && data.qty_sisa !== '') ? data.qty_sisa : data.qty_exist;
        inpQtyExist.val(remainVal)
        inpKodeBarang.val(data.kode_barang)
    } else {
        inpBarang.val("")
        inpIdBarang.val("")
        inpUnit.val("")
        inpQtyItem.val("")
        inpPrice.val("")
        inpLotNo.val("")
        inpEdit.val("")
        inpQtyExist.val("")
        inpIdLot.val("")
        inpPackName.val("")
        inpPackId.val("")
        inpKodeBarang.val("")
    }
    modalDet.modal("show")

    btnSimpanDetail.off("click").on("click", function () {
        let price = parseFloat(inpPrice.val().replace(/[^\d]/g, ''));
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

        if (selectKategori.val() == 5 && row) {
            let qtyInput = parseFloat(inpQtyItem.val()) || 0;
            let sisaBaris = parseFloat(row.getData().qty_sisa) || 0;
            if (sisaBaris > 0 && qtyInput > (sisaBaris + 0.01)) {
                return Swal.fire({
                    text: "Quantity (" + qtyInput.toFixed(2) + ") tidak boleh melebihi Qty Sisa baris ini (" + sisaBaris.toFixed(2) + ")",
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }

        if (row) {
            row.update({
                id: inpIdDetail.val(),
                nama_barang: inpBarang.val(),
                kode_barang: inpKodeBarang.val(),
                id_barang: inpIdBarang.val(),
                qty: inpQtyItem.val(),
                price: price,
                qty_exist: inpQtyExist.val(),
                lot_no: inpLotNo.val(),
                lot_id: inpIdLot.val(),
                nama_unit: inpUnit.val(),
                pack_name: inpPackName.val(),
                pack_id: inpPackId.val(),
            });
        } else {

            dtListDetail.addRow({
                id: null,
                nama_barang: inpBarang.val(),
                kode_barang: inpKodeBarang.val(),
                id_barang: inpIdBarang.val(),
                price: price,
                qty: inpQtyItem.val(),
                qty_exist: inpQtyExist.val(),
                lot_no: inpLotNo.val(),
                lot_id: inpIdLot.val(),
                nama_unit: inpUnit.val(),
                pack_name: inpPackName.val(),
                pack_id: inpPackId.val(),
            });
        }

        if (selectKategori.val() == 5) {
            let rowsNow = dtListDetail.getData();
            let totNow = rowsNow.reduce(function (sum, r) {
                return sum + (parseFloat(r.qty) || 0);
            }, 0);
            inpTotalPemakaian.val(totNow.toFixed(2));
        }

        modalDet.modal("hide")

    })

}


function simpanData(status) {
    $.ajax({
        type: 'POST',
        url: appUrl('trans/outgoing-goods/save'),
        data: {
            id: inpIdHeader.val(),
            id_gudang: selectGudang.val(),
            tanggal: formatLocaleDate(inpTglReceive.val()),
            id_kategori: selectKategori.val(),
            nama: inpVendor.val(),
            id_vendor: inpIdVendor.val(),
            data: dtListDetail.getData(),
            keterangan: inpKeterangan.val(),
            no_ref_trf: inpNoRefTrf.val(),
            no_ref_wo: inpNoRefWO.val(),
            status: status
        },
        dataType: "json",
        beforeSend: function () {
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
                window.location.href = 'trans/outgoing-goods'

            } else {
                Swal.fire({
                    text: response.message,
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        },
        error: function (e) {
            let msg = (e.responseJSON && e.responseJSON.message) ? e.responseJSON.message : "Terjadi kesalahan pada server, data gagal disimpan.";
            Swal.close();

            Swal.fire({
                text: msg,
                icon: 'error',
                showConfirmButton: false,
                timer: 3000
            });
        },
    });

}


