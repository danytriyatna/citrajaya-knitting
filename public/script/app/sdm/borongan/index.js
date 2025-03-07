$(document).ready(function () {

    //Create Date Editor
    var dateEditor = function(cell, onRendered, success, cancel){
        //cell - the cell component for the editable cell
        //onRendered - function to call when the editor has been rendered
        //success - function to call to pass thesuccessfully updated value to Tabulator
        //cancel - function to call to abort the edit and return to a normal cell

        //create and style input
        var cellValue = luxon.DateTime.fromFormat(cell.getValue(), "dd/MM/yyyy").toFormat("yyyy-MM-dd"),
        input = document.createElement("input");

        input.setAttribute("type", "date");

        input.style.padding = "4px";
        input.style.width = "100%";
        input.style.boxSizing = "border-box";

        input.value = cellValue;

        onRendered(function(){
            input.focus();
            input.style.height = "100%";
        });

        function onChange(){
            if(input.value != cellValue){
                success(luxon.DateTime.fromFormat(input.value, "yyyy-MM-dd").toFormat("dd/MM/yyyy"));
            }else{
                cancel();
            }
        }

        //submit new value on blur or change
        input.addEventListener("blur", onChange);

        //submit new value on enter
        input.addEventListener("keydown", function(e){
            if(e.keyCode == 13){
                onChange();
            }

            if(e.keyCode == 27){
                cancel();
            }
        });

        return input;
    };

    //Create Time Editor
    var timeEditor = function(cell, onRendered, success, cancel){
        //cell - the cell component for the editable cell
        //onRendered - function to call when the editor has been rendered
        //success - function to call to pass the successfully updated value to Tabulator
        //cancel - function to call to abort the edit and return to a normal cell

        //create and style input
        var cellValue = cell.getValue(); // assumes cellValue is in "HH:mm" format
        var input = document.createElement("input");

        input.setAttribute("type", "time");

        input.style.padding = "4px";
        input.style.width = "100%";
        input.style.boxSizing = "border-box";

        // Set the initial value of the input to the cell's value
        input.value = cellValue;

        onRendered(function(){
            input.focus();
            input.style.height = "100%";
        });

        function onChange(){
            if(input.value != cellValue){
                // Pass the updated value back in "HH:mm" format
                success(input.value);
            }else{
                cancel();
            }
        }

        //submit new value on blur or change
        input.addEventListener("blur", onChange);

        //submit new value on enter
        input.addEventListener("keydown", function(e){
            if(e.keyCode == 13){ // Enter key
                onChange();
            }

            if(e.keyCode == 27){ // Escape key
                cancel();
            }
        });

        return input;
    };

    let inpTglAwal = $("#filter_tgl_awal");
    let inpTglAkhir = $("#filter_tgl_akhir");
    let inpProses = $("#filter_proses");
    let inpOperator = $("#filter_operator");
    let dtList = new Tabulator("#dt-absensi", {
        columns: [
            {
                title: " ", field: "print", headerSort: false, formatter: "html",
                width: "8%"
            },
            {
				title: 'Proses', field: 'proses', headerSort:false, sorter: 'string', frozen: true,
				align:'center', width: "20%"
			} ,
            {
				title: 'Style', field: 'keterangan_style', headerSort:false, sorter: 'string', frozen: true,
				align:'center',
			} ,
            
			// {
			// 	title: 'Harga', field: 'harga', headerSort:false, sorter: 'string',
			// 	formatter : "money",width: 180, cssClass: "text-right", hozAlign: "right", 
            //     bottomCalc: "sum", bottomCalcFormatter: "money", 
			// }, 
				
			{
				title: 'Qty', field: 'qty', headerSort:false, sorter: 'string',
				formatter : "money",width: 180, cssClass: "text-right", hozAlign: "right",
                bottomCalc: "sum", bottomCalcFormatter: "money", 
			}, 

            {
				title: 'Harga Total', field: 'harga_total', headerSort:false, sorter: 'string',
				formatter : "money", width: 180, cssClass: "text-right", hozAlign: "right",
                bottomCalc: "sum", bottomCalcFormatter: "money", 
			},
        ],
        groupBy:['nama_operator'],
        layout: 'fitColumns',
        ajaxURL: "/sdm/borongan/list",
        placeholder: "Tidak ada data",
        ajaxConfig: "POST",
        ajaxSorting: true,
        ajaxFiltering: false,
        sortMode: "remote",
        filterMode: "remote",
        minHeight: 300,
        ajaxRequesting: function (url, params) {
            params.start = params.size * (params.page - 1);
            params.length = params.size;
            params.tgl_awal = formatLocaleDate(inpTglAwal.val())
            params.tgl_akhir = formatLocaleDate(inpTglAkhir.val())
            params.id_operator = (inpOperator.val())
            params.id_proses = (inpProses.val())
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
        paginationSize: 9999999999,
        paginationButtonCount: 10,
        dataSendParams: {
            sorters: "order"
        },
        selectableRows: false,
    });

    $("#btn-filter").on("click", function(){
        dtList.setData();
    });

    $(".btn-print-new").on("click", function() {
        // Ambil data dari atribut tombol
        alert("wewe");
        var id_proses = $(this).data("id_proses");
        var id_operator = $(this).data("id_operator");

        // Ambil nilai input tanggal
        var tanggal_awal = $("#filter_tgl_awal").val();
        var tanggal_akhir = $("#filter_tgl_akhir").val();

        // Cek apakah tanggal telah diisi
        if (tanggal_awal === "" || tanggal_akhir === "") {
            alert("Harap isi tanggal awal dan tanggal akhir!");
            return;
        }

        const params = `id_operator=${id_operator}&id_proses=${id_proses}&tgl_awal=${formatLocaleDate(tanggal_awal)}&tgl_akhir=${formatLocaleDate(tanggal_akhir)}`;
        window.open(`/sdm/borongan/generate_kar?${params}`, '_blank'); // Ganti '/page' dengan path yang diinginkan
    
        window.open(url.toString(), '_blank');
    });

    $("#btn-generate").on("click", function(){
        // dtList.setData();
        // const url = new URL('/sdm/borongan/generate_kar'); // Ganti dengan URL tujuan
        // url.searchParams.append('id_operator', inpOperator.val());
        // url.searchParams.append('id_proses', inpProses.val());
        // url.searchParams.append('tgl_awal', formatLocaleDate(inpTglAwal.val()));
        // url.searchParams.append('tgl_akhir', formatLocaleDate(inpTglAkhir.val()));

        const params = `id_operator=${inpOperator.val()}&id_proses=${inpProses.val()}&tgl_awal=${formatLocaleDate(inpTglAwal.val())}&tgl_akhir=${formatLocaleDate(inpTglAkhir.val())}`;
        window.open(`/sdm/borongan/generate_kar?${params}`, '_blank'); // Ganti '/page' dengan path yang diinginkan
    
        window.open(url.toString(), '_blank');
    });

    let searchThread = null;
    let elSearch = $("#tb-search");
    if (elSearch != null) {
        elSearch.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThread);
            searchThread = setTimeout(function () {
                dtList.setFilter("", "like", elSearch.val());
            }, 600);
        });
    }

    $('#btn-save').on('click', function(){
        saveData()
        
    });

    function formatLocaleDate(localeDate) {
    
        // var months = {
        //     "Januari": "01",
        //     "Februari": "02",
        //     "Maret": "03",
        //     "April": "04",
        //     "Mei": "05",
        //     "Juni": "06",
        //     "Juli": "07",
        //     "Agustus": "08",
        //     "September": "09",
        //     "Oktober": "10",
        //     "November": "11",
        //     "Desember": "12"
        // };

        // var parts = localeDate.split(" ");
        // var day = parts[0].padStart(2, '0'); 
        // var month = months[parts[1]]; 
        // var year = parts[2];

        // return `${year}-${month}-${day}`;
        return localeDate;//`${day}-${month}-${year}`;
    }
    function import_data(){
        const inpFile = $("#nmExcel");

        if (inpFile.val().length > 0) {

            Swal.fire({
                title: "Apakah anda yakin untuk import Absensi ?",
                icon: 'question',
                confirmButtonText: 'Ya',
                confirmButtonColor: '#dc3545',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                cancelButtonColor: '#6C757D'
            }).then((result) => {
                if (!result.isConfirmed) {
                    inpFile.val('');
                    return false
                }else{
                    const tglInput = inpTgl.val()
                    
                    let xform = new FormData();
                    
                    xform.append("tglAbsen", tglInput);
                    xform.append("fileImport",inpFile[0].files[0] == undefined ? null : inpFile[0].files[0] );

                    $.ajax({
                        type: 'POST',
                        url: 'sdm/absensi/importData', // point to server-side controller method
                        data: xform,
                        type: 'post',
                        processData: false,  // Jangan ubah data menjadi string
                        contentType: false,  // Agar jQuery tidak mengatur tipe konten
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
                        success: function (res) {
                            Swal.close();
                           if(res.status){
                               dtList.setData();
                           }else{
                            Swal.fire({
                                text: res,
                                icon: 'warning',
                                showConfirmButton: false,
                                timer: 2000
                            });
                           }
                        },
                        error: function (res) {
                            Swal.close();
                            console.log(res);
                        }
                    });
                }
            })

            
        }else{
            Swal.fire({
                text: "File yang akan diimport harus diisi !",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
        }
    }
});

function printLaporan(button) {
    // Ambil data dari tombol yang diklik
    var id_proses = $(button).data("id_proses");
    var id_operator = $(button).data("id_operator");
    
    // Ambil nilai input tanggal
    let tanggal_awal = $("#filter_tgl_awal");
    let tanggal_akhir = $("#filter_tgl_akhir");

    // let inpTglAwal = $("#filter_tgl_awal");
    // let inpTglAkhir = $("#filter_tgl_akhir");
    
    console.log(id_proses, id_operator, tanggal_awal, tanggal_akhir);
    // Cek apakah tanggal telah diisi
    if (tanggal_awal === "" || tanggal_akhir === "") {
        alert("Harap isi tanggal awal dan tanggal akhir!");
        return;
    }
    else if (tanggal_awal === undefined || tanggal_akhir === undefined) {
        alert("Harap isi tanggal awal dan tanggal akhir!");
        return;
    }

    const params = `id_operator=${id_operator}&id_proses=${id_proses}&tgl_awal=${tanggal_awal.val()}&tgl_akhir=${tanggal_akhir.val()}`;
    window.open(`/sdm/borongan/generate_kar?${params}`, '_blank'); // Ganti '/page' dengan path yang diinginkan

    // window.open(url.toString(), '_blank');
}