<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Controller;
use App\Models\adminModel;

class DashboardController extends BaseController
{

    public function __construct()
    {
        $this->session = session();
        helper(['form', 'url']);
        $this->admin = new adminModel();
    }

    public function index(){
        return view('Admin/dashboard');
    }

    public function my_profile()
    {
        $user_id = $this->session->get('user_id');
        $res = $this->admin->where('id' ,$user_id)->first();
        $data = ['resp' => $res ];
        return view("Admin/my_profile",$data);
    }

}