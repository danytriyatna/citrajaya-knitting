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
    
}