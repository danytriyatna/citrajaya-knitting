

let inpTglReceive = $('#tanggal');
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
let selectGudangAsal = $('#gudang_asal');
let selectGudangTujuan = $('#gudang_tujuan');
let selectTipe = $('#tipe');
let inpLotNo = $('#lot_no');
let inpIdLot = $('#id_lot');
let detailData = $("#data-details").val().replace(/&quot;/ig,'"');
let btnAdd = $('#btn-add');
let btnSimpan = $('#btn-simpan');
let btnView = $('#btn-view');
let btnApprove = $('#btn-approve');
let btnSimpanDetail = $('#btn-simpan-det');
let modalDet = $('#modal-detail-item');
let inpStatus = $('#status');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');
let inpIdGudangAsal = $('#id_gudang_asal');

if(inpIdHeader.val().length == 0){
    selectGudangAsal.val("").trigger("change")
    selectGudangTujuan.val("").trigger("change")
  
}

    if(selectTipe.val() == 1){
        btnView.addClass("d-none")
    } else{
        btnView.removeClass("d-none")
    } 

if(inpStatus.val() == 0){
    btnAdd.show()
    btnSimpan.show()
    btnApprove.show()
} else{
    btnAdd.hide()
    btnSimpan.hide()
    btnApprove.hide()
}
const regex = /^[0-9]+(\.[0-9]+)?$/; // Hanya angka dan desimal



