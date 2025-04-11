<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\userModel;

class userController extends BaseController
{

    public function __construct()
    {
        $this->session = session();
        helper(['form', 'url']);
        $this->user = new userModel();
    }
    public function index()
    {
        $res = $this->user->where('is_delete', 0)->findAll();
        echo view('Admin/users', [
            'resp' => $res
        ]);
    }

    public function user_status_change()
    {
        $is_active = $this->request->getVar("status");
        $id = $this->request->getVar("user_id");

        $res = $this->user->set(['is_active' => $is_active])->where('id', $id)->update();

        if ($res > 0) {
            echo SUCCESS_CODE;
        } else {
            echo  FAILD_CODE;
        }
    }

    public function getUserRecords()
    {      
        ## Read value
        $draw = $_POST['draw'];
        $row = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue =   $_POST['search']['value'];// Search value

        ## Total number of records without filtering
        $res = $this->user->select("count(*) as allcount")->first();
        
        $totalRecords = $res['allcount'];

        ## Total number of record with filtering
        $records = $this->user->select(" count(*) as allcount ")
                                ->join("user_points","user_points.user_id = user.id")
                                ->where('first_name',$searchValue)
                                ->orLike('last_name',$searchValue)
                                ->orLike('nick_name',$searchValue)
                                ->orLike('email_id',$searchValue)
                                ->orLike('date_of_birth',$searchValue)
                                ->orLike('marital_status',$searchValue)
                                ->first();
        $totalRecordwithFilter = $records['allcount'];
      
        ## Fetch records
        if($columnName == "")
            $order = " order by id DESC";
        else
            $order = " order by $columnName $columnSortOrder ";

        if($searchValue == '')
            $search = " where user.is_delete = 0 ";
        else
            $search = " where user.is_delete = 0 AND ( first_name LIKE '%$searchValue%' OR last_name LIKE '%$searchValue%' OR nick_name LIKE '%$searchValue%' OR email_id LIKE '%$searchValue%' OR date_of_birth LIKE '%$searchValue%' OR marital_status LIKE '%$searchValue%' OR point LIKE '%$searchValue%') ";

        $userQuery = $this->user->query("select user.*,user_points.point from user join user_points ON user_points.user_id = user.id  $search $order limit $row,$rowperpage");
        $res  = $userQuery->getResult();
        $lang_session = $this->session->get('lang');
        $data = array();
        foreach($res as $key){
            $get_min_max = $this->user->query("select min_point,max_point from points where min_point <= $key->point AND max_point >= $key->point ");
            $res_point  = $get_min_max->getRowArray();
            
            if(!isset($res_point)){
                $max_point = $key->point;
                $min_point = $key->point;
            }else{
                $min_point = $res_point['min_point'];
                $max_point = $res_point['max_point'];
            }
            
            $match_result = $this->user->query("SELECT count(*) as total_match FROM user  INNER JOIN user_points ON user_points.user_id = user.id LEFT JOIN reject_match

            ON reject_match.other_user_id = user.id AND reject_match.userid = $key->id AND reject_match.is_delete='0'

            LEFT JOIN user_request AS ur_sender ON ur_sender.receiver_id = user.id AND ur_sender.sender_id = $key->id AND ur_sender.is_delete = '0' 
            
            LEFT JOIN user_request AS ur_receiver ON ur_receiver.sender_id = user.id AND
            ur_receiver.receiver_id = $key->id AND ur_receiver.is_delete = '0'

            WHERE user_points.point BETWEEN $min_point AND $max_point AND user.is_delete='0' AND  user.id != $key->id AND
            reject_match.id IS NULL AND ur_sender.id IS NULL AND ur_receiver.id IS NULL");

            $res1  = $match_result->getRowArray();

            $action = "<td class='text-center'><ul class='icons-list'><li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='icon-menu9'></i></a><ul class='dropdown-menu dropdown-menu-right'><li><a href='#'  onclick='change_status(".$key-> id . ',' . $key->is_active. ")'><i class='icon-pencil4'></i>". lang('Validation.change_status') ."</a></li><li><a href='#'  data-toggle='modal' data-target='#modal_default' onclick='view_user_details(".$key-> id.")'><i class='icon-list3'></i>". lang('Validation.view_details') ."</a></li></ul></li></ul></td>";

            if($key -> profile_image != "")
                $img_path = '<a href="#" class="media-left"><img src='. UPLOAD_IMG_PATH . $key -> profile_image . ' class="img-circle img-sm" alt=""></a>';
            else
                $img_path = '<a href="#" class="media-left"><img src="'. base_url() .'/assets/images/user_placeholder.jpg" class="img-circle img-sm" alt=""></a>';

            $data[] = array(
                "profile_image" => $img_path,
                "first_name" => $key-> first_name. ' ' .$key-> last_name,
                "last_name" => $key-> last_name,
                "email_id" => $key-> email_id,
                "date_of_birth" => $key-> date_of_birth,
                "point"        => $key->point,
                "matches"   => $res1['total_match'],
                "marital_status" => $key-> marital_status,
                "nick_name" => $key-> nick_name,
                "id" => $key-> id,
                "is_active" => ($key-> is_active == 2) ? 'Deactive' : 'Active',
                "action" => $action
            );
        }

        ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        echo json_encode($response);
    }
    public function fetchUserDetails()
    {

        if ($this->request->getVar('uid') != "") {
        
          $res = $this->user->select('user.*,user_points.point')->join('user_points','user_points.user_id = user.id')->where('user.id', $this->request->getVar('uid'))->first();

            $Point = $res['point'];
           
            $get_min_max = $this->user->query("select min_point,max_point from points where min_point <= $Point AND max_point >= $Point ");

            $res_point  = $get_min_max->getRowArray();
            
            if(!isset($res_point)){
                $max_point = $Point;
                $min_point = $Point;
            }else{
                $min_point = $res_point['min_point'];
                $max_point = $res_point['max_point'];
            }
            $ids = $res['id'];

            $match_result = $this->user->query("SELECT count(*) as total_match FROM user  INNER JOIN user_points ON user_points.user_id = user.id LEFT JOIN reject_match
            ON reject_match.other_user_id = user.id AND reject_match.userid = $ids AND reject_match.is_delete='0'
            LEFT JOIN user_request AS ur_sender ON ur_sender.receiver_id = user.id AND ur_sender.sender_id = $ids AND ur_sender.is_delete = '0' 
            LEFT JOIN user_request AS ur_receiver ON ur_receiver.sender_id = user.id AND
            ur_receiver.receiver_id = $ids AND ur_receiver.is_delete = '0'
            WHERE user_points.point BETWEEN $min_point AND $max_point AND user.is_delete='0' AND  user.id != $ids AND
            reject_match.id IS NULL AND ur_sender.id IS NULL AND ur_receiver.id IS NULL");

            $matches  = $match_result->getRowArray();

            $res['matches'] = $matches['total_match'];

            if (!empty($res)) {
                $data[ERROR_CODE] = SUCCESS_CODE;
                $data[ERROR_MESSAGE] = FETCH_SUCCESS;
                $data[RESPONSE] = $res;
            } else {
                $data[ERROR_CODE] = FAILD_CODE;
                $data[ERROR_MESSAGE] = FAILD_MESSAGE;
            }
        } else {
            $res = $this->user->where('is_delete', 0)->findAll();
            if (!empty($res)) {
                $data[ERROR_CODE] = SUCCESS_CODE;
                $data[ERROR_MESSAGE] = FETCH_SUCCESS;
                $data[RESPONSE] = $res;
            } else {
                $data[ERROR_CODE] = FAILD_CODE;
                $data[ERROR_MESSAGE] = FAILD_MESSAGE;
            }
        }
        echo json_encode($data);
    }
}
