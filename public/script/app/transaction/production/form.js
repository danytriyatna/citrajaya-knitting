
$(document).ready(function () {

    let statusProses = $("#filter_status")
    let tglTransaksi = $("#tgl_prod")
    let noMesin = $("#nomesin")
    let operator = $("#filter_operator")
    const prosesMap = {
        1: "#proses_1",
        2: "#proses_2",
        3: "#proses_3",
        4: "#proses_4",
        5: "#proses_5",
        6: "#proses_6",
        7: "#proses_7",
    };

    // conf function 
    let cellMoney = function (cell, formatterParams) {
        let classN = "text-right tabulator-cell text-end";
        cell.getElement().className = classN;

        let isVal = number_format(cell.getValue(), 2, ',', '.');
        return isVal; //return the contents of the cell;
    }

    function number_format(number, decimals, dec_point, thousands_sep) {
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

    function calculateTotal(row) {
        if (row) {

            let qty = row.qty || 0;
            let harga = row.harga || 0;
            return qty * harga;
        }
    }

    let setColumn = [
        {
            title: "Colour", field: "colour_warna", sorter: "string", headerSort: false, align: "center", cssClass: "text-left",
        },
    ]

    const dt_ukuran = $("#data-ukuran").val().length > 0 ? JSON.parse($("#data-ukuran").val()) : []

    for (const el of dt_ukuran) {
        const isKey = (el.key_ukuran == 'all') ? 'all_' : el.key_ukuran
        setColumn.push(
            {
                title: el.kode_ukuran, field: isKey, sorter: "string", headerSort: false, align: "center", cssClass: "text-end",
                width: "9%", bottomCalc: "sum",
            }
        )
    }


    // last column 
    setColumn.push(
        {
            title: "QTY", field: "qty", sorter: "string", headerSort: false, align: "center", cssClass: "text-end",
            width: "10%", bottomCalc: "sum",
        }
    )

    setColumn.push(
        {
            title: "QTY<br>PRODUKSI", field: "qty_prod", sorter: "string", headerSort: false, align: "center", cssClass: "text-end",
            width: "10%", bottomCalc: "sum", visible: false
        }
    )

    setColumn.push(
        {
            title: "Berat (Kg)", field: "total_btm", sorter: "string", headerSort: false, align: "center", cssClass: "text-end",
            width: "10%", bottomCalc: "sum",
            formatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ maksimal 2 desimal
            },
            bottomCalcFormatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ total bawah juga 2 desimal
            }
        }
    )

    // table detail 
    let dtListDetail = new Tabulator("#dt-detail", {
        columns: setColumn,
        locale: 'id',
        placeholder: "Tidak ada data",
        layout: "fitColumns",
        resizableColumnFit: true,
        pagination: false,
        paginationSize: 99,
        paginationButtonCount: 2,
        paginationDataSent: {
            sorters: "order",
        },
        selectableRows: false
    });

    let detail_data = $("#data-details").val().replace(/&quot;/ig, '"');
    let detail_data_barang = $("#data-detail-barangs").val().replace(/&quot;/ig, '"');

    if (detail_data.length > 0) {
        setTimeout(() => {
            try {
                let isdata = JSON.parse(detail_data);
                // Set data ke Tabulator
                dtListDetail.setData(isdata);
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        }, 1000);
    }

    let buttonRowAction = function (cell) {
        let fmBtnDelete = ""
        var data = cell.getRow().getData(); // Ambil data row
        if (data.flag != 1) {
            fmBtnDelete = `<button type="button" class="btn btn-sm btn-danger" title='delete'><i class="fa fa-trash" title='delete'></i></button>`;
        }
        return fmBtnDelete;
    };

    let dtListDetailBarang = new Tabulator("#dt-detail-barang", {
        pagination: false,
        paginationSize: 10,
        paginationButtonCount: 5,
        columns: [
            { headerSort: false, title: "WARNA", field: "kode_warna", width: "27%" },
            {
                headerSort: false, title: "KEBUTUHAN<br>(QTY/KG)", field: "total", hozAlign: "right", width: "17%", headerHozAlign: "right", bottomCalc: "sum",
                formatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ maksimal 2 desimal
                },
                bottomCalcFormatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ total bawah juga 2 desimal
                }
            },
            {
                headerSort: false, title: "Pengiriman<br>(QTY/KG)", field: "total_qty_trf", hozAlign: "right", width: "17%", headerHozAlign: "right",
                bottomCalc: "sum",
                formatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ maksimal 2 desimal
                },
                bottomCalcFormatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ total bawah juga 2 desimal
                }
            },
            { headerSort: false, title: "LOT", field: "lot_no", hozAlign: "left", width: "12%", visible: false },
            {
                headerSort: false, title: "Pemakaian<br>(QTY/KG)", field: "total_qty_pakai", hozAlign: "right", width: "17%", headerHozAlign: "right",
                bottomCalc: "sum",
                formatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ maksimal 2 desimal
                },
                bottomCalcFormatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ total bawah juga 2 desimal
                }
            },
            {
                headerSort: false, title: "Sisa (Kg)", field: "sisa", hozAlign: "right", width: "17%", headerHozAlign: "right",
                bottomCalc: "sum",
                formatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ maksimal 2 desimal
                },
                bottomCalcFormatter: function (cell) {
                    return parseFloat(cell.getValue() || 0).toFixed(2); // ✅ total bawah juga 2 desimal
                }
            },
        ],
        locale: 'id',
        // layout: 'fitColumns',
        placeholder: "Tidak ada data",
    });

    if (detail_data_barang.length > 0) {
        setTimeout(() => {
            try {
                let isdata = JSON.parse(detail_data_barang);
                // Set data ke Tabulator
                dtListDetailBarang.setData(isdata);
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        }, 1000);
    }

    let dtListProd = new Tabulator("#dt-list-prod", {
        pagination: true,
        paginationSize: 10,
        paginationButtonCount: 5,
        columns: [
            // {title:"", field:"id", visible:false},
            // {field:"id_walkorder_proses_ukuran", visible:false},
            // {field:"id_operator", visible:false},
            // {field:"tgl_transaksi", visible:false},
            // {field:"id_ukuran", visible:false},
            // {field:"id_warna", visible:false},
            // {field:"flag", visible:false},
            // {
            //     headerSort: false,  
            //     title: '#', 
            //     formatter: buttonRowAction,
            //     width: '5%', align: "center", cssClass: "text-center",
            //     cellClick: function(e, cell) {
            //         let row = cell.getRow();
            //         if (e.target.title === 'delete') {
            //             Swal.fire({
            //                 title: "Apakah anda yakin ingin menghapus data?",
            //                 icon: 'question',
            //                 confirmButtonText: 'Hapus',
            //                 confirmButtonColor: '#dc3545',
            //                 showCancelButton: true,
            //                 cancelButtonText: 'Batal',
            //                 cancelButtonColor: '#6C757D'
            //             }).then((result) => {
            //                 if (result.isConfirmed) {
            //                     row.delete(); 
            //                 }
            //             })
            //         }
            //     }
            // },
            // {headerSort:false, title:"Date", field:"date", width:"8%"},
            { headerSort: false, title: "Kode Transaksi", field: "kode_transaksi", width: "8%" },
            { headerSort: false, title: "Colour", field: "kode_warna", hozAlign: "left", width: "15%" },
            { headerSort: false, title: "Process", field: "process", hozAlign: "left", width: "10%" },
            { headerSort: false, title: "Operator", field: "operator", hozAlign: "left", width: "19%" },
            { headerSort: false, title: "Nomor Mesin", field: "nomor_mesin", hozAlign: "left", width: "15%" },
            { headerSort: false, title: "Size", field: "kode_ukuran", hozAlign: "left", width: "5%" },
            { headerSort: false, title: "QTY", field: "qty", hozAlign: "center", width: "5%", editor: "number", cellEdited: updateTotal },
            {
                headerSort: false, title: "Price", field: "harga", hozAlign: "right", width: "12%", formatter: "money", editor: "number", formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                }, hozAlign: "right",
                cellEdited: function (cell) {

                    let rowData = cell.getRow().getData();
                    if (rowData.flag != 1) {
                        let tableColumn = cell._cell.column.cells;
                        let total_harga = 0;

                        let val_qty = rowData.qty ? rowData.qty : 0;
                        let val_harga = rowData.harga ? rowData.harga : 0;
                        // let val_persen = total_gram > 0 ? (rowData.gram/total_gram) * 100 : 0;
                        //     val_persen = val_persen > 0 ? val_persen.toFixed(2) : 0;
                        total_harga = val_harga * val_qty;

                        // Set nilai total di baris yang sama
                        cell.getRow().update({
                            // persen: val_persen,
                            harga: val_harga,
                            harga_total: total_harga
                        });
                    } else {
                        cell.restoreOldValue();
                    }
                },
            },
            {
                title: "Total", width: "12%", field: "harga_total", formatter: "money", formatterParams: {
                    decimal: ",",
                    thousand: ".",
                    symbol: "Rp",  // Simbol mata uang Rupiah
                    precision: 0,   // Tidak ada desimal
                }, hozAlign: "right"
            },
        ],
        groupBy: "date",
        locale: 'id',
        // layout: 'fitColumns',
        placeholder: "Tidak ada data",
    });

    let dtListUkuran = new Tabulator("#dt-list-ukuran", {
        columns: [
            {
                title: 'Colour', field: 'kode_warna', headerSort: true, formatter: "html", sorter: 'string',
                width: '40%', headerFilter: "input"
            },

            {
                title: 'Size', field: 'kode_ukuran', headerSort: false, formatter: "html", sorter: 'string',
                width: '30%'
            },

            {
                title: 'QTY', field: 'qty', headerSort: false, formatter: "html", sorter: 'string',
                width: '30%', hozAlign: 'center'
            },
        ],
        // layout: 'fitColumns',
        locale: 'id',
        placeholder: "Tidak ada data",
        pagination: true,
        paginationMode: "local",
        paginationSize: 10,
        paginationButtonCount: 10,
        dataSendParams: {
            sorters: "order"
        },
        selectableRows: true,
    });

    function load_ukuran_data() {
        let inpWO = $("#id_walkorder").val()
        let inpPro = $("#filter_status").val();

        $.ajax({
            type: 'POST',
            url: '/trans/production/list_ukuran_prod',
            data: {
                proses: inpPro,
                walkorders: inpWO,
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
                if (response.status == true) {
                    dtListUkuran.setData(response.data);

                    setTimeout(() => {
                        $("#modal-list-wo").modal("show");
                        setTimeout(() => {
                            dtListUkuran.redraw(true);
                        }, 600);
                    }, 500);
                } else {
                    Swal.fire({
                        text: response.message,
                        icon: 'error',
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
    }

    dtListUkuran.on("rowClick", function (e, row) {
        let data = row.getData()
        let arrOperator = operator.val().split(";")


        if (statusProses.val().length == 0) {
            return Swal.fire({
                text: "Status harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }
        if (operator.val().length == 0) {
            return Swal.fire({
                text: "CMT harus dipilih",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // if(!isNumeric(arrOperator[1])){
        //     return Swal.fire({
        //         text: "Harga Operator harus ditentukan direferensi",
        //         icon: 'error',
        //         showConfirmButton: false,
        //         timer: 2000
        //     });
        // }

        if (tglTransaksi.val().length == 0) {
            return Swal.fire({
                text: "Tanggal harus diisi",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (noMesin.val().length == 0) {
            return Swal.fire({
                text: "Nomor Mesin harus diisi",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // if (dtListProd.getData().some(x => x.id_ukuran == data.id_ukuran && x.id_walkorder_proses_ukuran == data.id_walkorder_proses_ukuran)){
        //     return Swal.fire({
        //         text: "Data sudah dipilih, silahkan pilih data yang lain",
        //         icon: 'error',
        //         showConfirmButton: false,
        //         timer: 2000
        //     });
        // }

        if (!data.harga_satuan) {
            return Swal.fire({
                text: "Data ukuran, tidak terdapat dalam sample",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // Mengecek kondisi berdasarkan nilai statusProses
        if (prosesMap[statusProses.val()] && $(prosesMap[statusProses.val()]).val() <= 0) {
            return Swal.fire({
                text: `Tidak bisa memilih status ${$("#filter_status option:selected").text()} , stok tidak tersedia`,
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // console.log(data.id_walkorder_proses_ukuran)

        dtListProd.addRow({
            id: data.id,
            kode_warna: data.kode_warna,
            kode_ukuran: data.kode_ukuran,
            id_ukuran: data.id_ukuran,
            id_warna: data.id_warna,
            ref_detail_id: data.ref_detail_id,
            harga: data.harga_proses,
            qty: 1,
            harga_total: 0,
            flag: 0,
            id_walkorder_proses_ukuran: data.id_walkorder_proses,
            id_proses: statusProses.val(),
            id_operator: operator.val(),
            operator: $('#filter_operator option:selected').text(),
            process: $('#filter_status option:selected').text(),
            date: formatLocaleDate(tglTransaksi.val()),
            nomor_mesin: noMesin.val(),

        });

        $("#modal-list-wo").modal("hide");
    });

    let searchThread2 = null;
    let elSearch2 = $("#tb-search2");
    if (elSearch2 != null) {
        elSearch2.change(function (e) {
            if ($(this).val().length < 3 && e.keyCode > 13) {
                return;
            }
            clearTimeout(searchThread2);
            searchThread2 = setTimeout(function () {
                dtListUkuran.setFilter("", "like", elSearch2.val());
            }, 600);
        });
    }
    function isNumeric(value) {
        return !isNaN(value) && !isNaN(parseFloat(value));
    }

    function updateTotal(cell) {
        let row = cell.getRow();
        if (row.flag != 0) {
            if (row) {
                const selectedProses = prosesMap[statusProses.val()];

                // if (!(row.getData().qty <= $(selectedProses).val())) {
                //     cell.restoreOldValue();
                //     Swal.fire({
                //         text: "Quantity tidak boleh melebihi stok.",
                //         icon: 'error',
                //         showConfirmButton: false,
                //         timer: 2000
                //     });
                // }else{
                let newTotal = calculateTotal(row.getData());
                row.update({ harga_total: newTotal });
                // }

            }
        }
        else {
            cell.restoreOldValue();
        }
    }
    function formatLocaleDate(localeDate) {

        let months = {
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

        let parts = localeDate.split(" ");
        let day = parts[0].padStart(2, '0');
        let month = months[parts[1]];
        let year = parts[2];

        if (month == undefined) {
            month = parts[1]
        }

        // return `${year}-${month}-${day}`;
        return `${day}-${month}-${year}`;
    }

    tglTransaksi.change(function (e) {
        // get_detailData(e.target.value)
        get_detailData()
    })

    statusProses.on("change", function () {
        get_detailData();
    });

    get_detailData();
    function get_detailData(tgl) {
        let date = tglTransaksi != undefined ? formatLocaleDate(tglTransaksi.val()) : ''
        let idProduksi = $("#id_produksi").val()
        let proses = statusProses.val();
        $.ajax({
            type: 'POST',
            url: '/trans/production/list_detail',
            data: {
                id: idProduksi,
                tglTransaksi: date,
                proses: proses
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
                dtListProd.clearData();
                if (response.status == true) {
                    let listProd = response.data
                    if (listProd.length > 0) {
                        setTimeout(() => {
                            try {
                                let isdata = JSON.parse(listProd);

                                // Set data ke Tabulator
                                dtListProd.setData(isdata);
                            } catch (e) {
                                console.error("Error parsing JSON:", e);
                            }
                        }, 0);
                    }

                } else {
                    Swal.fire({
                        text: response.message,
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
                Swal.close();
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
    }

    $("#btn-add-detail").click(function () {
        // dtListUkuran.setData()
        // $("#modal-list-wo").modal("show");
        load_ukuran_data();
        // dtListUkuran.deselectRow();
    });

    $("#btn-save-ukuran").on("click", function (e) {
        e.preventDefault()
        if (dtListProd.getData().filter(x => x.flag == 0).length > 0) {
            Swal.fire({
                title: "Apakah anda ingin mensubmit data Produksi?",
                icon: 'question',
                confirmButtonText: 'Simpan',
                confirmButtonColor: '#198754',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                cancelButtonColor: '#6C757D'
            }).then((result) => {
                if (result.isConfirmed) {
                    simpanDataDetail()
                }
            })
        } else {
            Swal.fire({
                text: "Detail data harus diisi",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

    });

    function simpanDataDetail() {
        $.ajax({
            type: 'POST',
            url: '/trans/production/save',
            data: {
                idProduksi: $("#id_produksi").val(),
                idWorkOrder: $("#id_walkorder").val(),
                data: dtListProd.getData().filter(x => x.flag == 0),
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

                if (response.status == true) {
                    Swal.fire({
                        text: response.message,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    Swal.close();
                    location.reload();

                } else {
                    Swal.fire({
                        text: response.message,
                        icon: 'error',
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
    }

    // fungsi barcode dan autocomplete 
    $("#text_barcode").autocomplete({
        source: function (request, response) {
            if (operator.val().length != 0) {
                $.ajax({
                    url: "/trans/production/cari_produk",
                    dataType: "json",
                    data: {
                        kata_kunci: request.term,
                        id_walkorder: $("#id_walkorder").val(),
                        id_produksi: $("#id_produksi").val(),
                        proses: statusProses.val(),
                    },
                    type: 'post',
                    success: function (data) {
                        if (data.status) {
                            response(data.slc);
                        } else {
                            console.log(data.msg);
                        }
                    }
                });
            } else {
                alert("Pilih CMT terlebih dahulu !");
            }
        },
        minLength: 2,
        select: function (event, ui) {
            addItem(ui.item.data);
        },
        open: function () {
            $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
        },
        close: function () {
            $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            $("#text_barcode").val("");
        }
    });

    function addItem(data, qty = 1) {
        let dataTable = dtListDetail.getData();
        let objIndex = dataTable.findIndex(obj => obj.colordasar == (data.kode_warna));

        let dataOrder = dtListProd.getData();
        let ix_order = dataOrder.findIndex(obj => obj.id_ukuran == (data.id_ukuran) && obj.id_warna == (data.id_warna)
            && obj.id_operator == (operator.val()) && obj.date == formatLocaleDate(tglTransaksi.val()));

        // dtListProd.addRow({
        //     id                          : data.id,
        //     kode_warna                  : data.kode_warna,
        //     kode_ukuran                 : data.kode_ukuran,
        //     id_ukuran                   : data.id_ukuran,
        //     id_warna                    : data.id_warna,
        //     ref_detail_id               : data.ref_detail_id,
        //     harga                       : 0,
        //     qty                         : 1,
        //     harga_total                 : 0,
        //     flag                        : 0,
        //     id_walkorder_proses_ukuran  : data.id_walkorder_proses,
        //     id_proses                   : statusProses.val(),
        //     id_operator                 : operator.val(),
        //     operator                    : $('#filter_operator option:selected').text(),
        //     process                     : $('#filter_status option:selected').text(),
        //     date                        : formatLocaleDate(tglTransaksi.val())

        // });

        if (tglTransaksi.val().length == 0) {
            return Swal.fire({
                text: "Tanggal harus diisi",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
        }

        if (noMesin.val().length == 0) {
            return Swal.fire({
                text: "Nomor Mesin harus diisi",
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        }

        // let ktQty     = isQty.findIndex(obj => obj.kategori_id == (data.kategori_id));
        // let ktQtyO    = isQtyO.findIndex(obj => parseInt(obj.sl_order_det_id) === parseInt(isSlc.val()));

        data.seq = dataOrder.length + 1;
        // console.log(dataTable[objIndex][data.ke_ukuran])
        // return false
        // if(data.qty > dataTable[objIndex].$[data.ke_ukuran]){
        //     if(dataTable[objIndex] == undefined){
        //         if(dataOrder.length > 0){
        //             let qty_order  = dataOrder[ix_order].qty;
        //             data.qty = data.qty_prod + 1; 
        //             if(qty_orderO < qty_order){
        //                 dataTable.push(data);
        //                 addkuota(data.ref_detail_id, false);
        //                 // $("#modal-list-item").modal("hide");
        //             }else{
        //                 alert("Jumlah Order item tersebut sudah terpenuhi !");
        //             }
        //         }

        //     }else{
        //         // alert("Warna ukuran sudah ada di list !");
        //         dataTable[objIndex].qty = dataTable[objIndex].qty + 1; 
        //         addkuota(data.ref_detail_id, false);
        //     }
        // }else{
        //     alert("Jumlah Produksi item tersebut sudah terpenuhi !");
        // }

        if (data.qty > 0) {
            if (ix_order < 0) {
                let isi = {
                    seq: data.seq,
                    id: data.id,
                    kode_warna: data.kode_warna,
                    kode_ukuran: data.kode_ukuran,
                    id_ukuran: data.id_ukuran,
                    id_warna: data.id_warna,
                    ref_detail_id: data.ref_detail_id,
                    harga: data.harga_proses,
                    qty: qty,
                    harga_total: 0,
                    flag: 0,
                    id_walkorder_proses_ukuran: data.id_walkorder_proses,
                    id_proses: statusProses.val(),
                    id_operator: operator.val(),
                    operator: $('#filter_operator option:selected').text(),
                    process: $('#filter_status option:selected').text(),
                    date: formatLocaleDate(tglTransaksi.val()),
                    nomor_mesin: noMesin.val(),

                }

                dataOrder.push(isi)
            } else {
                dataOrder[ix_order].qty = parseInt(dataOrder[ix_order].qty) + parseInt(qty)
            }
        } else {
            alert("Proses Belum mempunyai kuota !");
        }

        dtListProd.replaceData(dataOrder);
        $("#text_barcode").val("");
    }

    // function addkuota(ref_detail_id, hapus){
    //     seq = parseInt(ref_detail_id);

    //     let tblDetail = dtListDetail.getData();
    //     let objIndex = tblDetail.findIndex(obj => parseInt(obj.ref_detail_id) === seq);
    //     let arrData  = tblDetail[objIndex];

    //     if(objIndex >= 0){
    //         if(hapus){
    //             tblDetail[objIndex].qty_prod   = parseInt(tblDetail[objIndex].qty_prod) - 1;
    //             tblDetail[objIndex].qty_remain = parseInt(tblDetail[objIndex].qty_remain) + 1;
    //         }else{
    //             tblDetail[objIndex].qty_prod   = parseInt(tblDetail[objIndex].qty_prod) + 1;
    //             tblDetail[objIndex].qty_remain = parseInt(tblDetail[objIndex].qty_remain) - 1;
    //         }
    //     }

    //     dtListDetail.replaceData(tblDetail);
    // }	


    $("#text_barcode").on("keypress", function (e) {
        let key = e.which;
        if (key == 13) {
            $.ajax({
                url: "trans/production/src_produk",
                dataType: "json",
                data: {
                    kata_kunci: $("#text_barcode").val(),
                    id_walkorder: $("#id_walkorder").val(),
                    id_produksi: $("#id_produksi").val(),
                    proses: statusProses.val(),
                },
                type: 'post',
                success: function (es) {
                    // console.log(es)
                    if (es.status) {
                        // response(data.slc);
                        if (es.data.length > 0) {
                            addItem(es.data[0], es.data[0].qty_prod);
                        } else {
                            alert("Produk tidak ditemukan !");
                        }
                    } else {
                        console.log(es.msg);
                    }
                }
            });
        }
    });


});