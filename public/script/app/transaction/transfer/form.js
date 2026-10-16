

let inpTglReceive = $('#tanggal');
let inpBarang = $('#namaBarang');
let inpKeterangan = $('#trans_desc');
let inpKeteranganDet = $('#keterangan');
let inpIdBarang = $('#idBarang');
let inpKodeBarang = $('#kodeBarang');
let inpQtyItem = $('#qty_item');
let inpQtyExist = $('#qty_exist');
let inpPrice = $('#price');
let inpUnit = $('#unit');
let inpEdit = $('#edit');
let spanBarang = $('#spanBarang');
let spanWO = $('#spanWO');
let selectGudangAsal = $('#gudang_asal');
let inpIdWO = $('#id_wo');
let inpKodeWO = $('#kode_walkorder');
let selectGudangTujuan = $('#gudang_tujuan');
let selectPerusahaan = $('#select_perusahaan');
let selectProses = $('#select_proses');
let selectCMT = $('#select_cmt');
let inpCMT = $('#cmt');
let inpIdCMT = $('#id_cmt');
let divCMT = $('#div-cmt');
let selectTipe = $('#tipe');
function isSelectedProsesRajut() {
    let opt = selectProses.find(':selected');
    let isRajut = opt.data('is-rajut');
    return (isRajut == 1 || isRajut === true || isRajut === '1' || selectProses.val() == 1);
}
let inpLotNo = $('#lot_no');
let inpPackId = $('#pack_id');
let inpPackName = $('#pack_name');
let inpIdLot = $('#id_lot');
let detailData = $("#data-details").val().replace(/&quot;/ig, '"');
let dataSO = $("#data-so").val().replace(/&quot;/ig, '"');
let btnAdd = $('#btn-add');
let btnSimpan = $('#btn-simpan');
let btnSimpanCetak = $('#btn-simpan-cetak');
let btnView = $('#btn-view');
let btnApprove = $('#btn-approve');
let btnCancelApprove = $('#btn-cancel-approve');
let btnSimpanDetail = $('#btn-simpan-det');
let modalDet = $('#modal-detail-item');
let inpStatus = $('#status');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');
let inpIdGudangAsal = $('#id_gudang_asal');
let inpRole = $('#role_id');

const inpRefProduksi = $("#ref_prduksi");
const divDetail = $("#div_detail");

if (inpIdHeader.val().length == 0) {
    if (!selectGudangAsal.val()) {
        const defAsal = (selectPerusahaan.val() == 2) ? "60" : "1";
        selectGudangAsal.val(defAsal).trigger("change.select2");
    }
    selectGudangTujuan.val("").trigger("change.select2");
    selectCMT.val("").trigger("change.select2");
    selectProses.val("").trigger("change.select2");
} else {
    setTimeout(() => {
        try {
            divCMT.removeClass("d-none");
            const savedCmt = $("#id_cmt").val();
            if (savedCmt) {
                selectCMT.val(savedCmt).trigger("change.select2");
            }
            if (dataSO && dataSO.length > 0) {
                let parsedSO = typeof dataSO === 'string' ? JSON.parse(dataSO) : dataSO;
                dtList.setData(parsedSO)
            }
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 500);
}

if (inpStatus.val() == 0) {
    btnAdd.show()
    btnView.show()
    btnSimpan.show()
    btnSimpanCetak.show()
    btnApprove.show()
    btnCancelApprove.hide()
} else {
    btnAdd.hide()
    btnSimpan.hide()
    btnSimpanCetak.hide()
    btnApprove.hide()
    btnView.hide()
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
            title: "Kode Barang", field: "kode_barang", headerSort: false,
            width: "15%"
        },
        {
            title: "Nama Barang", field: "nama_barang", headerSort: false,
            width: "30%"
        },

        {
            title: "Satuan", field: "nama_satuan", headerSort: false,
            width: "15%"
        },
        {
            title: "Lot No.", field: "lot_no", headerSort: false,
            width: "15%"
        },
        {
            title: "Pack Name", field: "pack_name", headerSort: false,
            width: "10%"
        },
        {
            title: "Pack ID", field: "pack_id", visible: false, headerSort: false,
        },
        {
            title: "Qty", field: "qty", headerSort: false,
            width: "15%"
        },
    ],
    locale: 'id',
    // ajaxURL: "/trans/outgoing-goods/list-barang",
    ajaxURL: "/trans/item-transfer/list-barang",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    selectableRows: true,
    groupBy: "nama_barang",
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.idGudang = selectGudangAsal.val()
        params.kode_walkorder = inpKodeWO.val()
        params.tipe = 'transfer'
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
    paginationSize: 100,
    paginationSizeSelector: [25, 50, 100, 200],
    paginationButtonCount: 10,
    dataSendParams: {
        sorters: "order"
    },
    selectable: false,
});

let dtListWO = new Tabulator("#dt-list-workorder", {
    columns: [
        {
            title: "ID WO", field: "id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "Ref ID", field: "ref_id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "Tipe", field: "tipe_id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "No. WO", field: "kode_walkorder", headerSort: false,
            width: "20%"
        },
        {
            title: "Ref Kode", field: "ref_kode", headerSort: false,
            width: "20%"
        },
        {
            title: "Nama Konsumen", field: "konsumen_nama", headerSort: false,
            width: "20%"
        },
        {
            title: "Style", field: "keterangan_style", headerSort: false,
            width: "40%"
        }
    ],
    locale: 'id',
    // ajaxURL: "/trans/outgoing-goods/list-barang",
    ajaxURL: "/trans/work-order/list",
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
        let pageSize = dtListWO.getPageSize();
        let pageNo = dtListWO.getPage();
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

setTimeout(() => {
    inpRefProduksi.on("change", function () {
        const ch = $(this).is(':checked');

        if (ch) {
            divDetail.hide();
        } else {
            divDetail.show();
            dtListBarang.redraw(true)
        }
    });

    if (inpRefProduksi.is(':checked')) {
        divDetail.hide();
    }
}, 500);


let searchThreadBarang = null;
let elSearchBarang = $("#tb-search");
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

let searchThreadWO = null;
let elSearchWO = $("#tb-search-wo");
if (elSearchWO != null) {
    elSearchWO.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThreadWO);
        searchThreadWO = setTimeout(function () {
            dtListWO.setFilter("", "like", elSearchWO.val());
        }, 600);
    });
}

let searchSO = null;
let elSearchSO = $("#tb-search-so");
if (elSearchSO != null) {
    elSearchSO.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchSO);
        searchSO = setTimeout(function () {
            dtListSO.setFilter("", "like", elSearchSO.val());
        }, 600);
    });
}



