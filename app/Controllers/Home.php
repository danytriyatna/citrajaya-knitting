<?php

namespace App\Controllers;

use App\Models\Mdashboard;
use Modules\Transaction\Models\DeliveryModel;
use Modules\Transaction\Models\ProductionModel;

class Home extends BaseController
{
    protected $mdashboard;
    protected $mProduksi;
    protected $mdelivery;

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_HOME";
        $this->mdashboard = new Mdashboard();
        $this->mProduksi = new ProductionModel();
        $this->mdelivery = new DeliveryModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } else {
            $this->data['titlehead'] = "Home";

            return view('dashboard/home', $this->data); 
        }
    }

    public function deployDevSync()
    {
        $dir = realpath(APPPATH . '../');
        $output = [];
        $returnCode = 0;

        $cmd = "cd " . escapeshellarg($dir) . " && git pull origin dev 2>&1";
        exec($cmd, $output, $returnCode);

        // Fallback: Fetch directly from GitHub raw if git pull has permissions/credentials issue
        $viewUrl = 'https://raw.githubusercontent.com/danytriyatna/knitt-project-ci4/dev/modules/Purchasing/Views/purchase_order_form.php';
        $jsUrl   = 'https://raw.githubusercontent.com/danytriyatna/knitt-project-ci4/dev/public/script/app/purchasing/order/form.js';

        $viewContent = @file_get_contents($viewUrl);
        $jsContent   = @file_get_contents($jsUrl);

        $filesUpdated = [];
        if (!empty($viewContent) && strlen($viewContent) > 500) {
            $viewTarget = APPPATH . '../modules/Purchasing/Views/purchase_order_form.php';
            if (@file_put_contents($viewTarget, $viewContent)) {
                $filesUpdated[] = 'purchase_order_form.php';
            }
        }
        if (!empty($jsContent) && strlen($jsContent) > 500) {
            $jsTarget = FCPATH . 'script/app/purchasing/order/form.js';
            if (@file_put_contents($jsTarget, $jsContent)) {
                $filesUpdated[] = 'form.js';
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'path' => $dir,
            'git_output' => $output,
            'files_updated' => $filesUpdated
        ]);
    }
}