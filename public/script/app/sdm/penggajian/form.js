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
                width: 220, formatter:"money", formatterParams: { precision: 0, thousand: ".", decimal: "," }, cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalcFormatterParams: { precision: 0, thousand: ".", decimal: "," }, bottomCalc: 'sum'
			} ,

            {
				title: 'Sample/Perbaikan', field: 'jml_sample', headerSort:false, sorter: 'string', align: "center",
                width: 150, formatter:"money", formatterParams: { precision: 0, thousand: ".", decimal: "," }, cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalcFormatterParams: { precision: 0, thousand: ".", decimal: "," }, bottomCalc: 'sum'
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
                width: 220, cssClass:"text-end", formatter: "money", formatterParams: { precision: 0, thousand: ".", decimal: "," }
			} , 

            {
				title: 'Lembur', field: 'uang_lembur', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", formatterParams: { precision: 0, thousand: ".", decimal: "," }, cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalcFormatterParams: { precision: 0, thousand: ".", decimal: "," }, bottomCalc: 'sum'
			} ,

            {
				title: 'Penambahan', field: 'bonus', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", formatterParams: { precision: 0, thousand: ".", decimal: "," }, cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalcFormatterParams: { precision: 0, thousand: ".", decimal: "," }, bottomCalc: 'sum'
			} ,
            {
				title: 'Keterangan bonus', field: 'bonus_keterangan', headerSort:false, sorter: 'string',
				visible: false,
			}, 

            {
				title: 'Potongan', field: 'potongan', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", formatterParams: { precision: 0, thousand: ".", decimal: "," }, cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalcFormatterParams: { precision: 0, thousand: ".", decimal: "," }, bottomCalc: 'sum'
			} ,

            {
				title: 'Gaji/Upah', field: 'total', headerSort:false, sorter: 'string', align: "center",
                width: 220, formatter:"money", formatterParams: { precision: 0, thousand: ".", decimal: "," }, cssClass:"text-end", bottomCalcFormatter: 'money', bottomCalcFormatterParams: { precision: 0, thousand: ".", decimal: "," }, bottomCalc: 'sum'
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

    $("#btn-sync-tbmp").on("click", function(){
        const tglA = $("#filter_tgl_from").val();
        const tglZ = $("#filter_tgl_to").val();
        const perusahaan = $("#id_perusahaan").val();

        if (perusahaan == null || perusahaan.length == 0) {
            Swal.fire({
                text: "Harap Pilih Perusahaan Dahulu!",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
            return false;
        }

        if (!tglA || !tglZ) {
            Swal.fire({
                text: "Harap Tentukan Periode Tanggal Awal dan Akhir!",
                icon: 'warning',
                showConfirmButton: false,
                timer: 2000
            });
            return false;
        }

        Swal.fire({
            title: 'Sinkronisasi TBMP ➔ TPO',
            html: `Apakah Anda yakin ingin menyinkronkan data upah (<b>TPO</b>) dari data barang masuk (<b>TBMP</b>) untuk periode <b>${tglA} s.d ${tglZ}</b>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fa fa-refresh"></i> Ya, Sinkronkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.value || result.isConfirmed) {
                $.ajax({
                    url: '/sdm/penggajian/sync_tbmp_tpo',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        tgl_mulai: tglA,
                        tgl_akhir: tglZ,
                        id_perusahaan: perusahaan
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Sedang Sinkronisasi...',
                            text: 'Memproses rekonsiliasi TBMP ke TPO...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        Swal.close();
                        if (res.status) {
                            const formatRupiah = (val) => {
                                return 'Rp ' + Number(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            };

                            // 1. Build Tabel Rekapitulasi Per Karyawan / CMT
                            let rowKaryawanHtml = '';
                            if (res.summary_karyawan && res.summary_karyawan.length > 0) {
                                res.summary_karyawan.forEach((item, idx) => {
                                    const isMatch = (item.status === 'MATCH 100%');
                                    const badgeStatus = isMatch
                                        ? '<span class="badge bg-success" style="font-size: 10.5px; padding: 3px 6px;"><i class="fa fa-check-square"></i> MATCH 100%</span>'
                                        : `<span class="badge bg-danger" style="font-size: 10.5px; padding: 3px 6px;"><i class="fa fa-warning"></i> ${item.status}</span>`;

                                    rowKaryawanHtml += `
                                        <tr>
                                            <td class="text-center align-middle py-1 px-2" style="white-space: nowrap;">${idx + 1}</td>
                                            <td class="align-middle text-center py-1 px-2" style="white-space: nowrap;"><span class="badge bg-light text-danger border" style="font-family: monospace; font-size: 10.5px;">${item.nip}</span></td>
                                            <td class="align-middle fw-bold text-start py-1 px-2" style="white-space: nowrap;">${item.nama_karyawan}</td>
                                            <td class="align-middle text-start text-muted py-1 px-2" style="white-space: nowrap; font-size: 11px;">${item.posisi}</td>
                                            <td class="align-middle text-end fw-bold py-1 px-2" style="white-space: nowrap;">${formatRupiah(item.nilai_tpo)}</td>
                                            <td class="align-middle text-end fw-bold text-primary py-1 px-2" style="white-space: nowrap;">${formatRupiah(item.nilai_tbmp)}</td>
                                            <td class="align-middle text-center py-1 px-2" style="white-space: nowrap;">${badgeStatus}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                rowKaryawanHtml = `<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data hasil produksi yang disinkronkan.</td></tr>`;
                            }

                            // 2. Build Tabel Detail Dokumen TBMP Draft
                            let draftSectionHtml = '';
                            if (res.draft_list && res.draft_list.length > 0) {
                                let rowDraftHtml = '';
                                let totalDraftQty = 0;
                                let totalDraftNilai = 0;

                                res.draft_list.forEach((d, idx) => {
                                    totalDraftQty += Number(d.total_qty || 0);
                                    totalDraftNilai += Number(d.total_nilai || 0);

                                    let tglFormat = d.tanggal;
                                    try {
                                        const dt = new Date(d.tanggal);
                                        tglFormat = String(dt.getDate()).padStart(2, '0') + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + dt.getFullYear();
                                    } catch(e){}

                                    rowDraftHtml += `
                                        <tr>
                                            <td class="text-center align-middle py-1 px-2" style="white-space: nowrap;">${idx + 1}</td>
                                            <td class="align-middle text-center font-monospace fw-bold text-primary py-1 px-2" style="white-space: nowrap;">${d.kode_transaksi}</td>
                                            <td class="align-middle text-center py-1 px-2" style="white-space: nowrap;">${tglFormat}</td>
                                            <td class="align-middle text-start fw-bold py-1 px-2" style="white-space: nowrap;">${d.nama_cmt}</td>
                                            <td class="align-middle text-start text-muted py-1 px-2" style="white-space: nowrap;">${d.nama_proses}</td>
                                            <td class="align-middle text-end py-1 px-2" style="white-space: nowrap;">${Number(d.total_qty || 0).toLocaleString('id-ID')} pcs</td>
                                            <td class="align-middle text-end fw-bold text-danger py-1 px-2" style="white-space: nowrap;">${formatRupiah(d.total_nilai)}</td>
                                            <td class="align-middle text-center py-1 px-2" style="white-space: nowrap;"><span class="badge bg-warning text-dark" style="font-size: 10px;">Draft (Belum Approve)</span></td>
                                        </tr>
                                    `;
                                });

                                draftSectionHtml = `
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="text-danger fw-bold mb-0" style="font-size: 12.5px;">
                                                <i class="fa fa-exclamation-triangle me-1"></i> Rincian Dokumen TBMP Status DRAFT (${res.draft_count} Dokumen)
                                            </h6>
                                            <span class="badge bg-danger" style="font-size: 10.5px;">Belum Approve / Belum Masuk Gaji</span>
                                        </div>
                                        <div class="table-responsive border rounded" style="max-height: 220px; overflow-y: auto;">
                                            <table class="table table-sm table-striped table-hover mb-0" style="font-size: 11px;">
                                                <thead class="table-danger sticky-top" style="position: sticky; top: 0; z-index: 1;">
                                                    <tr class="text-center align-middle">
                                                        <th class="py-1 px-2" style="white-space: nowrap; width: 35px;">No</th>
                                                        <th class="py-1 px-2" style="white-space: nowrap;">Kode BTM/BTMC</th>
                                                        <th class="py-1 px-2" style="white-space: nowrap;">Tanggal</th>
                                                        <th class="py-1 px-2" style="white-space: nowrap;">Nama Operator / CMT</th>
                                                        <th class="py-1 px-2" style="white-space: nowrap;">Proses</th>
                                                        <th class="py-1 px-2" style="white-space: nowrap;">Qty Fisik</th>
                                                        <th class="py-1 px-2" style="white-space: nowrap;">Nilai Fisik (Rp)</th>
                                                        <th class="py-1 px-2" style="white-space: nowrap;">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${rowDraftHtml}
                                                </tbody>
                                                <tfoot class="table-light fw-bold" style="position: sticky; bottom: 0; z-index: 1;">
                                                    <tr class="align-middle">
                                                        <td colspan="5" class="text-center py-1 px-2" style="white-space: nowrap;">TOTAL TBMP DRAFT</td>
                                                        <td class="text-end py-1 px-2" style="white-space: nowrap;">${totalDraftQty.toLocaleString('id-ID')} pcs</td>
                                                        <td class="text-end text-danger py-1 px-2" style="white-space: nowrap;">${formatRupiah(totalDraftNilai)}</td>
                                                        <td class="py-1 px-2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                `;
                            } else {
                                draftSectionHtml = `
                                    <div class="alert alert-success text-start mt-2 mb-0 py-2 px-3" style="font-size: 11.5px;">
                                        <i class="fa fa-check-circle text-success me-1"></i>
                                        <b>Status TBMP Draft:</b> 0 Dokumen (Semua dokumen TBMP periode ini sudah berstatus Approved).
                                    </div>
                                `;
                            }

                            // 3. Modal Body HTML
                            const isAllMatch = (Math.abs(Number(res.grand_tpo || 0) - Number(res.grand_tbmp || 0)) < 1);
                            const grandBadge = isAllMatch
                                ? '<span class="badge bg-success" style="font-size: 11.5px; padding: 4px 8px;">🎉 MATCH 100%</span>'
                                : '<span class="badge bg-warning text-dark" style="font-size: 11.5px; padding: 4px 8px;">⚠️ Ada Selisih</span>';

                            let modalHtml = `
                                <div class="text-start" style="font-size: 11.5px;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted" style="font-size: 12px;">Periode: <b>${tglA} s.d ${tglZ}</b></span>
                                        <span class="badge bg-primary" style="font-size: 11px;">${res.total_dokumen} Dokumen Approved Disinkronkan</span>
                                    </div>

                                    <div class="table-responsive border rounded" style="max-height: 320px; overflow-y: auto;">
                                        <table class="table table-sm table-striped table-hover mb-0" style="font-size: 11px;">
                                            <thead class="table-secondary sticky-top" style="position: sticky; top: 0; z-index: 1;">
                                                <tr class="text-center align-middle">
                                                    <th class="py-1 px-2" style="white-space: nowrap; width: 30px;">No</th>
                                                    <th class="py-1 px-2" style="white-space: nowrap; width: 95px;">NIP</th>
                                                    <th class="py-1 px-2" style="white-space: nowrap;">Nama Karyawan / CMT</th>
                                                    <th class="py-1 px-2" style="white-space: nowrap;">Posisi</th>
                                                    <th class="py-1 px-2" style="white-space: nowrap;">Gaji/Upah Baru (Di Sistem)</th>
                                                    <th class="py-1 px-2" style="white-space: nowrap;">Nilai di Excel BTM</th>
                                                    <th class="py-1 px-2" style="white-space: nowrap; width: 110px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${rowKaryawanHtml}
                                            </tbody>
                                            <tfoot class="table-light fw-bold" style="position: sticky; bottom: 0; z-index: 1; border-top: 2px solid #bbb;">
                                                <tr class="align-middle">
                                                    <td colspan="4" class="text-center uppercase fw-bold py-1 px-2" style="white-space: nowrap;">GRAND TOTAL</td>
                                                    <td class="text-end fw-bold py-1 px-2" style="white-space: nowrap;">${formatRupiah(res.grand_tpo)}</td>
                                                    <td class="text-end fw-bold text-primary py-1 px-2" style="white-space: nowrap;">${formatRupiah(res.grand_tbmp)}</td>
                                                    <td class="text-center py-1 px-2" style="white-space: nowrap;">${grandBadge}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    ${draftSectionHtml}
                                </div>
                            `;

                            Swal.fire({
                                icon: 'success',
                                title: 'Hasil Rekonsiliasi & Sinkronisasi TBMP ➔ TPO',
                                html: modalHtml,
                                width: '1050px',
                                confirmButtonText: '<i class="fa fa-check"></i> OK & Refresh Penggajian',
                                confirmButtonColor: '#28a745',
                                customClass: {
                                    popup: 'text-start'
                                }
                            }).then(() => {
                                $("#btn-generate").trigger("click");
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Sinkronisasi Gagal',
                                text: res.message || 'Terjadi kesalahan saat memproses data sinkronisasi.'
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Jaringan',
                            text: 'Gagal menghubungi server untuk sinkronisasi.'
                        });
                    }
                });
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
        e.preventDefault();
        let tipe = $("#tipe_cetak").val();
        let params = {
            data_id : inpid.val(),
        };
        let queryString = $.param(params);
        let endpoint = (tipe === 'rekap') ? 'sdm/penggajian/generate-rekap' : 'sdm/penggajian/generate';
        let fullUrl = `${endpoint}?${queryString}`;
        window.open(fullUrl, '_blank');
    });

    $("#btn-cetak-export").on('click', function (e) {
        e.preventDefault();
        let tipe = $("#tipe_cetak").val();
        let params = {
            data_id : inpid.val(),
        };
        let queryString = $.param(params);
        let endpoint = (tipe === 'rekap') ? 'sdm/penggajian/generate-rekap-excel' : 'sdm/penggajian/generate-excel';
        let fullUrl = `${endpoint}?${queryString}`;
        window.open(fullUrl, '_blank');
    });
});