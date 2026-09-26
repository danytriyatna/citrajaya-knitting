<?php

namespace Modules\Utility\Controllers;

use App\Controllers\BaseController;

class CompanyProfile extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->MOD_ALIAS = "MOD_COMPANY_PROFILE";
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Company Profile & Pejabat Penandatangan";

        // Ambil daftar perusahaan aktif
        $companies = $this->db->table('ref_perusahaan')
            ->where('active', 1)
            ->orderBy('id', 'ASC')
            ->get()->getResult();

        $selectedId = (int)($this->request->getGet('id') ?: ($companies[0]->id ?? 1));

        $company = $this->db->table('ref_perusahaan')
            ->where('id', $selectedId)
            ->get()->getRow();

        if (!$company && !empty($companies)) {
            $company = $companies[0];
            $selectedId = $company->id;
        }

        // Set defaults if empty
        if ($company) {
            $company->nama_resmi = $company->nama_resmi ?: ($company->id == 1 ? 'CV. Citra Jaya Knitting' : $company->nama_perusahaan);
            $company->pihak1_nama = $company->pihak1_nama ?: 'Eva Fiantina';
            $company->pihak1_jabatan = $company->pihak1_jabatan ?: 'HRD Citra Jaya Knitting';
            $company->mengetahui_nama = $company->mengetahui_nama ?: 'Edwin Ferdiansyah';
            $company->mengetahui_jabatan = $company->mengetahui_jabatan ?: 'Pimpinan CV. Citra Jaya Knitting';

            $company->pihak1_ttd_url = !empty($company->pihak1_ttd) ? base_url('uploads/kontrak/signatures/' . $company->pihak1_ttd) : '';
            $company->mengetahui_ttd_url = !empty($company->mengetahui_ttd) ? base_url('uploads/kontrak/signatures/' . $company->mengetahui_ttd) : '';
            $company->logo_url = !empty($company->logo_file) ? base_url('uploads/company/' . $company->logo_file) : '';
        }

        $this->data['companies'] = $companies;
        $this->data['company'] = $company;
        $this->data['selectedId'] = $selectedId;

        return view('\Modules\Utility\Views\company_profile', $this->data);
    }

    public function save()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        }

        $id = (int)$this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID Perusahaan tidak valid.']);
        }

        $company = $this->db->table('ref_perusahaan')->where('id', $id)->get()->getRow();
        if (!$company) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data Perusahaan tidak ditemukan.']);
        }

        $namaResmi = trim($this->request->getPost('nama_resmi') ?? '');
        $namaPerusahaan = trim($this->request->getPost('nama_perusahaan') ?? '');
        $alamat = trim($this->request->getPost('alamat') ?? '');
        $telepon = trim($this->request->getPost('telepon') ?? '');
        $email = trim($this->request->getPost('email') ?? '');
        $npwp = trim($this->request->getPost('npwp') ?? '');

        $p1Nama = trim($this->request->getPost('pihak1_nama') ?: 'Eva Fiantina');
        $p1Jabatan = trim($this->request->getPost('pihak1_jabatan') ?: 'HRD Citra Jaya Knitting');
        $p3Nama = trim($this->request->getPost('mengetahui_nama') ?: 'Edwin Ferdiansyah');
        $p3Jabatan = trim($this->request->getPost('mengetahui_jabatan') ?: 'Pimpinan CV. Citra Jaya Knitting');

        $update = [
            'nama_resmi' => $namaResmi ?: ($company->nama_resmi ?: $namaPerusahaan),
            'nama_perusahaan' => $namaPerusahaan ?: $company->nama_perusahaan,
            'alamat' => $alamat,
            'telepon' => $telepon,
            'email' => $email,
            'npwp' => $npwp,
            'pihak1_nama' => $p1Nama,
            'pihak1_jabatan' => $p1Jabatan,
            'mengetahui_nama' => $p3Nama,
            'mengetahui_jabatan' => $p3Jabatan,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $sigDir = FCPATH . 'uploads/kontrak/signatures/';
        if (!is_dir($sigDir)) {
            mkdir($sigDir, 0777, true);
        }

        $now = date('Y-m-d H:i:s');

        // Handle Tanda Tangan Pihak Pertama (Eva / HRD)
        $sigP1 = $this->request->getPost('pihak1_signature');
        if (!empty($sigP1)) {
            $p1FileName = $this->saveBase64Signature($sigP1, 'ttd_p1_comp_' . $id . '_' . time() . '.png', $sigDir);
            if ($p1FileName) {
                $update['pihak1_ttd'] = $p1FileName;
                $this->syncToMasterTtd($p1Nama, $p1Jabatan, $p1FileName, $now);
            }
        }

        // Handle Tanda Tangan Mengetahui (Edwin / Pimpinan)
        $sigP3 = $this->request->getPost('mengetahui_signature');
        if (!empty($sigP3)) {
            $p3FileName = $this->saveBase64Signature($sigP3, 'ttd_p3_comp_' . $id . '_' . time() . '.png', $sigDir);
            if ($p3FileName) {
                $update['mengetahui_ttd'] = $p3FileName;
                $this->syncToMasterTtd($p3Nama, $p3Jabatan, $p3FileName, $now);
            }
        }

        // Handle Logo Upload
        $logoFile = $this->request->getFile('logo_file');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $companyDir = FCPATH . 'uploads/company/';
            if (!is_dir($companyDir)) {
                mkdir($companyDir, 0777, true);
            }
            $newLogoName = $logoFile->getRandomName();
            $logoFile->move($companyDir, $newLogoName);
            $update['logo_file'] = $newLogoName;
        }

        $this->db->table('ref_perusahaan')->where('id', $id)->update($update);

        // Jika TTD Pihak 1 atau Mengetahui baru saja diperbarui, otomatis sinkronkan ke master kontrak karyawan
        if (!empty($update['pihak1_ttd'])) {
            $this->db->table('ref_karyawan')
                ->where('type', 1)
                ->where('id_perusahaan', $id)
                ->update([
                    'ttd_pihak1_file' => $update['pihak1_ttd'],
                    'ttd_pihak1_tgl'  => $now,
                ]);
        }
        if (!empty($update['mengetahui_ttd'])) {
            $this->db->table('ref_karyawan')
                ->where('type', 1)
                ->where('id_perusahaan', $id)
                ->update([
                    'ttd_mengetahui_file' => $update['mengetahui_ttd'],
                    'ttd_mengetahui_tgl'  => $now,
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Profil Perusahaan dan Pejabat Penandatangan berhasil disimpan!',
            'data' => [
                'id' => $id,
                'pihak1_ttd_url' => !empty($update['pihak1_ttd']) ? base_url('uploads/kontrak/signatures/' . $update['pihak1_ttd']) : null,
                'mengetahui_ttd_url' => !empty($update['mengetahui_ttd']) ? base_url('uploads/kontrak/signatures/' . $update['mengetahui_ttd']) : null,
                'logo_url' => !empty($update['logo_file']) ? base_url('uploads/company/' . $update['logo_file']) : null,
            ]
        ]);
    }

    public function deleteTtd()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        }

        $id = (int)$this->request->getPost('id');
        $pihak = (int)$this->request->getPost('pihak'); // 1 = Pihak 1, 3 = Mengetahui

        if (empty($id) || empty($pihak)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter tidak valid.']);
        }

        $update = [];
        if ($pihak === 1) {
            $update['pihak1_ttd'] = null;
        } else if ($pihak === 3) {
            $update['mengetahui_ttd'] = null;
        }

        $this->db->table('ref_perusahaan')->where('id', $id)->update($update);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Tanda tangan berhasil dihapus dari profil perusahaan.'
        ]);
    }

    private function saveBase64Signature($base64Data, $fileName, $targetDir)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data)) {
            $cleanBase64 = substr($base64Data, strpos($base64Data, ',') + 1);
            $decoded = base64_decode($cleanBase64);
            if ($decoded !== false) {
                file_put_contents($targetDir . $fileName, $decoded);
                return $fileName;
            }
        }
        return null;
    }

    private function syncToMasterTtd($name, $role, $fileName, $timestamp)
    {
        if (empty($name) || empty($fileName)) return;

        $existing = $this->db->table('ref_tanda_tangan_master')
            ->where('LOWER(TRIM(nama))', strtolower(trim($name)))
            ->get()->getRow();

        if ($existing) {
            $this->db->table('ref_tanda_tangan_master')
                ->where('id', $existing->id)
                ->update([
                    'file_ttd'   => $fileName,
                    'jabatan'    => $role,
                    'updated_at' => $timestamp,
                ]);
        } else {
            $this->db->table('ref_tanda_tangan_master')->insert([
                'nama'       => trim($name),
                'jabatan'    => $role,
                'file_ttd'   => $fileName,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }
}
