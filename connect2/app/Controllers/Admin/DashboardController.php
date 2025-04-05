<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Controller;
use App\Models\adminModel;
use App\Models\userModel;

class DashboardController extends BaseController
{

    public function __construct()
    {
        $this->session = session();
        helper(['form', 'url']);
        $this->admin = new adminModel();
        $this->user = new userModel();
    }

    public function index()
    {
        $active_userQuery = $this->user->query("select * FROM user where is_active = 1");
        $data['active_users']  = $active_userQuery->getResult();

        $inactive_userQuery = $this->user->query("select * FROM user where is_active = 0");
        $data['inactive_users']  = $inactive_userQuery->getResult();

        $last_24_hours = $this->user->query("select * FROM user where user.created_date > DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
        $data['last_24_hours']  = $last_24_hours->getResult();

        /***** AVG CONNCETION ******/
        $count_connection = $this->user->query("select sender_id,COUNT(receiver_id)AS ct FROM `user_request` WHERE status = 'accept' GROUP BY sender_id");
        $total_connection  = $count_connection->getResult();
        $ct = [];
        $count=count($total_connection);
        foreach ($total_connection as $key => $value) 
        {
            $ct[]=$value->ct;
        }
        $total_connection=array_sum($ct);
        $avg_c = round($total_connection/$count);
        $data['avg_connection'] = $avg_c;

        $last_24_hours_chat = $this->user->query("select * FROM user where user.created_date > DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
        $data['last_24_hours_chat']  = $last_24_hours_chat->getResult();

        return view('Admin/dashboard',$data);
    }

    public function my_profile()
    {
        $user_id = $this->session->get('user_id');
        $res = $this->admin->where('id' ,$user_id)->first();
        $data = ['resp' => $res ];
        
        return view("Admin/my_profile",$data);
    }

    public function update_profile() {
        
        if(!empty($_POST)) {
            $user_details = $this->request->getVar();
            $admin_data = [
                "user_name" => $user_details['user_name'],
                "email_id" => $user_details['email_id'],
                "password" =>  $user_details['password'],
                "contact_no" =>  $user_details['contact_no'],
                "address1" =>  $user_details['address1'],
                "address2" =>  $user_details['address2']
            ];

            $admin_update = $this->admin->set($admin_data)->where('id',$user_details['admin_id'])->update();

            if($admin_update){
                $data[ERROR_CODE] = 1;
                $data[ERROR_MESSAGE] = SUCCESS_MESSGE;
            }else{
                $data[ERROR_CODE] = 0;
                $data[ERROR_MESSAGE] = FAILD_MESSAGE;
            }
        } else {
            $data[ERROR_CODE] = 0;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }
}