let spanPoNo = $('#spanPoNo');
let inpPoNo = $('#poNo');
let inpIdPo = $('#idPo');
let inpTglShip = $('#tgl_ship');
let inpTglReceive = $('#tgl_receive');
let inpNamaVendor = $('#nama_vendor');
let inpBarang = $('#namaBarang');
let inpIdBarang = $('#idBarang');
let inpKodeBarang = $('#kodeBarang');
let inpQtyPO = $('#qty_po');
let inpQtyItem = $('#qty_item');
let inpPrice = $('#price');
let inpUnit = $('#unit');
let inpEdit = $('#edit');
let inpFormNo = $('#form_no');
let spanBarang = $('#spanBarang');
let selectGudang = $('#select_warehouse');
let inpLotNo = $('#lot_no');
let selectPack = $('#pack_id');
let detailData = $("#data-details").val().replace(/&quot;/ig, '"');
let btnAdd = $('#btn-add');
let btnSimpan = $('#btn-simpan');
let btnApprove = $('#btn-approve');
let btnSimpanDetail = $('#btn-simpan-det');
let modalDet = $('#modal-detail-item');
let inpStatus = $('#status');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');
let inpGudangUtamaId = $('#gudang_utama_id');
let inpGudangUtamaNama = $('#gudang_utama_nama');

if (inpStatus.val() == 0) {
    btnAdd.show()
    btnSimpan.show()
    btnApprove.show()
} else {
    btnAdd.hide()
    btnSimpan.hide()
    btnApprove.hide()
}
// const regex = /^[0-9]+(\.[0-9]+)?$/; // Hanya angka dan desimal
// Angka, maksimal 2 desimal (permintaan user - qty sebelumnya bisa diisi
// desimal sebanyak apapun, jadi hasil penjumlahan di kolom QTY suka
// "kotor" seperti 293.09999999999997).
const regex = /^[0-9]+([.,][0-9]{1,2})?$/;
let dtList = new Tabulator("#dt-list", {
    columns: [
        {
            field: "id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "PO No.", field: "po_no", headerSort: false,
            width: "30%"
        },
        {
            title: "Ship Date", field: "date_exc", headerSort: false,
            width: "30%"
        },
        {
            title: "Nama Vendor", field: "nama_vendor", headerSort: false,
            width: "40%"
        }
    ],
    locale: 'id',
    ajaxURL: "/purchasing/purchase-order/list",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    selectableRows: true,
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.isReceive = true;
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
    selectable: false,
});


let searchThread = null;
let elSearch = $("#tb-search2");
if (elSearch != null) {
    elSearch.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return;
        }
        clearTimeout(searchThread);
        searchThread = setTimeout(function () {
            dtList.setFilter("", "like", elSearch.val());
        }, 600);
    });
}

dtList.on("rowClick", function (e, row) {
    var namaVendor = row._row.data.nama_vendor.replace(/<[^>]*>/g, '');
    var poNo = row._row.data.po_no.replace(/<[^>]*>/g, '');
    var ShipDate = row._row.data.date_exc.replace(/<[^>]*>/g, '');
    var idPo = row._row.data.id;

    var lanjutkan = function () {
        inpPoNo.val(poNo)
        inpIdPo.val(idPo)
        inpNamaVendor.val(namaVendor)
        inpTglShip.val(formatterDate(ShipDate))
        $("#modal-po").modal("hide");
        loadDataPo(idPo);
    };

    // Kalau baris detail sudah ada isinya (mis. tambah manual sebelumnya atau
    // ganti PO), auto-isi akan MENGGANTI seluruh baris - konfirmasi dulu
    // supaya tidak ada data yang hilang tanpa sengaja.
    if (dtListDetail.getData().length > 0) {
        $("#modal-po").modal("hide");
        Swal.fire({
            title: "Ganti PO akan mengganti seluruh baris detail yang sudah ada. Lanjutkan?",
            icon: 'question',
            confirmButtonText: 'Lanjutkan',
            confirmButtonColor: '#198754',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6C757D'
        }).then((result) => {
            if (result.isConfirmed) {
                lanjutkan();
            }
        });
    } else {
        lanjutkan();
    }
})

