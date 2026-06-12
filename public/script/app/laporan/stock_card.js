let dtList = new Tabulator("#dt-list", {
    pagination: true, 
    paginationSize: 10,
    paginationButtonCount: 5,
    columns:[
        {title:"Tanggal", field:"tanggal", width:"7%"},
        {title:"Transaksi", field:"transaksi", hozAlign:"left",width:"13%"},
        {title:"No. Transaksi", field:"kode_transaksi", hozAlign:"left",width:"12%"},
        {title:"Unit", field:"nama_satuan", hozAlign:"left",width:"10%"},
        {title:"Lot", field:"lot_no", hozAlign:"left",width:"10%"},
        {title:"Pack", field:"pack_name", hozAlign:"left",width:"10%"},
        // {title:"Saldo Awal", field:"saldo_awal", hozAlign:"right",width:"10%", bottomCalc: 'sum'},
        {title:"Qty<br>Masuk", field:"masuk", hozAlign:"right",width:"10%", bottomCalc: 'sum', headerHozAlign: "center"},
        {title:"Qty<br>Keluar", field:"keluar", hozAlign:"right",width:"10%", bottomCalc: 'sum', headerHozAlign: "center"},
        // {title:"Saldo Akhir", field:"saldo_akhir", hozAlign:"right",width:"10%", bottomCalc: 'sum'},
        {title:"Nilai", field:"price", hozAlign:"right",width:"12%", formatter:"money", headerHozAlign: "center"},
        {title:"Jumlah", field:"jumlah", hozAlign:"right",width:"15%", bottomCalc: 'sum', formatter:"money", bottomCalcFormatter: 'money', headerHozAlign: "center"},
        // {title:"Saldo", field:"saldo_akhir", hozAlign:"right",width:"15%"},
    ],
    locale: 'id',    
    // layout: 'fitColumns',
    placeholder: "Tidak ada data",
});

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
            title: "Stok Minimum", field: "stok_minimum", headerSort: false,
            width: "15%",visible:false
        },
        {
            title: "Satuan", field: "nama_satuan", headerSort: false,
            width: "10%"
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
            dtListBarang.setFilter("", "like", elSearch.val());
        }, 600);
    });
}

dtListBarang.on("rowClick", function(e, row){
    var kodeBarang = row._row.data.kode_barang.replace(/<[^>]*>/g, '');
    var idBarang = row._row.data.id;
    var namaBarang = row._row.data.nama_barang.replace(/<[^>]*>/g, '');
    $('#filter_barang_id').val(idBarang)
    $("#filter_barang").val(`${kodeBarang} - ${namaBarang}`);
    $("#modal-barang").modal("hide");
})

$("#filter_barang").click(function () {
    setTimeout(() => {
        dtListBarang.redraw(true)
    }, 500);
    $("#modal-barang").modal("show");
    dtListBarang.deselectRow();
});

$("#btn-tampilkan").click(function () {
    if($("#filter_barang_id").val() == "" || $("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "" ){
      Swal.fire({
        title: 'Warning',
        text: 'Tahun,Bulan,Barang & Gudang harus dipilih',
        icon: 'warning',
      })
      return false    
    }
    getDataLaporan()
  });

  $("#btn-reset").click(function () {
      $("#filter_barang").val("");
      $("#filter_barang_id").val("");
      $("#filter_gudang").val("").trigger("change");
      $("#filter_tahun").val("").trigger("change");
      $("#filter_bulan").val("").trigger("change");
  });

  function getDataLaporan(){
    $.ajax({
        url: `/laporan/stock-card/list?filter_barang_id=${$('#filter_barang_id').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}`,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            $("#total_awal").val(data.total_awal);
            $("#total_akhir").val(data.total_akhir);
            dtList.setData(data.data)
            
            setTimeout(() => {
                dtList.redraw(true)
            }, 500);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
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