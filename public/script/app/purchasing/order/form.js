let spanVendor = $('#spanVendor');
let inpVendor = $('#namaVendor');
let inpIdVendor = $('#idVendor');
let inpBarang = $('#namaBarang');
let inpIdBarang = $('#idBarang');
let inpKodeBarang = $('#kodeBarang');
let inpTglExpec = $('#tgl_expected');
let inpTglPO = $('#tgl_po');
let inpTglPOExp = $('#po_date_exp');
let inpShipTo = $('#ship_to');
let inpKeterangan = $('#keterangan');
let inpIdHeader = $('#id_header');
let inpIdDetail = $('#idDetail');
let inpQty = $('#qty_item');
let inpDisc = $('#disc_item');
let inpKode = $('#kode');
let inpCheckTax = $('#check_tax');
let inpUnitPrice = $('#unit_price');
let spanBarang = $('#spanBarang');
let selectTerm = $('#select_term');
let inpUnit = $('#unit');
let inpUnitID = $('#id_unit');
let inpStatus = $('#status');
let inpApproveStatus = $('#approve_status');
let inpTax = $('#tax');
let btnAdd = $('#btn-add');
let btnSimpan = $('#btn-simpan');
let btnApprove = $('#btn-approve');
let btnSimpanDetail = $('#btn-simpan-det');
let modalDet = $('#modal-detail-item');
let detailData = $("#data-details").val().replace(/&quot;/ig,'"');
// const regex = /^[0-9]+(\.[0-9]+)?$/; // Hanya angka dan desimal
const regex = /^[0-9.,]+$/;
   
// if(inpStatus.val() == 0){
//     btnAdd.show()
//     btnSimpan.show()
//     btnApprove.show()
// } else{
//     btnAdd.hide()
//     btnSimpan.hide()
//     btnApprove.hide()
// }

if(inpApproveStatus.val() == 0){
    btnAdd.show()
    // btnSimpan.show()
    btnApprove.show()
} else{
    btnAdd.hide()
    // btnSimpan.hide()
    btnApprove.hide()
}