dtListBarang.on("rowClick", function (e, row) {

    if (selectGudangAsal.val() == null) {
        return Swal.fire({
            text: "Gudang Asal harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if (inpIdWO.val() == null) {
        return Swal.fire({
            text: "No. Work Order harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if (selectGudangTujuan.val() == null) {
        return Swal.fire({
            text: "Gudang Tujuan harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }


    const kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    const idBarang = row._row.data.id;
    const idWo = row._row.data.id_wo;
    const idLot = row._row.data.lot_id;
    const qty = row._row.data.qty;
    const namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    const namaSatuan = row._row.data.nama_satuan.replace(/<[^>]*>/g, '');
    const lotNo = row._row.data.lot_no.replace(/<[^>]*>/g, '');
    const hargasatuan = row._row.data.harga_satuan;
    const packId = row._row.data.pack_id;
    // Sebelumnya cek pakai lot_id - ternyata lot_id di data ini hampir selalu
    // kosong/null (baik dari query picker maupun saat reload dokumen), jadi
    // dua lot yg BEDA (mis. lot 6414 vs 6431 A) sama-sama null == null dan
    // dianggap sama begitu barang+pack cocok - lot beda pun ikut ditolak.
    // lot_no (teks) selalu terisi benar di kedua sisi, jadi dipakai sbg
    // kriteria pembanding, bukan lot_id.
    if (dtListDetail.getData().some(x => x.id_barang == idBarang && x.lot_no == lotNo && x.pack_id == packId)) {
        return Swal.fire({
            text: `Barang ${namaBarang} dengan lot ${lotNo} dan pack yang sama telah dipilih`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    inpIdBarang.val(idBarang)
    inpIdWO.val(idWo)
    inpBarang.val(`${namaBarang}`)
    inpKodeBarang.val(`${kodeBarang}`)
    inpUnit.val(namaSatuan)
    inpLotNo.val(lotNo)
    inpPackId.val(row._row.data.pack_id)
    inpPackName.val(row._row.data.pack_name)
    inpQtyExist.val(qty)
    inpIdLot.val(idLot)
    inpPrice.val(hargasatuan)
    $("#modal-barang").modal("hide");
})

dtListWO.on("rowClick", function (e, row) {

    if (selectGudangAsal.val() == null) {
        return Swal.fire({
            text: "Gudang Asal harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if (selectGudangTujuan.val() == null) {
        return Swal.fire({
            text: "Gudang Tujuan harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }


    const kodeWO = row._row.data.kode_walkorder.replace(/<[^>]*>/g, '');
    const idWO = row._row.data.id;
    inpIdWO.val(idWO)
    inpKodeWO.val(`${kodeWO}`)
    $("#modal-workorder").modal("hide");
})


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
        return fmBtnEdit + " " + fmBtnDelete;
    } else {
        let row = cell.getRow();
        let index = row.getPosition(true);
        return `<span class="fw-bold text-muted">${index}</span>`;
    }
};


// Hitung lebar minimum kolom otomatis berdasarkan teks judul kolom (header title)
// Memastikan teks judul + icon sort + padding tidak terpotong (truncated)
function calcHeaderTitleMinWidth(titleText, hasSort = true, extraBuffer = 28) {
    if (!titleText || titleText === '#') return 45;
    const cleanText = titleText.replace(/<[^>]*>/g, ' ').trim();
    let textWidth = 0;
    try {
        const canvas = calcHeaderTitleMinWidth.canvas || (calcHeaderTitleMinWidth.canvas = document.createElement("canvas"));
        const ctx = canvas.getContext("2d");
        ctx.font = "bold 13px -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
        textWidth = Math.ceil(ctx.measureText(cleanText).width);
    } catch (e) {
        textWidth = cleanText.length * 9;
    }
    const sortWidth = hasSort ? 26 : 0;
    return Math.max(textWidth + sortWidth + extraBuffer, 80);
}

let dtList = new Tabulator("#dt-list", {
    layout: "fitDataFill",
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        {
            headerSort: false,
            title: '#',
            formatter: buttonRowSOAction,
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
            minWidth: Math.max(calcHeaderTitleMinWidth("Tipe", true), 105),
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
        { title: "NID", field: "id", visible: false },
        { title: "No.SO", field: "kode_sales_order", minWidth: Math.max(calcHeaderTitleMinWidth("No.SO", true), 125), hozAlign: "left", headerHozAlign: "left" },
        { title: "Ref Detail ID", field: "ref_detail_id", visible: false },
        { 
            title: "Style", 
            field: "style", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Style", true), 100), 
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
            minWidth: Math.max(calcHeaderTitleMinWidth("Deskripsi", true), 130), 
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
            minWidth: Math.max(calcHeaderTitleMinWidth("Colour", true), 180), 
            hozAlign: "left", 
            headerHozAlign: "left",
            formatter: function (cell) {
                let val = cell.getValue();
                return `<div style="white-space: normal; line-height: 1.3;">${val ? val : '-'}</div>`;
            }
        },
        { 
            title: "Qty Ref", 
            field: "qty_ref", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Qty Ref", true, 30), 115), 
            hozAlign: "right",
            headerHozAlign: "right",
            editor: "number", 
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            },
            formatter: "money", 
            formatterParams: { decimal: ",", thousand: ".", precision: 0 },
            bottomCalcFormatter: 'money', 
            bottomCalcFormatterParams: { decimal: ",", thousand: ".", precision: 0 },
            bottomCalc: 'sum' 
        },
        {
            title: "Qty", 
            field: "qty", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Qty", true, 30), 95), 
            hozAlign: "right",
            headerHozAlign: "right",
            editor: "number", 
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            },
            formatter: "money", 
            formatterParams: { decimal: ",", thousand: ".", precision: 0 },
            bottomCalcFormatter: 'money', 
            bottomCalcFormatterParams: { decimal: ",", thousand: ".", precision: 0 },
            bottomCalc: 'sum'
        },
        {
            title: "Berat (Kg)",
            field: "berat",
            minWidth: Math.max(calcHeaderTitleMinWidth("Berat (Kg)", true, 30), 130), 
            hozAlign: "right",
            headerHozAlign: "right",
            editor: "number",
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            },
            formatter: function (cell) {
                let val = cell.getValue();
                if (val !== null && val !== undefined && val !== "") {
                    return parseFloat(val).toFixed(2);
                }
                return "";
            },
            bottomCalc: "sum",
            bottomCalcFormatter: function (cell) {
                let val = cell.getValue();
                return val ? parseFloat(val).toFixed(2) : "0.00";
            }
        },
        { 
            title: "Ukuran", 
            field: "kode_ukuran", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Ukuran", true, 30), 105), 
            hozAlign: "center", 
            headerHozAlign: "center" 
        },
        { 
            title: "Keterangan", 
            field: "keterangan", 
            minWidth: Math.max(calcHeaderTitleMinWidth("Keterangan", true), 135), 
            hozAlign: "left",
            headerHozAlign: "left",
            editor: "input",
            editable: function (cell) {
                return inpStatus.val() != 1 && inpStatus.val() != "1";
            }
        },
        {
            title: "Status Reject",
            field: "is_reject",
            minWidth: Math.max(calcHeaderTitleMinWidth("Status Reject", false, 35), 135),
            hozAlign: "center",
            headerHozAlign: "center",
            headerSort: false,
            formatter: function (cell) {
                let val = cell.getValue();
                let checked = (val == 1 || val == "1" || val === true) ? "checked" : "";
                let disabled = (inpStatus.val() == 1 || inpStatus.val() == "1") ? "disabled" : "";
                return `<div class="form-check d-flex justify-content-center my-0" style="min-height: auto;">
                    <input type="checkbox" class="form-check-input check-reject" ${checked} ${disabled} style="cursor: pointer; width: 18px; height: 18px;">
                </div>`;
            },
            cellClick: function (e, cell) {
                if (inpStatus.val() == 1 || inpStatus.val() == "1") return;
                if ($(e.target).is('input[type="checkbox"]')) {
                    let isChecked = $(e.target).is(':checked') ? 1 : 0;
                    cell.getRow().update({ is_reject: isChecked });
                }
            }
        },
        { title: "Total Scanned", field: "total_scanned", visible: false },
    ],
    placeholder: "Tidak ada data",
    tableBuilt: function () {
        this.getColumns().forEach(function (col) {
            let headerEl = col.getElement();
            if (headerEl) {
                let titleEl = headerEl.querySelector('.tabulator-col-title');
                let sorterEl = headerEl.querySelector('.tabulator-col-sorter');
                if (titleEl) {
                    let requiredWidth = Math.ceil(titleEl.scrollWidth + (sorterEl ? (sorterEl.offsetWidth || 18) : 0) + 26);
                    let currentWidth = col.getWidth();
                    if (currentWidth < requiredWidth) {
                        col.setWidth(requiredWidth);
                    }
                }
            }
        });
    },
});

function cardFormatter(cell, formatterParams, onRendered) {
    let data = cell.getRow().getData(); // Ambil data row

    // HTML Card Layout
    var cardHtml = `<div class="card shadow-sm">
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-3 text-start">
                    <h6 class="f-w-700 m-b-6">${data.kode_sales_order}</h6>
                    <p class="f-w-500 m-y-0">${data.deskripsi}</p>
                    <hr class="m-y-8" />
            
                  </div>
                  <div class="col-sm-9">
                       <div id="dt-list-detail-${data.id}" class="table-responsive table-striped"></div>
                  </div>
                </div>
              </div>
            </div>`;

    onRendered(() => {

        document.querySelector(`.edit[data-id='${data.id}']`).addEventListener('click', () => {
            fileSalesOrder.val('')
            linkFileSalesOrder.attr('src', "")
            getDetail(data.id)
        });
        document.querySelector(`.print[data-id='${data.id}']`).addEventListener('click', () => {
            window.open(`${baseUrl}/trans/sales-order/print/${data.id}`, "_blank");
        });
        document.querySelector(`.delete[data-id='${data.id}']`).addEventListener('click', () => {
            if (confirm("Anda yakin akan menghapus data?")) {
                window.location.replace(baseUrl + "/trans/sales-order/delete/list" + data.id);
            }
        });

        let isColumn = [
            { headerSort: false, title: "No", field: "no", width: "5%" },
            { headerSort: false, cssClass: 'text-start', title: "Colour", field: "colordasar" }
        ]

        for (const el of data.key_ukuran) {
            const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
            isColumn.push({ headerSort: false, title: el.kode_ukuran, field: isKey, cssClass: "text-center", hozAlign: "center", width: "7%" })
        }

        isColumn.push(
            {
                headerSort: false, cssClass: 'text-center', title: "Amount", field: "total_harga", formatter: "money",
                formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                },
                hozAlign: "right", cssClass: 'text-end', width: "15%"
            })

        new Tabulator(`#dt-list-detail-${data.id}`, {
            data: data.detail,
            layout: "fitColumns",
            resizableColumnFit: true,
            pagination: true,
            paginationSize: 10,
            paginationButtonCount: 5,
            columns: isColumn,
        });
    });

    return cardHtml; // Return HTML Card
}

let dtListDetail = new Tabulator("#dt-list-detail", {
    pagination: true,
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        { field: "id", visible: false },
        { field: "isEdit", visible: false },
        { field: "id_barang", visible: false },
        { field: "id_header", visible: false },
        { title: "LOT ID", field: "lot_id", visible: false },
        { field: "qty_exist", visible: false },
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
        { title: "ID WO", field: "id_wo", visible: false, width: '10%' },
        { title: "No. Workorder", field: "kode_walkorder", hozAlign: "left", width: "10%" },
        { title: "ITEM CODE", field: "kode_barang", hozAlign: "left", width: "10%" },
        { title: "ITEM DESCRIPTION", field: "nama_barang", hozAlign: "left", width: "25%" },
        {
            title: "QTY", field: "qty", hozAlign: "center", width: "10%",
            bottomCalc: "sum",
            bottomCalcFormatter: function (cell) {
                let val = cell.getValue();
                return parseFloat(val.toFixed(2));
            },
            formatter: function (cell) {
                let val = cell.getValue();
                return parseFloat(parseFloat(val).toFixed(2));
            }
        },
        { title: "UNIT", field: "nama_unit", hozAlign: "center", width: "15%" },
        {
            title: "PRICE", field: "price", formatter: "money", visible: false,
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, width: "10%"
        },
        { title: "LOT NO", width: "12%", field: "lot_no", hozAlign: "left" },
        { title: "PACK NAME", width: "12%", field: "pack_name", hozAlign: "left" },
        { title: "PACK ID", field: "pack_id", visible: false },
        { title: "KETERANGAN", width: "16%", field: "keterangan", hozAlign: "left" },
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


// Lockdown field Price: tidak boleh diedit manual, selalu mengikuti harga lot/barang
inpPrice.prop('readonly', true).attr('tabindex', '-1').css({
    'pointer-events': 'none',
    'background-color': '#e9ecef',
    'cursor': 'not-allowed'
});

inpPrice.on("keydown keypress paste drop", function (e) {
    e.preventDefault();
    return false;
});

function formatRupiah(value) {
    value = (value || '').toString().replace(/[^\d]/g, '');
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

selectGudangTujuan.on("change", function (e) {
    checkCMT(e.target.value)
})

function checkCMT(nilai) {
    divCMT.removeClass("d-none");
    if (inpIdHeader.val().length == 0 && dataGudang.length > 0) {
        let resGudang = dataGudang.find(x => x.id == nilai);
        if (resGudang && resGudang.id_cmt) {
            selectCMT.val(resGudang.id_cmt).trigger("change");
        }
    }
}

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

spanBarang.click(function () {
    Swal.showLoading();
    
    let isRefProduksi = inpRefProduksi.is(':checked');
    if ((inpIdWO.val() == null || inpIdWO.val() == '' || inpIdWO.val() == undefined) && isRefProduksi == true) {
        return Swal.fire({
            text: "No. Work Order harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    else {
        dtListBarang.replaceData()
        dtListBarang.on("dataLoaded", function (data) {
            Swal.close();
            setTimeout(() => {
                dtListBarang.redraw(true)
            }, 500);
            $("#modal-barang").modal("show")
        });
    
        dtListBarang.deselectRow();
    }

});

spanWO.click(function () {
    Swal.showLoading();
    dtListWO.replaceData()

    if (!selectGudangAsal.val() || selectGudangAsal.val() == "") {
        return Swal.fire({
            text: "Gudang Asal belum dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (!selectGudangTujuan.val() || selectGudangTujuan.val() == "") {
        return Swal.fire({
            text: "Gudang Tujuan belum dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    dtListWO.on("dataLoaded", function (data) {
        Swal.close();
        setTimeout(() => {
            dtListWO.redraw(true)
        }, 500);
        $("#modal-workorder").modal("show")
    });

    dtListWO.deselectRow();
});


function submitData(status, message, isPrint = false) {

    if (!selectGudangAsal.val() || selectGudangAsal.val() == "") {
        return Swal.fire({
            text: "Gudang Asal harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (!selectGudangTujuan.val() || selectGudangTujuan.val() == "") {
        return Swal.fire({
            text: "Gudang Tujuan harus dipilih",
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

    let soData = dtList.getData();
    let detailData = dtListDetail.getData();

    let hasSO = soData && soData.length > 0;
    let hasDetail = detailData && detailData.length > 0;

    // Validasi mutlak: dokumen tidak boleh kosong sama sekali (harus ada minimal salah satu)
    if (!hasSO && !hasDetail) {
        return Swal.fire({
            title: "Data Item Kosong!",
            text: "Silakan tambahkan minimal 1 item SO atau rincian item barang/benang sebelum menyimpan atau mencetak.",
            icon: 'warning',
            confirmButtonColor: '#0284c7'
        });
    }

    if (!selectProses.val() || selectProses.val() == "") {
        return Swal.fire({
            text: "Proses harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (!selectCMT.val() || selectCMT.val() == "") {
        return Swal.fire({
            text: "CMT harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    let confirmTitle = `Apakah anda ingin ${message} data Transfer?`;
    let confirmText = '';
    let confirmIcon = 'question';

    // Jika Proses Produksi aktif tetapi tidak ada item SO (misal hanya kirim benang/WO)
    if (inpRefProduksi.is(':checked') && !hasSO && hasDetail) {
        confirmTitle = `Item SO Kosong (Kirim Benang/WO)`;
        confirmText = `Opsi 'Proses Produksi' aktif tanpa item SO. Anda tetap dapat melanjutkan ${message} data Transfer (Pengiriman Benang/WO). Lanjutkan?`;
        confirmIcon = 'warning';
    }

    Swal.fire({
        title: confirmTitle,
        text: confirmText,
        icon: confirmIcon,
        confirmButtonText: 'Ya, Lanjutkan',
        confirmButtonColor: '#198754',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#6C757D'
    }).then((result) => {
        if (result.isConfirmed) {
            simpanData(status, isPrint)
        }
    })
}


btnSimpan.on("click", function (e) {
    e.preventDefault()
    submitData(0, "menyimpan draft", false)
})

btnSimpanCetak.on("click", function (e) {
    e.preventDefault()
    submitData(0, "menyimpan draft & mencetak", true)
})

btnApprove.on("click", function (e) {
    e.preventDefault()
    submitData(1, "mengapprove", false)
})

// TAMBAHAN (2026-08-09): "Batalkan Approve" - membalik semua efek approve
// (stok trans_lots/trans_barang, qty_kirim tracking produksi WO) &
// kembalikan dokumen ke status Draft. Lihat ItemTransferModel::cancelApprove().
btnCancelApprove.on("click", function (e) {
    e.preventDefault()

    Swal.fire({
        title: 'Batalkan Approve dokumen ini?',
        text: 'Semua efek approve (perubahan stok & qty kirim ke tracking produksi) akan dibalik, dan dokumen kembali ke status Draft.',
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
            url: '/trans/item-transfer/cancel-approve',
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
    if (selectProses.val() == null) {
        Swal.fire({
            position: 'top-end',
            icon: 'warning',
            title: 'Harap pilih Proses dahulu!',
            showConfirmButton: false,
            timer: 2000,
        });
        return false;
    }
    setTimeout(() => {
        dtListSO.redraw(true)
    }, 500);
    $("#modal-so").modal("show")
    dtListSO.deselectRow();

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
        inpIdWO.val(data.id_wo)
        inpKodeWO.val(data.kode_walkorder)
        inpUnit.val(data.nama_unit)
        inpQtyItem.val(data.qty)
        inpPrice.val(formatRupiah(data.price.toString()))
        inpLotNo.val(data.lot_no)
        inpPackId.val(data.pack_id)
        inpPackName.val(data.pack_name)
        inpEdit.val(data.lot_no)
        inpIdLot.val(data.lot_id)
        inpQtyExist.val(data.qty_exist)
        inpKeteranganDet.html(data.keterangan)
    } else {
        inpBarang.val("")
        inpIdBarang.val("")
        inpIdWO.val('')
        inpKodeWO.val('')
        inpUnit.val("")
        inpQtyItem.val("")
        inpPrice.val("")
        inpLotNo.val("")
        inpPackId.val("")
        inpPackName.val("")
        inpEdit.val("")
        inpQtyExist.val("")
        inpIdLot.val("")
        inpKeteranganDet.html("")
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

        // if(!regex.test(price)){
        //     return Swal.fire({
        //         text: "Price harus berupa angka",
        //         icon: 'error',
        //         showConfirmButton: false,
        //         timer: 2000
        //     });
        // }

        if (selectGudangAsal.val() == null) {
            return Swal.fire({
                text: "Gudang Asal harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        if (selectGudangTujuan.val() == null) {
            return Swal.fire({
                text: "Gudang Tujuan harus dipilih",
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
                id_wo: inpIdWO.val(),
                kode_walkorder: inpKodeWO.val(),
                qty: inpQtyItem.val(),
                price: price,
                qty_exist: inpQtyExist.val(),
                lot_no: inpLotNo.val(),
                lot_id: inpIdLot.val(),
                nama_unit: inpUnit.val(),
                keterangan: inpKeteranganDet.val(),
                pack_id: inpPackId.val(),
                pack_name: inpPackName.val(),
            });
        } else {

            dtListDetail.addRow({
                id: null,
                nama_barang: inpBarang.val(),
                kode_barang: inpKodeBarang.val(),
                id_barang: inpIdBarang.val(),
                id_wo: inpIdWO.val(),
                kode_walkorder: inpKodeWO.val(),
                price: price,
                qty: inpQtyItem.val(),
                qty_exist: inpQtyExist.val(),
                lot_no: inpLotNo.val(),
                lot_id: inpIdLot.val(),
                nama_unit: inpUnit.val(),
                keterangan: inpKeteranganDet.val(),
                pack_id: inpPackId.val(),
                pack_name: inpPackName.val()
            });
        }

        // console.log("detail", {
        //     id:null,
        //     nama_barang                 : inpBarang.val(),
        //     kode_barang                 : inpKodeBarang.val(),
        //     id_barang                   : inpIdBarang.val(),
        //     price                         : inpPrice.val(),
        //     qty                         : inpQtyItem.val(),
        //     qty_exist                         : inpQtyExist.val(),
        //     lot_no                   : inpLotNo.val(),
        //     lot_id                   : inpIdLot.val(),
        //     nama_unit                   : inpUnit.val(),
        //     keterangan:inpKeteranganDet.val()
        // })
        modalDet.modal("hide")

    })

}

// let dtListSample = new Tabulator("#dt-list-sample_", {
//     columns: [
//         {title: "ID", field: "id", width: "20%",visible:false},
//         {title: "Informasi", field: "kode_sales_order", width: "20%",
//             formatter: function (cell) {
//                 let rowData = cell.getRow().getData();
//                 return `No SO: ${rowData.kode_sales_order} <br> Style: ${rowData.style ? rowData.style : rowData.deskripsi} <br> Buyer:${rowData.nama}`;
//             },
//         },
//         {
//             title: "Detail",
//             field: "detail",
//             width:"80%",
//             formatter: function(cell, formatterParams) {
//                 let data = cell.getValue();
//                 let listUkuran = cell.getData().key_ukuran;

//                 if (!data || data.length === 0) return "No Data";

//                 let tableHtml = `<table style="width:100%; border-collapse:collapse;">
//                     <thead>
//                         <tr style="background:#f2f2f2;">
//                             <th style="border:1px solid #ddd; padding:5px;">No.</th>
//                             <th style="border:1px solid #ddd; padding:5px;">Colour</th>`
//                             listUkuran.forEach( row => {
//                                 tableHtml +=  `<th style="border:1px solid #ddd; padding:5px;">${row.kode_ukuran}</th>`
//                             })
//                             tableHtml += `<th style="border:1px solid #ddd; padding:5px;">Total Qty</th>
//                         </tr>
//                     </thead>
//                     <tbody>`;

//                 data.forEach(row => {
//                     tableHtml += `<tr>
//                         <td style="border:1px solid #ddd; padding:5px;">${row.no}</td>
//                         <td style="border:1px solid #ddd; padding:5px;">${row.colour}</td>`
//                         listUkuran.forEach( x => {
//                             let ukuran = x.key_ukuran = "all" ? "all_" : x.key_ukuran;
//                             tableHtml +=  `<td style="border:1px solid #ddd; padding:5px;">${ukuran in row ? row[ukuran] : ""}</td>`
//                         })
//                       tableHtml +=  `<td style="border:1px solid #ddd;">${(row.qty)}</td>
//                     </tr>`;
//                 });

//                 tableHtml += `</tbody></table>`;

//                 return tableHtml;
//             }
//         }
//     ],

//     locale: 'id',    
//     ajaxURL: "/trans/sales-order/list",
//     ajaxConfig: "POST",
//     sortMode: "remote",
//     filterMode: "remote",
//     placeholder: "Tidak ada data",
//     selectableRows: true,
//     ajaxRequesting: function (url, params) {
//         params.start = params.size * (params.page - 1);
//         params.length = params.size;
//     },
//     ajaxResponse: function (url, params, response) {
//         let pageSize = dtListSample.getPageSize();
//         let pageNo = dtListSample.getPage();
//         let startRow = (pageSize * (pageNo - 1)) + 1;
//         let endRow = response.data.length + startRow - 1;
//         if (response.data.length === 0) {
//             startRow = 0; endRow = 0;
//         }
//         let recordsFiltered = parseInt(response.recordsFiltered);
//         let recordsTotal = parseInt(response.recordsTotal);

//         $("#table-footer .tabulator-startrow").text(startRow);
//         $("#table-footer .tabulator-endrow").text(endRow);
//         $("#table-footer .tabulator-totalrow").text(recordsFiltered);

//         let elTotalFilteredRow = $("#table-footer .tabulator-totalfilteredrow");
//         elTotalFilteredRow.text("");
//         if (recordsTotal > recordsFiltered) {
//             elTotalFilteredRow.text(" (disaring dari " + recordsTotal
//                 + " entri keseluruhan)");
//         }
//         return response;
//     },
//     footerElement: '<div id="table-footer" class="pull-left tabulator-info">'
//         + 'Menampilkan <span class="tabulator-startrow"></span> - <span class="tabulator-endrow"></span> dari '
//         + '<span class="tabulator-totalrow"></span> entri<span class="tabulator-totalfilteredrow"></span></div>',
//     pagination: true,
//     paginationMode: "remote",
//     paginationSize: 25,
//     paginationButtonCount: 10,
//     dataSendParams: {
//         sorters: "order"
//     },
// });

// let dtListSO = new Tabulator("#dt-list-sample", {
//     columns: [

//         // {title: "No.SO", field: "kode_sales_order", width: "20%"},
//         {title: "Style", field: "style", width: "20%"},
//         {title: "Deskripsi", field: "deskripsi", width: "20%"},
//         // {title: "Buyer", field: "buyer", width: "20%"},
//         {title: "Colour", field: "color", width: "20%"},
//         {title: "Qty", field: "qty", width: "20%"},
//         {title: "Ukuran", field: "kode_ukuran", width: "20%"},
//         // {title: "Amount", field: "amount", width: "20%",formatter: "money",    formatterParams: {
//         //     decimal: ",",
//         //     thousand: ".",
//         //     symbol: "Rp",  // Simbol mata uang Rupiah
//         //     precision: 0,   // Tidak ada desimal
//         // }},
//     ],
//     groupBy: ['kode_sales_order'],
//     locale: 'id',    
//     ajaxURL: "/trans/item-transfer/list-so",
//     ajaxConfig: "POST",
//     sortMode: "remote",
//     filterMode: "remote",
//     placeholder: "Tidak ada data",
//     selectableRows: true,
//     ajaxRequesting: function (url, params) {
//         params.start = params.size * (params.page - 1);
//         params.length = params.size;
//     },
//     ajaxResponse: function (url, params, response) {
//         let pageSize = dtListSO.getPageSize();
//         let pageNo = dtListSO.getPage();
//         let startRow = (pageSize * (pageNo - 1)) + 1;
//         let endRow = response.data.length + startRow - 1;
//         if (response.data.length === 0) {
//             startRow = 0; endRow = 0;
//         }
//         let recordsFiltered = parseInt(response.recordsFiltered);
//         let recordsTotal = parseInt(response.recordsTotal);

//         $("#table-footer .tabulator-startrow").text(startRow);
//         $("#table-footer .tabulator-endrow").text(endRow);
//         $("#table-footer .tabulator-totalrow").text(recordsFiltered);

//         let elTotalFilteredRow = $("#table-footer .tabulator-totalfilteredrow");
//         elTotalFilteredRow.text("");
//         if (recordsTotal > recordsFiltered) {
//             elTotalFilteredRow.text(" (disaring dari " + recordsTotal
//                 + " entri keseluruhan)");
//         }
//         return response;
//     },
//     footerElement: '<div id="table-footer" class="pull-left tabulator-info">'
//         + 'Menampilkan <span class="tabulator-startrow"></span> - <span class="tabulator-endrow"></span> dari '
//         + '<span class="tabulator-totalrow"></span> entri<span class="tabulator-totalfilteredrow"></span></div>',
//     pagination: true,
//     paginationMode: "remote",
//     paginationSize: 25,
//     paginationButtonCount: 10,
//     dataSendParams: {
//         sorters: "order"
//     },
// });

let dtListSO = null;

if (selectProses.val() != null) {
    let valp = selectProses.val();
    let idCmt = selectCMT.val();
    let idPerusahaan = $('#id_perusahaan').val();
    if (valp !== "") {
        if (dtListSO === null) {
            // Jika tabel belum ada, buat baru
            initTabulator(valp, idCmt, selectGudangAsal.val(), idPerusahaan);
        } else {
            // Jika tabel sudah ada, ganti parameter dan refresh data
            dtListSO.setData("/trans/item-transfer/list-so", { id_proses: valp, id_cmt: idCmt, id_gudang_asal: selectGudangAsal.val(), id_perusahaan: idPerusahaan });
        }
    }
}

const allGudangAsalOptions = [];
$('#gudang_asal option').each(function () {
    allGudangAsalOptions.push({
        id: $(this).val(),
        text: $(this).text(),
        id_perusahaan: $(this).attr('data-perusahaan') || ''
    });
});

function updateGudangAsalByPerusahaan(idPerusahaan, setDefault = false) {
    if (!$('#gudang_asal').length || allGudangAsalOptions.length === 0) return;
    const currentVal = $('#gudang_asal').val();
    const isEdit = (inpIdHeader.val() && inpIdHeader.val().length > 0);

    // Pada mode edit, jika gudang asal yang tersimpan memiliki asosiasi id_perusahaan,
    // pastikan dropdown perusahaan dan hidden id_perusahaan selaras dengan dokumen
    if (isEdit && currentVal) {
        const savedOpt = allGudangAsalOptions.find(o => o.id == currentVal);
        if (savedOpt && savedOpt.id_perusahaan && selectPerusahaan.length && selectPerusahaan.val() != savedOpt.id_perusahaan) {
            selectPerusahaan.val(savedOpt.id_perusahaan);
            $('#id_perusahaan').val(savedOpt.id_perusahaan);
            idPerusahaan = savedOpt.id_perusahaan;
        }
    }

    $('#gudang_asal').empty();
    $('#gudang_asal').append('<option value="">-- Pilih Warehouse --</option>');

    let defaultGudangId = (idPerusahaan == 2) ? "60" : "1";
    let hasMatchCurrent = false;

    allGudangAsalOptions.forEach(opt => {
        if (!opt.id) return;
        // Pada mode edit, gudang yang tersimpan di transaksi WAJIB selalu dimasukkan
        const isMatch = (!idPerusahaan || opt.id_perusahaan === "" || opt.id_perusahaan == idPerusahaan || (isEdit && opt.id == currentVal));
        if (isMatch) {
            const isSel = (!setDefault && opt.id == currentVal);
            if (isSel) hasMatchCurrent = true;
            const newOpt = new Option(opt.text, opt.id, false, isSel);
            $(newOpt).attr('data-perusahaan', opt.id_perusahaan);
            $('#gudang_asal').append(newOpt);
        }
    });

    if (setDefault || (!hasMatchCurrent && !isEdit)) {
        if (defaultGudangId && $('#gudang_asal').find(`option[value="${defaultGudangId}"]`).length > 0) {
            $('#gudang_asal').val(defaultGudangId).trigger('change');
        } else {
            const firstValid = $('#gudang_asal').find('option:not([disabled]):not([value=""])').first().val();
            if (firstValid) $('#gudang_asal').val(firstValid).trigger('change');
        }
    } else {
        if (currentVal && hasMatchCurrent) {
            $('#gudang_asal').val(currentVal);
        }
        $('#gudang_asal').trigger('change');
    }
}

if (selectPerusahaan.length) {
    if (inpIdHeader.val().length === 0) {
        updateGudangAsalByPerusahaan(selectPerusahaan.val(), true);
    } else {
        updateGudangAsalByPerusahaan(selectPerusahaan.val(), false);
    }

    selectPerusahaan.on('change', function () {
        const idPrsh = $(this).val();
        $('#id_perusahaan').val(idPrsh);
        updateGudangAsalByPerusahaan(idPrsh, inpIdHeader.val().length === 0);
        if (dtListSO) {
            dtListSO.setData("/trans/item-transfer/list-so", {
                id_proses: $('#select_proses').val(),
                id_cmt: selectCMT.val(),
                id_gudang_asal: selectGudangAsal.val(),
                id_perusahaan: idPrsh
            });
        }
    });
}

$('#gudang_asal').on('change', function () {
    let opt = $(this).find('option:selected');
    let p = opt.attr('data-perusahaan');
    if (p) {
        $('#id_perusahaan').val(p);
        if (selectPerusahaan.length && selectPerusahaan.val() != p) {
            selectPerusahaan.val(p);
        }
    }
});

$('#select_proses, #select_cmt, #gudang_asal, #id_gudang_asal, #id_perusahaan').on('change', function () {
    let val = $('#select_proses').val();
    let idCmt = selectCMT.val();
    let idGudangAsal = selectGudangAsal.val();
    let idPerusahaan = $('#id_perusahaan').val();

    if (val !== "") {
        if (dtListSO === null) {
            // Jika tabel belum ada, buat baru
            initTabulator(val, idCmt, idGudangAsal, idPerusahaan);
        } else {
            // Jika tabel sudah ada, ganti parameter dan refresh data
            dtListSO.setData("/trans/item-transfer/list-so", { id_proses: val, id_cmt: idCmt, id_gudang_asal: idGudangAsal, id_perusahaan: idPerusahaan });
        }
    }
});

function initTabulator(idProses, idCmt, idGudangAsal, idPerusahaan) {
    dtListSO = new Tabulator("#dt-list-sample", {
        ajaxURL: "/trans/item-transfer/list-so",
        ajaxConfig: "POST",
        // Mengirimkan id_proses, id_cmt, id_gudang_asal, dan id_perusahaan ke server
        ajaxParams: {
            id_proses: idProses,
            id_cmt: idCmt || selectCMT.val(),
            id_gudang_asal: idGudangAsal || selectGudangAsal.val(),
            id_perusahaan: idPerusahaan || $('#id_perusahaan').val()
        },
        columns: [
            { title: "Style", field: "style", width: "15%" },
            { title: "Deskripsi", field: "deskripsi", width: "25%" },
            { title: "Colour", field: "color", width: "30%" },
            { title: "Qty Ref", field: "qty_ref", width: "15%" },
            { title: "Ukuran", field: "kode_ukuran", width: "15%" },
        ],
        groupBy: ['kode_sales_order'],
        locale: 'id',
        sortMode: "remote",
        filterMode: "remote",
        placeholder: "Tidak ada data",
        selectableRows: true,
        pagination: true,
        paginationMode: "remote",
        paginationSize: 25,
        paginationButtonCount: 10,

        ajaxRequesting: function (url, params) {
            $(".preloader").css("opacity", "0.7").show();
            params.start = params.size * (params.page - 1);
            params.length = params.size;
            let idProses = $('#select_proses').val();

            // 2. Masukkan ke dalam parameter request
            params.id_proses = idProses;
            params.id_cmt = $('#select_cmt').val();
            params.id_gudang_asal = selectGudangAsal.val();
            params.id_perusahaan = $('#id_perusahaan').val();
        },

        ajaxResponse: function (url, params, response) {
            $(".preloader").hide().css("opacity", "1");
            let pageSize = dtListSO.getPageSize();
            let pageNo = dtListSO.getPage();
            let startRow = (pageSize * (pageNo - 1)) + 1;
            let endRow = response.data.length + startRow - 1;

            if (response.data.length === 0) {
                startRow = 0; endRow = 0;
            }

            let recordsFiltered = parseInt(response.recordsFiltered) || 0;
            let recordsTotal = parseInt(response.recordsTotal) || 0;

            // Update Text Footer
            $("#table-footer .tabulator-startrow").text(startRow);
            $("#table-footer .tabulator-endrow").text(endRow);
            $("#table-footer .tabulator-totalrow").text(recordsFiltered);

            let elTotalFilteredRow = $("#table-footer .tabulator-totalfilteredrow");
            elTotalFilteredRow.text("");
            if (recordsTotal > recordsFiltered) {
                elTotalFilteredRow.text(" (disaring dari " + recordsTotal + " entri)");
            }
            return response;
        },

        footerElement: '<div id="table-footer" class="pull-left tabulator-info">'
            + 'Menampilkan <span class="tabulator-startrow"></span> - <span class="tabulator-endrow"></span> dari '
            + '<span class="tabulator-totalrow"></span> entri<span class="tabulator-totalfilteredrow"></span></div>',
    });

    dtListSO.on("rowClick", function (e, row) {
        if (dtList.getData().some(x => x.kode_sales_order == row.getData().kode_sales_order && x.color == row.getData().color && x.kode_ukuran == row.getData().kode_ukuran)) {
            return Swal.fire({
                text: `SO ${row.getData().kode_sales_order} dengan warna ${row.getData().color} dan ukuran ${row.getData().kode_ukuran} telah dipilih`,
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        let rData = Object.assign({}, row.getData());
        rData.is_reject = 0;
        dtList.addRow(rData);
        $("#modal-so").modal("hide");
    })
}

// dtListSample.on("rowClick", function(e, row){
//     if(dtList.getData().some(x=>x.id == row.getData().id)){
//         return Swal.fire({
//             text: `SO ${row.getData().kode_sales_order} telah dipilih`,
//             icon: 'error',
//             showConfirmButton: false,
//             timer: 2000
//         });
//     }
//     dtList.addRow(row.getData())
//     $("#modal-so").modal("hide");
// })

// function getDetail(kodeOrder) {
//     $.ajax({
//         url: `/trans/sales-order/view`,
//         type: 'GET',
//         data:{kodeOrder:kodeOrder},
//         dataType: 'json', 
//         success: function(data) {
//             drawTableRefSO(data.ukuran,data.data)
//         },
//         error: function(xhr, status, error) {
//             console.error('Error fetching data:', error);
//         }
//     });
// }

// function drawTableRefSO(ukuran,detail){
//     let isColumn = [
//         {headerSort: false,title:"No", field:"no",   width: "5%"},
//         {headerSort: false, cssClass: 'text-start', title:"Colour", field:"colordasar"}
//     ]

//     for (const el of ukuran) {
//         const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
//         isColumn.push( {headerSort: false,  title:el.kode_ukuran, field: isKey, cssClass: "text-center", hozAlign:"center", width:"7%"} )
//     }

//     isColumn.push(
//         {
//             headerSort: false, cssClass: 'text-center', title:"Amount", field:"total_harga",formatter: "money", 
//             formatterParams: {
//                 decimal: ",",
//                 thousand: ".",
//                 symbol: "Rp",  // Simbol mata uang Rupiah
//                 precision: 0,   // Tidak ada desimal
//             },
//             hozAlign:"right", cssClass: 'text-end', width:"15%"})

//     new Tabulator(`#dt-list-sample`, {
//         data: detail, 
//         layout:"fitColumns",
//         resizableColumnFit:true,
//         pagination: true, 
//         paginationSize: 10,
//         paginationButtonCount: 5,
//         columns: isColumn,
//     });


// }




selectProses.on("change", function () {
    if (dtList.getData().length > 0) {
        Swal.fire({
            title: "Mengganti proses akan menghapus data SO yang sudah dipilih. Lanjutkan?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, lanjutkan",
            cancelButtonText: "Batal",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6C757D"
        }).then((result) => {
            if (result.isConfirmed) {
                dtList.clearData();
            } else {
                // Kembalikan ke nilai sebelumnya jika dibatalkan
                this.value = this.defaultValue || "";
            }
        });
    }
})

// function getDetail(kodeOrder) {
//     $.ajax({
//         url: `/trans/sales-order/view`,
//         type: 'GET',
//         data:{kodeOrder:kodeOrder},
//         dataType: 'json', 
//         success: function(data) {
//             drawTableRefSO(data.ukuran,data.data)
//         },
//         error: function(xhr, status, error) {
//             console.error('Error fetching data:', error);
//         }
//     });
// }

// function drawTableRefSO(ukuran,detail){
//     let isColumn = [
//         {headerSort: false,title:"No", field:"no",   width: "5%"},
//         {headerSort: false, cssClass: 'text-start', title:"Colour", field:"colordasar"}
//     ]

//     for (const el of ukuran) {
//         const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
//         isColumn.push( {headerSort: false,  title:el.kode_ukuran, field: isKey, cssClass: "text-center", hozAlign:"center", width:"7%"} )
//     }

//     isColumn.push(
//         {
//             headerSort: false, cssClass: 'text-center', title:"Amount", field:"total_harga",formatter: "money", 
//             formatterParams: {
//                 decimal: ",",
//                 thousand: ".",
//                 symbol: "Rp",  // Simbol mata uang Rupiah
//                 precision: 0,   // Tidak ada desimal
//             },
//             hozAlign:"right", cssClass: 'text-end', width:"15%"})

//     new Tabulator(`#dt-list-sample`, {
//         data: detail, 
//         layout:"fitColumns",
//         resizableColumnFit:true,
//         pagination: true, 
//         paginationSize: 10,
//         paginationButtonCount: 5,
//         columns: isColumn,
//     });


// }


function simpanData(status, isPrint = false, forceContinue = 0) {
    if (!selectGudangAsal.val() || selectGudangAsal.val() == "") {
        return Swal.fire({
            icon: 'warning',
            title: 'Gudang Asal Wajib Diisi!',
            text: 'Silakan pilih Gudang Asal (Transfer From) terlebih dahulu.'
        });
    }

    if (!selectGudangTujuan.val() || selectGudangTujuan.val() == "") {
        return Swal.fire({
            icon: 'warning',
            title: 'Gudang Tujuan Wajib Diisi!',
            text: 'Silakan pilih Gudang Tujuan (Transfer To) terlebih dahulu.'
        });
    }

    let soData = dtList.getData();
    let detailData = dtListDetail.getData();
    if ((!soData || soData.length === 0) && (!detailData || detailData.length === 0)) {
        return Swal.fire({
            icon: 'warning',
            title: 'Item Transfer Kosong!',
            text: 'Dokumen transfer tidak boleh disimpan tanpa rincian item. Tambahkan item terlebih dahulu.'
        });
    }

    let dataSOCheck = soData;
    // Validasi Qty > Qty Ref HANYA berlaku saat tombol Approve (status == 1) ditekan.
    // Simpan Draft (status == 0) tetap diizinkan.
    if (status == 1) {
        for (let i = 0; i < dataSOCheck.length; i++) {
            let row = dataSOCheck[i];
            if (row.is_reject == 1 || row.is_reject == "1" || row.is_reject === true) continue; // Bypass validasi untuk barang Reject (Opsi 1)
            let val = parseFloat(row.qty) || 0;
            let ref = parseFloat(row.qty_ref) || 0;
            if (val > ref) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal Meng-approve!',
                    text: `Terdapat item (${row.kode_sales_order || ''} ${row.color || ''}) dengan Qty (${val}) yang melebihi Qty Ref (${ref})! Dokumen tidak dapat di-approve, silakan simpan sebagai Draft.`,
                });
            }
        }
    }

    $(".preloader").css("opacity", "0.7").show();
    const valProduksi = inpRefProduksi.is(':checked') ? 1 : 0;

    $.ajax({
        type: 'POST',
        url: '/trans/item-transfer/save',
        data: {
            id: inpIdHeader.val(),
            id_gudang_asal: selectGudangAsal.val(),
            id_gudang_tujuan: selectGudangTujuan.val(),
            id_cmt: selectCMT.val(),
            id_proses: selectProses.val(),
            tipe: selectTipe.val(),
            tanggal: formatLocaleDate(inpTglReceive.val()),
            data: dtListDetail.getData(),
            dataSO: dtList.getData(),
            keterangan: inpKeterangan.val(),
            status: status,
            ref_produksi: valProduksi,
            statusFrom: inpStatus.val(),
            force_continue: forceContinue,

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
                    timer: 1500
                });
                Swal.close();

                if (isPrint && response.encrypted_id) {
                    window.open('/trans/item-transfer/print/' + response.encrypted_id, '_blank');
                }

                setTimeout(() => {
                    window.location.href = 'trans/item-transfer';
                }, isPrint ? 800 : 500);

            } else if (response.need_confirmation == true) {
                Swal.fire({
                    icon: 'warning',
                    title: response.title || 'Peringatan Stok Kurang!',
                    html: response.message ? response.message.replace(/\n/g, '<br>') : '',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa fa-check"></i> Ya, Tetap Lanjutkan',
                    confirmButtonColor: '#f59e0b',
                    cancelButtonText: '<i class="fa fa-times"></i> Batal',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        simpanData(status, isPrint, 1);
                    }
                });
            } else {
                Swal.fire({
                    text: response.message,
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
            $(".preloader").hide().css("opacity", "1");
        },
        error: function (e) {
            let msg = (e.responseJSON && e.responseJSON.message) ? e.responseJSON.message : (e.statusText || 'Terjadi kesalahan sistem');
            Swal.close();

            Swal.fire({
                text: msg,
                icon: 'error',
                showConfirmButton: false,
                timer: 3000
            });
            $(".preloader").hide().css("opacity", "1");
        },
    });

}


// fungsi barcode dan autocomplete 
$("#text_barcode").autocomplete({

    source: function (request, response) {
        if (selectProses.val() == null) {
            Swal.fire({
                position: 'top-end',
                icon: 'warning',
                title: 'Harap pilih Proses dahulu!',
                showConfirmButton: false,
                timer: 2000,
            });
            return false;
        }
        //   if(operator.val().length != 0){
        $.ajax({
            url: "/trans/item-transfer/cari_produk",
            dataType: "json",
            data: {
                kata_kunci: request.term,
                id_proses: selectProses.val(),
                id_gudang_asal: selectGudangAsal.val(),
                id_gudang_tujuan: selectGudangTujuan.val(),
                id_cmt: selectCMT.val(),
                id_perusahaan: $("#id_perusahaan").val(),
                id_header: inpIdHeader.val()
            },
            type: 'post',
            success: function (data) {
                if (data.status) {
                    response(data.slc);
                } else {
                    console.log(data.msg);
                }
            }
        });
        //   }else{
        //     alert("Pilih CMT terlebih dahulu !");
        //   }
    },
    minLength: 2,
    select: function (event, ui) {
        addItem(ui.item.data);
    },
    open: function () {
        $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
    },
    close: function () {
        $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
        $("#text_barcode").val("");
    }
});



function addItem(data, isScan = false) {
    if (!data.print_type) {
        data.print_type = 1;
    }

    let index = -1;
    if (dtList.getData().some(x => x.kode_sales_order == data.kode_sales_order && x.color == data.color && x.kode_ukuran == data.kode_ukuran)) {
        index = dtList.getData().findIndex(x => x.kode_sales_order == data.kode_sales_order && x.color == data.color && x.kode_ukuran == data.kode_ukuran);
    }


    if (index > -1) {
        const row = dtList.getData();
        row[index].qty = parseFloat(row[index].qty) + parseFloat(data.qty);
        if (isScan) {
            row[index].total_scanned = parseFloat(row[index].total_scanned) + 1;
            row[index].keterangan = (parseFloat(row[index].total_scanned)).toString() + ' Ikat';
        }
        dtList.setData(row)
    } else {
        if (isScan) {
            data.total_scanned = 1;
            data.keterangan = (data.total_scanned).toString() + ' Ikat';
        }
        if (data.is_reject === undefined || data.is_reject === null) {
            data.is_reject = 0;
        }
        dtList.addRow(data)
    }

    $("#text_barcode").val("");
}


$("#text_barcode").on("keypress", function (e) {
    if (selectProses.val() == null) {
        Swal.fire({
            position: 'top-end',
            icon: 'warning',
            title: 'Harap pilih Proses dahulu!',
            showConfirmButton: false,
            timer: 2000,
        });
        return false;
    }
    let key = e.which;
    if (key == 13) {
        $.ajax({
            url: "trans/item-transfer/src_produk",
            dataType: "json",
            data: {
                kata_kunci: $("#text_barcode").val(),
                id_proses: selectProses.val(),
                id_gudang_asal: selectGudangAsal.val(),
                id_gudang_tujuan: selectGudangTujuan.val(),
                id_cmt: selectCMT.val(),
                id_perusahaan: $("#id_perusahaan").val(),
                id_header: inpIdHeader.val()
            },
            type: 'post',
            success: function (es) {
                // console.log(es)
                if (es.status) {
                    // response(data.slc);
                    if (es.data.length > 0) {
                        addItem(es.data[0], true);
                    } else {
                        toastr.error("Produk tidak ditemukan", "Gagal", {
                            positionClass: "toast-top-right"
                        });
                    }
                } else {
                    console.log(es.msg);
                }
            }
        });
    }
});

// ==========================================
// INTEGRASI TIMBANGAN DIGITAL (YAOHUA YH-T3)
// ==========================================
let serialScalePort = null;
let serialScaleReader = null;
let isScaleAutoReading = false;
let latestScaleWeight = 0;
let activeTargetRow = null;

function parseYaohuaYHT3(bufferStr) {
    let matches = bufferStr.match(/(\d+\.\d+|\d+)\s*(?:kg|g)?/gi);
    if (matches && matches.length > 0) {
        let lastMatch = matches[matches.length - 1];
        let numVal = parseFloat(lastMatch);
        if (!isNaN(numVal) && numVal > 0) {
            return numVal;
        }
    }
    return 0;
}

async function startContinuousScaleStream() {
    if (!("serial" in navigator)) {
        let msgHtml = `Browser Anda tidak mendukung Web Serial API.<br>Gunakan browser <b>Google Chrome</b> atau <b>Microsoft Edge</b> versi terbaru.`;
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            msgHtml = `
                <div class="text-start">
                    <p class="text-danger fw-bold mb-2"><i class="fa fa-exclamation-triangle"></i> Fitur Serial Port Diblokir oleh Chrome (Insecure Origin HTTP)!</p>
                    <p class="mb-2">Karena aplikasi dibuka via IP/HTTP (bukan HTTPS), Chrome membatasi akses Web Serial port timbangan.</p>
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
                if (weightVal > 0 && weightVal !== latestScaleWeight) {
                    latestScaleWeight = weightVal;
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
    const btnScale = $('#btn-read-scale-prod');
    const statusText = $('#scale-status-text-prod');

    if (isActive) {
        btnScale.removeClass("btn-primary btn-warning").addClass("btn-success").html('<i class="fa fa-circle text-white"></i> 🟢 Timbangan OTOMATIS');
        statusText.html('<span class="text-success fw-bold"><i class="fa fa-check-circle"></i> Mode Otomatis Aktif! Kursor / baris yang dipilih akan langsung terisi berat Kg dari Yaohua YH-T3.</span>');
    } else {
        btnScale.removeClass("btn-success btn-warning").addClass("btn-primary").html('<i class="fa fa-balance-scale"></i> ⚖️ Timbangan');
        if (errorMsg) {
            statusText.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Gagal: ' + errorMsg + '</span>');
        } else {
            statusText.html('Ambil nilai Kg dari Timbangan Yaohua YH-T3 untuk kolom Berat (Kg) baris yang dipilih / terakhir');
        }
    }
}

function applyAutoWeightToActiveTarget(weightVal) {
    if (!weightVal || weightVal <= 0) return;

    if (typeof dtList !== 'undefined' && dtList) {
        let selectedRows = dtList.getSelectedRows();
        let targetRow = null;
        if (selectedRows.length > 0) {
            targetRow = selectedRows[0];
        } else if (activeTargetRow) {
            targetRow = activeTargetRow;
        }

        if (targetRow) {
            targetRow.update({ berat: weightVal });
        }
    }
}

if (typeof dtList !== 'undefined' && dtList) {
    dtList.on("rowClick", function (e, row) {
        activeTargetRow = row;
        if (latestScaleWeight > 0) {
            row.update({ berat: latestScaleWeight });
        }
    });

    dtList.on("cellClick", function (e, cell) {
        let row = cell.getRow();
        activeTargetRow = row;
        if (cell.getField() === 'berat' && latestScaleWeight > 0) {
            row.update({ berat: latestScaleWeight });
        }
    });
}

$(document).on('click', '#btn-read-scale-prod', function () {
    if (!isScaleAutoReading) {
        startContinuousScaleStream();
    } else {
        if (latestScaleWeight > 0) {
            applyAutoWeightToActiveTarget(latestScaleWeight);
            Swal.fire({
                icon: 'success',
                title: 'Berat Terisi!',
                text: `Berat dari YH-T3: ${latestScaleWeight} Kg`,
                timer: 1200,
                showConfirmButton: false
            });
        }
    }
});