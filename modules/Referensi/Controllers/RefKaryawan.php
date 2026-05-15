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

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/karyawan';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_KARYAWAN";
     
        $this->mkaryawan = new KaryawanModel();
        $this->mOperator = new OperatorModel();
        $this->mPerusahaan = new PerusahaanModel();
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
        $dataCMT = $this->mOperator->getData(null, 0, 99999, $sortCMT);
        $this->data['titlehead'] = "Master Data Karyawan";
        $this->data['cmt'] = $dataCMT;
        $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);

        return view($this->views.'\karyawan\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');
        $id_perusahaan = $this->request->getPost('id_perusahaan');

        $params = [
            'id_perusahaan' => $id_perusahaan
        ];

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
                    "tgl_bergabung" => !empty($row->tgl_bergabung) ? $row->tgl_bergabung : "",
                    "jenis_kelamin" => $row->jenis_kelamin,
                    "jenis_kelamint" => ($row->jenis_kelamin == 1) ? "Laki - Laki" : "Perempuan",
                    "tgl_lahir" => !empty($row->tgl_lahir) ? \fdate_eng_to_ind($row->tgl_lahir) : "",
                    "tempat_lahir" => $row->tempat_lahir,
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
                    "file_gambar" => !empty($row->file_name) ? base_url() . "uploads/karyawan/"  . $row->file_name : "",
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
            'tgl_bergabung' => $tgl_bergabung,
            'jenis_kelamin' => $jenis_kelamin,
            'alamat' => $alamat,
            // 'tgl_lahir' => $tgl_lahir,
            // 'tempat_lahir' => $tempat_lahir,
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
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        $activation = $this->mkaryawan->activate($id);
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
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
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
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
		    return redirect()->to($this->urlv);
        }
        
        $res = $this->mkaryawan->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Master Konsumen Dihapus");        
            $this->session->setFlashdata('message', "Master Konsumen berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Konsumen gagal dihapus");
        }
		return redirect()->to($this->urlv);
    }
}
