let dtList = new Tabulator("#dt-list", {
    pagination: true, 
    paginationSize: 100,
    paginationButtonCount: 5,
    paginationCounter:"rows",
    groupBy: ['barang', 'pack_name'], // 🔥 grup berdasarkan jenis barang, nama barang, lot no, dan pack name
    // groupCalcs: false, //
    columns:[
        // {title:"LOT ID", field:"lot_id", width:"10%"},
        {title:"PACK", field:"pack_name", width:"9%"},
        {title:"LOT", field:"lot_no", width:"9%"},
        {title:"Size/Warna", field:"nama_satuan", hozAlign:"left",width:"15%"},
        {title:"Qty<br>Awal", field:"saldo_awal", hozAlign:"right",width:"12%", headerHozAlign: "right"},
        {title:"Qty<br>Masuk", field:"masuk", hozAlign:"right",width:"12%", headerHozAlign: "right"},
        {title:"Qty<br>Keluar", field:"keluar", hozAlign:"right",width:"12%", headerHozAlign: "right"},
        {title:"Qty<br>Akhir", field:"saldo_akhir", hozAlign:"right",width:"12%", headerHozAlign: "right", bottomCalc:"sum", bottomCalcFormatter:"money", bottomCalcFormatterParams:{
            decimal:",",
            thousand:"."}
        },
        {
            title:"Nilai",
            field:"price",
            hozAlign:"right",
            width:"17%",
            formatter:"money",
            formatterParams:{
                decimal:",",
                thousand:".",
                symbol:"Rp",
                precision:0,
            },
            headerHozAlign: "right"
            // bottomCalc:"sum", // 🔥 ini menghitung total seluruh kolom
            // bottomCalcFormatter:"money",
            // bottomCalcFormatterParams:{
            //     decimal:",",
            //     thousand:".",
            //     symbol:"Rp ",
            //     precision:0,
            // },
        },
        {title:"Tanggal", field:"tanggal", hozAlign:"right",width:"10%"},
    ],
    // columnCalcs:"table",
    locale: 'id',    
    // layout: 'fitColumns',
    placeholder: "Tidak ada data",
    // Saat halaman berubah, update info pagination
    paginationDataReceived: function(data){
        updatePageInfo();
    },
    paginationChanged: function(pagenum){
        updatePageInfo();
    },
    dataLoaded: function(data){
        updatePageInfo();
    }
});

// let dtListBarang = new Tabulator("#dt-list-barang", {
//     columns: [
//         {
//             title: "ID Barang", field: "id", headerSort: false,
//             width: "15%",visible:false
//         },
//         {
//             title: "Kode Barang", field: "kode_barang", headerSort: false,
//             width: "20%"
//         },
//         {
//             title: "Nama Barang", field: "nama_barang", headerSort: false,
//             width: "30%"
//         },
//         {
//             title: "Jenis Barang", field: "nama_jenis_barang", headerSort: false,
//             width: "25%"
//         },
//         {
//             title: "Stok Minimum", field: "stok_minimum", headerSort: false,
//             width: "15%",visible:false
//         },
//         {
//             title: "Satuan", field: "nama_satuan", headerSort: false,
//             width: "10%"
//         },
//         {
//             title: "Harga Satuan",visible:false, field: "harga_satuan", headerSort: false,
//             width: "15%",formatter: "money", formatterParams: {
//                 decimal: ",",
//                 thousand: ".",
//                 symbol: "Rp",  // Simbol mata uang Rupiah
//                 precision: 0,   // Tidak ada desimal
//             }, hozAlign:"right",
//         },
       
//         {
//             title: "Keterangan", field: "keterangan", formatter: "html", headerSort: false,
//             width:"15%"
//         },
//     ],
//     locale: 'id',    
//     ajaxURL: "/master-data/barang/list",
//     ajaxConfig: "POST",
//     sortMode: "remote",
//     filterMode: "remote",
//     selectableRows: true,
//     placeholder: "Tidak ada data",
//     ajaxRequesting: function (url, params) {
//         params.start = params.size * (params.page - 1);
//         params.length = params.size;
//     },
//     ajaxResponse: function (url, params, response) {
//         let pageSize = dtList.getPageSize();
//         let pageNo = dtList.getPage();
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
//     selectable: false,
// });

// $(document).on("input", "#tb-search-list", function () {
//     if ($(this).val().length < 3 && e.keyCode > 13) {
//         return;
//     }
//     clearTimeout(searchThread);
//     searchThread = setTimeout(function () {
//         dtList.setFilter("", "like", elSearch.val());
//     }, 600);
// });


