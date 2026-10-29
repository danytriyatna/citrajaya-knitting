$(document).ready(function () {
    let slcBulan = $("#slc_bulan");
    let slcTahun = $("#tahun_trans");

    let dtTable = $("#dt-list-trans-masuk").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/keuangan/transaksi_masuk/list",
            type: "POST",
            data: function (d) {
                d.bulan = slcBulan.val();
                d.tahun = slcTahun.val();
            }
        },
        columns: [
            { data: "aksi", orderable: false, searchable: false },
            { data: "trans_akun_kode" },
            { data: "ref_rekening" },
            { data: "trans_akun_date" },
            { 
                data: "total", 
                render: function (data) {
                    return parseFloat(data || 0).toLocaleString('id-ID');
                },
                className: "text-end"
            },
            { data: "status", className: "text-center" },
            { data: "keterangan" }
        ],
        order: [[1, "desc"]]
    });

    slcBulan.on("change", function () {
        dtTable.ajax.reload();
    });

    slcTahun.on("change", function () {
        dtTable.ajax.reload();
    });

    $("#btn-export-excel").on("click", function () {
        let b = slcBulan.val();
        let t = slcTahun.val();
        window.location.href = `/keuangan/transaksi_masuk/exp_mutasi/${b}/${t}`;
    });
});