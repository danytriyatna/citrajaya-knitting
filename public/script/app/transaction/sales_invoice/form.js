
$(document).ready(function () {
    // let ppn = 11;
    let ppn = 0;

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

    let fmView = function(value, data, cell, row, options) {
		let row_data = value._cell.row.data;
		let fmBtnEdit = "<button class='btn btn-sm btn-info' type='button' title='view'><i class='fa fa-info-circle' title='view'></i></button>";
		
		let btnRes = row_data.detail_data.length > 0 ? fmBtnEdit : '';
		return btnRes; //+ "&nbsp;" + fmBtnDelete;
	};

    // detail pop pup 
    let detModal = $("#modal-view-detail-do");
    let detRefKode = $("#det_ref_so");
    let detRefTgl = $("#det_ref_tgl");
    let detRefKonsumen = $("#det_konsumen");
    let detStyle = $("#det_style");
    let detAlamat = $("#alamat_buyer");


    // set footer calculation
    let ttlText      = $("#ttl_text");
    let pajakText    = $("#pajak_text");
    let ttlHargaText = $("#ttl_harga_text");
    let ttlInp       = $("#ttl_inp");
    let pajakInp     = $("#pajak_inp");
    let ttlHargaInp  = $("#ttl_harga_inp");

    let dtList_detail1 = new Tabulator("#dt-list-delivery", {
        columns: [
            
			{
				title: 'No.', formatter: "rownum", headerSort:false, sorter: 'string',
			}, 
				
			{
				title: 'DO NO.', field: 'delivery_kode', headerSort:false, sorter: 'string',
				width: 140,
            }, 
				
			{
				title: 'COLOUR', field: 'kode_warna', headerSort:false, sorter: 'string',
				width: 200,
			}, 

            {
				title: 'S', field: 's', align: "center", cssClass: "text-center", headerSort:false,
                width: 120, bottomCalc: 'sum'
			} ,

            {
				title: 'M', field: 'm', align: "center", cssClass: "text-center", headerSort:false,
                width: 120,  bottomCalc: 'sum'
			},

            {
				title: 'L', field: 'l', headerSort:false, align: "center", cssClass: "text-end", sorter: 'string',
				width: 120,  bottomCalc: 'sum'
			},

            {
				title: 'XL', field: 'xl', headerSort:false, align: "center", cssClass: "text-end", sorter: 'string',
				width: 120,  bottomCalc: 'sum'
			},
            
            {
				title: '2XL', field: 'xxl', headerSort:false, align: "center", cssClass: "text-end", sorter: 'string',
				width: 120,  bottomCalc: 'sum'
			},

            {
				title: '3XL', field: 'xxxl', headerSort:false, align: "center", cssClass: "text-end", sorter: 'string',
				width: 120,  bottomCalc: 'sum'
			},

            {
				title: 'ALL', field: 'all_', headerSort:false, align: "center", cssClass: "text-end", sorter: 'string',
				width: 120,  bottomCalc: 'sum'
			},

            {
				title: 'DO QTY', field: 'qty_do', headerSort:false, align: "center", cssClass: "text-end", sorter: 'string',
				width: 140,  bottomCalc: 'sum'
			},

            {
				title: 'AMOUNT', field: 'total_harga', formatter : "money", headerSort:false, align: "center", cssClass: "text-end", sorter: 'string',
				width: 140,  bottomCalc: 'sum', bottomCalcFormatter: "money",
			},
				
        ],
        locale: 'id',
        placeholder: "Tidak ada data",
        pagination: false,
        paginationSize: 99,
        paginationButtonCount: 2,
        paginationDataSent: {
            sorters: "order",
        },
        selectableRows: false
    });

    // table detail 
    let dtListDetail = new Tabulator("#dt-detail", {
        columns: [
                {
                    field: 'bayar', headerSort:false, sorter: 'string', visible : true,
                    width: 60, align : 'center', editor:true, formatter:"tickCross", cssClass: 'text-center',
                    cellEdited : function(cell) {
                        let rowData = cell.getRow().getData();
                        setFooterHarga();
                        // isData(rowData.seq, rowData.bayar);
                    }
                },
                {
                    title: "Aksi.", formatter: fmView,  sorter: "string", headerSort:false, align: "center", cssClass: "text-left",
                    width:"7%",
                    cellClick: function(e, cell) {
                        if (e.target.title === 'view') {
                            let rowData = cell.getRow().getData();

                            detRefKode.val(rowData.ref_kode);
                            detRefTgl.val(rowData.tgl_transaksi);
                            detRefKonsumen.val(rowData.konsumen_nama);
                            detStyle.val(rowData.keterangan_style);
                            detAlamat.val("");
                            
                            dtList_detail1.setData(rowData.detail_data);
                            setTimeout(() => {
                                dtList_detail1.redraw(true)
                            }, 500);
                            detModal.modal("show");
                        }
                    }
                },
                {
                    title: "No.", formatter: "rownum",  sorter: "string", headerSort:false, align: "center", cssClass: "text-left",
                    width:"5%"
                },
                {
                    title: "List Delivery", visible:false, field: "list_delivery",  sorter: "string", headerSort:false, align: "center", cssClass: "text-start",
                    width:"10%", 
                },
                {
                    title: "Ref No.", field: "ref_kode",  sorter: "string", headerSort:false, align: "center", cssClass: "text-start",
                    width:"15%", 
                },
                {
                    title: "Ref Tgl", field: "tgl_transaksi",  sorter: "string", headerSort:false, align: "center", cssClass: "text-start",
                    width:"10%", 
                },

                {
                    title: "Style", field: "keterangan_style",  sorter: "string ", headerSort:false, align: "center", cssClass: "text-start",
                    width:"10%", 
                },

                {
                    title: "Ref Qty", field: "ref_qty",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                    width:"6%", //bottomCalc:"sum", 
                },

                {
                    title: "Delivery<br>Qty", field: "deliver_qty",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                    width:"6%", //bottomCalc:"sum", 
                },

                {
                    title: "Amount", field: "ref_total",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                    width:"12%", formatter : "money"
                },

                {
                    title: "DP", field: "ref_dp",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                    width:"12%", formatter : "money"
                },

                {
                    title: "INV. TOTAL", field: "totals",  sorter: "string", headerSort:false, align: "center", cssClass: "text-end",
                    width:"12%", formatter : "money"
                },
                
            ],
            locale: 'id',
            placeholder: "Tidak ada data",
            pagination: false,
            paginationSize: 99,
            paginationButtonCount: 2,
            paginationDataSent: {
                sorters: "order",
            },
            selectableRows: false
	});

    // let detail_data = $("#data-details").val().replace(/&quot;/ig,'"');

    // if(detail_data.length > 0){
    //     setTimeout(() => {
    //         try {
    //             let isdata = JSON.parse(detail_data);
                
    //             // Set data ke Tabulator
    //             dtListDetail.setData(isdata);
    //         } catch (e) {
    //             console.error("Error parsing JSON:", e);
    //         }
    //     }, 1000);
    // }

    let isBuyer = $("#select_buyer");

    if(isBuyer.attr('value').length > 0){
        isBuyer.val(isBuyer.attr('value')).trigger("change");
    }
    
    isBuyer.on("change", function(){
        let val = $(this).val();
        get_wokonsumen(val);
    })

    function get_wokonsumen(id){
        $.ajax({
            url: "/trans/sales-invoice/get_order", // point to server-side controller method
            dataType: "json", // what to expect back from the server
            data: { konsumen_id : id },
            type: "post",
            // cache: false,
            // contentType: false,
            // processData: false,
            beforeSend: function () {
                dtListDetail.setData([]);
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
                    Swal.fire({
                        text: res.message,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2500
                    }).then((result) => {
                        // similar behavior as clicking on a link
                        dtListDetail.setData(res.data)
                        setTimeout(() => {
                            setFooterHarga();
                        }, 500);
                    });
                }else{
                    Swal.fire({
                        text: res.message,
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2000
                    });

                }
                
            },
            error: function (res) {
                Swal.close();
                Swal.fire({
                    text: "Gagal memuat data",
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                }).then((result) => {
                    
                });
                
            },
        });
    }

    let detail_data = $("input[name='dt_details']");
    // console.log(detail_data.val());
    if(detail_data.val().length > 0){
        setTimeout(() => {
            try {
                let dataDetail = detail_data.val().replace(/&quot;/ig,'"');
                let isdata = JSON.parse(dataDetail);
                // Set data ke Tabulator
                dtListDetail.setData(isdata);
                setTimeout(() => {
                    setFooterHarga()
                }, 500);
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        }, 1000);
    } 
    

    function setFooterHarga(){
        let detailList = dtListDetail.getData();
        let harga      = 0;
        let harga_inv      = 0;

        for (const inv of detailList) {
            if(inv.bayar != undefined){
                harga_inv = harga_inv + inv.totals;
                harga = harga + inv.ref_total;
            }
        }

        // let pajak = (harga * ppn) / 100;
        // let total = harga + pajak;
        let pajak = (harga_inv * ppn) / 100;
        let total = harga_inv + pajak;

        ttlText.html(number_format(harga, 2, ',', '.'));
        pajakText.html(number_format(pajak, 2, ',', '.'));
        ttlHargaText.html(number_format(total, 2, ',', '.'));

        ttlInp.val(harga);
        pajakInp.val(pajak);
        ttlHargaInp.val(total);
    }

    // save form
	function setDataInputTable() {
        let dataItem = dtListDetail.getData();
        if (dataItem && dataItem.length > 0) {
            detail_data.val(JSON.stringify(dataItem));
        }

		$('select').prop('disabled', false);
		$('input').prop('disabled', false);
		$("textarea").prop('disabled', false);
		$("checkbox").prop('disabled', false);
    }

    $("#btn-save").on("click", function(e) {
        e.preventDefault();

        Swal.fire({
            title: "Apakah anda ingin menyimpan Work Order ?",
            icon: 'question',
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#198754',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6C757D'
        }).then((result) => {
            if (result.isConfirmed) {
                setDataInputTable();

                // $("#actionf").val('save');
                $("#fmain").submit();
            }
        })
    });

    $("#btn-send").on("click", function(e) {
        e.preventDefault();
        

        Swal.fire({
            title: "Apakah anda ingin mensubmit Work Order dan meneruskan ke Produksi ?",
            icon: 'question',
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#198754',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6C757D'
        }).then((result) => {
            if (result.isConfirmed) {
                saved(1)
            }
        })
    });



    function saved(isstataus){
        let dataJenis = $('input[name="jenis_proses"]');
        let dataid = $("#dataid").val();
        let listJenis = [];
        for (const inpel of dataJenis) {
            let inp = $("#" + inpel.id)
            if(inp.is(":checked")){
                listJenis.push(inp.val());
            }
        }

        let ukuran_data = dtListDetail.getData();
        let ukuran_calc = dtListDetail.getCalcResults();

        let form_data = new FormData();
        form_data.append('dataid', dataid);
        form_data.append('listproses', JSON.stringify(listJenis));
        form_data.append('status_data', isstataus);
        form_data.append('data_ukuran_warna', JSON.stringify(ukuran_data));
        form_data.append('data_ukuran', JSON.stringify(ukuran_calc));
        
        $.ajax({
            url: "/trans/work-order/save-data", // point to server-side controller method
            dataType: "json", // what to expect back from the server
            data: form_data,
            type: "post",
            cache: false,
            contentType: false,
            processData: false,
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
                    Swal.fire({
                        text: res.message,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2500
                    }).then((result) => {
                        // similar behavior as clicking on a link
                        window.location.href = "/trans/work-order";
                    });
                }else{
                    Swal.fire({
                        text: res.message,
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 2000
                    });

                }
                
            },
            error: function (res) {
                Swal.close();
                Swal.fire({
                    text: "Gagal simpan data",
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                }).then((result) => {
                    
                });
                
            },
        });
    }

});