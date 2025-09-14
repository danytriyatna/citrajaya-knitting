$(document).ready(function () {
    let dtList = new Tabulator("#dt-list", {
        columns: [
            {
				title: 'Nomor Akun', field: 'coa_kode', headerSort:false, sorter: 'string',
				width: "25%", formatter : "html", visible : false
			}, 
			{
				title: 'Akun', field: 'coa_nama', headerSort:false, sorter: 'string',
				width: "35%", formatter : "html"
			}, 
			{
				title: 'Januari', field: 'bln1', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Februari', field: 'bln2', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Maret', field: 'bln3', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'April', field: 'bln4', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Mei', field: 'bln5', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Juni', field: 'bln6', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Juli', field: 'bln7', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Agustus', field: 'bln8', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'September', field: 'bln9', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Oktober', field: 'bln10', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'November', field: 'bln11', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},
            {
				title: 'Desember', field: 'bln12', headerSort:false, sorter: 'string',
				width: "15%", formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-end"
			},

           
        ],
        layout: 'fitColumns',
        ajaxURL: "/keuangan/laporan_mutasi/list",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: true,
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
            params.tahun = $("#slc_tahun").val();
        },
        ajaxResponse: function (url, params, response) {
            return response.data;
        },
        
        dataTree: true,
        dataTreeChildField: "children",
        dataTreeStartExpanded : true,
        dataTreeChildIndent: 15,
        
        selectableRows: false,
    });

    let btnCari = $("#btn_cari");
    btnCari.on("click", function(){
        dtList.setData();
    });

    $("#slc_tahun").select2({
		allowClear: false,
		placeholder: "- Pilih Periode -",
	});

    let btnExcel = $("#btn_excel");
    btnExcel.on("click", function(){
        let periode = $("#slc_tahun").val();
        let from_date = $("#from_date").val();
        let to_date = $("#to_date").val();
        let select_payment_type = $("#select_payment_type").val();
        let select_type_export = $("#select_type_export").val();
        let filter_bulan = $("#filter_bulan").val();
        let filter_tahun = $("#filter_tahun").val();
        let select_payment_type_one = $("#select_payment_type_one").val();
        let select_payment_type_one_text = $("#select_payment_type_one").find(':selected').text();

        if (select_type_export == 0) {
            if (from_date == null || from_date == "" || from_date == undefined) {
                Swal.fire({
                    text: "From Date Export Harus Diisi!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }
            else if (to_date == null || to_date == "" || to_date == undefined) {
                Swal.fire({
                    text: "To Date Export Harus Diisi!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }
            else if (select_payment_type == null || select_payment_type == "" || select_payment_type == undefined) {
                Swal.fire({
                    text: "Payment Type Export Harus Diisi!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }
            else if (parseDate(from_date) > parseDate(to_date)) {
                Swal.fire({
                    text: "From Date tidak boleh lebih besar dari To Date!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }

            else {
                let url = "/keuangan/laporan_mutasi/getExcelNew/" + from_date + "/" + to_date + "/" + select_payment_type;
                window.open(url, '_blank');
            }
        }
        else {
            if (filter_bulan == null || filter_bulan == "" || filter_bulan == undefined) {
                Swal.fire({
                    text: "Bulan Export Harus Diisi!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }
            else if (filter_tahun == null || filter_tahun == "" || filter_tahun == undefined) {
                Swal.fire({
                    text: "Tahun Export Harus Diisi!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }
            else if (select_payment_type_one == null || select_payment_type_one == "" || select_payment_type_one == undefined) {
                Swal.fire({
                    text: "Payment Type Export Harus Diisi!",
                    icon: 'warning',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }

            else {
                let url = "/keuangan/laporan_mutasi/getExcelAll/" + filter_bulan + "/" + filter_tahun + "/" + select_payment_type_one + "/" + select_payment_type_one_text;
                window.open(url, '_blank');
            }
        }
    });

    function parseDate(str) {
        // format dd-mm-yyyy
        let parts = str.split("-");
        return new Date(parts[2], parts[1] - 1, parts[0]); 
        // year, monthIndex (0=Jan), day
    }
    
    $('#select_type_export').on('change', function() {
        let val = $(this).val();
        if (val == "0") {
            $('.type-export-beban').removeAttr('hidden'); // tampilkan
            $('.type-export-mutasi').attr('hidden', true); // sembunyikan
        } else if (val == "1") {
            $('.type-export-beban').attr('hidden', true); // sembunyikan
            $('.type-export-mutasi').removeAttr('hidden'); // tampilkan
        }
    });

    // let searchThread = null;
    // let elSearch = $("#tb-search");
    // if (elSearch != null) {
    //     elSearch.keyup(function (e) {
    //         if ($(this).val().length < 3 && e.keyCode > 13) {
    //             return;
    //         }
    //         clearTimeout(searchThread);
    //         searchThread = setTimeout(function () {
    //             dtList.setFilter("", "like", elSearch.val());
    //         }, 600);
    //     });
    // }

});