let dtList = new Tabulator("#dt-list", {
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
            title: "Stok Minimum", field: "stok_minimum", headerSort: false,
            width: "15%",visible:false
        },
        {
            title: "Satuan", field: "nama_satuan", headerSort: false,
            width: "10%"
        },
        {
            title: "ID Satuan", field: "id_satuan", headerSort: false,
            width: "10%",visible:false
        },
        {
            title: "Harga Satuan",visible:false, field: "harga_satuan", headerSort: false,
            width: "15%",formatter: "money", formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, hozAlign:"right",
        },
       
        {
            title: "Keterangan", field: "keterangan", formatter: "html", headerSort: false,
            width:"15%"
        },
        {
            title: "Kode Warna", field: "kode", formatter: "html", headerSort: false,
            width:"15%", visible:false
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
let elSearch = $("#tb-search");
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
            width: "15%", cssClass : 'text-center'
        },
        {
            title: "No. HP", field: "no_hp", headerSort: false,
            width: "15%", cssClass : 'text-center'
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

dtList.on("rowClick", function(e, row){
  
    var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    var idBarang = row._row.data.id;
    var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    var namaSatuan = row._row.data.nama_satuan.replace(/<[^>]*>/g, '');
    var idSatuan = row._row.data.id_satuan.replace(/<[^>]*>/g, '');
    var kode = row._row.data.kode.replace(/<[^>]*>/g, '');
    if(dtListDetailPO.getData().some(x => x.id_barang == idBarang)){
        return Swal.fire({
            text: "Barang sudah dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }

    inpUnit.val(namaSatuan)
    inpUnitID.val(idSatuan)
    inpIdBarang.val(idBarang)
    inpKodeBarang.val(kodeBarang)
    inpKode.val(kode)
    inpBarang.val(`${namaBarang}`)

    $.ajax({
        url: `/purchasing/purchase-order/check-unit-price?id_vendor=${inpIdVendor.val()}&id_barang=${idBarang}`,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            
            if(data.status == true){
                inpUnitPrice.val(formatRupiah(data.unit_price));
            }
            else {
                inpUnitPrice.val(formatRupiah("0"));
            }
            
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
    
    $("#modal-barang").modal("hide");
})

dtListVendor.on("rowClick", function(e, row){
    var namaVendor = row._row.data.nama.replace(/<[^>]*>/g, '');
    var idVendor = row._row.data.id;
    inpVendor.val(namaVendor)
    inpIdVendor.val(idVendor)
    $("#modal-vendor").modal("hide");
})

spanVendor.click(function () {
    setTimeout(() => {
        dtListVendor.redraw(true)
    }, 500);
    $("#modal-vendor").modal("show");
    dtListVendor.deselectRow();
});
spanBarang.click(function () {
    setTimeout(() => {
        dtList.redraw(true)
    }, 500);
    $("#modal-barang").modal("show");
    dtList.deselectRow();
});

btnAdd.click(function(){
    if (inpIdVendor.val() == null || inpIdVendor.val() == "" || inpIdVendor.val() == " ") {
        return Swal.fire({
            text: "Vendor Harus dipilih dahulu!",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    openModalDetail()
})

if(detailData.length > 0){
    setTimeout(() => {
        try {
            let isdata = JSON.parse(detailData);
            dtListDetailPO.setData(isdata);
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
} 

inpQty.on("input", function(e){
    e.target.value =  e.target.value.replace(",", ".");
})

inpQty.keyup(function (e) {
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

inpUnitPrice.on("input", function(e){
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

function openModalDetail(row = null){
    if(row){
        let data = row.getData()
        inpIdDetail.val(data.id)
        inpBarang.val(data.nama_barang)
        inpKodeBarang.val(data.kode_barang)
        inpKode.val(data.kode)
        inpIdBarang.val(data.id_barang)
        inpUnit.val(data.nama_unit)
        inpQty.val(data.qty)
        inpKode.val(data.kode)
        if(data.tax){
            $('input[type=checkbox]').prop('checked',true);
        } else{
            $('input[type=checkbox]').prop('checked',false);
        }
        inpUnitPrice.val(formatRupiah(data.price.toString()))
        inpDisc.val(data.disc)
    } else{
        inpBarang.val("")
        inpIdBarang.val("")
        inpUnit.val("")
        $('input[type=checkbox]').prop('checked',false);
        inpQty.val("")
        inpDisc.val("")
        inpUnitPrice.val("")
    }
    modalDet.modal("show")

    btnSimpanDetail.off("click").on("click",function(){
        
        let price = parseFloat(inpUnitPrice.val().replace(/[^0-9.,]/g, '').replace(/\./g, '').replace(',', '.'));
        let grandPrice = 0;
        let discPrice = 0;
        let taxAfterPrice = 0;
        let priceAfterDisc = 0;
        let tax = "";
        let disc = "";
        
    
     

        if(inpBarang.val().length == 0){
            return Swal.fire({
                text: "Barang harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    
        
        
        if(inpQty.val().length == 0 || inpQty.val() <= 0){
            return Swal.fire({
                text: "Quantity tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    
        if(price.length == 0){
            return Swal.fire({
                text: "Unit Price tidak boleh kosong",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    
        if(!regex.test(inpQty.val())){
            return Swal.fire({
                text: "Quantity harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        if(!regex.test(price)){
            return Swal.fire({
                text: "Unit Price harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        if(inpDisc.val().length > 0 && !regex.test(inpDisc.val())){
            return Swal.fire({
                text: "Discon harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
    
        if(inpCheckTax.is(':checked') && inpDisc.val().length > 0){
            grandPrice = parseFloat(inpQty.val()) * parseFloat(price)
            discPrice = grandPrice * (inpDisc.val()/100) 
            priceAfterDisc = grandPrice - discPrice;
            taxAfterPrice = priceAfterDisc * (inpTax.val()/100)
            grandPrice = priceAfterDisc + taxAfterPrice
            tax = inpTax.val()
            disc = inpDisc.val()
        } else if(inpCheckTax.is(':checked')){
            grandPrice = parseFloat(inpQty.val()) * parseFloat(price)
            taxAfterPrice = grandPrice * (inpTax.val()/100)
            grandPrice = grandPrice + taxAfterPrice
            tax = inpTax.val()
            disc =""
        } else if(inpDisc.val().length > 0){
            grandPrice = parseFloat(inpQty.val()) * parseFloat(price)
            discPrice = grandPrice * (inpDisc.val()/100) 
            grandPrice = grandPrice - discPrice
            tax = ""
            disc = inpDisc.val()
        } else{
            grandPrice = parseFloat(inpQty.val()) * parseFloat(price)
            tax = ""
            disc = ""
        }
       
        if(row){
            row.update({
                id:inpIdDetail.val(),
                nama_barang                 : inpBarang.val(),
                kode_barang                 : inpKodeBarang.val(),
                id_barang                   : inpIdBarang.val(),
                qty                         : inpQty.val(),
                nama_satuan                      : inpUnit.val(),
                id_satuan                      : inpUnitID.val(),
                price                       : price,
                grand_price                 : grandPrice,
                disc_price:discPrice,
                tax_price:taxAfterPrice,
                disc                 : disc,
                tax                 : tax,
                kode                        : inpKode.val(),
                nama_unit                 : inpUnit.val(),
                kode                 : inpKode.val(),
            });
        } else{

            dtListDetailPO.addRow({
                id:null,
                nama_barang                 : inpBarang.val(),
                kode_barang                 : inpKodeBarang.val(),
                id_barang                   : inpIdBarang.val(),
                qty                         : inpQty.val(),
                nama_satuan                      : inpUnit.val(),
                id_satuan                      : inpUnitID.val(),
                price                       : price,
                grand_price                 : grandPrice,
                disc_price                  : discPrice,
                tax_price                   : taxAfterPrice,
                disc                        : disc,
                tax                         : tax,
                kode                        : inpKode.val(),
                nama_unit                   : inpUnit.val(),
                kode                 : inpKode.val(),
            });
        }
        modalDet.modal("hide")
        
    })

}

btnSimpan.on("click",function(e){
    e.preventDefault()
    if(inpTglPO.val().length == 0){
        return Swal.fire({
            text: "PO Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(inpTglPOExp.val().length == 0){
        return Swal.fire({
            text: "Expired PO Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(inpTglExpec.val().length == 0){
        return Swal.fire({
            text: "Expected Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(inpVendor.val().length == 0){
        return Swal.fire({
            text: "Vendor harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(dtListDetailPO.getData().length == 0){
        return Swal.fire({
            text: "Data detail tidak boleh kosong",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    Swal.fire({
        title: "Apakah anda ingin mensubmit data Purchase Order?",
        icon: 'question',
        confirmButtonText: 'Simpan',
        confirmButtonColor: '#198754',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#6C757D'
    }).then((result) => {
        if (result.isConfirmed) {
            simpanData("draft")
        }
    })
  
})

btnApprove.on("click",function(e){
    e.preventDefault()
    if(inpTglPO.val().length == 0){
        return Swal.fire({
            text: "PO Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(inpTglPOExp.val().length == 0){
        return Swal.fire({
            text: "Expired PO Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(inpTglExpec.val().length == 0){
        return Swal.fire({
            text: "Expected Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(inpVendor.val().length == 0){
        return Swal.fire({
            text: "Vendor harus dipilih",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(dtListDetailPO.getData().length == 0){
        return Swal.fire({
            text: "Data detail tidak boleh kosong",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    Swal.fire({
        title: "Apakah anda ingin approve data Purchase Order?",
        icon: 'question',
        confirmButtonText: 'Approve',
        confirmButtonColor: '#198754',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#6C757D'
    }).then((result) => {
        if (result.isConfirmed) {
            simpanData("approve")
        }
    })
  
})

function simpanData(stringButton) {
    let totalQty = dtListDetailPO.getData().reduce((sum, item) => sum + parseFloat(item.qty), 0);
    let totalGrandPrice = dtListDetailPO.getData().reduce((sum, item) => sum + parseFloat(item.grand_price), 0);
    $.ajax({
        type: 'POST',
        url: '/purchasing/purchase-order/save',
        data: {
            id:inpIdHeader.val(),
            id_vendor:inpIdVendor.val(),
            po_date:formatLocaleDate(inpTglPO.val()),
            po_date_exp:formatLocaleDate(inpTglPOExp.val()),
            date_exc:formatLocaleDate(inpTglExpec.val()),
            id_term:selectTerm.val(),
            ship_to:inpShipTo.val(),
            keterangan:inpKeterangan.val(),
            qty:totalQty,
            total:totalGrandPrice,
            data:dtListDetailPO.getData(),
            buttonType:stringButton,
            status:inpStatus.val()
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
                window.location.href = 'purchasing/purchase-order'
              
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

let buttonRowAction = function(cell) {
    let fmBtnDelete = "";        
    let fmBtnEdit = "";        

    // if (inpStatus.val() == 0){
    //     fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
    //     fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;
    // }
    if (inpApproveStatus.val() == 0){
        fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
    }
    fmBtnEdit = ` <button type="button" class="btn btn-sm btn-warning text-dark" title='edit'><i class="fa fa-edit" title='edit'></i></button>`;
   

    return fmBtnEdit + " " + fmBtnDelete;
};

let dtListDetailPO = new Tabulator("#dt-list-po", {
    pagination: true, 
    paginationSize: 10,
    paginationButtonCount: 5,
    columns:[
        {field:"id", visible:false},
        {field:"id_barang", visible:false},
        {field:"id_header", visible:false},
        {field:"tax_price", visible:false},
        {field:"disc_price", visible:false},
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
        {title:"Kode Barang", field:"kode_barang",hozAlign:"left", width:"15%"},
        {title:"Nama Barang", field:"nama_barang", hozAlign:"left",width:"20%"},
        {title:"KODE", field:"kode", hozAlign:"left",width:"20%"},
        {title:"QTY", field:"qty", hozAlign:"center",width:"10%"},
        {title:"Satuan", field:"nama_satuan", hozAlign:"center",width:"10%"},
        {title:"ID Satuan", field:"id_satuan", hozAlign:"center",width:"10%", visible:false},
        {title:"Harga Per Unit", field:"price", hozAlign:"right",width:"15%",formatter: "money",formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }, hozAlign:"right",
        },
        {title:"DISC (%)", field:"disc", hozAlign:"center",width:"10%",visible:false},
        {title:"Pajak (%)", field:"tax", hozAlign:"center",width:"10%"},
        {title:"Total", width:"15%", field:"grand_price",formatter: "money", formatterParams: {
            decimal: ",",
            thousand: ".",
            symbol: "Rp",  // Simbol mata uang Rupiah
            precision: 0,   // Tidak ada desimal
        }, hozAlign:"right"},
    ],
    locale: 'id',    
    // layout: 'fitColumns',
    placeholder: "Tidak ada data",
});

