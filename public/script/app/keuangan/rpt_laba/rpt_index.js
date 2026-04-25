$(document).ready(function () {
    // Inisialisasi selector
    let slBulanFrom = $("#bulan_from");
    let slBulanTo   = $("#bulan_to");
    let slTahunFrom = $("select[name='slc_tahun']").first();
    let slTahunTo   = $("select[name='slc_tahun']").last();

    setTimeout(() => {

        $("#btn_excel").on("click", function () {
            // 1. Ambil Nilai
            let b_from = slBulanFrom.val();
            let t_from = slTahunFrom.val();
            let b_to   = slBulanTo.val();
            let t_to   = slTahunTo.val();

            // 2. Validasi: Semua field harus dipilih
            if (!b_from || !t_from || !b_to || !t_to || t_from == "0" || t_to == "0") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Silakan pilih semua filter Periode (Bulan dan Tahun) terlebih dahulu.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // 3. Validasi: Tanggal "To" tidak boleh lebih kecil dari "From"
            // Konversi ke format YYYYMM untuk perbandingan angka
            let periodStart = parseInt(t_from + b_from);
            let periodEnd   = parseInt(t_to + b_to);

            if (periodEnd < periodStart) {
                Swal.fire({
                    icon: 'error',
                    title: 'Periode Tidak Valid',
                    text: 'Periode "Sampai" tidak boleh lebih kecil dari periode "Dari"!',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // 4. Jika lolos validasi, eksekusi export
            Swal.fire({
                title: 'Sedang Memproses...',
                text: 'Mohon tunggu sebentar, file Excel sedang disiapkan.',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let url = "/keuangan/laporan_laba/getExcel/" + 
                      b_from + "/" + 
                      t_from + "/" + 
                      b_to + "/" + 
                      t_to;

            window.open(url, "_blank");
        });

    }, 1000);
});