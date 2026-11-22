let dtList = new Tabulator("#dt-list", {
  pagination: true,
  paginationSize: 100,
  paginationButtonCount: 5,
  paginationCounter: "rows",
  groupBy: ['barang', 'pack_name'],
  groupClosedShowCalcs: true,

  // ✅ Ganti groupFooter → groupHeader
  groupHeader: function (value, count, data, group) {
    let isBarang = !group.getParentGroup();

    if (isBarang) {
      let totalMasuk = data.reduce((s, r) => s + (parseFloat(r.masuk) || 0), 0);
      let totalKeluar = data.reduce((s, r) => s + (parseFloat(r.keluar) || 0), 0);
      let totalAkhir = data.reduce((s, r) => s + (parseFloat(r.saldo_akhir) || 0), 0);

      let fmt = (n) => n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      return `
        <span style="font-weight:bold;">${value}</span>
        <span style="font-size:0.85em; color:#666;">(${count} items)</span>
        <span style="float:right; font-weight:bold; color:#c00; font-size:0.85em; margin-right:10px;">
          Masuk: ${fmt(totalMasuk)} &nbsp;|&nbsp;
          Keluar: ${fmt(totalKeluar)} &nbsp;|&nbsp;
          Akhir: ${fmt(totalAkhir)}
        </span>
      `;
    }

    // Level pack_name: jika pack_name null/empty/undefined, tampilkan "-"
    let displayPack = (value === null || value === "null" || value === "" || !value) ? "-" : value;
    return `<b>${displayPack}</b> <span style="color:#666;">(${count} items)</span>`;
  },

  columns: [
    { title: "PACK", field: "pack_name", width: "7%" },
    { title: "LOT", field: "lot_no", width: "8%" },
    { title: "Size/Warna", field: "nama_satuan", hozAlign: "left", width: "10%" },
    {
      title: "Qty<br>Awal", field: "saldo_awal", hozAlign: "right", width: "9%", headerHozAlign: "right",
      formatter: "money",
      formatterParams: { decimal: ",", thousand: ".", precision: 2 }
    },
    {
      title: "Qty<br>Masuk", field: "masuk", hozAlign: "right", width: "9%", headerHozAlign: "right",
      formatter: "money",
      formatterParams: { decimal: ",", thousand: ".", precision: 2 },
      bottomCalc: "sum",
      bottomCalcFormatter: "money",
      bottomCalcFormatterParams: { decimal: ",", thousand: ".", precision: 2 }
    },
    {
      title: "Qty<br>Keluar", field: "keluar", hozAlign: "right", width: "9%", headerHozAlign: "right",
      formatter: "money",
      formatterParams: { decimal: ",", thousand: ".", precision: 2 },
      bottomCalc: "sum",
      bottomCalcFormatter: "money",
      bottomCalcFormatterParams: { decimal: ",", thousand: ".", precision: 2 }
    },
    {
      title: "Qty<br>Akhir", field: "saldo_akhir", hozAlign: "right", width: "10%", headerHozAlign: "right",
      formatter: "money",
      formatterParams: { decimal: ",", thousand: ".", precision: 2 },
      bottomCalc: "sum",
      bottomCalcFormatter: "money",
      bottomCalcFormatterParams: { decimal: ",", thousand: ".", precision: 2 }
    },
    {
      title: "Qty<br>Booked", field: "qty_booked", hozAlign: "right", width: "10%", headerHozAlign: "right",
      formatter: function (cell, formatterParams, onRendered) {
        let val = parseFloat(cell.getValue() || 0);
        if (val > 0) {
          let str = val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          return `<span class="badge bg-warning text-dark font-weight-bold" style="cursor:pointer; font-size:11px;" title="Klik untuk melihat rincian dokumen booking">${str} <i class="ti-info-alt"></i></span>`;
        }
        return `<span style="color:#aaa;">0,00</span>`;
      },
      cellClick: function (e, cell) {
        let rowData = cell.getRow().getData();
        let val = parseFloat(rowData.qty_booked || 0);
        if (val > 0) {
          showBookingDetailModal(rowData);
        }
      },
      bottomCalc: "sum",
      bottomCalcFormatter: "money",
      bottomCalcFormatterParams: { decimal: ",", thousand: ".", precision: 2 }
    },
    {
      title: "Available<br>Qty", field: "available_qty", hozAlign: "right", width: "10%", headerHozAlign: "right",
      formatter: function (cell, formatterParams, onRendered) {
        let val = parseFloat(cell.getValue() || 0);
        let str = val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (val <= 0) {
          return `<span class="text-danger font-weight-bold">${str}</span>`;
        }
        return `<span class="text-success font-weight-bold">${str}</span>`;
      },
      bottomCalc: "sum",
      bottomCalcFormatter: "money",
      bottomCalcFormatterParams: { decimal: ",", thousand: ".", precision: 2 }
    },
    {
      title: "Nilai", field: "price", hozAlign: "right", width: "10%",
      formatter: "money",
      formatterParams: { decimal: ",", thousand: ".", symbol: "Rp", precision: 0 },
      headerHozAlign: "right",
    },
    { title: "Tanggal", field: "tanggal", hozAlign: "right", width: "8%" },
  ],

  locale: 'id',
  placeholder: "Tidak ada data",
  paginationDataReceived: function (data) { updatePageInfo(); },
  paginationChanged: function (pagenum) { updatePageInfo(); },
  dataLoaded: function (data) { updatePageInfo(); }
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
      if ($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "") {
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
  if ($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "") {
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
  if ($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "") {
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
  const selectGudang = document.getElementById("filter_gudang");
  let defaultGudangVal = "";
  if (selectGudang) {
    let optUtama = Array.from(selectGudang.options).find(opt => opt.text.trim().toLowerCase() === "gudang utama" || opt.value == "1");
    if (optUtama) defaultGudangVal = optUtama.value;
  }

  const selectJenisBarang = document.getElementById("filter_jenis_barang");
  let defaultJenisVal = "";
  if (selectJenisBarang) {
    let optBenang = Array.from(selectJenisBarang.options).find(opt => opt.text.trim().toUpperCase() === "BENANG RAJUT" || opt.value == "2");
    if (optBenang) defaultJenisVal = optBenang.value;
  }

  $("#filter_jenis_barang").val(defaultJenisVal).trigger("change");
  $("#filter_gudang").val(defaultGudangVal).trigger("change");
  $("#filter_tahun").val("").trigger("change");
  $("#filter_bulan").val("").trigger("change");
});

function getDataLaporan() {
  $url = `/laporan/persediaan/list?filter_jenis_id=${$('#filter_jenis_barang').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}`;
  if (elSearch.val() != null && elSearch.val().length > 3) {
    $url = `/laporan/persediaan/list?filter_jenis_id=${$('#filter_jenis_barang').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}&search=${$('#tb-search').val()}`;
  }
  $.ajax({
    url: $url,
    type: 'GET',
    dataType: 'json',
    success: function (data) {

      dtList.setData(data.data)

      setTimeout(() => {
        dtList.redraw(true)
      }, 500);
      $(".preloader").hide().css("opacity", "1");
    },
    error: function (xhr, status, error) {
      $(".preloader").hide().css("opacity", "1");
      console.error('Error fetching data:', error);
    }
  });
}

$("#updateData").click(function () {
  $(".preloader").css("opacity", "0.7").show();
  if ($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "") {
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

function getUpdateDataLaporan() {
  $.ajax({
    url: `/laporan/persediaan/update-list?filter_jenis_id=${$('#filter_jenis_barang').val()}&tahun=${$('#filter_tahun').val()}&bulan=${$('#filter_bulan').val()}&filter_gudang_id=${$('#filter_gudang').val()}`,
    type: 'GET',
    dataType: 'json',
    success: function (data) {

      dtList.setData(data.data)

      setTimeout(() => {
        dtList.redraw(true)
      }, 500);

      $(".preloader").hide().css("opacity", "1");
    },
    error: function (xhr, status, error) {
      console.error('Error fetching data:', error);
      $(".preloader").hide().css("opacity", "1");
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const selectGudang = document.getElementById("filter_gudang");
  const selectJenisBarang = document.getElementById("filter_jenis_barang");
  const selectBulan = document.getElementById("filter_bulan");
  const selectTahun = document.getElementById("filter_tahun");

  // Set Gudang Utama sebagai default
  if (selectGudang) {
    let optUtama = Array.from(selectGudang.options).find(opt => opt.text.trim().toLowerCase() === "gudang utama" || opt.value == "1");
    if (optUtama) {
      $(selectGudang).val(optUtama.value).trigger("change");
    }
  }

  // Set BENANG RAJUT sebagai default
  if (selectJenisBarang) {
    let optBenang = Array.from(selectJenisBarang.options).find(opt => opt.text.trim().toUpperCase() === "BENANG RAJUT" || opt.value == "2");
    if (optBenang) {
      $(selectJenisBarang).val(optBenang.value).trigger("change");
    }
  }

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

/**
 * Menampilkan modal rincian transaksi draft yang sedang booking lot/pack ini.
 */
function showBookingDetailModal(data) {
  let modalEl = document.getElementById('modal-detail-booking');
  if (!modalEl) return;
  let modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  let idBarang = data.id_barang;
  let lotNo = data.lot_no || '-';
  let packId = data.pack_id || '';
  let packName = data.pack_name || '-';
  let namaBarang = data.nama_barang || data.barang || `ID ${idBarang}`;
  let kodeBarang = data.kode_barang || '';
  let idGudang = $('#filter_gudang').val() || '';
  let namaGudang = $('#filter_gudang option:selected').text() || 'Semua Gudang';

  $('#modal-booking-info-barang').html(`${kodeBarang} - ${namaBarang}`);
  $('#modal-booking-info-meta').html(`<b>Gudang:</b> ${namaGudang} &nbsp;|&nbsp; <b>Lot:</b> ${lotNo} &nbsp;|&nbsp; <b>Pack:</b> ${packName}`);

  $('#modal-booking-loading').show();
  $('#modal-booking-content').hide();
  $('#tbl-booking-body').empty();
  $('#tbl-booking-total').text('0,00');

  modal.show();

  $.ajax({
    url: 'laporan/persediaan/get-booked-details',
    type: 'GET',
    data: {
      id_barang: idBarang,
      lot_no: lotNo,
      pack_id: packId,
      id_gudang: idGudang
    },
    dataType: 'json',
    success: function (res) {
      $('#modal-booking-loading').hide();
      $('#modal-booking-content').show();

      if (res && res.status && res.data && res.data.length > 0) {
        let rowsHtml = '';
        let total = 0;
        res.data.forEach((item, idx) => {
          let qty = parseFloat(item.qty || 0);
          total += qty;
          let qtyStr = qty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          let badgeTipe = item.jenis_transaksi === 'Transfer Antar Gudang'
            ? '<span class="badge bg-info text-white">Transfer</span>'
            : '<span class="badge bg-primary text-white">Barang Keluar</span>';

          let routeUrl = item.url ? item.url : (item.jenis_transaksi === 'Transfer Antar Gudang'
            ? `trans/transfer/edit/${item.header_id}`
            : `trans/outgoing-goods/edit/${item.header_id}`);

          let ruteGudang = item.jenis_transaksi === 'Transfer Antar Gudang'
            ? `${item.gudang_asal || '-'} &rarr; ${item.gudang_tujuan || '-'}`
            : (item.gudang_asal || '-');

          let tglStr = item.tanggal ? item.tanggal : '-';

          rowsHtml += `
            <tr>
              <td class="text-center">${idx + 1}</td>
              <td class="font-weight-bold">
                <a href="${routeUrl}" target="_blank" class="text-primary text-decoration-underline" title="Buka transaksi">
                  ${item.kode_transaksi} <i class="ti-new-window small"></i>
                </a>
              </td>
              <td class="text-center">${tglStr}</td>
              <td class="text-center">${badgeTipe}</td>
              <td>${ruteGudang}</td>
              <td class="text-end font-weight-bold text-dark">${qtyStr}</td>
              <td><small>${item.pembuat || '-'}</small></td>
            </tr>
          `;
        });

        $('#tbl-booking-body').html(rowsHtml);
        $('#tbl-booking-total').text(total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kg');
      } else {
        $('#tbl-booking-body').html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada dokumen draft booking aktif.</td></tr>');
      }
    },
    error: function () {
      $('#modal-booking-loading').hide();
      $('#modal-booking-content').show();
      $('#tbl-booking-body').html('<tr><td colspan="7" class="text-center text-danger py-3">Gagal memuat rincian booking. Silakan coba lagi.</td></tr>');
    }
  });
}
