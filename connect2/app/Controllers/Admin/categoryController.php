<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\categoryModel;
use App\Models\QuestionModel;

class categoryController extends BaseController
{

    public function __construct()
    {
        $this->session = session();
        helper(['form', 'url']);
        $this->category = new categoryModel();
        $this->question = new QuestionModel();
    }

    public function index()
    {
        $res = $this->category->where('is_delete', 0)->findAll();
        return view('Admin/category', ['resp' => $res]);
    }

    public function category_list()
    {
        if ($this->request->getVar('id')) {
            $res = $this->category->where('id', $this->request->getVar('id'))->first();
        } else {
            $res = $this->category->where('is_delete', 0)->orderBy('id', 'DESC')->findAll();
        }

        if (!empty($res)) {
            $data[ERROR_CODE] = SUCCESS_CODE;
            $data[ERROR_MESSAGE] = FETCH_SUCCESS;
            $data[RESPONSE] = $res;
        } else {
            $data[ERROR_CODE] = FAILD_CODE;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }

    public function fetchCategoryList()
    {

        ## Read value
        $draw = $_POST['draw'];
        $row = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue =   $_POST['search']['value']; // Search value

        if ($searchValue == '')
            $search = " where is_delete = 0 ";
        else
            $search = " where is_delete = 0 and category_name LIKE '%$searchValue%' OR category_name_fr LIKE '%$searchValue%' OR score LIKE '%$searchValue%'  ";


        ## Total number of records without filtering
        $res = $this->category->select("count(*) as allcount")->where('is_delete', 0)->first();

        $totalRecords = $res['allcount'];

        ## Total number of record with filtering
        $records = $this->category->query("select count(*) as allcount from category $search ");
        $res  = $records->getResult();
        $totalRecordwithFilter = $res[0]->allcount;
        // echo $row,$rowperpage;

        ## Fetch records

        if ($columnName == "")
            $order = " order by id DESC";
        else
            $order = " order by $columnName $columnSortOrder ";

        $categoryQuery = $this->category->query("select * from category $search $order limit $row,$rowperpage");
        $res  = $categoryQuery->getResult();

        $lang_session = $this->session->get('lang');

        $data = array();
        foreach ($res as $key) {

            $action = "<td class='text-center' ><ul class='icons-list'><li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='icon-menu9'></i></a><ul class='dropdown-menu dropdown-menu-right'><li><a href='#' data-toggle='modal' data-target='#modal-login' onclick='edit_category(" . $key->id . ")'><i class='icon-pencil4'></i>" . lang('Validation.edit') . "</a></li><li><a href='#' data-toggle='modal' data-target='#modal_default' onclick='delete_category(" . $key->id . ")'><i class='icon-trash'></i>" . lang('Validation.delete') . "</a></li></ul></li></ul></td>";
            $data[] = array(
                "category_name" => $key->category_name,
                "category_name_fr" => $key->category_name_fr,
                "score" => $key->score,
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
    public function add_category()
    {

        $data = [
            "category_name" => $this->request->getVar('category_name'),
            "category_name_fr" => $this->request->getVar('category_name_fr'),
            "score" => $this->request->getVar('score'),
            "parentid" => 0,
            "is_testdata" => TESTDATA,
            "is_delete" => 0
        ];

        if ($this->request->getVar('cat_id') == "") {

            $isExist = $this->category->where('category_name', $this->request->getVar('category_name'))->first();

            if (!empty($isExist)) {
                $resp[ERROR_CODE] = 0;
                $resp[ERROR_MESSAGE] = $this->request->getVar('category_name') . ' Category ' . EXIST_MESSAGE;
            } else {

                $data['created_date'] = date('Y-m-d H:i:s');
                $res = $this->category->save($data);

                if ($res) {
                    $resp[ERROR_CODE] = 1;
                    $resp[ERROR_MESSAGE] = SUCCESS_MESSGE;
                } else {
                    $resp[ERROR_CODE] = 0;
                    $resp[ERROR_MESSAGE] = FAILD_MESSAGE;
                }
            }
        } else {

            $data['modified_date'] = date('Y-m-d H:i:s');
            // $data['id'] = $this->request->getVar('cat_id');
            $res = $this->category->set($data)->where('id', $this->request->getVar('cat_id'))->update();

            if ($res) {
                $resp[ERROR_CODE] = 1;
                $resp[ERROR_MESSAGE] = SUCCESS_MESSGE;
            } else {
                $resp[ERROR_CODE] = 0;
                $resp[ERROR_MESSAGE] = FAILD_MESSAGE;
            }
        }
        echo json_encode($resp);
    }

    public function delete_category()
    {

        $res = $this->category->set(['is_delete' => 1])->where('id', $this->request->getVar("id"))->update();

        if ($res > 0) {
            $res = $this->question->set(['is_delete' => 1])->where('category_id', $this->request->getVar("id"))->update();
            $data[ERROR_CODE] = 1;
            $data[ERROR_MESSAGE] = DELETE_MESSAGE;
        } else {
            $data[ERROR_CODE] = 0;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }
}
