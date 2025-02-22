let spanVendor = $('#spanVendor');
let inpVendor = $('#namaVendor');
let inpIdVendor = $('#idVendor');
let inpIdHeader = $('#id_header');
let inpPPDate = $('#pp_cr');
let selectPaymentTipe = $('#select_payment_type');
let btnSimpan = $('#btn-simpan');
let btnApprove = $('#btn-approve');
let inpStatus = $('#status');
let detailData = $("#data-details").val().replace(/&quot;/ig,'"');
const regex = /^[0-9]+(\.[0-9]+)?$/; // Hanya angka dan desimal
if(inpStatus.val() == 0){
    btnSimpan.show()
    btnApprove.show()
} else{
    btnSimpan.hide()
    btnApprove.hide()
}
if(inpIdHeader.val().length == 0){
    selectPaymentTipe.val("").trigger("change")
}
if(detailData.length > 0){
    setTimeout(() => {
        try {
            let isdata = JSON.parse(detailData);
            dtListPayment.setData(isdata);
        } catch (e) {
            console.error("Error parsing JSON:", e);
        }
    }, 1000);
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

dtListVendor.on("rowClick", function(e, row){
    var namaVendor = row._row.data.nama.replace(/<[^>]*>/g, '');
    var idVendor = row._row.data.id;
    inpVendor.val(namaVendor)
    inpIdVendor.val(idVendor)
    getDataPayment()
    $("#modal-vendor").modal("hide");
})

spanVendor.click(function () {
    if(inpStatus.val() == 0 ){
        setTimeout(() => {
            dtListVendor.redraw(true)
        }, 500);
        $("#modal-vendor").modal("show");
        dtListVendor.deselectRow();
    }
   
});
let dtListPayment = new Tabulator("#dt-list-payment", {
    pagination: true, 
    paginationSize: 10,
    paginationButtonCount: 5,
    columns:[
        {field:"id_po", visible:false},
        {field:"qty_receive", visible:false},
        {field:"qty", visible:false},
        {title:"ID PO", field:"id_po", visible:false},
        {title:"PO NO.", field:"po_no", width:"15%"},
        {title:"PO DATE", field:"po_date", hozAlign:"center",width:"20%"},
        {title:"DUE DATE", field:"do_date", hozAlign:"center",width:"20%"},
        {title:"PO QTY STATUS", field:"qty_status", hozAlign:"center",width:"20%"},
        {title:"PO AMOUNT", field:"hutang", hozAlign:"right",width:"15%",formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }},
        {title:"DISCOUNT", field:"diskon", hozAlign:"right",width:"15%",formatter: "money",
            editor:"number",cellEdited: checkDiskon,
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }},
        {title:"PAID", field:"grand_total", hozAlign:"right",width:"15%",formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            },
        },
        {title:"REMAINING", field:"sisa_bayar", hozAlign:"right",width:"15%",formatter: "money",
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            },
        },
        {title:"PAYMENT", field:"total_bayar", hozAlign:"right",width:"15%",formatter: "money",
            editor:"number",cellEdited: checkRegex,
            formatterParams: {
                decimal: ",",
                thousand: ".",
                symbol: "Rp",  // Simbol mata uang Rupiah
                precision: 0,   // Tidak ada desimal
            }},
        
        // {title:"PAYMENT", field:"sisa_bayar", hozAlign:"right",width:"15%",formatter: "money",
        //     formatterParams: {
        //         decimal: ",",
        //         thousand: ".",
        //         symbol: "Rp",  // Simbol mata uang Rupiah
        //         precision: 0,   // Tidak ada desimal
        //     }},
        
            
            // {title:"GRAND TOTAL", field:"grand_total", hozAlign:"right",width:"15%",formatter: "money",
            //     editor:"number",
            //     formatterParams: {
            //         decimal: ",",
            //         thousand: ".",
            //         symbol: "Rp",  // Simbol mata uang Rupiah
            //         precision: 0,   // Tidak ada desimal
            //     },
            //     mutator: function(value, data) {
            //         return (data.total_bayar || 0) - (data.diskon || 0);
            //     }
            // },
    ],
    locale: 'id',    
    // layout: 'fitColumns',
    placeholder: "Tidak ada data",
});
function checkRegex(cell) {
    let row = cell.getRow();
    if (row) { 
        if(!regex.test(row.getData().total_bayar)){
            cell.restoreOldValue();
            return Swal.fire({
                text: "Payment amount harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }  
        let selisihBayar = row.getData().sisa_bayar-row.getData().total_bayar;
        if(selisihBayar < 0){
            cell.restoreOldValue();
            return Swal.fire({
                text: "Payment amount lebih besar dari yang dibayarkan",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        let diskon = 0;
        if (row.getData().diskon > 0) {
            diskon = row.getData().diskon;
        }

        let remainAmount = row.getData().hutang - diskon - row.getData().grand_total - row.getData().total_bayar;
        // if (row.getData().grand_total > 0) {
        //     remainAmount = row.getData().hutang - diskon + row.getData().grand_total - row.getData().total_bayar;
        // } 
        row.update({ sisa_bayar: remainAmount });

    } 
}

// function checkDiskon(cell) {
//     let row = cell.getRow();
//     if (row) { 
//         if(!regex.test(row.getData().total_bayar)){
//             cell.restoreOldValue();
//             return Swal.fire({
//                 text: "Diskon harus berupa angka",
//                 icon: 'error',
//                 showConfirmButton: false,
//                 timer: 2000
//             });
//         }  
//         let selisihBayar = row.getData().hutang-row.getData().diskon;
//         if(selisihBayar < 0){
//             cell.restoreOldValue();
//             return Swal.fire({
//                 text: "Diskon lebih besar dari yang dibayarkan",
//                 icon: 'error',
//                 showConfirmButton: false,
//                 timer: 2000
//             });
//         }

//         let remainAmount = row.getData().total_bayar - row.getData().diskon;
        

//         row.update({ grand_total: remainAmount });

//     } 
// }

function checkDiskon(cell) {
    let row = cell.getRow();
    if (row) { 
        if(!regex.test(row.getData().hutang)){
            cell.restoreOldValue();
            return Swal.fire({
                text: "Diskon harus berupa angka",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }  
        let selisihBayar = row.getData().sisa_bayar-row.getData().diskon;
        if(selisihBayar < 0){
            cell.restoreOldValue();
            return Swal.fire({
                text: "Diskon lebih besar dari yang dibayarkan",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        let total_bayar = 0;
        if (row.getData().total_bayar > 0) {
            total_bayar = row.getData().total_bayar;
        }

        let remainAmount = row.getData().hutang - row.getData().diskon - row.getData().grand_total - total_bayar;

        row.update({ sisa_bayar: remainAmount });

    } 
}
function getDataPayment(){
    let id_header = $('#id_header').val();
    let type = "edit";
    if (id_header == null || id_header == undefined || id_header == "") {
        id_header = $('#idVendor').val();
        type = "new";
    }
    $.ajax({
        url: `/purchasing/purchase-payment/list-payment?idVendor=${id_header}&type=${type}`,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            dtListPayment.setData(data.data)
    
            setTimeout(() => {
                dtListPayment.redraw(true)
            }, 500);
            if(data.data.length == 0){
                return Swal.fire({
                    text: "Vendor tidak memiliki Piutang",
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

  
btnSimpan.on("click",function(e){
    e.preventDefault()
    if(inpPPDate.val().length == 0){
        return Swal.fire({
            text: "PP Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(selectPaymentTipe.val() == null || selectPaymentTipe.val() == ""){
        return Swal.fire({
            text: "Payment Type harus dipilih",
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
    if(dtListPayment.getData().length == 0){
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
    if(inpPPDate.val().length == 0){
        return Swal.fire({
            text: "PP Date harus diisi",
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
    }
    if(selectPaymentTipe.val() == null || selectPaymentTipe.val() == ""){
        return Swal.fire({
            text: "Payment Type harus dipilih",
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
    if(dtListPayment.getData().length == 0){
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
        confirmButtonText: 'Simpan',
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

function simpanData(type) {
    let totalBayar = dtListPayment.getData().reduce((sum, item) => {
        let total = parseFloat(item.total_bayar);
        return sum + (isNaN(total) ? 0 : total);
    }, 0);
    let grandTotal = dtListPayment.getData().reduce((sum, item) => {
        let total = parseFloat(item.grand_total);
        return sum + (isNaN(total) ? 0 : total);
    }, 0);
    let hutang = dtListPayment.getData().reduce((sum, item) => {
        let total = parseFloat(item.hutang);
        return sum + (isNaN(total) ? 0 : total);
    }, 0);
    let sisaBayar = dtListPayment.getData().reduce((sum, item) => {
        let total = parseFloat(item.sisa_bayar);
        return sum + (isNaN(total) ? 0 : total);
    }, 0);
    let diskon = dtListPayment.getData().reduce((sum, item) => {
        let total = parseFloat(item.diskon);
        return sum + (isNaN(total) ? 0 : total);
    }, 0);
    
    $.ajax({
        type: 'POST',
        url: '/purchasing/purchase-payment/save',
        data: {
            id:inpIdHeader.val(),
            id_vendor:inpIdVendor.val(),
            pp_date:formatLocaleDate(inpPPDate.val()),
            id_rek:selectPaymentTipe.val(),
            totalBayar:totalBayar,
            grandTotal:grandTotal,
            diskon:diskon,
            hutang:hutang,
            sisaBayar:sisaBayar,
            buttonType:type,
            data:dtListPayment.getData(),
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
                window.location.href = 'purchasing/purchase-payment'
              
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