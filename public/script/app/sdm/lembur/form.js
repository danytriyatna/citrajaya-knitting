$(document).ready(function () {
    const isReadOnly = (docStatus === 2);

    // =========================================================================
    // 1. TABULATOR DETAIL LEMBUR
    // =========================================================================
    function recalculateRow(row) {
        let data = row.getData();
        let target = parseFloat(data.target) || 0;
        let realisasi = parseFloat(data.realisasi) || 0;

        let persentase = 0;
        if (target > 0) {
            persentase = Math.min(100.00, (realisasi / target) * 100);
        }

        let jamAbsen = parseFloat(data.jam_lembur_absen) || 0;
        let jamDihitung = (jamAbsen * (persentase / 100));

        row.update({
            persentase: Math.round(persentase * 100) / 100,
            jam_lembur_dihitung: Math.round(jamDihitung * 100) / 100
        });

        updateSummaryTotals();
    }

    function updateSummaryTotals() {
        let rows = dtDetail.getData();
        let totalKaryawan = rows.length;
        let totalJamDihitung = 0;

        rows.forEach(r => {
            totalJamDihitung += parseFloat(r.jam_lembur_dihitung) || 0;
        });

        $("#lbl-total-karyawan").text(totalKaryawan);
        $("#lbl-total-jam-dihitung").text(totalJamDihitung.toFixed(2));
    }

    let detailColumns = [];

    if (!isReadOnly) {
        detailColumns.push({
            title: "Aksi", width: "4%", hozAlign: "center", headerHozAlign: "center", headerSort: false,
            formatter: function () {
                return '<button type="button" class="btn btn-xs btn-danger py-1 px-2 btn-delete-row shadow-sm" title="Hapus Karyawan"><i class="fa fa-trash"></i></button>';
            },
            cellClick: function (e, cell) {
                if ($(e.target).closest(".btn-delete-row").length) {
                    let d = cell.getRow().getData();
                    Swal.fire({
                        title: "Hapus Karyawan?",
                        text: `Karyawan "${d.full_name}" akan dihapus dari daftar penugasan lembur ini.`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Ya, Hapus",
                        cancelButtonText: "Batal"
                    }).then((res) => {
                        if (res.isConfirmed) {
                            cell.getRow().delete();
                            updateSummaryTotals();
                        }
                    });
                }
            }
        });
    }

    detailColumns.push(
        {
            title: "No", formatter: "rownum", width: "4%", hozAlign: "center", headerHozAlign: "center", headerSort: false
        },
        {
            title: "NIP & Nama Karyawan", field: "full_name", width: "15%", headerSort: false,
            formatter: function (cell) {
                let d = cell.getRow().getData();
                let jamAbsen = parseFloat(d.jam_lembur_absen) || 0;
                let warningBadge = (jamAbsen <= 0 && d.jam_masuk === "-") ? '<span class="badge bg-danger ms-1 small">Lembur 0</span>' : '';
                return `<div><b>${d.full_name}</b> ${warningBadge}<br><small class="text-muted">${d.nip || '-'} (${d.posisi || '-'})</small></div>`;
            }
        },
        {
            title: "Pekerjaan / Tugas", field: "pekerjaan", width: "13%", headerSort: false,
            editor: isReadOnly ? false : "input",
            formatter: function (cell) {
                let val = cell.getValue();
                return val ? val : '<span class="text-muted font-italic small">+ isi pekerjaan...</span>';
            }
        },
        {
            title: "Target", field: "target", width: "7%", hozAlign: "right", headerHozAlign: "right", headerSort: false,
            editor: isReadOnly ? false : "number",
            editorParams: { min: 0, step: 1 },
            formatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            },
            cellEdited: function (cell) {
                recalculateRow(cell.getRow());
            }
        },
        {
            title: "Satuan", field: "satuan_target", width: "6%", hozAlign: "center", headerHozAlign: "center", headerSort: false,
            editor: isReadOnly ? false : "list",
            editorParams: {
                values: ["Pcs", "Lusin", "Kg", "Meter", "Box", "Lot", "Roll", "Lembar"],
                autocomplete: true,
                allowEmpty: false
            }
        },
        {
            title: "Realisasi", field: "realisasi", width: "7%", hozAlign: "right", headerHozAlign: "right", headerSort: false,
            editor: isReadOnly ? false : "number",
            editorParams: { min: 0, step: 1 },
            formatter: function (cell) {
                return parseFloat(cell.getValue() || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            },
            cellEdited: function (cell) {
                recalculateRow(cell.getRow());
            }
        },
        {
            title: "% Capai", field: "persentase", width: "7%", hozAlign: "center", headerHozAlign: "center", headerSort: false,
            formatter: function (cell) {
                let val = parseFloat(cell.getValue()) || 0;
                let colorClass = val >= 100 ? 'badge bg-success' : (val >= 80 ? 'badge bg-info text-white' : (val > 0 ? 'badge bg-warning text-dark' : 'badge bg-secondary'));
                return `<span class="${colorClass} px-2 py-1">${val.toFixed(1)}%</span>`;
            }
        },
        {
            title: "Masuk", field: "jam_masuk", width: "6%", hozAlign: "center", headerHozAlign: "center", headerSort: false,
            formatter: function (cell) {
                let val = cell.getValue();
                if (!val || val === "-") {
                    return '<span class="text-danger font-weight-bold">-</span>';
                }
                return `<b>${val}</b>`;
            }
        },
        {
            title: "Pulang", field: "jam_keluar", width: "6%", hozAlign: "center", headerHozAlign: "center", headerSort: false,
            formatter: function (cell) {
                let val = cell.getValue();
                if (!val || val === "-") {
                    return '<span class="text-danger font-weight-bold">-</span>';
                }
                return `<b>${val}</b>`;
            }
        },
        {
            title: "Status Lembur", field: "status_lembur", width: "8%", hozAlign: "center", headerHozAlign: "center", headerSort: false,
            formatter: function (cell) {
                let val = cell.getValue();
                if (val == 1 || val === "1" || val === "Lembur Weekday") {
                    return '<span class="badge bg-primary text-white px-2 py-1">Lembur Weekday</span>';
                } else if (val == 2 || val === "2" || val === "Lembur Weekend/Hari Libur") {
                    return '<span class="badge bg-info text-white px-2 py-1">Lembur Weekend</span>';
                }
                return '<span class="badge bg-secondary px-2 py-1">-</span>';
            }
        },
        {
            title: "Jam Absen", field: "jam_lembur_absen", width: "7%", hozAlign: "right", headerHozAlign: "right", headerSort: false,
            formatter: function (cell) {
                let val = parseFloat(cell.getValue()) || 0;
                if (val <= 0) {
                    return `<span class="badge bg-danger text-white font-weight-bold">0.00 Jam</span>`;
                }
                return `<b class="text-success">${val.toFixed(2)} Jam</b>`;
            }
        },
        {
            title: "Jam Dihitung", field: "jam_lembur_dihitung", width: "8%", hozAlign: "right", headerHozAlign: "right", headerSort: false,
            formatter: function (cell) {
                let val = parseFloat(cell.getValue()) || 0;
                let badgeClass = val > 0 ? 'bg-light-primary text-primary border border-primary' : 'bg-light text-muted border';
                return `<span class="badge ${badgeClass} font-weight-bold fs-6">${val.toFixed(2)} Jam</span>`;
            }
        },
        {
            title: "Catatan", field: "catatan", width: "8%", headerSort: false,
            editor: isReadOnly ? false : "input",
            formatter: function (cell) {
                let val = cell.getValue();
                return val ? val : '<span class="text-muted small">-</span>';
            }
        }
    );

    let dtDetail = new Tabulator("#dt-detail-lembur", {
        data: initialDetailData,
        layout: "fitColumns",
        placeholder: "Belum ada karyawan yang ditambahkan. Klik tombol '+ Tambah Karyawan' di atas.",
        minHeight: 250,
        columns: detailColumns,
        rowFormatter: function (row) {
            let data = row.getData();
            let jamAbsen = parseFloat(data.jam_lembur_absen) || 0;
            let el = row.getElement();

            // Jika nilai jam lembur 0 atau tidak ada data lembur (jam_masuk "-"), beri warna merah pada barisnya
            if (data.is_synced || (data.jam_masuk === "-" && jamAbsen <= 0)) {
                if (jamAbsen <= 0) {
                    el.style.backgroundColor = "#fff0f0";
                    el.style.borderLeft = "4px solid #dc3545";
                } else {
                    el.style.backgroundColor = "";
                    el.style.borderLeft = "";
                }
            } else {
                el.style.backgroundColor = "";
                el.style.borderLeft = "";
            }
        }
    });

    dtDetail.on("tableBuilt", function () {
        updateSummaryTotals();
    });

    // =========================================================================
    // 2. MODAL MULTI-SELECT KARYAWAN (TANPA PAGING - KHUSUS NON-CMT)
    // =========================================================================
    let dtModalKaryawan = null;

    function initModalKaryawan() {
        if (dtModalKaryawan) return;

        dtModalKaryawan = new Tabulator("#dt-pilih-karyawan", {
            columns: [
                {
                    formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerHozAlign: "center",
                    headerSort: false, width: 60
                },
                {
                    title: "NIP", field: "nip", width: 140, headerSort: false
                },
                {
                    title: "Nama Lengkap (Non-CMT)", field: "full_name", minWidth: 230, headerSort: false,
                    formatter: function(cell) {
                        return `<b>${cell.getValue()}</b>`;
                    }
                },
                {
                    title: "Posisi / Jabatan", field: "posisi", minWidth: 180, headerSort: false
                },
                {
                    title: "Perusahaan", field: "nama_perusahaan", minWidth: 160, headerSort: false
                }
            ],
            layout: "fitColumns",
            height: "380px",
            pagination: false,
            placeholder: "Tidak ada data karyawan non-CMT",
            selectableRows: true
        });

        dtModalKaryawan.on("rowSelectionChanged", function (data) {
            $("#badge-selected-count").text(data.length + " Terpilih");
        });
    }

    function loadModalKaryawanData() {
        let idPerusahaan = $("#modal_filter_perusahaan").val();
        if (idPerusahaan === "all") idPerusahaan = "";

        $.ajax({
            url: "/sdm/lembur/list-karyawan",
            type: "POST",
            dataType: "json",
            data: { id_perusahaan: idPerusahaan },
            success: function (res) {
                if (dtModalKaryawan && res.data) {
                    dtModalKaryawan.setData(res.data);
                    dtModalKaryawan.deselectRow();
                    $("#badge-selected-count").text("0 Terpilih");
                    $("#search-modal-karyawan").val("");
                }
            }
        });
    }

    $("#btn-tambah-karyawan").on("click", function () {
        initModalKaryawan();
        $("#modal_filter_perusahaan").val($("#id_perusahaan").val());
        loadModalKaryawanData();
        $("#modal-karyawan").modal("show");
    });

    // Redraw table when modal is fully shown to prevent squished columns
    $("#modal-karyawan").on("shown.bs.modal", function () {
        if (dtModalKaryawan) {
            dtModalKaryawan.redraw(true);
        }
    });

    $("#modal_filter_perusahaan").on("change", function () {
        loadModalKaryawanData();
    });

    // Instant local client search
    $("#search-modal-karyawan").on("keyup", function () {
        let val = $(this).val().toLowerCase();
        if (dtModalKaryawan) {
            if (val) {
                dtModalKaryawan.setFilter(function (data) {
                    let nip = (data.nip || "").toLowerCase();
                    let name = (data.full_name || "").toLowerCase();
                    let pos = (data.posisi || "").toLowerCase();
                    return nip.includes(val) || name.includes(val) || pos.includes(val);
                });
            } else {
                dtModalKaryawan.clearFilter();
            }
        }
    });

    // Masukkan Karyawan Terpilih dari Modal ke Tabel Detail
    $("#btn-insert-selected-karyawan").on("click", function () {
        if (!dtModalKaryawan) return;

        let selectedRows = dtModalKaryawan.getSelectedData();
        if (selectedRows.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Pilih Karyawan",
                text: "Silakan centang minimal 1 karyawan terlebih dahulu!"
            });
            return;
        }

        let currentDetails = dtDetail.getData();
        let existingIds = currentDetails.map(d => parseInt(d.id_karyawan));

        let addedCount = 0;
        selectedRows.forEach(emp => {
            if (!existingIds.includes(parseInt(emp.id))) {
                dtDetail.addRow({
                    id_karyawan: parseInt(emp.id),
                    id_absensi: null,
                    nip: emp.nip,
                    full_name: emp.full_name,
                    posisi: emp.posisi,
                    pekerjaan: "",
                    target: 0,
                    satuan_target: "Pcs",
                    realisasi: 0,
                    persentase: 0,
                    jam_masuk: "",
                    jam_keluar: "",
                    status_lembur: null,
                    jam_lembur_absen: 0,
                    jam_lembur_dihitung: 0,
                    catatan: "",
                    is_synced: false
                });
                addedCount++;
            }
        });

        $("#modal-karyawan").modal("hide");
        updateSummaryTotals();

        // Otomatis jalankan sinkronisasi absensi untuk karyawan baru
        if (addedCount > 0) {
            triggerSyncAbsensi(false);
        }
    });

    // =========================================================================
    // 3. SINKRONISASI DATA ABSENSI
    // =========================================================================
    function triggerSyncAbsensi(showManualAlert = true) {
        let tanggal = $("#tanggal").val();
        if (!tanggal) {
            if (showManualAlert) {
                Swal.fire({ icon: "warning", title: "Perhatian", text: "Silakan isi Tanggal Lembur terlebih dahulu!" });
            }
            return;
        }

        let currentRows = dtDetail.getData();
        let idKaryawans = currentRows.map(r => parseInt(r.id_karyawan));

        let btn = $("#btn-sync-absensi");
        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i> Sinkronisasi & menarik data...');

        $.ajax({
            url: "/sdm/lembur/sync-absensi",
            type: "POST",
            dataType: "json",
            data: {
                tanggal: tanggal,
                id_karyawan: idKaryawans
            },
            success: function (res) {
                btn.prop("disabled", false).html('<i class="fa fa-sync me-1"></i> 🔄 Tarik / Sinkronisasi Absensi & Target');

                if (res.status && res.data) {
                    let listData = res.data;
                    let addedCount = 0;
                    let updatedCount = 0;

                    listData.forEach(item => {
                        let empId = parseInt(item.id_karyawan);
                        let existingRow = dtDetail.getRows().find(r => parseInt(r.getData().id_karyawan) === empId);

                        if (existingRow) {
                            let rData = existingRow.getData();
                            // Jika target harian memiliki data, perbarui target & realisasi
                            let target = item.has_target_harian ? item.target : (parseFloat(rData.target) || 0);
                            let realisasi = item.has_target_harian ? item.realisasi : (parseFloat(rData.realisasi) || 0);
                            let satuan = item.has_target_harian ? item.satuan_target : (rData.satuan_target || "Pcs");

                            let persentase = target > 0 ? Math.min(100.00, (realisasi / target) * 100) : 0;
                            let jamAbsen = parseFloat(item.jam_lembur_absen) || 0;
                            let jamDihitung = (jamAbsen * (persentase / 100));

                            existingRow.update({
                                id_absensi: item.id_absensi,
                                target: target,
                                realisasi: realisasi,
                                satuan_target: satuan,
                                persentase: Math.round(persentase * 100) / 100,
                                jam_masuk: item.jam_masuk || "-",
                                jam_keluar: item.jam_keluar || "-",
                                status_kehadiran: item.status_kehadiran,
                                status_lembur: item.status_lembur,
                                jam_lembur_absen: jamAbsen,
                                jam_lembur_dihitung: Math.round(jamDihitung * 100) / 100,
                                is_synced: true
                            });
                            updatedCount++;
                        } else {
                            // Karyawan baru yang ditarik karena absensinya memiliki status lembur atau ada di target harian
                            dtDetail.addRow({
                                id_karyawan: empId,
                                id_absensi: item.id_absensi,
                                nip: item.nip,
                                full_name: item.full_name,
                                posisi: item.posisi,
                                pekerjaan: "",
                                target: item.target,
                                satuan_target: item.satuan_target || "Pcs",
                                realisasi: item.realisasi,
                                persentase: item.persentase,
                                jam_masuk: item.jam_masuk || "-",
                                jam_keluar: item.jam_keluar || "-",
                                status_kehadiran: item.status_kehadiran,
                                status_lembur: item.status_lembur,
                                jam_lembur_absen: item.jam_lembur_absen,
                                jam_lembur_dihitung: item.jam_lembur_dihitung,
                                catatan: "",
                                is_synced: true
                            });
                            addedCount++;
                        }
                    });

                    updateSummaryTotals();

                    if (showManualAlert) {
                        let textDesc = `Data berhasil disinkronkan (${updatedCount} karyawan diperbarui).`;
                        if (addedCount > 0) {
                            textDesc = `Ditemukan ${addedCount} karyawan berstatus lembur baru yang ditarik, dan ${updatedCount} karyawan diperbarui.`;
                        } else if (listData.length === 0) {
                            textDesc = "Tidak ditemukan karyawan dengan status lembur di absensi atau target harian pada tanggal ini.";
                        }
                        Swal.fire({
                            icon: "success",
                            title: "Sinkronisasi Berhasil",
                            text: textDesc,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                }
            },
            error: function () {
                btn.prop("disabled", false).html('<i class="fa fa-sync me-1"></i> 🔄 Tarik / Sinkronisasi Absensi & Target');
                if (showManualAlert) {
                    Swal.fire({ icon: "error", title: "Error", text: "Gagal menghubungkan ke data absensi!" });
                }
            }
        });
    }

    $("#btn-sync-absensi").on("click", function () {
        triggerSyncAbsensi(true);
    });

    // Tarik karyawan beserta target & realisasinya langsung dari menu Target Harian
    $("#btn-tarik-target-harian").on("click", function () {
        let tanggal = $("#tanggal").val();
        if (!tanggal) {
            Swal.fire({ icon: "warning", title: "Perhatian", text: "Silakan isi Tanggal Lembur terlebih dahulu!" });
            return;
        }

        let btn = $(this);
        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menarik Target Harian...');

        $.ajax({
            url: "/sdm/lembur/get-target-harian-karyawan",
            type: "POST",
            dataType: "json",
            data: { tanggal: tanggal },
            success: function (res) {
                btn.prop("disabled", false).html('<i class="fa fa-bullseye me-1"></i> 🎯 Tarik dari Target Harian');

                if (res.status && res.data && res.data.length > 0) {
                    let currentDetails = dtDetail.getData();
                    let existingIds = currentDetails.map(d => parseInt(d.id_karyawan));
                    let addedCount = 0;
                    let updatedCount = 0;

                    res.data.forEach(item => {
                        let empId = parseInt(item.id_karyawan);
                        let existingRow = dtDetail.getRows().find(r => parseInt(r.getData().id_karyawan) === empId);

                        if (existingRow) {
                            existingRow.update({
                                target: item.target,
                                realisasi: item.realisasi,
                                satuan_target: item.satuan_target,
                                persentase: item.persentase,
                                id_absensi: item.id_absensi,
                                jam_masuk: item.jam_masuk,
                                jam_keluar: item.jam_keluar,
                                status_kehadiran: item.status_kehadiran,
                                status_lembur: item.status_lembur,
                                jam_lembur_absen: item.jam_lembur_absen,
                                jam_lembur_dihitung: item.jam_lembur_dihitung,
                                is_synced: true
                            });
                            updatedCount++;
                        } else {
                            dtDetail.addRow(item);
                            addedCount++;
                        }
                    });

                    updateSummaryTotals();

                    Swal.fire({
                        icon: "success",
                        title: "Tarik Target Harian Berhasil",
                        text: `Ditemukan ${res.data.length} karyawan lembur di Target Harian (${addedCount} baru ditambahkan, ${updatedCount} diperbarui).`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: "info",
                        title: "Informasi",
                        text: res.message || "Tidak ada karyawan dengan target/realisasi lembur di Target Harian pada tanggal ini."
                    });
                }
            },
            error: function () {
                btn.prop("disabled", false).html('<i class="fa fa-bullseye me-1"></i> 🎯 Tarik dari Target Harian');
                Swal.fire({ icon: "error", title: "Error", text: "Gagal mengambil data dari Target Harian!" });
            }
        });
    });

    $("#tanggal").on("change", function () {
        if (dtDetail.getData().length > 0) {
            triggerSyncAbsensi(false);
        }
    });

    // =========================================================================
    // 4. SIMPAN DRAFT / UPDATE FORM
    // =========================================================================
    $("#btn-save-draft").on("click", function () {
        let tanggal = $("#tanggal").val();
        if (!tanggal) {
            Swal.fire({ icon: "warning", title: "Validasi", text: "Tanggal lembur wajib diisi!" });
            return;
        }

        let detailRows = dtDetail.getData();
        if (detailRows.length === 0) {
            Swal.fire({ icon: "warning", title: "Validasi", text: "Tambahkan minimal 1 karyawan untuk penugasan lembur!" });
            return;
        }

        let payload = {
            id: $("#id_lembur").val(),
            tanggal: tanggal,
            id_perusahaan: $("#id_perusahaan").val(),
            keterangan: $("#keterangan").val(),
            detail: detailRows
        };

        let btn = $(this);
        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: "/sdm/lembur/save",
            type: "POST",
            dataType: "json",
            data: payload,
            success: function (res) {
                btn.prop("disabled", false).html('<i class="fa fa-save me-1"></i> Simpan Data');

                if (res.status) {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil",
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "/sdm/lembur/form/" + res.id;
                    });
                } else {
                    Swal.fire({ icon: "error", title: "Gagal", text: res.message });
                }
            },
            error: function () {
                btn.prop("disabled", false).html('<i class="fa fa-save me-1"></i> Simpan Data');
                Swal.fire({ icon: "error", title: "Error", text: "Terjadi kesalahan pada server saat menyimpan data!" });
            }
        });
    });

    // =========================================================================
    // 5. APPROVAL WORKFLOW
    // =========================================================================
    // Approve 1: Persetujuan Penugasan Karyawan
    $("#btn-approve-1").on("click", function () {
        let id = $("#id_lembur").val();
        if (!id) return;

        Swal.fire({
            title: "Approve 1 (Persetujuan Penugasan)?",
            text: "Menyetujui daftar pemilihan karyawan dan target pekerjaan yang ditugaskan untuk lembur.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#007bff",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Setujui Penugasan Karyawan!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("/sdm/lembur/approve1", { id: id }, function (res) {
                    if (res.status) {
                        Swal.fire({ icon: "success", title: "Disetujui!", text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: "error", title: "Gagal", text: res.message });
                    }
                }, "json");
            }
        });
    });

    // Approve 2: Persetujuan Jam Lembur & Sync ke Absensi
    $("#btn-approve-2").on("click", function () {
        let id = $("#id_lembur").val();
        if (!id) return;

        Swal.fire({
            title: "Approve 2 (Persetujuan Jam Lembur)?",
            text: "Perhitungan Jam Lembur Efektif akan difinalkan dan sistem akan OTOMATIS meng-update data 'Jam Lembur' di menu Absensi Karyawan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Setujui & Sinkronkan Absensi!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("/sdm/lembur/approve2", { id: id }, function (res) {
                    if (res.status) {
                        Swal.fire({ icon: "success", title: "Lembur Berhasil Disahkan!", text: res.message, timer: 2000, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: "error", title: "Gagal", text: res.message });
                    }
                }, "json");
            }
        });
    });

    // Batal Approve
    $("#btn-cancel-approve").on("click", function () {
        let id = $("#id_lembur").val();
        if (!id) return;

        Swal.fire({
            title: "Batalkan Persetujuan?",
            text: "Status persetujuan akan diturunkan ke tahap sebelumnya.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ffc107",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Batalkan Persetujuan!",
            cancelButtonText: "Tutup"
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("/sdm/lembur/cancel-approve", { id: id }, function (res) {
                    if (res.status) {
                        Swal.fire({ icon: "success", title: "Berhasil", text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: "error", title: "Gagal", text: res.message });
                    }
                }, "json");
            }
        });
    });
});
