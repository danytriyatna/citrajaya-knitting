<?php

namespace Modules\Referensi\Controllers;

use App\Models\FileModel;
use App\Controllers\BaseController;
use Modules\Referensi\Models\KaryawanModel;
use Modules\Referensi\Models\OperatorModel;
use Modules\Referensi\Models\PerusahaanModel;

class RefKaryawan extends BaseController
{
    protected $mkaryawan;
    protected $mOperator;
    protected $mPerusahaan;
    protected $mShift;

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/karyawan';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_KARYAWAN";
     
        $this->mkaryawan = new KaryawanModel();
        $this->mOperator = new OperatorModel();
        $this->mPerusahaan = new PerusahaanModel();
        $this->mShift = new \Modules\Referensi\Models\ShiftModel();
        $this->files  = new FileModel();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        $sortCMT = [
            [
                'field' => 'nama_operator',
                'dir' => 'ASC'
            ]
        ];
        $sortPerusahaan = [
            [
                'field' => 'nama_perusahaan',
                'dir' => 'ASC'
            ]
        ];
        $sortShift = [
            [
                'field' => 'id',
                'dir' => 'ASC'
            ]
        ];
        $dataCMT = $this->mOperator->getData(null, 0, 99999, $sortCMT);
        $dataShift = $this->mShift->getData(null, 0, 99999, $sortShift);
        $this->data['titlehead'] = "Master Data Karyawan";
        $this->data['cmt'] = $dataCMT;
        $this->data['shift'] = $dataShift;
        