// Ambil baris PO (item code, description, qty sisa, unit, price) dan isi
// langsung ke tabel detail - menggantikan alur tambah manual satu-per-satu.
// Warehouse default "Gudang Utama" (bisa diubah), Lot No & Pack dikosongkan
// karena tidak ada di data PO - dilengkapi user lewat tombol edit per baris.
function loadDataPo(idPo) {
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
            Swal.showLoading();
        }
    });
    $.ajax({
        url: `/purchasing/receive-item/data-po?idPo=${idPo}`,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            Swal.close();
            if (data.status) {
                let cleanedData = data.data.map(item => {
                    let q = parseFloat(item.qty);
                    if (!isNaN(q)) item.qty = Math.round(q * 100) / 100;
                    let qr = parseFloat(item.qty_receive);
                    if (!isNaN(qr)) item.qty_receive = Math.round(qr * 100) / 100;
                    return item;
                });
                dtListDetail.setData(cleanedData);
            }
        },
        error: function (xhr, status, error) {
            Swal.close();
            console.error('Error fetching data PO:', error);
        }
    });
}

let dtListBarang = new Tabulator("#dt-list-barang", {
    columns: [
        {
            title: "ID Barang", field: "id", headerSort: false,
            width: "15%", visible: false
        },
        {
            title: "Kode Barang", field: "kode_barang", headerSort: false,
            width: "25%"
        },
        {
            title: "Nama Barang", field: "nama_barang", headerSort: false,
            width: "35%"
        },
        {
            title: "Satuan", field: "nama_satuan", headerSort: false,
            width: "10%"
        },
        {
            title: "Qty PO", field: "qty", headerSort: false,
            width: "15%", hozAlign: "right",
        },
        {
            title: "Qty Receive", field: "qty_receive", headerSort: false,
            width: "15%", hozAlign: "right",
        },
        {
            title: "Price", field: "price", headerSort: false,
            width: "15%", hozAlign: "right", formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            },
        },
    ],
    locale: 'id',
    ajaxURL: "/purchasing/receive-item/list-barang",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    selectableRows: true,
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.idHeader = inpIdPo.val()
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

dtListBarang.on("rowClick", function (e, row) {

    var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    var idBarang = row._row.data.id;
    var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    var namaSatuan = row._row.data.nama_satuan.replace(/<[^>]*>/g, '');
    let q = parseFloat(row._row.data.qty);
    var qty = !isNaN(q) ? Math.round(q * 100) / 100 : row._row.data.qty;
    var price = row._row.data.price;
    // if (dtListDetail.getData().some(x => x.id_barang == idBarang)) {
    //     return Swal.fire({
    //         text: `Barang ${namaBarang} sudah dipilih`,
    //         icon: 'error',
    //         showConfirmButton: false,
    //         timer: 2000
    //     });
    // }

    inpUnit.val(namaSatuan)
    inpIdBarang.val(idBarang)
    inpKodeBarang.val(kodeBarang)
    inpBarang.val(`${namaBarang}`)
    // inpPrice.val(`${formatRupiah(price.toString())}`)
    inpPrice.val(`${price}`)
    inpQtyPO.val(qty)
    inpLotNo.val("")
    $("#modal-barang").modal("hide");
})


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


let buttonRowAction = function (cell) {
    let fmBtnDelete = "";
    let fmBtnEdit = "";

    if (inpStatus.val() == 0) {
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;
    }


    return fmBtnEdit + " " + fmBtnDelete;
};

let customSumQtyReceive = function (values, data, calcParams) {
    let sum = 0;
    values.forEach(function (val) {
        let num = parseFloat(val);
        if (!isNaN(num)) {
            sum += num;
        }
    });
    return sum > 0 ? (Math.round(sum * 100) / 100).toString() : "0";
};

let customSumPackReceive = function (values, data, calcParams) {
    let count = 0;
    values.forEach(function (val) {
        if (val && val.toString().trim() !== "" && val !== "-") {
            count++;
        }
    });
    return count > 0 ? count + " PACK" : "0 PACK";
};

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
        { field: "qty_receive", visible: false },
        { field: "nama_unit", visible: false },
        {
            headerSort: false,
            title: '#',
            formatter: buttonRowAction,
            width: '10%', align: "center", cssClass: "text-center",
            bottomCalc: function () { return "TOTAL"; },
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
            title: "QTY", field: "qty", hozAlign: "center", width: "10%", bottomCalc: customSumQtyReceive,
            // Tampilan dibulatkan 2 desimal - dokumen lama datanya bisa masih
            // "kotor" (mis. 293.09999999999997) walau input baru sudah dibatasi.
            formatter: function (cell) {
                let val = parseFloat(cell.getValue());
                return isNaN(val) ? cell.getValue() : val.toFixed(2);
            }
        },
        { title: "UNIT", field: "nama_unit", hozAlign: "center", width: "10%" },
        {
            title: "PRICE", field: "price", formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, width: "10%"
        },
        { title: "WAREHOUSE", field: "nama_gudang", hozAlign: "center", width: "20%" },
        { title: "LOT NO", width: "10%", field: "lot_no", hozAlign: "left" },
        { title: "Pack", width: "10%", field: "pack_name", hozAlign: "left", bottomCalc: customSumPackReceive },
    ],
    locale: 'id',
    placeholder: "Tidak ada data",
});

