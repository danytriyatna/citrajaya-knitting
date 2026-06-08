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

    let inpTgl = $("#filter_tgl");
    let dtList = new Tabulator("#dt-absensi", {
        columns: [
            {
				title: 'Shift', field: 'nama_shift', headerSort:false, sorter: 'string', frozen: true,
				width: 180, align:'center', editor:"list", editorParams:{values:{"NORMAL":"NORMAL", "SHIFT PAGI":"SHIFT PAGI", "SHIFT MALAM":"SHIFT MALAM", "ART":"ART"}}
			} ,
            
			{
				title: 'NIK', field: 'nip', headerSort:false, sorter: 'string',
				width: 160, frozen: true
			}, 
				
			{
				title: 'Nama', field: 'full_name', headerSort:false, sorter: 'string',
				width: 240, frozen: true
			}, 

            {
				title: 'Posisi', field: 'posisi', headerSort:false, sorter: 'string',
				formatter : "html", width: 150,
			},

            {
				title: 'Tanggal', field: 'tgl_absen', headerSort:false, sorter: 'string',
				width: 100, align:'center',
			},

            {
				title: 'Jam Masuk', field: 'jam_masuk', headerSort:false, sorter: 'string',
				width: 120, align:'center', editor: timeEditor,
                formatter: "time",
			},

            {
				title: 'Jam Keluar', field: 'jam_keluar', headerSort:false, sorter: 'string',
				width: 120, align:'center', editor: timeEditor,
                formatter: "time",
			},

            {
				title: 'Status Kehadiran', field: 'status_kehadiran', headerSort:false, sorter: 'string',
				width: 180, align:'center', editor:"list", editorParams:{values:{"Hadir":"Hadir", "Izin":"Izin", "Sakit":"Sakit" , "Tanpa Keterangan":"Tanpa Keterangan", "Rolling Shift":"Rolling Shift", "Cuti":"Cuti"}}
			} ,

            {
				title: 'Kehadiran', field: 'hari_hadir', headerSort:false, sorter: 'string', align: "center",
                width: 220, editor:"number"
			} ,

            { title:"Tambahan Durasi", field:"tanggal_merah", hozAlign:"center", editor:true, formatter:"tickCross", mutator: function(value) {
                return value == 1; // convert to boolean
            }},

            
            {
				title: 'Keterangan Kehadiran', field: 'keterangan_kehadiran', headerSort:false, sorter: 'string', align: "center",
                width: 220, editor:"input"
			} ,
            {
				title: 'Terlambat', field: 'terlambat', headerSort:false, sorter: 'string', align: "center",
                width: 160, editor:"number"
			} , 

            {
				title: 'Status Lembur', field: 'status_lembur', headerSort:false, sorter: 'string',
				width: 200, align:'center', editor:"list", editorParams:{values:{"-":"-", "Lembur Weekday":"Lembur Weekday", "Lembur Weekend/Hari Libur":"Lembur Weekend/Hari Libur"}}
			} ,
            {
				title: 'Jam Lembur', field: 'jml_lembur', headerSort:false, sorter: 'string', align: "center",
                width: 160, editor:"number"
			} , 
            {
				title: 'Keterangan Lembur', field: 'keterangan_lembur', headerSort:false, sorter: 'string', align: "center",
                width: 220, editor:"input"
			} ,

            {
				title: 'Bonus', field: 'bonus', headerSort:false, sorter: 'string', align: "center",
                width: 160, editor:"number", formatter: "money"
			} , 
            {
				title: 'Keterangan Bonus', field: 'bonus_keterangan', headerSort:false, sorter: 'string', align: "center",
                width: 220, editor:"input"
			} ,

            {
				title: 'Potongan', field: 'potongan', headerSort:false, sorter: 'string', align: "center",
                width: 160, editor:"number", formatter: "money"
			} , 
            {
				title: 'Keterangan Potongan', field: 'potongan_keterangan', headerSort:false, sorter: 'string', align: "center",
                width: 220, editor:"input"
			} ,
				
        ],
        // layout: 'fitColumns',
        height: "800px",  // ← ini kuncinya, header akan otomatis sticky
        layout: "fitColumns",
        ajaxURL: "/sdm/absensi/list",
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
            params.tgl_absen = formatLocaleDate(inpTgl.val())
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

    $("#btn-generate").on("click", function(){
        // dtList.setData();
        $.ajax({
            url: 'sdm/absensi/dataGenerate', // point to server-side controller method
            dataType: 'json', // what to expect back from the server
            data: {tgl_absen : formatLocaleDate(inpTgl.val())},
            type : 'post',
            success: function (res) {
               if(res.status){
				//    console.log(res.msg);
				   dtList.setData();
			   }else{
				   alert(res.msg)
			   }
            },
            error: function (res) {
                // console.log(res);
            }
        });
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

    function saveData(){
        const data_table = dtList.getData();
        const tglInput = inpTgl.val()
        // console.log("save")
        $.ajax({
            url: 'sdm/absensi/saveData', // point to server-side controller method
            dataType: 'json', // what to expect back from the server
            data: {tgl_absen : tglInput, data_list : JSON.stringify(data_table)},
            type : 'post',
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
				   console.log(res.msg);
				   dtList.setData();
			   }else{
				   alert(res.msg)
			   }
            },
            error: function (res) {
                Swal.close();
                console.log(res);
            }
        });
    }

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

    $("#btn-import").on("click", function(){
        import_data();
    });

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

    $('#pdf_type').on('change', function () {
        let type = $(this).val();

        if (type === 'rekap') {
            // Disable & reset pilihan karyawan
            $('#pdf_karyawan')
                .val(null)
                .trigger('change')       // reset select2
                .prop('disabled', true);
        } else {
            // Enable kembali pilihan karyawan
            $('#pdf_karyawan').prop('disabled', false);
        }
    });

    $('#btn-download-pdf').on('click', function (e) {
        e.preventDefault();

        let type       = $('#pdf_type').val();
        let karyawanId = $('#pdf_karyawan').val();
        let fromDate   = $('#pdf_from').val();
        let toDate     = $('#pdf_to').val();

        let chkJam   = $('#chk_jam').is(':checked') ? 1 : 0;
        let chkKet   = $('#chk_ket').is(':checked') ? 1 : 0;
        let chkRekap = $('#chk_rekap').is(':checked') ? 1 : 0;

        // Validasi karyawan hanya jika tipe = karyawan
        if (type === 'karyawan' && !karyawanId) {
            toastr.warning('Silakan pilih karyawan terlebih dahulu!', 'Gagal', {
                positionClass: 'toast-top-right'
            });
            return;
        }

        if (!fromDate || !toDate) {
            toastr.warning('Silakan isi rentang tanggal dengan lengkap!', 'Gagal', {
                positionClass: 'toast-top-right'
            });
            return;
        }

        if (parseDate(toDate) < parseDate(fromDate)) {
            toastr.warning('Tanggal "Sampai Tanggal" tidak boleh lebih kecil dari "Dari Tanggal"!', 'Gagal', {
                positionClass: 'toast-top-right'
            });
            return;
        }

        let urlExport = '/sdm/absensi/export_absensi';

        let params = $.param({
            type        : type,
            id_karyawan : type === 'karyawan' ? karyawanId : '',
            from        : fromDate,
            to          : toDate,
            jam         : chkJam,
            ket         : chkKet,
            rekap       : chkRekap
        });

        window.open(urlExport + '?' + params, '_blank');
    });

    function parseDate(str) {
        // format dd-mm-yyyy
        let parts = str.split("-");
        return new Date(parts[2], parts[1] - 1, parts[0]); 
        // year, monthIndex (0=Jan), day
    }
});