let dtListBarang = new Tabulator("#dt-list-barang", {
    columns: [
        {
            title: "ID Barang", field: "id", headerSort: false,
            width: "15%",visible:false
        },
        {
            title: "ID Lots", field: "lot_id", headerSort: false,
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
            title: "Satuan", field: "nama_satuan", headerSort: false,
            width: "10%"
        },
        {
            title: "Lot No.", field: "lot_no", headerSort: false,
            width: "25%"
        },
        {
            title: "Qty", field: "qty", headerSort: false,
            width: "25%"
        },
    ],
    locale: 'id',    
    ajaxURL: "/trans/outgoing-goods/list-barang",
    ajaxConfig: "POST",
    sortMode: "remote",
    filterMode: "remote",
    selectableRows: true,
    groupBy:"nama_barang",
    placeholder: "Tidak ada data",
    ajaxRequesting: function (url, params) {
        params.start = params.size * (params.page - 1);
        params.length = params.size;
        params.idGudang = selectGudangAsal.val()
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

let elSearchSO = $("#tb-search-so");
    if (elSearchSO != null) {
        elSearchSO.on("keyup", function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            getDetail(elSearchSO.val())
        });
    }


dtListBarang.on("rowClick", function(e, row){
    
    if(selectGudangAsal.val() == null){
        return Swal.fire({
            text: "Gudang Asal harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(selectGudangTujuan.val() == null){
        return Swal.fire({
            text: "Gudang Tujuan harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }


    var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    var idBarang = row._row.data.id;
    var idLot = row._row.data.lot_id;
    var qty = row._row.data.qty;
    var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    var namaSatuan = row._row.data.nama_satuan.replace(/<[^>]*>/g, '');
    var lotNo = row._row.data.lot_no.replace(/<[^>]*>/g, '');
    
    if(dtListDetail.getData().some(x=>x.id_barang == idBarang && x.lot_id == idLot)){
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
    // inpLotNo.val(lotNo)
    inpQtyExist.val(qty)
    inpIdLot.val(idLot)
    $("#modal-barang").modal("hide");
})


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
        {field:"lot_id", visible:false},
        {field:"qty_exist", visible:false},
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
        {title:"QTY", field:"qty", hozAlign:"center",width:"10%",editor: "number"},
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

    if(!regex.test(e.target.value)){
        e.target.value = ""
        return Swal.fire({
            text: "Price harus berupa angka",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

})


selectTipe.on("change",function(e){
    var nilai = e.target.value;
    if(nilai == 1){
        btnView.addClass("d-none")
    } else{
        btnView.removeClass("d-none")
    } 
    
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

    if(e.target.value > parseFloat(inpQtyExist.val()) ){
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
    dtListBarang.replaceData() 
  
    if(selectGudangAsal.val() == null ){
        return Swal.fire({
                    text: "Gudang Asal belum dipilih",
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
    }
  
    if(selectGudangTujuan.val() == null ){
        return Swal.fire({
                    text: "Gudang Tujuan belum dipilih",
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

    if(selectGudangAsal.val() == null){
        return Swal.fire({
            text: "Gudang Asal harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if(selectGudangTujuan.val() == null){
        return Swal.fire({
            text: "Gudang Tujuan harus dipilih",
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

    if(dtListDetail.getData().length == 0){
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
    $("#modal-so").modal("show")
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
        inpPrice.val(data.price)
        inpLotNo.val(data.lot_no)
        inpEdit.val(data.lot_no)
        inpIdLot.val(data.lot_id)
        inpQtyExist.val(data.qty_exist)
    } else{
        inpBarang.val("")
        inpIdBarang.val("")
        inpUnit.val("")
        inpQtyItem.val("")
        inpPrice.val("")
        inpLotNo.val("")
        inpEdit.val("")
        inpQtyExist.val("")
        inpIdLot.val("")
    }
    modalDet.modal("show")

    btnSimpanDetail.off("click").on("click",function(){
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

        if(!regex.test(inpPrice.val())){
            return Swal.fire({
                text: "Price harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        
        if(selectGudangAsal.val() == null){
            return Swal.fire({
                text: "Gudang Asal harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        if(selectGudangTujuan.val() == null){
            return Swal.fire({
                text: "Gudang Tujuan harus dipilih",
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
                price                         : inpPrice.val(),
                qty_exist                         : inpQtyExist.val(),
                lot_no                   : inpLotNo.val(),
                lot_id                   : inpIdLot.val(),
                nama_unit                 : inpUnit.val(),
            });
        } else{

            dtListDetail.addRow({
                id:null,
                nama_barang                 : inpBarang.val(),
                kode_barang                 : inpKodeBarang.val(),
                id_barang                   : inpIdBarang.val(),
                price                         : inpPrice.val(),
                qty                         : inpQtyItem.val(),
                qty_exist                         : inpQtyExist.val(),
                lot_no                   : inpLotNo.val(),
                lot_id                   : inpIdLot.val(),
                nama_unit                   : inpUnit.val(),
            });
        }
        modalDet.modal("hide")
        
    })
 
}

function getDetail(kodeOrder) {
    $.ajax({
        url: `/trans/sales-order/view`,
        type: 'GET',
        data:{kodeOrder:kodeOrder},
        dataType: 'json', 
        success: function(data) {
            drawTableRefSO(data.ukuran,data.data)
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

function drawTableRefSO(ukuran,detail){
    let isColumn = [
        {headerSort: false,title:"No", field:"no",   width: "5%"},
        {headerSort: false, cssClass: 'text-start', title:"Colour", field:"colordasar"}
    ]

    for (const el of ukuran) {
        const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
        isColumn.push( {headerSort: false,  title:el.kode_ukuran, field: isKey, cssClass: "text-center", hozAlign:"center", width:"7%"} )
    }

    isColumn.push(
        {
            headerSort: false, cssClass: 'text-center', title:"Amount", field:"total_harga",formatter: "money", 
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            },
            hozAlign:"right", cssClass: 'text-end', width:"15%"})

    new Tabulator(`#dt-list-sample`, {
        data: detail, 
        layout:"fitColumns",
        resizableColumnFit:true,
        pagination: true, 
        paginationSize: 10,
        paginationButtonCount: 5,
        columns: isColumn,
    });


}



function simpanData(status) {
    $.ajax({
        type: 'POST',
        url: '/trans/item-transfer/save',
        data: {
            id:inpIdHeader.val(),
            id_gudang_asal:selectGudangAsal.val(),
            id_gudang_tujuan:selectGudangTujuan.val(),
            tipe:selectTipe.val(),
            tanggal:formatLocaleDate(inpTglReceive.val()),
            data:dtListDetail.getData(),
            keterangan:inpKeterangan.val(),
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
                window.location.href = 'trans/item-transfer'
        
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