        $this->data['superadmin'] = $this->auth->isSuperadmin();
        if (!$this->auth->isSuperadmin()) {
            if (empty($this->currentUser->id_perusahaan)) {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => 1]);
            }
            else {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => $this->currentUser->id_perusahaan]);
            }
            $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) || $this->currentUser->id_perusahaan != 1 ? $this->currentUser->id_perusahaan : 1;
        }
        else {
            $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
            $this->data['user_perusahaan'] = null;
        }
        return view($this->views.'\karyawan\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');
        $id_perusahaan = $this->request->getPost('id_perusahaan');
        $type = $this->request->getPost('type');

        $params = [
            'id_perusahaan' => $id_perusahaan,
            'type' => (!empty($type) && $type !== 'all') ? (int)$type : null,
            // TAMBAHAN (2026-08-08): list Master Data Karyawan sekarang
            // menampilkan karyawan aktif MAUPUN nonaktif (ada kolom status
            // di akhir tabel yang bisa diklik utk toggle) - modul lain yang
            // manggil KaryawanModel::getData() tanpa flag ini tetap cuma
            // dapat yang aktif seperti sebelumnya.
            'show_all_status' => true,
        ];

        if (!$this->auth->isSuperadmin()) {
            if (empty($this->currentUser->id_perusahaan)) {
                $params['id_perusahaan'] = 1;
            }
            else {
                $params['id_perusahaan'] = $this->currentUser->id_perusahaan;
            }
            $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) || $this->currentUser->id_perusahaan != 1 ? $this->currentUser->id_perusahaan : 1;
        }

        $results = $this->mkaryawan->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mkaryawan->getDataCnt($filters, $params);
        $totaldata = $this->mkaryawan->getDataCnt(null, $params);
        $maxpage = ceil($totalfiltered / $limit);

        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );

        foreach ($results as $row) {
            $id = encrypt($row->id);

            $atr_edit = null; $atr_del = null; $btnAction = null;
            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = $this->urlv . '/edit/';
                $atr_edit['class'] = '';
            }
            if ($this->_delete) {
                $atr_del['title'] = 'Hapus';
                $atr_del['url'] = $this->urlv . '/delete/';
                $atr_del['class'] = '';
                $atr_del['onclick'] = "return confirm('Hapus Data ?')";
            }
            if($atr_edit || $atr_del)
                $btnAction = btn_action_group($id, $atr_edit, $atr_del);

            // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
            //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

            array_push($build_array["data"],
                array(
                    "aksi" => $btnAction ? $btnAction : '',
                    "id"   => ($id),
                    "nip" => $row->nip,
                    "full_name" => $row->full_name,
                    "email" => $row->email,
                    "alamat" => $row->alamat,
                    "posisi" => $row->posisi,
                    "kategori_posisi" => (int)($row->kategori_posisi ?? 1),
                    "kategori_posisi_text" => ($row->kategori_posisi == 3) ? "Staff Khusus" : (($row->kategori_posisi == 2) ? "Non Mesin" : "Mesin"),
                    "id_shift" => (int)($row->id_shift ?? 1),
                    "nama_shift" => $row->nama_shift_karyawan ?? "NORMAL",
                    "tgl_bergabung" => !empty($row->tgl_bergabung) ? $row->tgl_bergabung : "",
                    "jenis_kelamin" => $row->jenis_kelamin,
                    "jenis_kelamint" => ($row->jenis_kelamin == 1) ? "Laki - Laki" : "Perempuan",
                    "tgl_lahir" => !empty($row->tgl_lahir) ? \fdate_eng_to_ind($row->tgl_lahir) : "",
                    "tgl_lahir_raw" => $row->tgl_lahir ?? "",
                    "tempat_lahir" => $row->tempat_lahir ?? "",
                    "no_hp" => $row->no_hp,
                    "no_rekening" => $row->no_rekening,
                    "nama_bank" => $row->nama_bank,
                    "upah_lembur" => $row->upah_lembur,
                    "upah_harian" => $row->upah_harian,
                    "upah_lembur_we" => $row->upah_lembur_we,
                    "upah_jam" => $row->upah_jam,
                    "premi_kehadiran" => $row->premi_kehadiran,
                    "type" => $row->type,
                    "id_operator" => $row->id_operator,
                    "id_perusahaan" => $row->id_perusahaan,
                    "nama_perusahaan" => $row->nama_perusahaan,
                    "nama_operator" => $row->nama_operator,
                    "file_gambar" => !empty($row->file_name) ? base_url() . "uploads/karyawan/"  . $row->file_name : "",
                    "tgl_mulai_kontrak" => !empty($row->tgl_mulai_kontrak) ? $row->tgl_mulai_kontrak : "",
                    "tgl_mulai_kontrak_formatted" => !empty($row->tgl_mulai_kontrak) ? \fdate_eng_to_ind($row->tgl_mulai_kontrak) : "",
                    "durasi_kontrak_bulan" => (int)($row->durasi_kontrak_bulan ?? 0),
                    "tgl_selesai_kontrak" => !empty($row->tgl_selesai_kontrak) ? $row->tgl_selesai_kontrak : "",
                    "tgl_selesai_kontrak_formatted" => !empty($row->tgl_selesai_kontrak) ? \fdate_eng_to_ind($row->tgl_selesai_kontrak) : "",
                    "status_kontrak" => (int)($row->status_kontrak ?? 0),
                    "file_kontrak_id" => $row->file_kontrak_id,
                    "file_kontrak_name" => $row->file_kontrak_name,
                    "file_kontrak_origin" => $row->file_kontrak_origin,
                    "file_kontrak_url" => !empty($row->file_kontrak_name) ? base_url() . "uploads/kontrak/"  . $row->file_kontrak_name : "",
                    "ttd_pihak1_file" => $row->ttd_pihak1_file ?? "",
                    "ttd_pihak1_url" => !empty($row->ttd_pihak1_file) ? base_url() . "uploads/kontrak/signatures/"  . $row->ttd_pihak1_file : "",
                    "ttd_pihak1_tgl" => !empty($row->ttd_pihak1_tgl) ? date('d/m/Y H:i', strtotime($row->ttd_pihak1_tgl)) : "",
                    "ttd_pihak2_file" => $row->ttd_pihak2_file ?? "",
                    "ttd_pihak2_url" => !empty($row->ttd_pihak2_file) ? base_url() . "uploads/kontrak/signatures/"  . $row->ttd_pihak2_file : "",
                    "ttd_pihak2_tgl" => !empty($row->ttd_pihak2_tgl) ? date('d/m/Y H:i', strtotime($row->ttd_pihak2_tgl)) : "",
                    "ttd_mengetahui_file" => $row->ttd_mengetahui_file ?? "",
                    "ttd_mengetahui_url" => !empty($row->ttd_mengetahui_file) ? base_url() . "uploads/kontrak/signatures/"  . $row->ttd_mengetahui_file : "",
                    "ttd_mengetahui_tgl" => !empty($row->ttd_mengetahui_tgl) ? date('d/m/Y H:i', strtotime($row->ttd_mengetahui_tgl)) : "",
                    "all_signed" => (!empty($row->ttd_pihak1_file) && !empty($row->ttd_pihak2_file) && !empty($row->ttd_mengetahui_file)),
                    "active" => (int) $row->active,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    function save(){
        $id           = $this->request->getPost('dataId');
        
        $nip =  $this->request->getPost('nip');
        $full_name =  $this->request->getPost('full_name');
        $email =  $this->request->getPost('email');
        $posisi =  $this->request->getPost('posisi');
        $kategori_posisi =  $this->request->getPost('kategori_posisi');
        $id_shift =  $this->request->getPost('id_shift');
        $tgl_bergabung =  $this->request->getPost('tgl_bergabung');
        $jenis_kelamin =  $this->request->getPost('jenis_kelamin');
        $jenis_kelamint =  $this->request->getPost('jenis_kelamint');
        $alamat =  $this->request->getPost('alamat');
        $tgl_lahir =  $this->request->getPost('tgl_lahir');
        $tempat_lahir =  $this->request->getPost('tempat_lahir');
        $no_hp =  $this->request->getPost('no_hp');
        $nama_bank =  $this->request->getPost('nama_bank');
        $no_rekening =  $this->request->getPost('no_rekening');
        $upah_lembur =  $this->request->getPost('upah_lembur');
        $upah_harian =  $this->request->getPost('upah_harian');
        $upah_lembur_we =  $this->request->getPost('upah_lembur_we');
        $upah_jam =  $this->request->getPost('upah_jam');
        $premi_kehadiran =  $this->request->getPost('premi_kehadiran');
        $type =  $this->request->getPost('type');
        $id_operator =  $this->request->getPost('id_operator');
        $id_perusahaan =  $this->request->getPost('id_perusahaan');
        $fileIdKaryawanOld = $this->request->getPost('fileIdKaryawanOld');
        if ($id_operator == "" || $id_operator=='null') {
            $id_operator = null;
        }
        $msg    = "Data gagal ditambahkan !";
        $status = false;

        if (!empty($this->request->getFile('fileKaryawan'))) {
            $fileKaryawan       = $this->request->getFile('fileKaryawan');
            $fileName       = $fileKaryawan->getRandomName();
            $originName     = $fileKaryawan->getName();
            $fileType       = $fileKaryawan->getMimeType();
            $fileSize       = $fileKaryawan->getSize();


            if (!in_array($fileType, ['image/png', 'image/jpeg', 'image/webp'])) {
                $build_array['message'] = "<br>File <b>Karyawan</b> hanya menerima tipe file <b>*.webp</b> <b>*.jpg</b>, atau <b>*.png</b>";
                $build_array['status']  = false;
                return $this->response->setJSON($build_array);
            }

            // $path = FCPATH . 'uploads/karyawan/';

            // // Cek apakah folder sudah ada
            // if (!is_dir($path)) {
            //     mkdir($path, 0777, true); // true = recursive (buat parent folder juga kalau belum ada)
            // }

            $this->files->insert([
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'file_name_origin' => $originName,
                'active'    => 1,
            ]);

            $fileIdKaryawan = $this->files->insertID();
            $fileKaryawan->move(WRITEPATH . 'uploads/karyawan/', $fileName);

            if ($fileIdKaryawanOld != "") {
                // $nama_file =  $this->files->where('id', $fileIdKaryawanOld)->get()->getRow()->file_name;
                // unlink(WRITEPATH . 'uploads/karyawan/' . $nama_file);

                // $this->files->delete(['id' => $fileIdKaryawanOld]);
            }
        } else {
            $fileIdKaryawan = $fileIdKaryawanOld;
        }

        // $tgl_lahir = \fdate_eng_to_ind_3($tgl_lahir);
        // $tgl_bergabung = \fdate_ind_to_eng($tgl_bergabung);
        // dd($tgl_bergabung);
        $arr_isi = [
            'nip' => $nip,
            'full_name' => $full_name,
            'email' => $email,
            'posisi' => $posisi,
            'kategori_posisi' => !empty($kategori_posisi) ? (int)$kategori_posisi : 1,
            'id_shift' => !empty($id_shift) ? (int)$id_shift : 1,
            'tgl_bergabung' => $tgl_bergabung,
            'jenis_kelamin' => $jenis_kelamin,
            'alamat' => $alamat,
            'tgl_lahir' => !empty($tgl_lahir) ? $tgl_lahir : null,
            'tempat_lahir' => !empty($tempat_lahir) ? $tempat_lahir : null,
            'no_hp' => $no_hp,
            'nama_bank' => $nama_bank,
            'no_rekening' => $no_rekening,
            'upah_lembur' => $upah_lembur,
            'upah_harian' => $upah_harian,
            'upah_lembur_we' => $upah_lembur_we,
            'upah_jam' => $upah_jam,
            'premi_kehadiran' => $premi_kehadiran,
            'type' => $type,
            'id_operator' => $id_operator,
            'id_perusahaan' => $id_perusahaan,
            'gambar_id' => !empty($fileIdKaryawan) ? $fileIdKaryawan : null
        ];

        $tgl_mulai_kontrak = $this->request->getPost('tgl_mulai_kontrak');
        $durasi_kontrak_bulan = $this->request->getPost('durasi_kontrak_bulan');
        if (!empty($tgl_mulai_kontrak) && !empty($durasi_kontrak_bulan)) {
            $arr_isi['tgl_mulai_kontrak'] = $tgl_mulai_kontrak;
            $arr_isi['durasi_kontrak_bulan'] = (int)$durasi_kontrak_bulan;
            $arr_isi['tgl_selesai_kontrak'] = date('Y-m-d', strtotime("+" . (int)$durasi_kontrak_bulan . " month -1 day", strtotime($tgl_mulai_kontrak)));
        }

        
        if(empty($id)){
            $id = $this->mkaryawan->insertRecordGetid($this->mkaryawan->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        }else{
            $id = decrypt($id);
            $this->mkaryawan->updateRecord($this->mkaryawan->table, $arr_isi, 'id', $id);
            $msg    = "Data berhasil diupdate !";
            $status = true;
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;

        return $this->response->setJSON($build_array);
    }

    public function activate($id)
    {
        // FIX (2026-08-08): sebelumnya cuma Admin/Superadmin yang bisa
        // mengaktifkan karyawan, padahal tombol toggle status di list
        // ditampilkan ke semua role yang punya izin edit ATAU delete modul
        // ini ($this->_edit / $this->_delete, dari CRUD permission per-role)
        // - role lain yang berhak tapi bukan Admin/Superadmin sebelumnya
        // gagal dgn error "You must be an administrator...".
        if (!$this->auth->loggedIn() || (!$this->_edit && !$this->_delete)) {
			throw new \Exception('Anda tidak memiliki izin untuk mengubah data ini.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        // FIX (2026-08-08): KaryawanModel tidak punya method activate() -
        // method ini sebelumnya tidak pernah benar-benar terpanggil karena
        // route-nya belum terdaftar (baru ditambahkan hari ini), jadi bug
        // ini belum pernah ketahuan. Pakai updateRecord() langsung, sama
        // seperti pola yang sudah dipakai di deactivate().
        $activation = $this->mkaryawan->updateRecord($this->mkaryawan->table, ['active' => 1], 'id', $id);
        if ($activation) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"User Diaktifkan");        
            $this->session->setFlashdata('message', "User berhasil di aktifkan");
        } else {
            $this->session->setFlashdata('err', "User gagal di aktifkan !");
        }
		return redirect()->to($this->urlv);

    }

    public function deactivate($id = NULL)
    {
        // FIX (2026-08-08): sama seperti activate() - cek izin edit ATAU
        // delete, bukan hardcode Admin/Superadmin. (Catatan: tombol Hapus
        // di list sekarang mengarah ke delete() yang terpisah, bukan ke
        // sini lagi - lihat method delete() di bawah.)
        if (!$this->auth->loggedIn() || (!$this->_edit && !$this->_delete)) {
			throw new \Exception('Anda tidak memiliki izin untuk mengubah data ini.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
		    return redirect()->to($this->urlv);
        }
        $data = ['active' => 0];
        $deactivate = $this->mkaryawan->updateRecord($this->mkaryawan->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Data Konsumen Dinonaktifkan");        
            $this->session->setFlashdata('message', "Data Konsumen berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Konsumen gagal di Hapus !");
        }
		return redirect()->to($this->urlv);
    }

    public function delete($id = NULL)
    {
        // FIX (2026-08-08): dulu cuma Admin/Superadmin yang bisa hapus,
        // padahal tombol Hapus di list ditampilkan berdasarkan izin delete
        // per-role ($this->_delete, dari CRUD permission) - role lain yang
        // sudah diberi izin delete tetap gagal krn hardcode ini. Sekarang
        // pakai $this->_delete supaya konsisten dgn tombol yang ditampilkan.
        if (!$this->auth->loggedIn() || !$this->_delete) {
			throw new \Exception('Anda tidak memiliki izin untuk menghapus data ini.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
		    return redirect()->to($this->urlv);
        }

        // FIX (2026-08-08): dulu manggil deleteUser() yang tidak pernah ada
        // di KaryawanModel (baru ketahuan skrg karena route ini sebelumnya
        // ikut dialihkan ke deactivate()). Sekarang delete = status BARU
        // (active=2, "Dihapus"), beda dari Nonaktif (active=0) - datanya
        // tetap ada di database tapi tidak akan pernah tampil lagi di list
        // karyawan manapun (lihat KaryawanModel::getData()/getDataCnt()).
        $data = ['active' => 2];
        $res = $this->mkaryawan->updateRecord($this->mkaryawan->table, $data, 'id', $id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Master Karyawan Dihapus");
            $this->session->setFlashdata('message', "Data karyawan berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Data karyawan gagal dihapus");
        }
		return redirect()->to($this->urlv);
    }

    public function checkKontrak()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        }

        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID karyawan tidak valid.']);
        }

        $rawId = decrypt($id);
        if (empty($rawId)) {
            $rawId = (int)$id;
        }

        $row = $this->mkaryawan->getData($rawId);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data karyawan tidak ditemukan.']);
        }

        $missing = [];
        if (empty(trim($row->full_name ?? ''))) $missing[] = 'Nama Lengkap';
        if (empty(trim($row->tempat_lahir ?? ''))) $missing[] = 'Tempat Lahir';
        if (empty($row->tgl_lahir) || $row->tgl_lahir == '0000-00-00') $missing[] = 'Tanggal Lahir';
        if (empty($row->jenis_kelamin)) $missing[] = 'Jenis Kelamin';
        if (empty(trim($row->alamat ?? ''))) $missing[] = 'Alamat Lengkap';
        if (empty(trim($row->posisi ?? ''))) $missing[] = 'Posisi / Bagian';
        if (empty($row->upah_harian) || (float)$row->upah_harian <= 0) $missing[] = 'Upah Harian';

        $isComplete = empty($missing);

        $tglMulai = !empty($row->tgl_mulai_kontrak) ? $row->tgl_mulai_kontrak : date('Y-m-d');
        $durasi = !empty($row->durasi_kontrak_bulan) ? (int)$row->durasi_kontrak_bulan : 3;
        $tglSelesai = !empty($row->tgl_selesai_kontrak) ? $row->tgl_selesai_kontrak : '';
        if (empty($tglSelesai) && !empty($tglMulai)) {
            $startObj = new \DateTime($tglMulai);
            $endObj = (clone $startObj)->modify("+{$durasi} months")->modify('-1 day');
            $tglSelesai = $endObj->format('Y-m-d');
        }

        return $this->response->setJSON([
            'status' => true,
            'is_complete' => $isComplete,
            'missing_fields' => $missing,
            'message' => $isComplete ? 'Data profil lengkap.' : 'Profil belum lengkap: ' . implode(', ', $missing) . '. Wajib dilengkapi sebelum generate kontrak!',
            'data' => [
                'id' => encrypt($row->id),
                'raw_id' => $row->id,
                'nip' => $row->nip,
                'full_name' => $row->full_name,
                'tempat_lahir' => $row->tempat_lahir ?? '',
                'tgl_lahir' => $row->tgl_lahir ?? '',
                'tgl_lahir_ind' => !empty($row->tgl_lahir) ? \fdate_eng_to_ind($row->tgl_lahir) : '-',
                'jenis_kelamin' => $row->jenis_kelamin,
                'jenis_kelamin_text' => ($row->jenis_kelamin == 1 ? 'Laki-Laki' : 'Perempuan'),
                'alamat' => $row->alamat ?? '',
                'posisi' => $row->posisi ?? '',
                'upah_harian' => (float)($row->upah_harian ?? 0),
                'upah_harian_formatted' => number_format((float)($row->upah_harian ?? 0), 0, ',', '.'),
                'tgl_mulai_kontrak' => $tglMulai,
                'durasi_kontrak_bulan' => $durasi,
                'tgl_selesai_kontrak' => $tglSelesai,
                'tgl_selesai_kontrak_formatted' => !empty($tglSelesai) ? \fdate_eng_to_ind($tglSelesai) : '-',
                'status_kontrak' => (int)($row->status_kontrak ?? 0),
                'file_kontrak_id' => $row->file_kontrak_id,
                'file_kontrak_name' => $row->file_kontrak_name,
                'file_kontrak_origin' => $row->file_kontrak_origin,
                'file_kontrak_url' => !empty($row->file_kontrak_name) ? base_url() . "uploads/kontrak/" . $row->file_kontrak_name : "",
                'ttd_pihak1_file' => $row->ttd_pihak1_file ?? null,
                'ttd_pihak1_url' => !empty($row->ttd_pihak1_file) ? base_url() . "uploads/kontrak/signatures/" . $row->ttd_pihak1_file : "",
                'ttd_pihak1_tgl' => !empty($row->ttd_pihak1_tgl) ? date('d/m/Y H:i', strtotime($row->ttd_pihak1_tgl)) : "",
                'ttd_pihak2_file' => $row->ttd_pihak2_file ?? null,
                'ttd_pihak2_url' => !empty($row->ttd_pihak2_file) ? base_url() . "uploads/kontrak/signatures/" . $row->ttd_pihak2_file : "",
                'ttd_pihak2_tgl' => !empty($row->ttd_pihak2_tgl) ? date('d/m/Y H:i', strtotime($row->ttd_pihak2_tgl)) : "",
                'ttd_mengetahui_file' => $row->ttd_mengetahui_file ?? null,
                'ttd_mengetahui_url' => !empty($row->ttd_mengetahui_file) ? base_url() . "uploads/kontrak/signatures/" . $row->ttd_mengetahui_file : "",
                'ttd_mengetahui_tgl' => !empty($row->ttd_mengetahui_tgl) ? date('d/m/Y H:i', strtotime($row->ttd_mengetahui_tgl)) : "",
                'all_signed' => (!empty($row->ttd_pihak1_file) && !empty($row->ttd_pihak2_file) && !empty($row->ttd_mengetahui_file)),
            ]
        ]);
    }

    public function generateKontrak()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        }

        $id = $this->request->getPost('id');
        $tgl_mulai = $this->request->getPost('tgl_mulai_kontrak');
        $durasi = (int)$this->request->getPost('durasi_kontrak_bulan');

        if (empty($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID karyawan tidak valid.']);
        }
        if (empty($tgl_mulai)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tanggal mulai kontrak wajib diisi.']);
        }
        if ($durasi <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Durasi kontrak bulan harus lebih dari 0.']);
        }

        $rawId = decrypt($id);
        if (empty($rawId)) {
            $rawId = (int)$id;
        }

        $row = $this->mkaryawan->getData($rawId);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data karyawan tidak ditemukan.']);
        }

        // Mandatory check
        $missing = [];
        if (empty(trim($row->full_name ?? ''))) $missing[] = 'Nama Lengkap';
        if (empty(trim($row->tempat_lahir ?? ''))) $missing[] = 'Tempat Lahir';
        if (empty($row->tgl_lahir) || $row->tgl_lahir == '0000-00-00') $missing[] = 'Tanggal Lahir';
        if (empty($row->jenis_kelamin)) $missing[] = 'Jenis Kelamin';
        if (empty(trim($row->alamat ?? ''))) $missing[] = 'Alamat Lengkap';
        if (empty(trim($row->posisi ?? ''))) $missing[] = 'Posisi / Bagian';
        if (empty($row->upah_harian) || (float)$row->upah_harian <= 0) $missing[] = 'Upah Harian';

        if (!empty($missing)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data karyawan belum lengkap: ' . implode(', ', $missing) . '. Wajib dilengkapi sebelum generate kontrak!'
            ]);
        }

        $startDate = new \DateTime($tgl_mulai);
        $endDate = (clone $startDate)->modify("+{$durasi} months")->modify('-1 day');
        $tgl_selesai = $endDate->format('Y-m-d');

        $updateData = [
            'tgl_mulai_kontrak' => $tgl_mulai,
            'durasi_kontrak_bulan' => $durasi,
            'tgl_selesai_kontrak' => $tgl_selesai,
        ];
        if (empty($row->status_kontrak) || $row->status_kontrak == 0) {
            $updateData['status_kontrak'] = 1; // 1 = Draf Terbit
        }

        $this->mkaryawan->updateRecord($this->mkaryawan->table, $updateData, 'id', $rawId);

        $encId = encrypt($rawId);
        $downloadUrl = base_url() . 'master-data/karyawan/download-kontrak/' . $encId;

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Draf kontrak berhasil digenerate!',
            'tgl_selesai_kontrak' => $tgl_selesai,
            'tgl_selesai_kontrak_formatted' => \fdate_eng_to_ind($tgl_selesai),
            'status_kontrak' => $updateData['status_kontrak'] ?? $row->status_kontrak,
            'download_url' => $downloadUrl
        ]);
    }

    public function downloadKontrak($encId)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $rawId = decrypt($encId);
        if (empty($rawId)) {
            $rawId = (int)$encId;
        }

        $row = $this->mkaryawan->getData($rawId);
        if (!$row) {
            throw new \Exception('Data karyawan tidak ditemukan.');
        }

        $templatePath = WRITEPATH . 'templates/TEMPLATE_KONTRAK_KERJA.docx';
        if (!file_exists($templatePath)) {
            throw new \Exception('Template kontrak kerja tidak ditemukan di server.');
        }

        $tgl_mulai = !empty($row->tgl_mulai_kontrak) ? $row->tgl_mulai_kontrak : date('Y-m-d');
        $durasi = !empty($row->durasi_kontrak_bulan) ? (int)$row->durasi_kontrak_bulan : 3;
        if (!empty($row->tgl_selesai_kontrak)) {
            $tgl_selesai = $row->tgl_selesai_kontrak;
        } else {
            $startDate = new \DateTime($tgl_mulai);
            $endDate = (clone $startDate)->modify("+{$durasi} months")->modify('-1 day');
            $tgl_selesai = $endDate->format('Y-m-d');
        }

        $tempDir = WRITEPATH . 'cache/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $cleanNip = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row->nip ?? 'KRY');
        $tempFile = $tempDir . 'KONTRAK_' . $cleanNip . '_' . uniqid() . '.docx';
        copy($templatePath, $tempFile);

        $zip = new \ZipArchive();
        if ($zip->open($tempFile) === TRUE) {
            $xml = $zip->getFromName('word/document.xml');

            $namaKaryawan = htmlspecialchars($row->full_name ?? '', ENT_XML1, 'UTF-8');
            $ttl = htmlspecialchars(($row->tempat_lahir ?? '-') . ', ' . $this->formatTglIndoLengkap($row->tgl_lahir), ENT_XML1, 'UTF-8');
            $jenisKelamin = ($row->jenis_kelamin == 1) ? 'Laki-Laki' : 'Perempuan';
            $alamat = htmlspecialchars($row->alamat ?? '-', ENT_XML1, 'UTF-8');
            $durasiBulan = (string)$durasi;
            $durasiTerbilang = $this->terbilangAngka($durasi);
            $tglMulaiIndo = $this->formatTglIndoLengkap($tgl_mulai);
            $tglSelesaiIndo = $this->formatTglIndoLengkap($tgl_selesai);
            $posisiBagian = htmlspecialchars($row->posisi ?? '-', ENT_XML1, 'UTF-8');
            $upahHarian = number_format((float)($row->upah_harian ?? 0), 0, ',', '.');
            $tglTtd = $this->formatTglIndoLengkap(date('Y-m-d'));

            $replacements = [
                '${NAMA}' => $namaKaryawan,
                '${TEMPAT_TGL_LAHIR}' => $ttl,
                '${JENIS_KELAMIN}' => $jenisKelamin,
                '${ALAMAT}' => $alamat,
                '${DURASI_BULAN}' => $durasiBulan,
                '${DURASI_TERBILANG}' => $durasiTerbilang,
                '${TGL_MULAI_KONTRAK}' => $tglMulaiIndo,
                '${TGL_SELESAI_KONTRAK}' => $tglSelesaiIndo,
                '${POSISI_BAGIAN}' => $posisiBagian,
                '${UPAH_HARIAN}' => $upahHarian,
                '${TANGGAL_TTD}' => $tglTtd,
                '${NAMA_TTD}' => $namaKaryawan,
            ];

            foreach ($replacements as $key => $val) {
                $xml = str_replace($key, $val, $xml);
            }

            $this->injectSignaturesToDocx($zip, $xml, $row);

            $zip->addFromString('word/document.xml', $xml);
            $zip->close();

            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row->full_name ?? 'Karyawan');
            $downloadName = 'KONTRAK_KERJA_' . $cleanNip . '_' . $cleanName . '.docx';

            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempFile));
            readfile($tempFile);
            @unlink($tempFile);
            exit;
        } else {
            throw new \Exception('Gagal memproses berkas dokumen kontrak.');
        }
    }

    public function uploadKontrak()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        }

        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID karyawan tidak valid.']);
        }

        $rawId = decrypt($id);
        if (empty($rawId)) {
            $rawId = (int)$id;
        }

        $fileKontrak = $this->request->getFile('file_kontrak');
        if (!$fileKontrak || !$fileKontrak->isValid()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Berkas kontrak belum dipilih atau tidak valid.']);
        }

        $fileType = $fileKontrak->getMimeType();
        $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/webp'];
        if (!in_array($fileType, $allowedTypes)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Berkas kontrak hanya menerima format PDF, JPG, PNG, atau WEBP.'
            ]);
        }

        $targetFolder = WRITEPATH . 'uploads/kontrak/';
        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }

        $fileName   = $fileKontrak->getRandomName();
        $originName = $fileKontrak->getName();
        $fileSize   = $fileKontrak->getSize();

        $this->files->insert([
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'file_name_origin' => $originName,
            'active'    => 1,
        ]);

        $fileId = $this->files->insertID();
        $fileKontrak->move($targetFolder, $fileName);

        // Update ref_karyawan
        $this->mkaryawan->updateRecord($this->mkaryawan->table, [
            'file_kontrak_id' => $fileId,
            'status_kontrak' => 2, // 2 = Signed Uploaded
        ], 'id', $rawId);

        $fileUrl = base_url() . "uploads/kontrak/" . $fileName;

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Berkas kontrak bertandatangan berhasil diunggah!',
            'file_id' => $fileId,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_name_origin' => $originName,
            'status_kontrak' => 2
        ]);
    }

    private function formatTglIndoLengkap($tanggal)
    {
        if (empty($tanggal) || $tanggal == '0000-00-00') return '-';
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $time = strtotime($tanggal);
        $d = date('j', $time);
        $m = $bulan[(int)date('n', $time)] ?? '';
        $y = date('Y', $time);
        return "$d $m $y";
    }

    private function terbilangAngka($angka)
    {
        $angka = (int)$angka;
        $bilang = [
            0 => "Nol", 1 => "Satu", 2 => "Dua", 3 => "Tiga", 4 => "Empat",
            5 => "Lima", 6 => "Enam", 7 => "Tujuh", 8 => "Delapan",
            9 => "Sembilan", 10 => "Sepuluh", 11 => "Sebelas", 12 => "Dua Belas"
        ];
        return $bilang[$angka] ?? (string)$angka;
    }

    public function simpanTtdDigital()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        }

        $id = $this->request->getPost('id');
        $pihak = (int)$this->request->getPost('pihak'); // 1 = Pihak Pertama, 2 = Pihak Kedua, 3 = Mengetahui
        $sigData = $this->request->getPost('signature_data');

        if (empty($id) || empty($pihak) || empty($sigData)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter tanda tangan tidak lengkap.']);
        }

        $rawId = decrypt($id);
        if (empty($rawId)) {
            $rawId = (int)$id;
        }

        $row = $this->mkaryawan->getData($rawId);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data karyawan tidak ditemukan.']);
        }

        // Decode base64 PNG
        if (preg_match('/^data:image\/(\w+);base64,/', $sigData, $type)) {
            $sigData = substr($sigData, strpos($sigData, ',') + 1);
            $decoded = base64_decode($sigData);
            if ($decoded === false) {
                return $this->response->setJSON(['status' => false, 'message' => 'Format gambar tanda tangan tidak valid.']);
            }
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Format base64 tanda tangan tidak dikenali.']);
        }

        $sigDir = FCPATH . 'uploads/kontrak/signatures/';
        if (!is_dir($sigDir)) {
            mkdir($sigDir, 0777, true);
        }

        $cleanNip = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row->nip ?? 'KRY');
        $fileName = 'ttd_' . $pihak . '_' . $cleanNip . '_' . time() . '.png';
        $targetFile = $sigDir . $fileName;

        file_put_contents($targetFile, $decoded);

        $now = date('Y-m-d H:i:s');
        $update = [];
        if ($pihak === 1) {
            $update['ttd_pihak1_file'] = $fileName;
            $update['ttd_pihak1_tgl'] = $now;
        } else if ($pihak === 2) {
            $update['ttd_pihak2_file'] = $fileName;
            $update['ttd_pihak2_tgl'] = $now;
        } else if ($pihak === 3) {
            $update['ttd_mengetahui_file'] = $fileName;
            $update['ttd_mengetahui_tgl'] = $now;
        }

        // Cek kelengkapan tanda tangan ketiga pihak
        $p1 = ($pihak === 1) ? $fileName : ($row->ttd_pihak1_file ?? null);
        $p2 = ($pihak === 2) ? $fileName : ($row->ttd_pihak2_file ?? null);
        $p3 = ($pihak === 3) ? $fileName : ($row->ttd_mengetahui_file ?? null);

        $allSigned = (!empty($p1) && !empty($p2) && !empty($p3));
        if ($allSigned) {
            $update['status_kontrak'] = 2; // Signed
        } else if (empty($row->status_kontrak) || $row->status_kontrak == 0) {
            $update['status_kontrak'] = 1; // Minimal draf terbit
        }

        $this->mkaryawan->updateRecord($this->mkaryawan->table, $update, 'id', $rawId);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Tanda tangan digital berhasil disimpan!',
            'file_name' => $fileName,
            'file_url' => base_url() . 'uploads/kontrak/signatures/' . $fileName,
            'tgl_formatted' => date('d/m/Y H:i', strtotime($now)),
            'all_signed' => $allSigned,
            'status_kontrak' => $update['status_kontrak'] ?? $row->status_kontrak
        ]);
    }

    public function hapusTtdDigital()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        }

        $id = $this->request->getPost('id');
        $pihak = (int)$this->request->getPost('pihak');

        if (empty($id) || empty($pihak)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter tidak valid.']);
        }

        $rawId = decrypt($id);
        if (empty($rawId)) {
            $rawId = (int)$id;
        }

        $row = $this->mkaryawan->getData($rawId);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data karyawan tidak ditemukan.']);
        }

        $update = [];
        if ($pihak === 1) {
            $update['ttd_pihak1_file'] = null;
            $update['ttd_pihak1_tgl'] = null;
        } else if ($pihak === 2) {
            $update['ttd_pihak2_file'] = null;
            $update['ttd_pihak2_tgl'] = null;
        } else if ($pihak === 3) {
            $update['ttd_mengetahui_file'] = null;
            $update['ttd_mengetahui_tgl'] = null;
        }

        if (($row->status_kontrak ?? 0) == 2 && empty($row->file_kontrak_id)) {
            $update['status_kontrak'] = 1;
        }

        $this->mkaryawan->updateRecord($this->mkaryawan->table, $update, 'id', $rawId);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Tanda tangan digital berhasil direset.',
            'status_kontrak' => $update['status_kontrak'] ?? $row->status_kontrak
        ]);
    }

    private function injectSignaturesToDocx(&$zip, &$xml, $row)
    {
        $sig1Path = !empty($row->ttd_pihak1_file) ? FCPATH . 'uploads/kontrak/signatures/' . $row->ttd_pihak1_file : null;
        $sig2Path = !empty($row->ttd_pihak2_file) ? FCPATH . 'uploads/kontrak/signatures/' . $row->ttd_pihak2_file : null;
        $sig3Path = !empty($row->ttd_mengetahui_file) ? FCPATH . 'uploads/kontrak/signatures/' . $row->ttd_mengetahui_file : null;

        $has1 = ($sig1Path && file_exists($sig1Path));
        $has2 = ($sig2Path && file_exists($sig2Path));
        $has3 = ($sig3Path && file_exists($sig3Path));

        if (!$has1 && !$has2 && !$has3) {
            return;
        }

        $rels = $zip->getFromName('word/_rels/document.xml.rels');
        $newRels = '';

        $drawSig1 = '';
        $drawSig2 = '';
        $drawSig3 = '';

        if ($has1) {
            $zip->addFile($sig1Path, 'word/media/ttd_pihak1.png');
            $newRels .= '<Relationship Id="rIdTtd1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/ttd_pihak1.png"/>';
            $drawSig1 = $this->buildDrawingXml('rIdTtd1', 9991, 'TTD_Pihak_1');
        }

        if ($has2) {
            $zip->addFile($sig2Path, 'word/media/ttd_pihak2.png');
            $newRels .= '<Relationship Id="rIdTtd2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/ttd_pihak2.png"/>';
            $drawSig2 = $this->buildDrawingXml('rIdTtd2', 9992, 'TTD_Pihak_2');
        }

        if ($has3) {
            $zip->addFile($sig3Path, 'word/media/ttd_mengetahui.png');
            $newRels .= '<Relationship Id="rIdTtd3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/ttd_mengetahui.png"/>';
            $drawSig3 = $this->buildDrawingXml('rIdTtd3', 9993, 'TTD_Mengetahui');
        }

        if (!empty($newRels)) {
            $rels = str_replace('</Relationships>', $newRels . '</Relationships>', $rels);
            $zip->addFromString('word/_rels/document.xml.rels', $rels);
        }

        // Sematkan ke document.xml
        // 1. Baris Pihak Pertama & Kedua:
        if ($has1 || $has2) {
            $replacementRow1 = '<w:p w14:paraId="00101CFB" w14:textId="221C93B7" w:rsidR="00BD78BE" w:rsidRPr="0045474D" w:rsidRDefault="00D17176" w:rsidP="00D17176"><w:pPr><w:tabs><w:tab w:val="left" w:pos="6643"/></w:tabs><w:spacing w:line="192" w:lineRule="auto"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="20"/><w:szCs w:val="20"/><w:lang w:val="sv-SE"/></w:rPr></w:pPr>'
                . (!empty($drawSig1) ? $drawSig1 : '<w:r><w:t xml:space="preserve">                </w:t></w:r>')
                . '<w:r><w:tab/><w:tab/><w:tab/><w:tab/></w:r>'
                . (!empty($drawSig2) ? $drawSig2 : '')
                . '</w:p>';
            $xml = preg_replace('/<w:p w14:paraId="00101CFB".*?<\/w:p>/s', $replacementRow1, $xml);
        }

        // 2. Baris Mengetahui:
        if ($has3) {
            $replacementRow2 = '<w:p w14:paraId="658818E4" w14:textId="77777777" w:rsidR="00BD78BE" w:rsidRPr="0045474D" w:rsidRDefault="00BD78BE" w:rsidP="00BD78BE"><w:pPr><w:spacing w:line="192" w:lineRule="auto"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="20"/><w:szCs w:val="20"/><w:lang w:val="sv-SE"/></w:rPr></w:pPr>'
                . '<w:r><w:tab/><w:tab/><w:tab/><w:tab/><w:tab/><w:tab/><w:tab/></w:r>'
                . $drawSig3
                . '</w:p>';
            $xml = preg_replace('/<w:p w14:paraId="658818E4".*?<\/w:p>/s', $replacementRow2, $xml);
        }
    }

    private function buildDrawingXml($rId, $docPrId, $name, $cx = 1371600, $cy = 685800)
    {
        return '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">'
            . '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/>'
            . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
            . '<wp:docPr id="' . $docPrId . '" name="' . $name . '"/>'
            . '<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:nvPicPr><pic:cNvPr id="0" name="' . $name . '.png"/><pic:cNvPicPr/></pic:nvPicPr>'
            . '<pic:blipFill><a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            . '</pic:pic>'
            . '</a:graphicData></a:graphic>'
            . '</wp:inline></w:drawing></w:r>';
    }
}