let searchThread = null;
let elSearch = $("#tb-search");
if (elSearch != null) {
    elSearch.on("keyup", function (e) {
        if ($(this).val().length < 3 && e.keyCode > 13) {
            return false;
        }
        else {
            $(".preloader").css("opacity", "0.7").show();
            if($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "" ){
                Swal.fire({
                    title: 'Warning',
                    text: 'Tahun,Bulan & Gudang harus dipilih',
                    icon: 'warning',
                })
                $(".preloader").hide().css("opacity", "1");
                return false    
            }
            getDataLaporan()
        }
    });
}

// dtListBarang.on("rowClick", function(e, row){
//     var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
//     var idBarang = row._row.data.id;
//     var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
//     $('#filter_barang_id').val(idBarang)
//     $("#filter_barang").val(`${kodeBarang} - ${namaBarang}`);
//     $("#modal-barang").modal("hide");
// })

// $("#filter_barang").click(function () {
//     setTimeout(() => {
//         dtListBarang.redraw(true)
//     }, 500);
//     $("#modal-barang").modal("show");
//     dtListBarang.deselectRow();
// });

$("#btn-tampilkan").click(function () {
    $(".preloader").css("opacity", "0.7").show();
    if($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "" ){
      Swal.fire({
        title: 'Warning',
        text: 'Tahun,Bulan & Gudang harus dipilih',
        icon: 'warning',
      })
      $(".preloader").hide().css("opacity", "1");
      return false    
    }
    getDataLaporan()
  });

  $("#exportExcel").click(function () {
    if($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "" ){
      Swal.fire({
        title: 'Warning',
        text: 'Tahun,Bulan & Gudang harus dipilih',
        icon: 'warning',
      })
      $(".preloader").hide().css("opacity", "1");
      return false    
    }

    let url = `/laporan/persediaan/print_excel_lists?filter_jenis_id=${$('#filter_jenis_barang').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}`
    window.open(url, '_blank');
  });

  $("#btn-reset").click(function () {
      $("#filter_jenis_barang").val("").trigger("change");
      $("#filter_gudang").val("").trigger("change");
      $("#filter_tahun").val("").trigger("change");
      $("#filter_bulan").val("").trigger("change");
  });

  function getDataLaporan(){
    $url = `/laporan/persediaan/list?filter_jenis_id=${$('#filter_jenis_barang').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}`;
    if (elSearch.val() != null && elSearch.val().length > 3) {
        $url = `/laporan/persediaan/list?filter_jenis_id=${$('#filter_jenis_barang').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}&search=${$('#tb-search').val()}`;
    }
    $.ajax({
        url: $url,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            
            dtList.setData(data.data)
    
            setTimeout(() => {
                dtList.redraw(true)
            }, 500);
            $(".preloader").hide().css("opacity", "1");
        },
        error: function(xhr, status, error) {
            $(".preloader").hide().css("opacity", "1");
            console.error('Error fetching data:', error);
        }
    });
  }

  $("#updateData").click(function () {
    $(".preloader").css("opacity", "0.7").show();
    if($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "" ){
      Swal.fire({
        title: 'Warning',
        text: 'Tahun,Bulan & Gudang harus dipilih',
        icon: 'warning',
      })
      $(".preloader").hide().css("opacity", "1");
      return false    
    }
    getUpdateDataLaporan()
    
  });

  function getUpdateDataLaporan(){
    $.ajax({
        url: `/laporan/persediaan/update-list?filter_jenis_id=${$('#filter_jenis_barang').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}`,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            
            dtList.setData(data.data)
    
            setTimeout(() => {
                dtList.redraw(true)
            }, 500);

            $(".preloader").hide().css("opacity", "1");
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
            $(".preloader").hide().css("opacity", "1");
        }
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    const selectBulan = document.getElementById("filter_bulan");
    const selectTahun = document.getElementById("filter_tahun");

    const now = new Date();
    const bulanSekarang = now.getMonth() + 1; // getMonth() = 0–11
    const tahunSekarang = now.getFullYear();

    // Set bulan jika opsi tersedia
    if (selectBulan.querySelector(`option[value="${bulanSekarang}"]`)) {
      selectBulan.value = bulanSekarang;
      selectBulan.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Set tahun jika opsi tersedia
    if (selectTahun.querySelector(`option[value="${tahunSekarang}"]`)) {
      selectTahun.value = tahunSekarang;
      selectTahun.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });