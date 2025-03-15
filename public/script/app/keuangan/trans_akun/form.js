$(document).ready(function () {
    $("[data-politespace]").politespace();
    $("#alert-success").hide();

	let fmDate = $("#trans_akun_date");

    // fmDate.datepicker({
    //     format: 'dd-mm-yyyy',
    //     clearBtn: true,
    //     autoclose: true,
    //     todayHighlight: true,
    //     readonly: false
    // });

	let fmRekening = $("#ref_rekening_id");
	fmRekening.select2({
		allowClear: false,
		placeholder: "- Pilih Kas/Bank -",
	});
	fmRekening.val(fmRekening.attr("value")).trigger("change");


    // set option detail list
    let newDetail = function() {
		let res = "<button class='btn btn-xs btn-primary' type='button' id='btn-add-detail'><i class='fa fa-plus'></i> Tambah</button>";
		return res;
	};

	let fmEditDetail = function(value) {
		// Removed unused variables: data, cell, row, options, row_data, fmBtnView
		let fmBtnEdit = "<button class='btn btn-xs btn-warning' type='button' title='edit'><i class='fa fa-edit' title='edit'></i></button>";
		let fmBtnDelete = "<button class='btn btn-xs btn-danger' type='button' title='delete'><i class='fa fa-trash' title='delete'></i></button>";
		
		return fmBtnEdit + "&nbsp;" + fmBtnDelete;
	}; 

    let fmDModal = $("#modal-item");
    let fmDCoa = $("#coa_id");
    let fmDJumlah = $("#jumlah");
    let fmDKeterangan = $("#keterangan_det");
	let fmDSeq = $("#detail_seq");

    build_jenis_risiko();   
    function build_jenis_risiko() {
        fmDCoa.html('');
        fmDCoa.val(fmDCoa.attr('value'));

        $.get("/keuangan/transaksi_akun/get_org/", {}, function (data) {
            fmDCoa.html('');
            fmDCoa.select2ToTree({
                treeData: {
                    dataArr: data,
                    valFld: "id",
                    labelFld: "unit_name",
                    incFld: "children",
                    dftVal: null
                },
                allowClear: false,
                placeholder: '- Pilih -',
                multiple: false,
                width: '100%'
            });
            fmDCoa.val(fmDCoa.attr('value')).trigger('change');
        }, "json");
    }

    let dtList_det = new Tabulator("#dt-list-det", {
		columns: [
            {
                titleFormatter: newDetail, headerSort: false, formatter: fmEditDetail,
                width: 120, align: "center", cssClass: "text-center",
                visible: !disabled_input,
                cellClick: function(e, cell) {
                    if (e.target.title === 'delete') {
                        if (confirm("Anda yakin akan menghapus data?")) {
                            let seq = cell.getRow().getData().korp_terkait_seq;
                            cell.getRow().delete();

                            // update penyebab
                            let dataDet = dtList_det.getData();

                        //    remove rencana pengendalian
                            dataDet = dataDet.filter(function(item) {
                                return parseInt(item.seq) !== seq;
                            });
                            dtList_det.replaceData(dataDet);
                        }
                    } else if (e.target.title === 'edit') {
                        let rowData = cell.getRow().getData();
                        
                        fmDCoa.val(rowData.coa_id).trigger("change");
                        fmDJumlah.val(rowData.jumlah);
                        fmDKeterangan.val(rowData.keterangan);
						fmDSeq.val(rowData.seq);

                        fmDModal.modal("show");
                    }
                }
            },
            {
                title: "Nama Akun", field: "coa_nama",  sorter: "string", headerSort:false,
				width: 220
            },
            {
                title: "Keterangan", field: "keterangan",  sorter: "string", headerSort:false,
                
            },
            {
                title: "Nilai", field: "jumlah",  sorter: "string", headerSort:false,
                width: 160, formatter : "money", bottomCalc:"sum", bottomCalcFormatter:"money", align: "right", cssClass: "text-start"
            }
		],
		layout: 'fitColumns',
		locale: 'id',
		placeholder: "Tidak ada data",
		selectable: false
	});

	
	$("#btn-save-det").on("click", function(){
		let seq = fmDSeq.val();
		let insCoa = fmDCoa.val();
		let insCoaText = $("#coa_id :selected")[0].text;
		let insJumlah = fmDJumlah.val();
		let insKeterangan = fmDKeterangan.val();

		let fail = false;
		if(insCoa == "") fail = true;
		if(insJumlah == "" || insJumlah == 0) fail = true;
		if(insKeterangan == "") fail = true;

		if(fail) {alert("lengkapi isian pada form !"); return false;}

		let tblDetail = dtList_det.getData();
		if (seq === '') {
			let seqA = 1;
			// let objCek = tblDetail.findIndex(obj => parseInt(obj.kategori_id) === parseInt(insKategori));

			// if(objCek < 0){
			if (tblDetail.length >= 1) seqA = tblDetail[tblDetail.length-1].seq + 1;
			tblDetail.push({
				seq: seqA,
				coa_nama : insCoaText,
				coa_id: insCoa,
				jumlah: insJumlah,
				keterangan: insKeterangan,
				trans_akun_id: null
			});
			// }else{
			// 	alert("Produk sudah ada dalam list !");
			// }
		} else {
			seq = parseInt(seq);
			let objIndex = tblDetail.findIndex(obj => obj.seq === seq);
			if (objIndex >= 0) {
				tblDetail[objIndex].coa_text = insCoaText;
				tblDetail[objIndex].coa_id = insCoa;
				tblDetail[objIndex].jumlah = insJumlah;
				tblDetail[objIndex].keterangan = insKeterangan;
			}
		}
		
		dtList_det.replaceData(tblDetail);
		fmDModal.modal('hide');
	});

	//load item 
	let inpLDetail = $("input[name='Ldetail']");
	if(inpLDetail.val().length > 0){
		let dt_det = inpLDetail.val().replace(/&quot;/ig,'"');
		let dt_ldet = JSON.parse(dt_det);
		if(dt_ldet.length > 0){
			setTimeout(() => {
				dtList_det.replaceData(dt_ldet);
			}, 500);
		}
	}


	// save form
	function setDataInputTable() {
        let dataItem = dtList_det.getData();
        if (dataItem && dataItem.length > 0) {
            inpLDetail.val(JSON.stringify(dataItem));
        }
    }

	// on save
    $('#btn-save').on('click', function (e) {
        e.preventDefault();

        setDataInputTable();
        $("#actionf").val('save');
        $("#fmain").submit();
    });

	setTimeout(() => {
		$("#btn-add-detail").on("click", function(e){
			e.preventDefault();
			console.log("samep sini");
			fmDSeq.val("");
			fmDCoa.val("").trigger("change");
			fmDJumlah.val("").blur();
			fmDKeterangan.val("");
	
			fmDModal.modal("show");
		});
	
	}, 500);
	
});


