<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Controller;
use App\Models\adminModel;
use App\Models\LanguageModel;
class adminController extends BaseController
{

    public function __construct()
    {
        helper(['form', 'url']);
        $this->admin = new adminModel();
    }
    public function index()
    {
        echo view('Admin/login');
    }

    public function LoginAuth()
    {
        $session = session();

        $email_id = $this->request->getVar('email_id');
        $password = $this->request->getVar('password');
        $data = $this->admin->where('email_id', $email_id)->first();

        if ($data) {
            $pass = $data['password'];
            if ($password == $pass) {
                
                $ses_data = [
                    'user_id'       => $data['id'],
                    'user_name'     => $data['user_name'],
                    'logged_in'     => TRUE,
                ];
                $session->set($ses_data);
                $locale = $this->request->getLocale();
                $session->remove('lang');
                $session->set('lang', $locale);

                $data[ERROR_CODE] = SUCCESS_CODE;
                $data[ERROR_MESSAGE] = "success";
            } else {
                $data[ERROR_CODE] = FAILD_CODE;
                $data[ERROR_MESSAGE] = "Please Enter Valid Password.";
            }
        } else {
            $data[ERROR_CODE] = FAILD_CODE;
            $data[ERROR_MESSAGE] = "Email not Found.";
        }

        echo json_encode($data);
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/admin');
    }

    public function fetch_language()
    {
        $session = session();
        $locale = $this->request->getLocale();
        $session->remove('lang');
        $session->set('lang', $locale);
        $url = base_url('/admin/dashboard');
        
        return redirect()->to($_SESSION['_ci_previous_url']); 
    }
}