if (detailData.length > 0) {
    setTimeout(() => {
        try {
            let isdata = JSON.parse(detailData);
            isdata = isdata.map(item => {
                let q = parseFloat(item.qty);
                if (!isNaN(q)) item.qty = Math.round(q * 100) / 100;
                let qr = parseFloat(item.qty_receive);
                if (!isNaN(qr)) item.qty_receive = Math.round(qr * 100) / 100;
                return item;
            });
            dtListDetail.setData(isdata);
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
}

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

function isDuplicateCombination(lotNo, packId, gudangId, excludeLot = null) {
    return dtListDetail.getData().some(x => 
        x.lot_no == lotNo && 
        x.pack_id == packId && 
        x.id_barang == inpIdBarang.val() &&
        x.id_gudang == gudangId &&
        excludeLot != lotNo
    );
}

inpLotNo.on("change", function (e) {
    let lotNo = inpLotNo.val().trim();
    let packId = selectPack.val();
    let gudangId = selectGudang.val();

    if (!lotNo) return;

    // cek kombinasi barang + lot + pack + gudang di grid tabel
    if (packId && gudangId && isDuplicateCombination(lotNo, packId, gudangId, inpEdit.val())) {
        e.target.value = "";
        return Swal.fire({
            text: `Kombinasi Barang, Lot No ${lotNo}, Pack, dan Gudang sudah ada di tabel`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (packId) {
        checkLotNo(lotNo);
    }
});

selectPack.change(function () {
    let lotNo = inpLotNo.val();
    let packId = selectPack.val();
    let gudangId = selectGudang.val();

    if (!lotNo) return;

    // cek kombinasi barang + lot + pack + gudang
    if (gudangId && isDuplicateCombination(lotNo, packId, gudangId, inpEdit.val())) {
        $(this).val(null).trigger('change');
        return Swal.fire({
            text: `Kombinasi Barang, Lot No ${lotNo}, Pack, dan Gudang sudah ada`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    // Di form ini Lot No diisi SEBELUM Pack, jadi saat checkLotNo() pertama
    // kali jalan (dari keyup Lot No), pack_id belum ada nilainya. Cek ulang
    // ke server di sini supaya validasi "lot dipakai barang lain" ikut
    // mempertimbangkan pack yang baru saja dipilih.
    checkLotNo(lotNo);
});

selectGudang.change(function () {
    let lotNo = inpLotNo.val();
    let packId = selectPack.val();
    let gudangId = selectGudang.val();

    if (!lotNo || !packId) return;

    // cek kombinasi barang + lot + pack + gudang
    if (isDuplicateCombination(lotNo, packId, gudangId, inpEdit.val())) {
        $(this).val(null).trigger('change');
        return Swal.fire({
            text: `Kombinasi Barang, Lot No ${lotNo}, Pack, dan Gudang sudah ada`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
});

inpQtyItem.on("input", function (e) {
    if (inpBarang.val().length === 0) {
        $(this).val("");
        return Swal.fire({
            text: `Barang harus dipilih dahulu.`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    // Ganti koma jadi titik dan hanya izinkan angka + titik
    let val = $(this).val().replace(',', '.');
    val = val.replace(/[^0-9.]/g, '');
    let parts = val.split('.');
    if (parts.length > 2) {
        val = parts[0] + '.' + parts.slice(1).join('');
    }
    // Batasi maksimal 2 desimal di belakang titik secara halus
    if (parts.length === 2 && parts[1].length > 2) {
        val = parts[0] + '.' + parts[1].substring(0, 2);
    }
    $(this).val(val);
});

spanPoNo.click(function () {
    setTimeout(() => {
        dtList.redraw(true)
    }, 500);
    $("#modal-po").modal("show");
    dtList.deselectRow();
});
spanBarang.click(function () {
    Swal.showLoading();
    dtListBarang.replaceData()
    if (inpPoNo.val().length == 0) {
        return Swal.fire({
            text: "PO No. belum dipilih",
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

// Dipanggil sebelum Simpan/Approve dokumen - mengecek SEMUA baris detail
// sekaligus, karena setelah auto-isi dari PO banyak baris bisa kosong
// Lot No/Pack secara bersamaan (baru dilengkapi user satu-satu lewat tombol
// edit). Mengembalikan pesan error baris pertama yang bermasalah, atau null
// kalau semua baris valid.
function validateDetailRows() {
    let rows = dtListDetail.getData();
    for (let i = 0; i < rows.length; i++) {
        let r = rows[i];
        let namaBrg = r.nama_barang || `baris ke-${i + 1}`;

        let qty = Math.round(parseFloat(r.qty) * 100) / 100;
        let qtySisa = Math.round(parseFloat(r.qty_receive) * 100) / 100;
        if (!isNaN(qtySisa) && !isNaN(qty) && qty > qtySisa) {
            return `Quantity ${namaBrg} tidak boleh melebihi qty sisa PO (${qtySisa})`;
        }

        if (!r.id_gudang) {
            return `Warehouse pada ${namaBrg} belum terisi`;
        }

        if (!r.lot_no || r.lot_no.toString().trim().length == 0) {
            return `Lot No. pada ${namaBrg} belum diisi`;
        }

        if (!r.pack_id) {
            return `Pack pada ${namaBrg} belum dipilih`;
        }
    }
    return null;
}

function submitData(status, message) {
    if (inpTglReceive.val().length == 0) {
        return Swal.fire({
            text: "Receive Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (inpPoNo.val().length == 0) {
        return Swal.fire({
            text: "PO No. harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (dtListDetail.getData().length == 0) {
        return Swal.fire({
            text: "Data detail tidak boleh kosong",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    let errDetail = validateDetailRows();
    if (errDetail) {
        return Swal.fire({
            text: errDetail,
            icon: 'error',
            showConfirmButton: false,
            timer: 2500
        });
    }

    Swal.fire({
        title: `Apakah anda ingin ${message} data Receive Item?`,
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


btnAdd.click(function () {
    openModalDetail()
})

function checkLotNo(value) {
    let lotNo = (value || '').toString().trim();
    let packId = selectPack.val();
    let idBarang = inpIdBarang.val();

    if (!lotNo || !packId || !idBarang) return;

    $.ajax({
        url: `/purchasing/receive-item/check-lot?id_barang=${encodeURIComponent(idBarang)}&lot_no=${encodeURIComponent(lotNo)}&pack_id=${encodeURIComponent(packId)}`,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                inpLotNo.val('');
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
        // inpEdit (lot_no asli baris ini, dipakai utk mengecualikan baris sendiri
        // dari cek duplikat) HARUS diisi duluan, sebelum Gudang/Pack di-trigger
        // change di bawah - sebelumnya diisi paling akhir, jadi saat validasi
        // duplikat jalan (dipicu trigger change Pack), inpEdit masih kosong/nilai
        // lama, dan baris yang sedang diedit malah dianggap duplikat dgn dirinya
        // sendiri walau belum ada yang diubah.
        inpEdit.val(data.lot_no)
        inpBarang.val(data.nama_barang)
        inpIdBarang.val(data.id_barang)
        inpUnit.val(data.nama_unit)
        let numQty = parseFloat(data.qty);
        if (!isNaN(numQty)) {
            inpQtyItem.val(Math.round(numQty * 100) / 100);
        } else {
            inpQtyItem.val(data.qty);
        }
        // inpPrice.val(formatRupiah(data.price.toString()))
        inpPrice.val(data.price)
        selectGudang.val(data.id_gudang).trigger("change")
        inpLotNo.val(data.lot_no)
        selectPack.val(data.pack_id).trigger("change")
        // qty_receive dipakai sbg referensi qty sisa PO (lihat loadDataPo &
        // btnSimpanDetail) - dipulihkan di sini supaya validasi qty saat edit
        // tetap membandingkan ke sisa PO baris ini, bukan sisa item lain.
        let numSisa = parseFloat(data.qty_receive);
        if (!isNaN(numSisa)) {
            inpQtyPO.val(Math.round(numSisa * 100) / 100);
        } else {
            inpQtyPO.val(data.qty_receive);
        }
    } else {
        inpBarang.val("")
        inpIdBarang.val("")
        inpUnit.val("")
        inpQtyItem.val("")
        inpPrice.val("")
        inpLotNo.val("")
        inpEdit.val("")
        inpQtyPO.val("")
        selectPack.val("").trigger("change")
        // Default Warehouse ke Gudang Utama utk baris baru (bisa diubah user).
        selectGudang.val(inpGudangUtamaId.val() || "").trigger("change")
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

        let valRaw = inpQtyItem.val().toString().replace(',', '.').trim();
        if (valRaw.length == 0 || parseFloat(valRaw) <= 0 || isNaN(parseFloat(valRaw))) {
            return Swal.fire({
                text: "Quantity tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // Bulatkan ke 2 desimal bersih (permintaan user: maksimal 2 desimal)
        let qtyDiedit = Math.round(parseFloat(valRaw) * 100) / 100;
        if (isNaN(qtyDiedit) || qtyDiedit <= 0) {
            return Swal.fire({
                text: "Quantity harus berupa angka valid",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // Qty tidak boleh melebihi sisa PO (qty PO - qty yg sudah diterima).
        let qtySisaPo = Math.round(parseFloat(inpQtyPO.val().toString().replace(',', '.')) * 100) / 100;
        if (!isNaN(qtySisaPo) && qtyDiedit > qtySisaPo) {
            return Swal.fire({
                text: `Quantity tidak boleh melebihi qty sisa PO (${qtySisaPo})`,
                icon: 'error',
                showConfirmButton: false,
                timer: 2500
            });
        }

        if (selectGudang.val().length == 0) {
            return Swal.fire({
                text: "Warehouse harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (inpLotNo.val().trim().length == 0) {
            return Swal.fire({
                text: "Lot No. harus diisi",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (selectPack.val().length == 0) {
            return Swal.fire({
                text: "Pack harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // Sebelumnya di sini ada pengecekan duplikat terpisah yang selalu error
        // (memakai variabel inpLoNo yang tidak pernah dideklarasikan, dan nama
        // field row.gudang/row.lo_no/row.pack yang tidak sesuai data asli
        // id_gudang/lot_no/pack_id) - jadi akan crash begitu baris ke-2 dst
        // disimpan. Diganti pakai isDuplicateCombination() yang sudah benar
        // dan dipakai juga di listener Lot No/Pack/Gudang di atas.
        if (isDuplicateCombination(inpLotNo.val(), selectPack.val(), selectGudang.val(), inpEdit.val())) {
            return Swal.fire({
                text: "Kombinasi Barang, Lot No, Pack, dan Gudang yang sama sudah ada",
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
                qty: qtyDiedit,
                price: price,
                id_gudang: selectGudang.val(),
                qty_receive: inpQtyPO.val(),
                nama_gudang: $('#select_warehouse option:selected').text(),
                lot_no: inpLotNo.val(),
                pack_id: selectPack.val(),
                pack_name: selectPack.find('option:selected').text(),
                nama_unit: inpUnit.val(),
            });
        } else {

            dtListDetail.addRow({
                id: null,
                nama_barang: inpBarang.val(),
                kode_barang: inpKodeBarang.val(),
                id_barang: inpIdBarang.val(),
                id_gudang: selectGudang.val(),
                price: price,
                qty_receive: inpQtyPO.val(),
                nama_gudang: $('#select_warehouse option:selected').text(),
                qty: qtyDiedit,
                lot_no: inpLotNo.val(),
                pack_id: selectPack.val(),
                pack_name: selectPack.find('option:selected').text(),
                nama_unit: inpUnit.val(),
            });
        }
        modalDet.modal("hide")

    })

}


function simpanData(status) {
    // Dibulatkan 2 desimal - penjumlahan float di JS bisa hasilkan angka
    // "kotor" (mis. 293.09999999999997) walau tiap baris qty-nya sudah rapi.
    let totalQty = Math.round(dtListDetail.getData().reduce((sum, item) => sum + parseFloat(item.qty), 0) * 100) / 100;
    $.ajax({
        type: 'POST',
        url: '/purchasing/receive-item/save',
        data: {
            id: inpIdHeader.val(),
            id_po: inpIdPo.val(),
            rec_date: formatLocaleDate(inpTglShip.val()),
            receive_date: formatLocaleDate(inpTglReceive.val()),
            qty: totalQty,
            namaVendor: inpNamaVendor.val(),
            form_no: inpFormNo.val(),
            data: dtListDetail.getData(),
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
                window.location.href = 'purchasing/receive-item'

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
            let msg = e.responseJSON.message;
            Swal.close();

            Swal.fire({
                text: msg,
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        },
    });

}


