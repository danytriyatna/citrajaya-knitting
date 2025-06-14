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
let detailData = $("#data-details").val().replace(/&quot;/ig,'"');
let btnAdd = $('#btn-add');
let btnSimpan = $('#btn-simpan');
let btnApprove = $('#btn-approve');
let btnSimpanDetail = $('#btn-simpan-det');
let modalDet = $('#modal-detail-item');
let inpStatus = $('#status');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');

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
let dtList = new Tabulator("#dt-list", {
    columns: [
        {
            field: "id", headerSort: false,
            width: "15%",visible:false
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

dtList.on("rowClick", function(e, row){
    var namaVendor = row._row.data.nama_vendor.replace(/<[^>]*>/g, '');
    var poNo = row._row.data.po_no.replace(/<[^>]*>/g, '');
    var ShipDate = row._row.data.date_exc.replace(/<[^>]*>/g, '');
    var idPo = row._row.data.id;
    inpPoNo.val(poNo)
    inpIdPo.val(idPo)
    inpNamaVendor.val(namaVendor)
    inpTglShip.val(formatterDate(ShipDate))
    $("#modal-po").modal("hide");
})

let dtListBarang = new Tabulator("#dt-list-barang", {
    columns: [
        {
            title: "ID Barang", field: "id", headerSort: false,
            width: "15%",visible:false
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
            width: "15%",hozAlign:"right",
        },
        {
            title: "Qty Receive", field: "qty_receive", headerSort: false,
            width: "15%",hozAlign:"right",
        },
        {
            title: "Price", field: "price", headerSort: false,
            width: "15%",hozAlign:"right",formatter : "money",
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

dtListBarang.on("rowClick", function(e, row){
  
    var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    var idBarang = row._row.data.id;
    var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    var namaSatuan = row._row.data.nama_satuan.replace(/<[^>]*>/g, '');
    var qty = row._row.data.qty;
    var price = row._row.data.price;
    if(dtListDetail.getData().some(x => x.id_barang == idBarang)){
        return Swal.fire({
            text: `Barang ${namaBarang} sudah dipilih`,
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

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


let buttonRowAction = function(cell) {
    let fmBtnDelete = "";        
    let fmBtnEdit = "";        

    if (inpStatus.val() == 0){
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;
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
        {title:"UNIT", field:"nama_unit", hozAlign:"center",width:"10%"},
        {title:"PRICE", field:"price", formatter : "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
        },width:"10%"},
        {title:"WAREHOUSE", field:"nama_gudang", hozAlign:"center",width:"20%"},
        {title:"LOT NO", width:"10%", field:"lot_no", hozAlign:"left"},
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

    checkLotNo(e.target.value)
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

    // if(e.target.value > parseFloat(inpQtyPO.val())){
    //     e.target.value = ""
    //     return Swal.fire({
    //                 text: `Barang hanya memiliki quantity ${inpQtyPO.val()}` ,
    //                 icon: 'error',
    //                 showConfirmButton: false,
    //                 timer: 2000
    //             });
    // }
})

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
    if(inpPoNo.val().length == 0){
        return Swal.fire({
                    text: "PO No. belum dipilih",
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
    }
  
    dtListBarang.on("dataLoaded", function(data){
        Swal.close();
        setTimeout(() => {
            dtListBarang.redraw(true)
        }, 500);
        $("#modal-barang").modal("show")
    });

    dtListBarang.deselectRow();
});

function submitData(status,message){
    if(inpTglReceive.val().length == 0){
        return Swal.fire({
            text: "Receive Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if(inpPoNo.val().length == 0){
        return Swal.fire({
            text: "PO No. harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    
    if(dtListDetail.getData().length == 0){
        return Swal.fire({
            text: "Data detail tidak boleh kosong",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
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
        // inpPrice.val(formatRupiah(data.price.toString()))
        inpPrice.val(data.price)
        selectGudang.val(data.id_gudang).trigger("change")
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
        selectGudang.val("").trigger("change")
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
        
        if(selectGudang.val().length == 0){
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
                id_gudang                   : selectGudang.val(),
                qty_receive                 :inpQtyPO.val(),
                nama_gudang                    : $('#select_warehouse option:selected').text(),
                lot_no                   : inpLotNo.val(),
                nama_unit                 : inpUnit.val(),
            });
        } else{

            dtListDetail.addRow({
                id:null,
                nama_barang                 : inpBarang.val(),
                kode_barang                 : inpKodeBarang.val(),
                id_barang                   : inpIdBarang.val(),
                id_gudang                   : selectGudang.val(),
                price                         : price,
                qty_receive:inpQtyPO.val(),
                nama_gudang                    : $('#select_warehouse option:selected').text(),
                qty                         : inpQtyItem.val(),
                lot_no                   : inpLotNo.val(),
                nama_unit                   : inpUnit.val(),
            });
        }
        modalDet.modal("hide")
        
    })
 
}


function simpanData(status) {
    let totalQty = dtListDetail.getData().reduce((sum, item) => sum + parseFloat(item.qty), 0);
    $.ajax({
        type: 'POST',
        url: '/purchasing/receive-item/save',
        data: {
            id:inpIdHeader.val(),
            id_po:inpIdPo.val(),
            rec_date:formatLocaleDate(inpTglShip.val()),
            qty:totalQty,
            namaVendor:inpNamaVendor.val(),
            form_no:inpFormNo.val(),
            data:dtListDetail.getData(),
            status:status
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
                window.location.href = 'purchasing/receive-item'
        
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


