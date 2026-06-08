$(document).ready(function () {

    const inpKeterangan = $("#keterangan");
    const inpTglAwal = $("#filter_tgl_from");
    const inpTglAkhir = $("#filter_tgl_to");
    let inpPerusahaan = $("#id_perusahaan");
    const inpid = $("#id_transaksi");
    const inpDet = $("#detailData");

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

    let inpTglA = $("#filter_tgl_from");
    let inpTglS = $("#filter_tgl_to");
    let inpCmt = $("#filter_cmt");

    //Create custom filter
    let vprint = false;

    let buttonRowAction = function(cell) {

        const data = cell.getData();

        let params = {
            data_id : inpid.val(),
            nip : data.nip,
        };

        // Buat query string
        let queryString = $.param(params); // Convert objek ke query string
        let fullUrl = `sdm/penggajian/generate_kar?${queryString}`;

        let fmBtnEdit = ` <a target="_blank" href="${fullUrl}" class="btn btn-sm btn-warning text-dark" title='print'><i class="fa fa-print" title='print'></i></a>`;

        return fmBtnEdit;
    };

    let dtList = new Tabulator("#dt-penggajian", {
        columns: [
            {
                headerSort: false,  
                title: 'Aksi', 
                formatter: buttonRowAction,
                width: '5%', align: "center", cssClass: "text-center",
                visible: inpid.val().length > 0 ? true : false, frozen:true
            },
            
			{
				title: 'NIK', field: 'nip', headerSort:false, sorter: 'string',
				width: 160, frozen:true
			}, 
				
			{
				title: 'Nama', field: 'full_name', headerSort:false, sorter: 'string',
				width: 240, frozen:true
			}, 

            {
				title: 'Posisi', field: 'posisi', headerSort:false, sorter: 'string',
				formatter : "html", width: 200,
			},

            {
				title: 'Hadir', field: 'hadir', headerSort:false, sorter: 'string',
				width: 100, cssClass:'text-center', bottomCalc: 'sum'
			},

            {
				title: 'Izin', field: 'izin', headerSort:false, sorter: 'string',
				width: 100, cssClass:'text-center', bottomCalc: 'sum'
			},

            {
				title: 'Sakit', field: 'sakit', headerSort:false, sorter: 'string',
				width: 100, cssClass:'text-center', bottomCalc: 'sum'
			},
           
            {
				title: 'Tanpa<br>Keterangan', field: 'alpha', headerSort:false, sorter: 'string',
				width: 120, cssClass:'text-center', bottomCalc: 'sum'
			},

            {
				title: 'Jam Kerja', field: 'jam_kerja', headerSort:false, sorter: 'string',
				width: 100, cssClass:'text-center', bottomCalc: 'sum'
			},

            {
				title: 'Terlambat', field: 'terlambat', headerSort:false, sorter: 'string',
				width: 100, cssClass:'text-center', bottomCalc: 'sum'
			},
            {
				title: 'Gaji/Upah', field: 'gaji_jam', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalc: 'sum'
			} ,

            {
				title: 'Sample/Perbaikan', field: 'jml_sample', headerSort:false, sorter: 'string', align: "center",
                width: 150, formatter:"money", cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalc: 'sum'
			},


            {
				title: 'Lembur HK', field: 'lembur', headerSort:false, sorter: 'string',
				width: 100, cssClass:'text-center', bottomCalc: 'sum'
			},

            {
				title: 'Lembur HL', field: 'lembur_we', headerSort:false, sorter: 'string',
				width: 100, cssClass:'text-center', bottomCalc: 'sum'
			},

            {
				title: 'Premi Kehadiran', field: 'premi', headerSort:false, sorter: 'string', align: "center",
                width: 220, cssClass:"text-end", formatter: "money"
			} , 

            {
				title: 'Lembur', field: 'uang_lembur', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalc: 'sum'
			} ,

            {
				title: 'Penambahan', field: 'bonus', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalc: 'sum'
			} ,
            {
				title: 'Keterangan bonus', field: 'bonus_keterangan', headerSort:false, sorter: 'string',
				visible: false,
			}, 

            {
				title: 'Potongan', field: 'potongan', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalc: 'sum'
			} ,

            {
				title: 'Gaji/Upah', field: 'total', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalc: 'sum'
			} ,
				
        ],
        height: "900px",  // ← ini kuncinya, header akan otomatis sticky
        layout: "fitColumns",
		locale: 'id',
		placeholder: "Tidak ada data",
		selectable: false
    });

    $("#btn-generate").on("click", function(){
        // dtList.setData();
        const tglA = inpTglA.val();
        const tglZ = inpTglS.val();
        const cmt = inpCmt.val();
        const perusahaan = inpPerusahaan.val();
        if (perusahaan == null || perusahaan.length == 0) {
            Swal.fire({
                text: "Harap Pilih Perusahaan Dahulu!",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
            return false;
        }
        $.ajax({
            url: 'sdm/penggajian/get_laporan', // point to server-side controller method
            dataType: 'json', // what to expect back from the server
            data: {tgl_mulai : tglA, tgl_akhir : tglZ, type : cmt, id_perusahaan : perusahaan},
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
				//    console.log(res.msg);
				   dtList.setData(res.data);
			   }else{
				   alert(res.msg)
			   }
            },
            error: function (res) {
                Swal.close();
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


    const detail = inpDet.val();
    if(detail.length > 0){  
        
        const dtDet = JSON.parse(detail);
        // console.log('detail', dtDet)
        setTimeout(() => {
            dtList.setData(dtDet);
        }, 1000);
    }   

    function setDataInputTable() { 
        const data = dtList.getData();
        if(data.length > 0){
            const data_cvt = JSON.stringify(data);
            inpDet.val(data_cvt);
        }

        $('select').prop('disabled', false);
		$('input').prop('disabled', false);
		$("textarea").prop('disabled', false);
    }


    // on save
  $("#btn-save").on("click", function (e) {
    e.preventDefault();
    setDataInputTable();
    $("#actionf").val("save");
    $("#fmain").submit();
  });

    // on save
    $("#btn-approve").on("click", function (e) {
        e.preventDefault();

        setDataInputTable();
        $("#actionf").val("approve");
        $("#fmain").submit();
    });

    $("#btn-cetak-print").on('click', function (e) {
        e.preventDefault()

        // Query parameters
        let params = {
            data_id : inpid.val(),
        };

        // Buat query string
        let queryString = $.param(params); // Convert objek ke query string
        let fullUrl = `sdm/penggajian/generate?${queryString}`;

        // Buka link di tab baru
        window.open(fullUrl, '_blank');
    //   setTimeout(() => {
    //     // inpp_trans.html(data.id);
    //     mdlPrint.modal("hide");
    // }, 500);

    });

    $("#btn-cetak-export").on('click', function (e) {
        e.preventDefault()

        // Query parameters
        let params = {
            data_id : inpid.val(),
        };

        // Buat query string
        let queryString = $.param(params); // Convert objek ke query string
        let fullUrl = `sdm/penggajian/generate-excel?${queryString}`;

        // Buka link di tab baru
        window.open(fullUrl, '_blank');
    //   setTimeout(() => {
    //     // inpp_trans.html(data.id);
    //     mdlPrint.modal("hide");
    // }, 500);

    });
});