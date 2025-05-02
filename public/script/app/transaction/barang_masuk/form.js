
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
let selectKategori = $('#select_kategori');
let inpLotNo = $('#lot_no');
let detailData = $("#data-details").val().replace(/&quot;/ig,'"');
let dataSO = $("#data-so").val().replace(/&quot;/ig,'"');
let btnAdd = $('#btn-add');
let btnSimpan = $('#btn-simpan');
let btnApprove = $('#btn-approve');
let btnSimpanDetail = $('#btn-simpan-det');
let btnView = $('#ic_ref_transfer');
let inpNoRefTrf = $('#no_ref_transfer');
const inpIdRefTrf = $("#id_ref_transfer");
let modalDet = $('#modal-detail-item');
let inpStatus = $('#status');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');

const divDetail = $(".div_detail");
const divRefProduk = $(".div_produksi");

if(inpIdHeader.val().length == 0){
    selectGudang.val("").trigger("change")
    selectKategori.val("").trigger("change")
}

setTimeout(() => {
    if(selectKategori.val() == 3){
        divNamaKonsumen.removeClass("d-none")
    }else if(selectKategori.val() == 12){
        dtList.hideColumn('amount');
        divDetail.hide();
        divRefProduk.show();
    }
}, 500);

if(inpStatus.val() == 0){
    btnAdd.show()
    btnSimpan.show()
    btnApprove.show()
} else{
    btnAdd.hide()
    btnSimpan.hide()
    btnApprove.hide()
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
            width: "15%", cssClass : 'text-center'
        },
        {
            title: "No. HP", field: "no_hp", headerSort: false,
            width: "15%", cssClass : 'text-center'
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

dtListKonsumen.on("rowClick", function(e, row){
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
            width: "15%",visible:false
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


dtListBarang.on("rowClick", function(e, row){

    if(selectKategori.val() == null){
        return Swal.fire({
            text: "Kategori harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    
    if(selectGudang.val() == 0){
        return Swal.fire({
            text: "Gudang Tujuan harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    
    if(dtListDetail.getData().some(x=>x.id_barang == idBarang)){
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
        {title: "ID", field: "id", width: "20%",visible:false},
        {
            title: 'TRANSACTION NO.', field: 'kode_transaksi', headerSort:false, sorter: 'string',
            width: "15%", formatter : "html"
        }, 
            
        {
            title: 'DATE', field: 'tanggal', headerSort:false, sorter: 'string',
            width: "15%"
        }, 
    
        {
            title: 'TRANSFER FORM', field: 'gudang_asal', formatter : "html", align: "center", cssClass: "text-center", headerSort:false,
            width: "20%", 
        } ,
        {
            title: 'TRANSFER TO', field: 'gudang_tujuan', formatter : "html", align: "center", cssClass: "text-center", headerSort:false,
            width: "20%", 
        } ,
        {
            title: 'CMT', field: 'nama_operator', formatter : "html", align: "center", cssClass: "text-center", headerSort:false,
            width: "20%", 
        } ,
        {
            title: 'STATUS', field: 'status', formatter : "html", align: "center", headerSort:false,
            width: "15%",hozAlign:"center",
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

const inpProses = $("#proses");
const inpIdproses = $("#id_proses");

const inpOperator = $("#nama_operator");
const inpIdCmt = $("#id_cmt");


let refData = [];
dtListSO.on("rowClick", function(e, row){
        const xdata = row.getData();
        
        // console.log("data", xdata)

        inpNoRefTrf.val(xdata.kode_transaksi)
        inpIdRefTrf.val(xdata.id)
        inpProses.val(xdata.proses);
        inpIdproses.val(xdata.id_proses);

        inpOperator.val(xdata.nama_operator);
        inpIdCmt.val(xdata.id_cmt);

        setTimeout(() => {
            loadDataSo();
        }, 500);
       

    $("#modal-so").modal("hide");
})


// load data som
function loadDataSo() { 
    const kode_transaksi = inpNoRefTrf.val();
    $.ajax({
        url: `/trans/item-transfer/data-so?noSO=${kode_transaksi}`,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            
            if(data.status){
                if(selectKategori.val() != 12){
                    refData = [];
                    dtList.setData(data.dataSO);
                    dtListProduksi.setData(data.dataSO);
                }else{
                    refData = data.dataSO
                }
            }
           
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

setTimeout(() => {
    if(selectKategori.val() == 12){
        loadDataSo();
    }
}, 500);


let buttonRowAction = function(cell) {
    let fmBtnDelete = "";        
    let fmBtnEdit = "";        

    if (inpStatus.val() == 0){
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;
    }
   

    return fmBtnEdit + " " + fmBtnDelete;
};

let buttonRowSOAction = function(cell) {
    let fmBtnDelete = "";        
    let fmBtnEdit = "";        

    if (inpStatus.val() == 0){
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
    }
   

    return fmBtnEdit + " " + fmBtnDelete;
};

let dtListDetail = new Tabulator("#dt-list-detail", {
    pagination: true, 
    paginationSize: 10,
    paginationButtonCount: 5,
    columns:[
        {field:"id", visible:false},
        {field:"isEdit", visible:false},
        {field:"id_barang", visible:false},
        {field:"id_header", visible:false},
        {field:"qty_receive", visible:false},
        {field:"nama_unit", visible:false},
        {
            headerSort: false,  
            title: '#', 
            formatter: buttonRowAction,
            width: '10%', align: "center", cssClass: "text-center",
            cellClick: function(e, cell) {
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
                } else if(e.target.title === 'edit'){
                
                    openModalDetail(row)
                    
                } 
            
            }
        },
        {title:"ITEM CODE", field:"kode_barang",hozAlign:"left", width:"15%"},
        {title:"ITEM DESCRIPTION", field:"nama_barang", hozAlign:"left",width:"25%"},
        {title:"QTY", field:"qty", hozAlign:"center",width:"10%"},
        {title:"UNIT", field:"nama_unit", hozAlign:"center",width:"15%"},
        {title:"PRICE", field:"price", formatter : "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
        },width:"10%"},
        {title:"LOT NO", width:"15%", field:"lot_no", hozAlign:"left"},
    ],
    locale: 'id',    
    // layout: 'fitColumns',
    placeholder: "Tidak ada data",
});

if(detailData.length > 0){
    setTimeout(() => {
        try {
            let isdata = JSON.parse(detailData);
            dtListDetail.setData(isdata);
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
        {title: "ID", field: "id_konsumen", width: "20%",visible:false},
        {title: "No.SO", field: "kode_sales_order", width: "20%"},
        {title: "Style", field: "style", width: "20%"},
        {title: "Deskripsi", field: "deskripsi", width: "20%"},
        {title: "Buyer", field: "buyer", width: "20%"},
        {title: "Colour", field: "color", width: "20%"},
        {title: "Qty", field: "qty", width: "20%"},
        {title: "Ukuran", field: "kode_ukuran", width: "20%"},
        {title: "Amount", field: "amount", width: "20%",formatter: "money",    formatterParams: {
            decimal: ",",
            thousand: ".",
            symbol: "Rp",  // Simbol mata uang Rupiah
            precision: 0,   // Tidak ada desimal
        }},
        // {title: "Amount", field: "amount_edit", width: "20%",formatter: "money",    formatterParams: {
        //     decimal: ",",
        //     thousand: ".",
        //     symbol: "Rp",  // Simbol mata uang Rupiah
        //     precision: 0,   // Tidak ada desimal
        // }, editor: "number"},
    ],
    placeholder: "Tidak ada data",
});

let dtListProduksi = new Tabulator("#dt-list-so-produksi", {
    pagination: true, 
    paginationSize: 10,
    paginationButtonCount: 5,
    columns: [
        {title: "ID", field: "id_konsumen", width: "20%",visible:false},
        {title: "No.SO", field: "kode_sales_order", width: "20%"},
        {title: "Style", field: "style", width: "20%"},
        {title: "Deskripsi", field: "deskripsi", width: "20%"},
        {title: "Buyer", field: "buyer", width: "20%"},
        {title: "Colour", field: "color", width: "20%"},
        {title: "Tgl Transaksi", field: "tgl_transaksi", width: "15%", sorter:"date", sorterParams:{
                format:"dd-MM-yyyy",
                alignEmptyValues:"top",
            },
            editor: "date"
        },
        {title: "Nomor Mesin", field: "nomor_mesin", width: "15%", editor: "input"},
        {title: "Jam Mesin", field: "jam_mesin", width: "15%", editor: "number"},
        {title: "Nilai Mesin", field: "nilai_mesin", width: "15%", editor: "number",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }
        },
        {title: "Qty", field: "qty_kirim", width: "15%"},
        {title: "Qty Terima", field: "qty", width: "10%", editor: "number"},
        {title: "Ukuran", field: "kode_ukuran", width: "10%"},
        {title: "Amount", field: "amount", width: "20%",formatter: "money",    formatterParams: {
            decimal: ",",
            thousand: ".",
            symbol: "Rp",  // Simbol mata uang Rupiah
            precision: 0,   // Tidak ada desimal
        }, editor: "number"},
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
        {title: "ID", field: "id_konsumen", width: "20%",visible:false},
        {title: "No.SO", field: "kode_sales_order", width: "20%"},
        {title: "Style", field: "style", width: "20%"},
        {title: "Deskripsi", field: "deskripsi", width: "20%"},
        {title: "Buyer", field: "buyer", width: "20%"},
        {title: "Colour", field: "color", width: "15%"},
        {title: "Qty", field: "qty_kirim", width: "15%"},
        {title: "Ukuran", field: "kode_ukuran", width: "10%"},
    ],
    placeholder: "Tidak ada data",
});


btnRefPo.on("click", function(e) { 
    e.preventDefault();

    if(refData.length == 0) { 
        return Swal.fire({
            text: "Referensi transfer barang harus dipilih",
            icon: 'warning',
            showConfirmButton: false,
            timer: 2000
        });
    }
    dtListProduksiRef.setData(refData);

    modalRefpo.modal("show");
})

dtListProduksiRef.on("rowClick", function(e, row){
    const data = row._row.data
    let produksi_data = dtListProduksi.getData();

    let list_verif = produksi_data.filter(item => item.color === data.color && item.kode_ukuran === data.kode_ukuran);

    // if (list_verif.length > 0) {
    //     return Swal.fire({
    //         text: `Data dengan warna ${data.color} dan ukuran ${data.kode_ukuran} sudah ada.`,
    //         icon: 'error',
    //         showConfirmButton: false,
    //         timer: 2000
    //     });
    // }

    dtListProduksi.addRow(data);
    modalRefpo.modal("hide");
})


if(dataSO.length > 0){
    setTimeout(() => {
        try {
            let isdata = JSON.parse(dataSO);
            dtList.setData(isdata)
            dtListProduksi.setData(isdata)
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
}

inpLotNo.keyup(function (e){
    let lotNo = inpLotNo.val()
    if(dtListDetail.getData().some(x => x.lot_no == inpLotNo.val() && inpEdit.val() != inpLotNo.val())){
        e.target.value = ""
        return Swal.fire({
            text: `Lot No ${lotNo} sudah ada`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    } 

    // checkLotNo(e.target.value)
})


inpPrice.on("input", function(e){
    e.target.value = formatRupiah( e.target.value)
})

function formatRupiah(value){
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

    if(inpBarang.val().length === 0){
        e.target.value = ""
        return Swal.fire({
            text: `Barang harus dipilih dahulu.` ,
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

inpQtyItem.on("input", function(e){
    e.target.value =  e.target.value.replace(",", ".");
})


inpQtyItem.keyup(function (e) {

    if(inpBarang.val().length === 0){
        e.target.value = ""
        return Swal.fire({
            text: `Barang harus dipilih dahulu.` ,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if(!regex.test(e.target.value)){
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
selectKategori.on("change",function(e){
    const nilai = e.target.value;

    const dtProduksi = dtListProduksi.getData().length
    if(dtProduksi > 0){
        Swal.fire({
            title: "Apakah Anda yakin ingin mengubah tipe transaksi?",
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

    divNamaKonsumen.addClass("d-none")
    divDetail.show();
    divRefProduk.hide();
    // if(nilai == 3){
    //     divNamaKonsumen.removeClass("d-none")
    // } else
    
    if(nilai == 9){
        divNamaKonsumen.addClass("d-none")
    }else if(nilai == 12){
        divDetail.hide();
        divRefProduk.show();
    }

    dtListSO.setData();
})


function submitData(status,message){

    if(selectKategori.val() == null){
        return Swal.fire({
            text: "Tipe harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if(selectGudang.val() == null){
        return Swal.fire({
            text: "Warehouse harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(inpIdKonsumen.val().length == 0 && selectKategori.val() ==3){
        return Swal.fire({
            text: "Buyer harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if(inpTglReceive.val().length == 0){
        return Swal.fire({
            text: "Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }



    
    if(selectKategori.val() != 12){
        if(dtListDetail.getData().length == 0){
            return Swal.fire({
                text: "Data detail tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
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


btnSimpan.on("click",function(e){
    e.preventDefault()
    submitData(0,"menyimpan draft")
})

btnApprove.on("click",function(e){
    e.preventDefault()
    submitData(1,"mengapprove")
})


btnAdd.click(function(){
    openModalDetail()
})

btnView.click(function(){
    setTimeout(() => {
        dtListSO.redraw(true)
    }, 500);
    $("#modal-so").modal("show")
    dtListSO.deselectRow();
   
})

function checkLotNo(value){
    $.ajax({
        url: `/purchasing/receive-item/check-lot?id_barang=${inpIdBarang.val()}&lot_no=${value}`,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            
            if(data.status){
                value = ''
                return Swal.fire({
                    text: data.message,
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
           
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
  }

function openModalDetail(row = null){
    if(row){
        let data = row.getData()
        inpBarang.val(data.nama_barang)
        inpIdBarang.val(data.id_barang)
        inpUnit.val(data.nama_unit)
        inpQtyItem.val(data.qty)
        inpPrice.val(formatRupiah(data.price.toString()))
        inpLotNo.val(data.lot_no)
        inpEdit.val(data.lot_no)
    } else{
        inpBarang.val("")
        inpIdBarang.val("")
        inpUnit.val("")
        inpQtyItem.val("")
        inpPrice.val("")
        inpLotNo.val("")
        inpEdit.val("")
    }
    modalDet.modal("show")

    btnSimpanDetail.off("click").on("click",function(){
        let price = parseFloat(inpPrice.val().replace(/[^0-9.,]/g, '').replace(/\./g, '').replace(',', '.'));
        if(inpBarang.val().length == 0){
            return Swal.fire({
                text: "Barang harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    
        if(inpQtyItem.val().length == 0 || inpQtyItem.val() <= 0){
            return Swal.fire({
                text: "Quantity tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    
    
        if(!regex.test(inpQtyItem.val())){
            return Swal.fire({
                text: "Quantity harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if(!regex.test(price)){
            return Swal.fire({
                text: "Price harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        
        if(selectGudang.val()  == null){
            return Swal.fire({
                text: "Warehouse harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if(row){
            row.update({
                id:inpIdDetail.val(),
                nama_barang                 : inpBarang.val(),
                kode_barang                 : inpKodeBarang.val(),
                id_barang                   : inpIdBarang.val(),
                qty                         : inpQtyItem.val(),
                price                         : price,
                lot_no                   : inpLotNo.val(),
                nama_unit                 : inpUnit.val(),
            });
        } else{

            dtListDetail.addRow({
                id:null,
                nama_barang                 : inpBarang.val(),
                kode_barang                 : inpKodeBarang.val(),
                id_barang                   : inpIdBarang.val(),
                price                         : price,
                qty                         : inpQtyItem.val(),
                lot_no                   : inpLotNo.val(),
                nama_unit                   : inpUnit.val(),
            });
        }
        modalDet.modal("hide")
        
    })
 
}


const inpJmlMesin = $("#jml_mesin");
const inpJmlQc = $("#jml_qc");
const inpJmlLain = $("#jml_lain");

function simpanData(status) {
    $.ajax({
        type: 'POST',
        url: '/trans/incoming-goods/save',
        data: {
            id:inpIdHeader.val(),
            id_gudang:selectGudang.val(),
            tanggal:formatLocaleDate(inpTglReceive.val()),
            id_kategori:selectKategori.val(),
            nama:inpKonsumen.val(),
            id_buyer:inpIdKonsumen.val(),
            no_ref_trf:inpNoRefTrf.val(),
            data:dtListDetail.getData(),
            dataProduksi:dtListProduksi.getData(),
            keterangan:inpKeterangan.val(),
            status:status,
            id_proses: inpIdproses.val(),
            id_cmt: inpIdCmt.val(),
            jml_qc: inpJmlQc.val(),
            jml_mesin: inpJmlMesin.val(),
            jml_lain: inpJmlLain.val(),
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

            if(response.status == true){
                Swal.fire({
                    text: response.message,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000
                });
                Swal.close();
                window.location.href = 'trans/incoming-goods'
        
            }else{
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


