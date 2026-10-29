<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use Modules\Referensi\Models\TargetProsesModel;

class RefTargetProses extends BaseController
{
    protected $mTargetProses;
    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/target-proses';

    public function __construct()
    {
        $this->MOD_ALIAS     = "MOD_REFERENSI_TARGET_PROSES";
        $this->mTargetProses = new TargetProsesModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Master Data Proses Target Harian";
        $this->data['name_app']  = "Citra Jaya Knitting";
        $this->data['urlv']      = $this->urlv;

        return view($this->views . '\target_proses\index', $this->data);
    }

    public function lists()
    {
        $start   = (int) $this->request->getPost('start');
        $limit   = (int) $this->request->getPost('length');
        $filters = $this->request->getPost('filter');
        $order   = $this->request->getPost('sort');

        $params = [];

        $results       = $this->mTargetProses->getData(null, $start, $limit > 0 ? $limit : 25, $order, $filters, $params);
        $totalfiltered = $this->mTargetProses->getDataCnt($filters, $params);
        $totaldata     = $this->mTargetProses->getDataCnt(null, $params);
        $maxpage       = $limit > 0 ? ceil($totalfiltered / $limit) : 1;

        $dataRows = [];
        foreach ($results as $row) {
            $idEnc = encrypt($row->id);
            $row->id_encrypted = $idEnc;
            $dataRows[] = $row;
        }

        return $this->response->setJSON([
            "last_page"       => $maxpage,
            "recordsTotal"    => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data"            => $dataRows
        ]);
    }

    public function save()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $idEnc         = $this->request->getPost('id');
        $id            = !empty($idEnc) ? decrypt($idEnc) : null;
        $kode          = strtoupper(trim($this->request->getPost('kode') ?? ''));
        $nama          = trim($this->request->getPost('nama') ?? '');
        $target_harian = (float)($this->request->getPost('target_harian') ?? 0);
        $satuan        = trim($this->request->getPost('satuan') ?? 'Pcs');
        $warna_badge   = trim($this->request->getPost('warna_badge') ?? '#007bff');
        $keterangan    = trim($this->request->getPost('keterangan') ?? '');
        $seq           = (int) $this->request->getPost('seq');

        if (empty($kode)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kode Singkatan wajib diisi!']);
        }
        if (empty($nama)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama Pekerjaan/Proses wajib diisi!']);
        }

        // Cek duplikasi kode
        $db = \Config\Database::connect();
        $builder = $db->table('ref_target_proses')->where('active', 1)->where('kode', $kode);
        if (!empty($id)) {
            $builder->where('id !=', $id);
        }
        $existing = $builder->countAllResults();
        if ($existing > 0) {
            return $this->response->setJSON(['status' => false, 'message' => "Kode Singkatan '{$kode}' sudah digunakan!"]);
        }

        $userId = session()->get('user_id') ?? session()->get('id') ?? 1;

        $saveData = [
            'kode'          => $kode,
            'nama'          => $nama,
            'target_harian' => $target_harian >= 0 ? $target_harian : 0,
            'satuan'        => !empty($satuan) ? $satuan : 'Pcs',
            'warna_badge'   => !empty($warna_badge) ? $warna_badge : '#007bff',
            'keterangan'    => $keterangan,
            'seq'           => $seq > 0 ? $seq : 1,
            'updated_at'    => date('Y-m-d H:i:s'),
            'updated_by'    => $userId
        ];

        try {
            if (!empty($id)) {
                $db->table('ref_target_proses')->where('id', $id)->update($saveData);
                $msg = "Data proses '{$nama}' berhasil diperbarui.";
            } else {
                $saveData['active']     = 1;
                $saveData['created_at'] = date('Y-m-d H:i:s');
                $saveData['created_by'] = $userId;
                $db->table('ref_target_proses')->insert($saveData);
                $msg = "Data proses '{$nama}' berhasil ditambahkan.";
            }

            return $this->response->setJSON([
                'status'  => true,
                'message' => $msg
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    public function deactivate($idEnc = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        if (empty($idEnc)) {
            return redirect()->to($this->urlv)->with('error', 'ID tidak valid!');
        }

        $id = decrypt($idEnc);
        $db = \Config\Database::connect();
        $db->table('ref_target_proses')->where('id', $id)->update([
            'active'     => 0,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session()->get('user_id') ?? 1
        ]);

        return redirect()->to($this->urlv)->with('message', 'Data proses berhasil dinonaktifkan.');
    }

    public function getActiveProsesList()
    {
        $list = $this->mTargetProses->getActiveProsesList();
        return $this->response->setJSON([
            'status' => true,
            'data'   => $list
        ]);
    }
}
