$(document).ready(function () {
    $("[data-politespace]").politespace();
    $("#alert-success").hide();

    let fmRekening = $("#ref_rekening_id");
    fmRekening.select2({
        allowClear: false,
        placeholder: "- Pilih Kas/Bank -",
    });
    fmRekening.val(fmRekening.attr("value")).trigger("change");

    let newDetail = function() {
        let res = "<button class='btn btn-xs btn-primary' type='button' id='btn-add-detail'><i class='fa fa-plus'></i> Tambah</button>";
        return res;
    };

    let fmEditDetail = function(value) {
        let fmBtnEdit = "<button class='btn btn-xs btn-warning' type='button' title='edit'><i class='fa fa-edit' title='edit'></i></button>";
        let fmBtnDelete = "<button class='btn btn-xs btn-danger' type='button' title='delete'><i class='fa fa-trash' title='delete'></i></button>";
        return fmBtnEdit + "&nbsp;" + fmBtnDelete;
    }; 

    let fmDModal = $("#modal-item");
    let fmDCoa = $("#coa_id");
    let fmDJumlah = $("#jumlah");
    let fmDKeterangan = $("#keterangan_det");
    let fmDSeq = $("#detail_seq");

    build_tree_coa();   
    function build_tree_coa() {
        fmDCoa.html('');
        fmDCoa.val(fmDCoa.attr('value'));

        $.get("/keuangan/transaksi_masuk/get_org/", {}, function (data) {
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
                placeholder: '- Pilih Akun -',
                multiple: false,
                width: '100%',
                dropdownParent: $('#modal-item')
            });
            fmDCoa.val(fmDCoa.attr('value')).trigger('change');
        }, "json");
    }

    let dtList_det = new Tabulator("#dt-list-det", {
        columns: [
            {
                titleFormatter: newDetail, headerSort: false, formatter: fmEditDetail,
                width: 120, align: "center", cssClass: "text-center",
                visible: (typeof disabled_input !== 'undefined' ? !disabled_input : true),
                cellClick: function(e, cell) {
                    if (e.target.title === 'delete') {
                        if (confirm("Anda yakin akan menghapus data?")) {
                            cell.getRow().delete();
                        }
                    } else if (e.target.title === 'edit') {
                        let rowData = cell.getRow().getData();
                        fmDJumlah.val(rowData.jumlah);
                        fmDKeterangan.val(rowData.keterangan);
                        fmDSeq.val(rowData.seq);

                        setTimeout(() => {
                            fmDCoa.val(rowData.coa_id).trigger("change");
                        }, 500);

                        fmDModal.modal("show");
                    }
                }
            },
            {
                title: "id", field: "id", sorter: "string", headerSort: false, visible: false,
                width: 220
            },
            {
                title: "Nama Akun", field: "coa_nama", sorter: "string", headerSort: false,
                width: 260
            },
            {
                title: "Keterangan", field: "keterangan", sorter: "string", headerSort: false
            },
            {
                title: "Nilai (Rp)", field: "jumlah", sorter: "string", headerSort: false,
                width: 180, formatter: "money", bottomCalc: "sum", bottomCalcFormatter: "money", align: "right", cssClass: "text-start"
            }
        ],
        layout: "fitColumns",
        placeholder: "Belum ada detail akun"
    });

    function loadDetailData() {
        let inpLDetail = $("input[name='Ldetail']");
        if (inpLDetail.length > 0 && inpLDetail.val() && inpLDetail.val().trim() !== '') {
            let rawVal = inpLDetail.val();
            let cleanJson = rawVal.replace(/&quot;/ig, '"').replace(/&#34;/ig, '"');
            try {
                let dt_ldet = JSON.parse(cleanJson);
                if (Array.isArray(dt_ldet) && dt_ldet.length > 0) {
                    dtList_det.replaceData(dt_ldet);
                }
            } catch (e) {
                console.error("Failed to parse Ldetail JSON:", e, cleanJson);
            }
        }
    }

    dtList_det.on("tableBuilt", function() {
        loadDetailData();
    });

    setTimeout(function() {
        loadDetailData();
    }, 300);

    $(document).on("click", "#btn-add-detail", function () {
        fmDSeq.val("");
        fmDJumlah.val("");
        fmDKeterangan.val("");
        fmDCoa.val("").trigger("change");
        fmDModal.modal("show");
    });

    $("#btn-save-det").on("click", function () {
        let coaId = fmDCoa.val();
        let coaText = fmDCoa.find("option:selected").text();
        let jumlah = fmDJumlah.val();
        let ket = fmDKeterangan.val();
        let seq = fmDSeq.val();

        if (!coaId || coaId === "") {
            alert("Pilih akun terlebih dahulu!");
            return;
        }
        if (!jumlah || parseFloat(jumlah) <= 0) {
            alert("Masukkan nilai yang valid!");
            return;
        }

        let curData = dtList_det.getData();
        if (seq && seq !== "") {
            let idx = curData.findIndex(x => x.seq == seq);
            if (idx > -1) {
                curData[idx].coa_id = coaId;
                curData[idx].coa_nama = coaText;
                curData[idx].jumlah = parseFloat(jumlah);
                curData[idx].keterangan = ket;
            }
        } else {
            let nextSeq = 1;
            if (curData.length >= 1) {
                nextSeq = curData[curData.length - 1].seq + 1;
            }
            curData.push({
                id: null,
                seq: nextSeq,
                coa_id: coaId,
                coa_nama: coaText,
                jumlah: parseFloat(jumlah),
                keterangan: ket
            });
        }

        dtList_det.replaceData(curData);
        fmDModal.modal("hide");
    });

    $("#btn-save").on("click", function () {
        let det = dtList_det.getData();
        if (!det || det.length === 0) {
            alert("Tambahkan minimal 1 baris detail akun!");
            return;
        }
        $("input[name='Ldetail']").val(JSON.stringify(det));
        $("#status").val("0");
        $("#fmain").submit();
    });

    $("#btn-approve").on("click", function () {
        let det = dtList_det.getData();
        if (!det || det.length === 0) {
            alert("Tambahkan minimal 1 baris detail akun!");
            return;
        }
        if (confirm("Apakah Anda yakin ingin menyetujui transaksi ini? Transaksi yang disetujui akan terkunci.")) {
            $("input[name='Ldetail']").val(JSON.stringify(det));
            $("#status").val("1");
            $("#fmain").submit();
        }
    });
});
