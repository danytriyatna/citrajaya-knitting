
$(document).ready(function () {
    $("[data-politespace]").politespace();
    $("#alert-success").hide();

	let fmKonsumen = $("#select_buyer");
	let fmRekening = $("#select_payment_type");
    let fmTgl = $("#tgl_cr");


    // set tabulator for detail order
    fmKonsumen.val(fmKonsumen.attr("value")).trigger("change");
    fmRekening.val(fmRekening.attr("value")).trigger("change");

	let newDetail = function() {
		let res = "<button class='btn btn-xs btn-primary' type='button' id='btn-add-detail'><i class='fa fa-plus'></i> Tambah</button>";
		return res;
	};

	let fmEditDetail = function(value, data, cell, row, options) {
		let row_data = value._cell.row.data;
		let fmBtnEdit = "<button class='btn btn-xs btn-warning' type='button' title='edit'><i class='fa fa-edit' title='edit'></i></button>";
		let fmBtnDelete = "<button class='btn btn-xs btn-danger' type='button' title='delete'><i class='fa fa-trash' title='delete'></i></button>";
		
		return fmBtnEdit + "&nbsp;" + fmBtnDelete;
	}; 

    
	var harga_bayar = function(cell){
		let vals = 0.00;
		
		let rowData = cell.getRow().getData();
		let ldDetail = dtList_det.getData();
	
		let nrencana = parseFloat(rowData.remain_item);
		let nbayar = parseFloat(rowData.pay_item);
		let npph23 = parseFloat(rowData.pph);
	
		let objIndex = ldDetail.findIndex(obj => parseInt(obj.seq) === parseInt(rowData.seq));
		if (objIndex >= 0) {
			
			if(nbayar <= nrencana){
			  vals = nbayar;
			}else{
			  alert("Nilai bayar tidak boleh melebihi nilai sisa !");
			}

			ldDetail[objIndex].pay_item = vals;
	
			if(nrencana > nbayar){
			  ldDetail[objIndex].bayar = false;
			}
		}
		cell.getElement().className = "row_bayar tabulator-cell";
		dtList_det.replaceData(ldDetail);
	  }

      function setPPH(seq, bayar){
		// console.log(bayar);
		let ldDetail = dtList_det.getData();
	
		let objIndex = ldDetail.findIndex(obj => obj.seq === seq);
		if (objIndex >= 0) {
			if(ldDetail[objIndex].pay_item == 0){
				let nrencana = parseFloat(ldDetail[objIndex].total_item);
				let npph23 = (nrencana * 2) / 100;
				let nbayar = parseFloat(ldDetail[objIndex].pay_item);
				let nramain = parseFloat(ldDetail[objIndex].remain_item);
				
				if(bayar){
					ldDetail[objIndex].pph23 = npph23;
					if(ldDetail[objIndex].bayar){
						ldDetail[objIndex].pay_item = nbayar - npph23;
					}
					ldDetail[objIndex].remain_item = nramain - npph23;
				}else{
					ldDetail[objIndex].pph23 = 0.00;
					if(ldDetail[objIndex].bayar){
						ldDetail[objIndex].pay_item = nbayar + npph23;
					}
					ldDetail[objIndex].remain_item = nramain + npph23;
				}
			}
		}
		dtList_det.replaceData(ldDetail);
	  }

      function isData(seq, bayar) {
		let ldDetail = dtList_det.getData();
        
		let objIndex = ldDetail.findIndex(obj => obj.seq === seq);
        
		if (objIndex >= 0) {
			let nrencana = parseFloat(ldDetail[objIndex].remain_item);
			let npph23 = parseFloat(ldDetail[objIndex].pph);
			ldDetail[objIndex].bayar = bayar;
			if(bayar){
				// if(ldDetail[objIndex].pay_item == 0){
				if(false){
					ldDetail[objIndex].pay_item = (nrencana - npph23);
				}else{
					ldDetail[objIndex].pay_item = (nrencana);
				}
			}else{
				ldDetail[objIndex].pay_item = 0.00;
			}
		}
		dtList_det.replaceData(ldDetail);
	  }

	  let editCheck = function (cell) {
			let rowData = cell.getRow().getData();
			let isEditable = false;
			if(rowData.pays_item == 0){
				isEditable = true;
			}
			return isEditable;
		}

	let dtList_det = new Tabulator("#dt-list-det", {
		columns: [
			{
				field: 'bayar', headerSort:false, sorter: 'string', visible : !disabled_input,
				width: 60, align : 'center', editor:true, formatter:"tickCross",
				cellEdited : function(cell) {
					let rowData = cell.getRow().getData();
					isData(rowData.seq, rowData.bayar);
				}
			},
			{
				title: "No. Invoice", field: "kode_invoice_url",  sorter: "string", headerSort:false, 
				formatter : "html",
			},
			{
				title: "Tgl Invoice", field: "tgl_transaksi",  sorter: "string", headerSort:false,
				width: 130
			},
			{
				title: "Subtotal", field: "total_item",  sorter: "string", headerSort:false, align: "right", cssClass: 'text-end',
				width: 200, formatter : "money",
			},
            // {
			// 	title: "PPH (2%)", field: 'pphv', headerSort:false, sorter: 'string',
			// 	width: 100, align : 'center', editor:true, formatter:"tickCross", enable : true,
			// 	cellEdited : function(cell) {
			// 		let rowData = cell.getRow().getData();
			// 		setPPH(rowData.seq, rowData.pph);
			// 	}, editable:editCheck 
			// }
            , {
				title: "PPH Nilai", field: "pph",
				width: 200, headerSort:false, align: "right", cssClass: 'text-end', formatter : "money" //, editor:!disabled_input ,  cellEdited: harga_pph23
			},
			
            // {
			// 	title: "Dibayar", field: "sl_pays_item",  sorter: "string", headerSort:false,
			// 	width: 130, align: "right", cssClass: 'text-end', formatter : "money"
			// }
            , {
				title: "Sisa", field: "remain_item",
				width: 200, headerSort:false, align: "right", cssClass: 'text-end', formatter : "money"
			}, {
				title: "Jumlah Pembayaran", field: "pay_item", formatter : "money",
				width: 200, headerSort:false, align: "right", cssClass: 'text-end', cellEdited: harga_bayar
			}
		],
		layout: 'fitColumns',
		locale: 'id',
		placeholder: "Tidak ada data",
		// responsiveLayout:"collapse",
		// pagination: "local",
		// paginationSize: 10,
		// paginationButtonCount: 2,
		// paginationDataSent: {
		// sorters: "order",
		// },
		selectable: false
	});

    fmKonsumen.on("change", function(e) {
		get_invoice();
	});

	function get_invoice(){
		let konsumen_id = fmKonsumen.val();
		$.ajax({
            url: 'trans/customer-receipt/getInvoice', // point to server-side controller method
            dataType: 'json', // what to expect back from the server
            data: {id_konsumen : konsumen_id},
            type : 'post',
            success: function (res) {
               if(res.status){
				   console.log(res.msg);
				   dtList_det.replaceData(res.data);
			   }else{
				   alert(res.msg)
			   }
            },
            error: function (res) {
                // console.log(res);
            }
        });
	}

	const formatToCurrency = amount => {
		return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,");
	};


    //load item 
    let inpLDetail = $("input[name='Ldetail']");
    if(inpLDetail.val().length > 0){
        let dt_det = inpLDetail.val().replace(/&quot;/ig,'"');
        let dt_ldet = JSON.parse(dt_det);
        
        if(dt_ldet.length > 0){
            for (let i = 0; i < dt_ldet.length; i++) {
                const element = dt_ldet[i];
                dt_ldet[i].kode_invoice_url = element.kode_invoice_url.replace(/&lt;/ig,'<').replace(/&#039;/ig,"'").replace(/&gt;/ig,">")
            }
            // console.log(dt_ldet);
            setTimeout(() => {
                dtList_det.setData(dt_ldet);
            }, 1000);
        }
    }

    function formatLocaleDate(localeDate) {
    
        var months = {
            "Januari": "01",
            "Februari": "02",
            "Maret": "03",
            "April": "04",
            "Mei": "05",
            "Juni": "06",
            "Juli": "07",
            "Agustus": "08",
            "September": "09",
            "Oktober": "10",
            "November": "11",
            "Desember": "12"
        };

        var parts = localeDate.split(" ");
        var day = parts[0].padStart(2, '0'); 
        var month = months[parts[1]]; 
        var year = parts[2];

        // return `${year}-${month}-${day}`;
        return `${day}-${month}-${year}`;
    }

    // save form
    function setDataInputTable() {
        let dataItem = dtList_det.getData();
        if (dataItem && dataItem.length > 0) {
            inpLDetail.val(JSON.stringify(dataItem));
        }
		// console.log(fmTgl.val());
        // if(fmTgl.val().length > 0){
        //     fmTgl.val(formatLocaleDate(fmTgl.val())).trigger("change");
        // }
    }

    // on save
    $('#btn-save').on('click', function (e) {
        e.preventDefault();

        setDataInputTable();
        $("#actionf").val('save');
        $("#fmain").submit();
    });

    // on approve
    $('#btn-approve').on('click', function (e) {
        if (confirm('Anda yakin akan melakukan APPROVE ?')) {
            setDataInputTable();
            $("#actionf").val('approve');
            $("#fmain").submit();
        }
    });

    // on reject
    $('#btn-reject').on('click', function (e) {
        if (confirm('Anda yakin akan melakukan REJECT ?')) {
            $("#actionf").val('reject');
            $("#fmain").submit();
        }
    });

});