
$(document).ready(function () {

    let btnProduksi = $("#list_prod");
    let mdlProd     = $("#modal-list-produksi");
    let inpKonsumen = $("#select_buyer");
    let inpAlamat   = $("#alamat_buyer");
    let inpDoNo     = $("#do_no");
    let inpDoTgl    = $("#tgl_do");
    let inpStyle    = $("#keterangan_style");
    let inpRefNi    = $("#kode_produksi");
    let inpRefSO    = $("#kode_so");
    let inpProduksi = $("#id_produksi");
    let inpWo = $("#id_walkorder");


    if(inpKonsumen.attr('value').length > 0) {
        inpKonsumen.val(inpKonsumen.attr('value')).trigger('change')
    }
    
    // conf function 
    let cellMoney = function(cell, formatterParams){
        let classN = "text-right tabulator-cell text-end";
        cell.getElement().className = classN;
    
        let isVal = number_format(cell.getValue(), 2, ',', '.'); 
        return isVal; //return the contents of the cell;
    }

    function number_format (number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    let setColum = [{
                        title: "Colour", field: "colordasar",  sorter: "string", headerSort:false, align: "center", cssClass: "text-left",
                    },
                    {
                        title: "Qty", field: "qty",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                        width:"9%", bottomCalc:"sum", 
                    },
                    {
                        title: "DO Qty", field: "qty_prod",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                        width:"10%", bottomCalc:"sum"
                    },
                    {
                        title: "Qty<br>Remain", field: "qty_remain",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                        width:"10%", bottomCalc:"sum"
                    }];

    // table detail 
    let dtListDetail = new Tabulator("#dt-prod", {
            columns: setColum,
            locale: 'id',
            layout:"fitColumns",
            resizableColumnFit:true,
            placeholder: "Tidak ada data",
            pagination: false,
            paginationSize: 99,
            paginationButtonCount: 2,
            paginationDataSent: {
                sorters: "order",
            },
            selectable: false
	});

    let buttonRowAction = function(cell) {
        let fmBtnDelete = ""
        var data = cell.getRow().getData(); // Ambil data row
        
        if (data.flag != 1){
            fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        }
        return fmBtnDelete;
    };


    const inpStatus = ($("#status").val())
    // console.log('inpStatus', inpStatus)
    // console.log('inpStatus', !(inpStatus == 2))
    let dtListProduksi = new Tabulator("#dt-list-detail", {
        columns: [
            {
                title: '', headerSort:false, formatter: buttonRowAction, sorter: 'string',
                width: '10%', visible: !(inpStatus == 2), 
                cellClick: function(e, cell) {
                    let row = cell.getRow();
                    let data_row = row.getData();
                    if (e.target.title === 'delete') {
                        if (confirm("Anda yakin akan menghapus data?")) {
                            let seq = cell.getRow().getData().korp_terkait_seq;
                            cell.getRow().delete();

                            // update penyebab
                            let dataDet = dtListProduksi.getData();
                            addkuota(cell.getRow().getData().ref_detail_id, true)
                            //    remove rencana pengendalian
                            dataDet = dataDet.filter(function (item) {
                                return parseInt(item.seq) !== seq;
                            });

                            dtListProduksi.replaceData(dataDet);
                        }
                    }  
                }
            }, 
            {
                title: 'Colour', field: 'kode_warna', headerSort:false, formatter: "html", sorter: 'string',
                // width: '40%',
            }, 
    
            {
                title: 'Size', field: 'kode_ukuran', headerSort:false, formatter: "html", sorter: 'string',
                width: '20%', hozAlign: 'right', cssClass: 'text-end'
            }, 
    
            {
                title: 'QTY', field: 'qty', headerSort:false, formatter: "html", sorter: 'string',
                width: '30%', hozAlign: 'right', cssClass: 'text-end',
                editor: "input",
                editorParams: {
                    min: 1
                },
                cellEdited: function(cell) {
                    let newQty = parseInt(cell.getValue());
                    let rowData = cell.getRow().getData();
                    // console.log(rowData)
                    let refDetailId = rowData.ref_detail_id;

                    let tblDetail = dtListDetail.getData();
                    
                    let objIndex = tblDetail.findIndex(obj => parseInt(obj.ref_detail_id) === parseInt(refDetailId));
                    // console.log("tblDetail",objIndex)
                    // console.log("tblDetail",tblDetail[objIndex])
                    if (objIndex >= 0) {
                        let originalQty = tblDetail[objIndex].qty;
                        let currentDoQty = tblDetail[objIndex].qty_prod;

                        if (newQty <= (originalQty)) {
                            tblDetail[objIndex].qty_prod = newQty;
                            tblDetail[objIndex].qty_remain = originalQty - newQty;
                        } else {
                            alert("Jumlah DO Qty melebihi jumlah yang tersedia!");
                            cell.setValue(currentDoQty); // Revert to the original value
                        }
                    }

                    dtListDetail.replaceData(tblDetail);
                }
            }, 
        ],
        layout: 'fitColumns',
        locale: 'id',
        placeholder: "Tidak ada data",
        pagination: false,
        paginationSize: 99,
        paginationButtonCount: 2,
        paginationDataSent: {
            sorters: "order",
        },
        selectable: false
    });

    // get data produksi 
    let dtListProds = new Tabulator("#dt-list-produksi", {
        columns: [

            {
				title: 'No. Produksi', field: 'kode_prod', headerSort:false, sorter: 'string',
				width: 140, 
			}, 

            {
				title: 'No. Ref', field: 'kode_walkorder_ref', headerSort:false, sorter: 'string',
				width: 140, 
			}, 

			{
				title: 'Tgl Produksi', field: 'tgl_transaksi', headerSort:false, sorter: 'string',
				width: 140
			},

            {
				title: 'Tipe', field: 'tipe', headerSort:false, sorter: 'string',
				width: 140
			},

            {
				title: 'Style', field: 'keterangan_style', headerSort:false, sorter: 'string',
				formatter : "html"
			},

            {
				title: 'Qty', field: 'qty', headerSort:false, sorter: 'string',
				width: 120, formatter : "html"
			},				
        ],
        layout: 'fitColumns',
        ajaxURL: "/trans/delivery-order/list_produksi",
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
            params.id_konsumen = inpKonsumen.val()
        },
        ajaxResponse: function (url, params, response) {
            let pageSize = dtListProds.getPageSize();
            let pageNo = dtListProds.getPage();
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
    let elSearchx = $("#tb-produksi");
    if (elSearchx != null) {
        elSearchx.keyup(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThread);
            searchThread = setTimeout(function () {
                dtListProds.setFilter("", "like", elSearchx.val());
            }, 600);
        });
    }
  
    dtListProds.on("rowClick", function(e, row){
        let data =  row.getData()

        // console.log(data);
        $.ajax({
            type: 'POST',
            url: '/trans/delivery-order/det_produksi',
            data: {
                produkds     : data.id,
                id_walkorder : data.id_walkorder,
            },
            dataType: "json",
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
            success: function (response) {
                Swal.close();
                if(response.status == true){
                    
                    let isProd = response.data.produksi;
                    let data = response.data;

                    inpRefNi.val(isProd.kode_prod);
                    inpRefSO.val(isProd.kode_walkorder_ref);
                    inpStyle.val(isProd.keterangan_style);
                    inpProduksi.val(isProd.id).trigger("change");
                    inpWo.val(isProd.id_walkorder).trigger("change");
                    
                    setColumn(data.detail_produksi, data.data_ukuran);
                }else{
                    Swal.fire({
                        text: response.message,
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function (e) {
                let msg = e.responseJSON.message;
                Swal.close();
    
                Swal.fire({
                    text: msg,
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
            },
        });
    });

    function setColumn(data, ukuran){
        let newColum =  [{
                            title: "Colour", field: "colordasar",  sorter: "string", headerSort:false, align: "center", cssClass: "text-left",
                        },];
        const dt_Ukuran = ukuran;
        // console.log(dt_Ukuran)
        // console.log(data)
        for (const el of dt_Ukuran) {
            const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
            newColum.push(
            {
                title:el.kode_ukuran, field: isKey,  sorter: "string", headerSort:false, align: "center", cssClass: "text-center",
                width:"8%", bottomCalc:"sum"
            })
        }

        newColum.push(
        {
            title: "Qty", field: "qty",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
            width:"9%", bottomCalc:"sum", 
        })

        newColum.push(
        {
            title: "DO Qty", field: "qty_prod",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
            width:"10%", bottomCalc:"sum"
        })

        newColum.push(
        {
            title: "Qty<br>Remain", field: "qty_remain",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
            width:"10%", bottomCalc:"sum"
        })

        setTimeout(() => {
            dtListDetail.setColumns(newColum);
            dtListDetail.setData(data);
            setTimeout(() => {
                mdlProd.modal("hide");
                dtListDetail.redraw(true);
            }, 600);
        }, 500);
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

        return `${year}-${month}-${day}`;
    }

    btnProduksi.on("click", function(e){
        e.preventDefault();

        if(inpKonsumen.val().length == 0){
            Swal.fire({
                text: "Pilih Buyer terlebih dahulu !",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });

            return false;
        }else{
            dtListProds.setData();
            mdlProd.modal("show");
        }
       
    });

    $( "#text_barcode" ).autocomplete({
		source: function( request, response ) {
		  $.ajax({
			url: "/trans/delivery-order/cari_produk",
			dataType: "json",
			data: {
			  kata_kunci   : request.term,
              id_walkorder : inpWo.val(),
              id_produksi  : inpProduksi.val(),
			},
			type : 'post',
			success: function( data ) {
			  if(data.status){
				  response(data.slc);
			  }else{
				  console.log(data.msg);
			  }
			}
		  });
		},
		minLength: 0,
		select: function( event, ui ) {
			addItem(ui.item.data);
		},
		open: function() {
		  $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
		},
		close: function() {
		  $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
		  $( "#text_barcode" ).val("");
		}
	});

    function addItem(data){
		let dataTable = dtListProduksi.getData();

		let dataOrder = dtListDetail.getData();
		let objIndex  = dataTable.findIndex(obj => obj.id_ukuran == (data.id_ukuran) && obj.id_warna == (data.id_warna));
		let ix_order  = dataOrder.findIndex(obj => obj.ref_detail_id == (data.ref_detail_id));
        // console.log(dataOrder);
        // console.log(data);
		// let ktQty     = isQty.findIndex(obj => obj.kategori_id == (data.kategori_id));
		// let ktQtyO    = isQtyO.findIndex(obj => parseInt(obj.sl_order_det_id) === parseInt(isSlc.val()));
		

		if(dataOrder.length > 0){
			if(dataOrder[ix_order] == undefined){
				alert("Warna tidak ada dalam list produksi !");
				return false;
			}
		}

		data.seq = dataTable.length + 1;
		// if(data.qty > dataOrder[ix_order].qty_prod){
            if(dataTable[objIndex] == undefined){
                // console.log('ix_order', ix_order);
                // console.log('dataOrder', dataOrder);
                // data.sl_order_det_id = isSlc.val();//get_sl_orderID(dataTable, dataOrder, ix_order, data.kategori_id);
                if(dataOrder.length > 0){
                    let qty_order  = dataOrder[ix_order].qty;
                    let qty_orderO = dataOrder[ix_order].qty_prod;
                    data.qty = data.qty_prod + 1; 
                    if(qty_orderO < qty_order){
                        dataTable.push(data);
                        addkuota(data.ref_detail_id, false);
                        // $("#modal-list-item").modal("hide");
                    }else{
                        alert("Jumlah Order item tersebut sudah terpenuhi !");
                    }
                }
                // else{
                // 	dataTable.push(data);
                // 	$("#modal-list-item").modal("hide");
                // }
                
            }else{
                // alert("Warna ukuran sudah ada di list !");
                // dataTable[objIndex].qty = dataTable[objIndex].qty + 1; 
                // addkuota(data.ref_detail_id, false);

                if(dataOrder.length > 0){
                    let qty_order  = dataOrder[ix_order].qty;
                    let qty_orderO = dataOrder[ix_order].qty_prod;
                    data.qty = data.qty_prod + 1; 
                    if(qty_orderO < qty_order){
                        dataTable[objIndex].qty = dataTable[objIndex].qty + 1; 
                        addkuota(data.ref_detail_id, false);
                        // $("#modal-list-item").modal("hide");
                    }else{
                        alert("Jumlah Order item tersebut sudah terpenuhi !");
                    }
                }
            }
        // }else{
        //     alert("Jumlah Order item tersebut sudah terpenuhi !");
        // }
		
		dtListProduksi.replaceData(dataTable);
		$( "#text_barcode" ).val("");
	}

    function addkuota(ref_detail_id, hapus){
		seq = parseInt(ref_detail_id);

		let tblDetail = dtListDetail.getData();
		let objIndex = tblDetail.findIndex(obj => parseInt(obj.ref_detail_id) === seq);
		let arrData  = tblDetail[objIndex];
		
		if(objIndex >= 0){
			if(hapus){
				tblDetail[objIndex].qty_remain   = parseInt(tblDetail[objIndex].qty);
				tblDetail[objIndex].qty_prod = 0;
			}else{
				tblDetail[objIndex].qty_prod   = parseInt(tblDetail[objIndex].qty_prod) + 1;
                tblDetail[objIndex].qty_remain = parseInt(tblDetail[objIndex].qty_remain) - 1;
			}
		}

		dtListDetail.replaceData(tblDetail);
	}	

    
    let detail_data = $("#data-details").val().replace(/&quot;/ig,'"');
    let detail_ukuran = $("#data-ukuran").val().replace(/&quot;/ig,'"');
    if(detail_data.length > 0){
        setTimeout(() => {
            try {
                let isdata = JSON.parse(detail_data);
                let isukuran = JSON.parse(detail_ukuran);
                // Set data ke Tabulator
                // dtListDetail.setData(isdata);
                setColumn(isdata, isukuran);
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        }, 1000);
    } 

    let detail_produksi = $("#data-prods").val().replace(/&quot;/ig,'"');
    

    if(detail_produksi.length > 0){
        setTimeout(() => {
            try {
                let isdatap = JSON.parse(detail_produksi);
                
                // Set data ke Tabulator
                dtListProduksi.setData(isdatap);
                
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        }, 1000);
    } 

    // save form
	function setDataInputTable() {
        let dataItem = dtListDetail.getData();
        if (dataItem && dataItem.length > 0) {
            $("#data-details").val(JSON.stringify(dataItem));
        }

		let dataIl = dtListProduksi.getData();
		if(dataIl && dataIl.length > 0){
			$("#data-prods").val(JSON.stringify(dataIl));
		}

		$('select').prop('disabled', false);
		$('input').prop('disabled', false);
		$("textarea").prop('disabled', false);
		$("checkbox").prop('disabled', false);
    }

    $("#btn-save").click(function (e) {
        e.preventDefault();

        setDataInputTable();
        $("#actionf").val('save');
        $("#fmain").submit();
    });


    $("#btn-send").on("click", function(e) {
        e.preventDefault();
        

        Swal.fire({
            title: "Apakah anda ingin meng Approve data pengiriman ?",
            icon: 'question',
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#198754',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6C757D'
        }).then((result) => {
            if (result.isConfirmed) {
                setDataInputTable();
                $("#actionf").val('kirim');
                $("#fmain").submit();
            }
        })
    });
});