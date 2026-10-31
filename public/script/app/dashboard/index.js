$(document).ready(function () {
    // Base URL aplikasi (mengikuti folder deploy, misal /citraknitt/public/).
    // Dipakai untuk semua endpoint AJAX di Dashboard supaya tidak nyasar ke root
    // domain kalau app tidak di-hosting persis di root (lihat <base href> di template.php).
    const DASH_BASE = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/');

    // Format angka konsisten dengan konvensi Indonesia (titik ribuan, koma desimal)
    // dipakai untuk semua kolom "money" Tabulator di seluruh Dashboard.
    const idMoneyParams = { thousand: ".", decimal: ",", precision: 2, symbol: "" };

    // Formatter untuk kolom QTY (angka biasa, bukan uang) - tetap pakai pemisah ribuan
    // tapi tanpa paksa 2 angka desimal kalau nilainya bulat.
    function cellQty(cell) {
        let val = parseFloat(cell.getValue());
        if (isNaN(val)) val = 0;
        return val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    const bulanSekarang = new Date().getMonth() + 1;
    const tahunSekarang = new Date().getFullYear();

    $('#filter_lap_bulan').val(bulanSekarang);
    $('#filter_lap_tahun').val(tahunSekarang);

    $('#filter_saldo_bulan').val(bulanSekarang);
    $('#filter_saldo_tahun').val(tahunSekarang);
    // table tracking order 
    let dtListTracking = new Tabulator("#tbl-tracking", {
        columns: [
            {
                title: "FOTO", field: "btnGambar", headerSort: false, formatter: "html",
                width: 120, hozAlign: 'center', cssClass: 'text-center'
            },
			{
				title: 'TGL TRANSAKSI', field: 'tgl_transaksi', headerSort:false, sorter: 'string',
				width: 130, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 
				
			{
				title: 'BUYER', field: 'nama', headerSort:false, sorter: 'string',
				width: 180
			}, 

            {
				title: 'STYLE', field: 'keterangan', headerSort:false, sorter: 'string',
				formatter : "html", width: 180
			},

            {
				title: 'TGL DEADLINE', field: 'tgl_deadline', headerSort:false, sorter: 'string',
				width: 120, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 

            {
				title: 'TGL DEADLINE 2', field: 'tgl_deadline_dua', headerSort:false, sorter: 'string',
				width: 130, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			}, 

            {
				title: 'QTY', field: 'qty', headerSort:false, sorter: 'string',
				width: 100, formatter: cellQty, hozAlign: 'right', cssClass: 'text-end', bottomCalc: 'sum', bottomCalcFormatter: cellQty,
			},

            {
                title: "NO. PRODUKSI", field: "btnProd", headerSort: false, formatter: "html",
                width: 120, hozAlign: 'center', cssClass: 'text-center'
            },

            {
				title: 'PROSES<br>PRODUKSI', field: 'qty_prod', headerSort:false, sorter: 'string',
				width: 100, formatter: cellQty, hozAlign: 'right', cssClass: 'text-end', bottomCalc: 'sum', bottomCalcFormatter: cellQty,
			},

            {
				title: 'HASIL<br>PRODUKSI', field: 'qty_hasil', headerSort:false, sorter: 'string',
				width: 100, formatter: cellQty, hozAlign: 'right', cssClass: 'text-end', bottomCalc: 'sum', bottomCalcFormatter: cellQty,
			},

            {
				title: 'SISA<br>PRODUKSI', field: 'qty_sisa', headerSort:false, sorter: 'string',
				width: 100, formatter: cellQty, hozAlign: 'right', cssClass: 'text-end', bottomCalc: 'sum', bottomCalcFormatter: cellQty,
			},

            {
                title: "NO. PENGIRIMAN", field: "btnDev", headerSort: false, formatter: "html",
                width: 150, hozAlign: 'center', cssClass: 'text-center'
            },

            {
				title: 'QTY<br>PENGIRIMAN', field: 'qty_kirim', headerSort:false, sorter: 'string',
				width: 110, formatter: cellQty, hozAlign: 'right', cssClass: 'text-end', bottomCalc: 'sum', bottomCalcFormatter: cellQty,
			},

            {
				title: 'QTY BELUM<br>DIKIRIM ', field: 'qty_sisa_kirim', headerSort:false, sorter: 'string',
				width: 100, formatter: cellQty, hozAlign: 'right', cssClass: 'text-end', bottomCalc: 'sum', bottomCalcFormatter: cellQty,
			},
            {
                title: "NO. TRANSAKSI", field: "btnOrder", headerSort: false, formatter: "html",
                width: 130, hozAlign: 'center', cssClass: 'text-center'
            },
            {
				title: 'NILAI SO', field: 'harga_total', headerSort:false, sorter: 'number',
				width: 160, formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end',
                bottomCalc: 'sum', bottomCalcFormatter: "money", bottomCalcFormatterParams: idMoneyParams,
                bottomCalcParams: { allRows: true }
			},
            {
				title: 'NILAI INVOICE', field: 'nilai_invoice', headerSort:false, sorter: 'number',
				width: 160, formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end',
                bottomCalc: 'sum', bottomCalcFormatter: "money", bottomCalcFormatterParams: idMoneyParams,
                bottomCalcParams: { allRows: true }
			},
            {
				title: 'NILAI PEMBAYARAN', field: 'pembayaran', headerSort:false, sorter: 'number',
				width: 160, formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end',
                bottomCalc: 'sum', bottomCalcFormatter: "money", bottomCalcFormatterParams: idMoneyParams,
                bottomCalcParams: { allRows: true }
			},
        ],
        layout: 'fitColumns',
        ajaxURL: DASH_BASE + "dashboard/list_order",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        height: 800,
        ajaxRequesting: function (url, params) {
            params.start = 0;
            params.length = 99999;
            params.tracking_id = $("#filter_tracking").val();
        },
        ajaxResponse: function (url, params, response) {
            return response.data || [];
        },
        dataSendParams: {
            sorters: "order"
        },
        selectableRows: false,
    });

    $("#filter_tracking").on("change", function () {
        let val = $(this).val();
        console.log("Filter tracking berubah ke:", val);

        // Reset ke halaman pertama biar data sesuai
        dtListTracking.setData(DASH_BASE + "dashboard/list_order");
    });

    let searchThread = null;
    let elSearch = $("#inp-tracking");
    if (elSearch != null) {
        elSearch.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThread);
            searchThread = setTimeout(function () {
                dtListTracking.setFilter("", "like", elSearch.val());
            }, 600);
        });
    }

    // end table tracking order 


    // table invoice
    let dtListInvoice = new Tabulator("#tbl-invoice", {
        columns: [
            // {
            //     title: "NO. INVOICE", field: "btnInv", headerSort: false, formatter: "html",
            //     width: 110, hozAlign: 'center', cssClass: 'text-center'
            // }, 

			// {
			// 	title: 'TGL<br>INVOICE', field: 'tgl_invoice', headerSort:false, sorter: 'string',
			// 	width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			// }, 
				
			{
				title: 'BUYER', field: 'nama', headerSort:false, sorter: 'string',
				width: "16%", formatter : "textarea", vertAlign: 'middle'
			}, 

            // {
			// 	title: 'TGL<br>JATUH<br>TEMPO', field: 'tgl_jatuh_tempo', headerSort:false, sorter: 'string',
			// 	width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			// }, 

            {
				title: 'NILAI SO', field: 'nilai_so', headerSort:false, sorter: 'string',
				width: "17%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end', vertAlign: 'middle', bottomCalcFormatter: 'money', bottomCalcFormatterParams: idMoneyParams, bottomCalc: 'sum',
                bottomCalcParams: { allRows: true }
			},
            {
				title: 'NILAI<br>INVOICE', field: 'nilai_invoice', headerSort:false, sorter: 'string',
				width: "17%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end', vertAlign: 'middle', bottomCalcFormatter: 'money', bottomCalcFormatterParams: idMoneyParams, bottomCalc: 'sum',
                bottomCalcParams: { allRows: true }
			},
            {
				title: 'SISA <br>TAGIHAN', field: 'sisa_tagihan', headerSort:false, sorter: 'string',
				width: "17%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end', vertAlign: 'middle', bottomCalcFormatter: 'money', bottomCalcFormatterParams: idMoneyParams, bottomCalc: 'sum',
                bottomCalcParams: { allRows: true }
			},
            {
				title: 'PEMBAYARAN', field: 'pembayaran', headerSort:false, sorter: 'string',
				width: "16%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end', vertAlign: 'middle', bottomCalcFormatter: 'money', bottomCalcFormatterParams: idMoneyParams, bottomCalc: 'sum',
                bottomCalcParams: { allRows: true }
			},
            {
				title: 'SISA <br> PEMBAYARAN', field: 'sisa_pembayaran', headerSort:false, sorter: 'string',
				width: "17%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end', vertAlign: 'middle', bottomCalcFormatter: 'money', bottomCalcFormatterParams: idMoneyParams, bottomCalc: 'sum',
                bottomCalcParams: { allRows: true }
			},

            
        ],
        layout: 'fitColumns',
        ajaxURL: DASH_BASE + "dashboard/list_invoice",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        height: 700,
        ajaxRequesting: function (url, params) {
            // Ambil SEMUA baris - tabel scroll ke bawah kalau datanya banyak,
            // supaya tidak ada data yang "hilang" saat scroll padahal cuma belum ke-fetch.
            params.start = 0;
            params.length = 99999;
        },
        ajaxResponse: function (url, params, response) {
            return response.data || [];
        },
        dataSendParams: {
            sorters: "order"
        },
        selectableRows: false,
    });

    let searchThreadInv = null;
    let elSearchInv = $("#inp-invoice");
    if (elSearchInv != null) {
        elSearchInv.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThreadInv);
            searchThreadInv = setTimeout(function () {
                dtListInvoice.setFilter("", "like", elSearchInv.val());
            }, 600);
        });
    }
    // end table invoice

    // table PO
    let dtListPo = new Tabulator("#tbl-po", {
        columns: [
            // {
            //     title: "NO. PO", field: "btnPo", headerSort: false, formatter: "html",
            //     width: 110, hozAlign: 'center', cssClass: 'text-center'
            // }, 

			// {
			// 	title: 'TGL<br>PO', field: 'po_date', headerSort:false, sorter: 'string',
			// 	width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			// }, 
				
			{
				title: 'SUPPLIER', field: 'nama', headerSort:false, sorter: 'string', formatter : "textarea",
				width: "25%"
			}, 
			{
				title: 'JATUH TEMPO', field: 'po_date_exp', headerSort:false, sorter: 'string', formatter : "textarea",
				width: "15%"
			}, 

            // {
			// 	title: 'TGL<br>JATUH<br>TEMPO', field: 'date_exc', headerSort:false, sorter: 'string',
			// 	width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			// }, 
            // {
			// 	title: 'TGL<br>JATUH<br>TEMPO', field: 'due_date', headerSort:false, sorter: 'string',
			// 	width: 100, formatter : "html", hozAlign: 'center', cssClass: 'text-center'
			// }, 

            {
				title: 'NILAI PO', field: 'total_bayar', headerSort:false, sorter: 'string',
				width: "20%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end'
			},

            {
				title: 'PEMBAYARAN', field: 'dibayar', headerSort:false, sorter: 'string',
				width: "20%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end'
			},

            {
				title: 'SISA', field: 'sisa_bayar', headerSort:false, sorter: 'string',
				width: "20%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end'
			},

            
        ],
        layout: 'fitColumns',
        ajaxURL: DASH_BASE + "dashboard/list_po",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        height: 260, // +2 baris data dibanding sebelumnya (200px/5 baris)
        ajaxRequesting: function (url, params) {
            // Ambil SEMUA baris - tabel scroll ke bawah kalau datanya banyak,
            // supaya tidak ada data yang "hilang" saat scroll padahal cuma belum ke-fetch.
            params.start = 0;
            params.length = 99999;
        },
        ajaxResponse: function (url, params, response) {
            return response.data || [];
        },
        dataSendParams: {
            sorters: "order"
        },
        selectableRows: false,
    });

    // table saldo
    let dtListSaldo = new Tabulator("#tbl-saldo", {
        columns: [
            {
                title: "KODE", field: "kode", headerSort: false, formatter: "html",
                width: "10%", cssClass: 'text-center'
            }, 
				
			{
				title: 'NAMA', field: 'nama', headerSort:false, sorter: 'string', formatter : "textarea",
				width: "40%"
			}, 

            {
				title: 'SALDO', field: 'saldo', headerSort:false, sorter: 'string',
				width: "50%", formatter : "money", formatterParams: idMoneyParams, hozAlign: 'right', cssClass: 'text-end', bottomCalcFormatter: 'money', bottomCalcFormatterParams: idMoneyParams, bottomCalc: 'sum',
                bottomCalcParams: { allRows: true }
			},

            
        ],
        layout: 'fitColumns',
        ajaxURL: DASH_BASE + "dashboard/list_saldo",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        height: 300, // +3 baris data dibanding sebelumnya (200px/5 baris)
        ajaxRequesting: function (url, params) {
            // Ambil SEMUA baris yang sesuai (bukan dibatasi angka tertentu) - tabel
            // scroll ke bawah kalau datanya banyak, supaya tidak ada data yang
            // "hilang" saat scroll padahal sebenarnya cuma belum ke-fetch.
            params.start = 0;
            params.length = 99999;
            params.month = $("#filter_saldo_bulan").val();
            params.year = $("#filter_saldo_tahun").val();
        },
        ajaxResponse: function (url, params, response) {
            // TAMBAHAN (2026-08-08): m_mutasi_history (sumber saldo ini) cuma
            // terisi kalau tombol "Update" pernah diklik utk bulan/tahun yang
            // dipilih - kalau belum pernah, grid-nya kosong tapi itu BUKAN
            // berarti datanya memang kosong. Bedakan pesannya supaya tidak
            // disangka bug/data hilang.
            // FIX (2026-08-08): versi Tabulator yang dipakai di proyek ini (5.6.1)
            // tidak punya method setPlaceholder() - itu menyebabkan TypeError
            // "dtListSaldo.setPlaceholder is not a function" setiap kali grid ini
            // di-load. Placeholder yang benar diubah lewat properti
            // options.placeholder, yang dibaca ulang oleh Tabulator setiap kali
            // mau menampilkan placeholder (lihat RowManager.js initializePlaceholder()).
            if (response && response.sudah_diupdate === false) {
                dtListSaldo.options.placeholder = 'Data belum diperbarui untuk periode ini - klik tombol "Update" di atas untuk menghitungnya.';
            } else {
                dtListSaldo.options.placeholder = "Tidak ada data";
            }
            return response.data || [];
        },
        dataSendParams: {
            sorters: "order"
        },
        selectableRows: false,
    });

    $("#filter_saldo_bulan").on("change", function () {
        let val = $(this).val();
        // Reset ke halaman pertama biar data sesuai
        dtListSaldo.setData(DASH_BASE + "dashboard/list_saldo");
    });
    
    $("#filter_saldo_tahun").on("change", function () {
        let val = $(this).val();
        // Reset ke halaman pertama biar data sesuai
        dtListSaldo.setData(DASH_BASE + "dashboard/list_saldo");
    });

    $("#btn_update").click(function () {
        $(".preloader").css("opacity", "0.7").show();
        // if($("#filter_tahun").val() == "" || $("#filter_bulan").val() == "" || $('#filter_gudang').val() == "" ){
        //     Swal.fire({
        //         title: 'Warning',
        //         text: 'Tahun,Bulan & Gudang harus dipilih',
        //         icon: 'warning',
        //     })
        //      $(".preloader").hide().css("opacity", "1");
        //     return false    
        // }
        getUpdateDataLaporan()
    });

  function getUpdateDataLaporan(){
    const tahun = $('#filter_saldo_tahun').val();
    const bulan = $('#filter_saldo_bulan').val();
    const url = `/dashboard/update_saldo/${tahun}/${bulan}`;
    // let url = "/keuangan/laporan_mutasi/getExcelAll/" + filter_bulan + "/" + filter_tahun + "/" + select_payment_type_one + "/" + select_payment_type_one_text;
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json', 
        success: function(data) {
            
            // dtListSaldo.setData(data.data)
    
            // setTimeout(() => {
            //     dtListSaldo.redraw(true)
            // }, 500);
            dtListSaldo.setData(DASH_BASE + "dashboard/list_saldo");
            $(".preloader").hide().css("opacity", "1");
        },
        error: function(xhr, status, error) {
            $(".preloader").hide().css("opacity", "1");
            console.error('Error fetching data:', error);
        }
    });
  }

    let searchThreadSaldo = null;
    let elSearchSaldo = $("#inp-saldo");
    if (elSearchSaldo != null) {
        elSearchSaldo.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThreadSaldo);
            searchThreadSaldo = setTimeout(function () {
                dtListSaldo.setFilter("", "like", elSearchSaldo.val());
            }, 600);
        });
    }

    let searchThreadPo = null;
    let elSearchPo = $("#inp-po");
    if (elSearchPo != null) {
        elSearchPo.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThreadPo);
            searchThreadPo = setTimeout(function () {
                dtListPo.setFilter("", "like", elSearchPo.val());
            }, 600);
        });
    }
    // end table PO

    $('#filter_lap_bulan').on('change', function () {
        $(".preloader").css("opacity", "0.7").show();
        fetchData(); // Panggil fungsi fetchData saat halaman dimuat
    });

    $('#filter_lap_tahun').on('change', function () {
        $(".preloader").css("opacity", "0.7").show();
        fetchGrafikData();
        fetchData(); // Panggil fungsi fetchData saat halaman dimuat
    });

    fetchGrafikData();
    fetchData(); // Panggil fungsi fetchData saat halaman dimuat

    function fetchData() {
        const val1 = $('#filter_lap_bulan').val();
        const val2 = $('#filter_lap_tahun').val();

        if (!val1 || !val2) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Kedua dropdown harus dipilih terlebih dahulu.'
            });
            return;
        }
        
        // Hanya panggil jika keduanya terisi (atau sesuai logikamu)
        if (val1 && val2) {
            
          $.ajax({
            url: DASH_BASE + 'dashboard/list_laba',
            method: 'GET',
            data: {
              month: val1,
              year: val2
            },
            success: function (data) {
                const penjualan = data.penjualan;
                const pemakaian = data.pemakaian;
                const laba_kotor = data.laba_kotor;
                const total_biaya = data.total_biaya;
                const laba_bersih = data.laba_bersih;
                const nama_biaya = data.nama_biaya;
                const harga_per_biaya = data.harga_per_biaya;

                const targetRow = $("#total_operasional").closest('tr');

                $(".biaya-operasional-row").remove();
        
                $('#penjualan').text(penjualan);     
                $('#total_penjualan').text(penjualan); 
                $('#pemakaian').text(pemakaian);      
                $('#pemakaian_lainnya').text(data.pemakaian_lainnya || '0,00');
                $('#total_pemakaian').text(data.total_pemakaian || pemakaian); 
                $('#laba_kotor').text(laba_kotor); 
                $('#total_operasional').text(total_biaya); 
                $('#laba_bersih').text(laba_bersih); 

                // Pastikan kedua array punya panjang sama
                for (let i = 0; i < nama_biaya.length; i++) {
                    const rowHtml = `
                        <tr class="biaya-operasional-row">
                        <td class="p-s-24">- ${nama_biaya[i]}</td>
                        <td class="text-end">${harga_per_biaya[i]}</td>
                        <td></td>
                        </tr>
                    `;
                    targetRow.before(rowHtml);
                }
                $(".preloader").hide().css("opacity", "1");
            },
            error: function () {
              $('#result').html('Gagal memuat data.');
              $(".preloader").hide().css("opacity", "1");
            }
          });
        }
      }

      function fetchGrafikData() {
            const val2 = $('#filter_lap_tahun').val();
            if (!val2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops!',
                    text: 'Harap Pilih tahun Dahulu!'
                });
                return;
            }
            
            // Hanya panggil jika keduanya terisi (atau sesuai logikamu)
            if (val2) {
                
            $.ajax({
                url: DASH_BASE + 'dashboard/list_grafik',
                method: 'GET',
                data: {
                year: val2
                },
                success: function (data) {

                    const penjualan = data.penjualan;
                    

                    // Inisialisasi elemen chart
                    var chartDom = document.getElementById('chartBar');
                    var myChart = echarts.init(chartDom);

                    // Contoh data statis (12 bulan)
                    const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const biaya = data.biaya;
                    // Konfigurasi grafik batang

                    const option = {
                                responsive: true,
                                 maintainAspectRatio: false,
                                // title: {
                                //     text: 'Grafik Penjualan & Biaya per Bulan',
                                //     left: 'center'
                                // },
                                tooltip: {
                                    trigger: 'axis'
                                },
                                legend: {
                                    data: ['Penjualan', 'Biaya']
                                },
                                toolbox: {
                                    show: true,
                                    feature: {
                                        dataView: { show: true, readOnly: false },
                                        magicType: { show: true, type: ['line', 'bar'] },
                                        restore: { show: true },
                                        saveAsImage: { show: true }
                                    }
                                },
                                calculable: true,
                                xAxis: [
                                    {
                                        type: 'category',
                                        // prettier-ignore
                                        data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                                    }
                                ],
                                yAxis: [
                                    {
                                        type: 'value',
                                        axisLabel: {
                                            formatter: function (value) {
                                                if (value >= 1_000_000_000_000) return (value / 1_000_000_000_000) + 'T';
                                                if (value >= 1_000_000_000) return (value / 1_000_000_000) + 'M';
                                                if (value >= 1_000_000) return (value / 1_000_000) + 'Jt';
                                                if (value >= 1_000) return (value / 1_000) + 'K';
                                                return value;
                                            }
                                        }
                                    }
                                ],
                                series: [
                                    {
                                        name: 'Penjualan',
                                        type: 'bar',
                                        data: penjualan,
                                        markPoint: {
                                            data: [
                                                // { type: 'max', name: 'Max' },
                                                { type: 'min', name: 'Min' }
                                            ]
                                        },
                                        itemStyle: {
                                            color: "#28a745"  // pakai warna bootstrap success
                                        },
                                        // markLine: {
                                        //     data: [{ type: 'average', name: 'Avg' }]
                                        // }
                                    },
                                    {
                                        name: 'Biaya',
                                        type: 'bar',
                                        data: biaya,
                                        markPoint: {
                                            data: [
                                                // { type: 'max', name: 'Max' },
                                                { type: 'min', name: 'Min' }
                                            ]
                                        },
                                        itemStyle: {
                                            color: "#dc3545"  // pakai warna bootstrap success
                                        },
                                        // markLine: {
                                        //     data: [{ type: 'average', name: 'Avg' }]
                                        // }
                                    }
                                ]
                            };

                    // Render chart
                    myChart.setOption(option);
                            
                    // Biar responsive
                    window.addEventListener('resize', () => {
                        myChart.resize();
                    });
                },
                error: function () {
                    $('#result').html('Gagal memuat data.');
                }
            });
        }
      }

    
});
