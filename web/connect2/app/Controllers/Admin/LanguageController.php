<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\LanguageModel;

class LanguageController extends Controller
{
    public function __construct()
    {
        $this->session = session();
        helper(['form', 'url']);
        $this->language = new LanguageModel();
    }

    public function index()
    {
        return view('Admin/language_specification');
    }

    public function add_language()
    {
        $id = $this->request->getVar('lid');

        $data = [
            "en_lang" => $this->request->getVar('en_lang'),
            "fr_lang" => $this->request->getVar('fr_lang'),
            "modified_date" => CURRENT_DATE,
            "is_testdata" => TESTDATA
        ];

        if ($id == "") {
            $exist = $this->language->where('label',  $this->request->getVar('label'))->first();

            if(empty($exist)){
                $data['label'] = $this->request->getVar('label');
                $data['created_date'] = CURRENT_DATE;
                $res = $this->language->save($data);
            }
        } else {
            $res = $this->language->set($data)->where('id', $id)->update();
        }

        if(!empty($exist)){
            $resp[ERROR_CODE] = FAILD_CODE;
            $resp[ERROR_MESSAGE] = 'Label alredy exist.'; 
        } else if (!empty($res)) {
            $resp[ERROR_CODE] = SUCCESS_CODE;
            $resp[ERROR_MESSAGE] = SUCCESS_MESSGE;
        } else {
            $resp[ERROR_CODE] = FAILD_CODE;
            $resp[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($resp);
    }

    public function delete_language()
    {

        $res = $this->language->set(['is_delete' => 1])->where('id', $this->request->getVar('id'))->update();

        if ($res > 0) {
            $data[ERROR_CODE] = 1;
            $data[ERROR_MESSAGE] = DELETE_MESSAGE;
        } else {
            $data[ERROR_CODE] = 0;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }

    public function language_list()
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
            $search = " where is_delete = 0 and label LIKE '%$searchValue%'  ";

        ## Total number of records without filtering
        $res = $this->language->select("count(*) as allcount")->where('is_delete', 0)->first();

        $totalRecords = $res['allcount'];

        ## Total number of record with filtering
        $records = $this->language->query("select count(*) as allcount from language_specification $search ");
        $res  = $records->getResult();
        $totalRecordwithFilter = $res[0]->allcount;
        // echo $row,$rowperpage;

        ## Fetch records

        if ($columnName == "")
            $order = " order by id DESC ";
        else
            $order = " order by $columnName $columnSortOrder ";

        $languageQuery = $this->language->query("select * from language_specification $search $order limit $row,$rowperpage");
        $res  = $languageQuery->getResult();
        $lang_session = $this->session->get('lang');
        $data = array();
        foreach ($res as $key) {

            $action = "<td class='text-center' ><ul class='icons-list'><li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='icon-menu9'></i></a><ul class='dropdown-menu dropdown-menu-right'><li><a href='#' data-toggle='modal' data-target='#modal-login' onclick='edit_language(" . $key->id . ")'><i class='icon-pencil4'></i>".$lang_session['edit']."</a></li><li><a href='#' data-toggle='modal' data-target='#modal_default' onclick='delete_language(" . $key->id . ")'><i class='icon-trash'></i>".$lang_session['delete']."</a></li></ul></li></ul></td>";
            $data[] = array(
                "label" => $key->label,
                "en_lang" => $key->en_lang,
                "fr_lang" => $key->fr_lang,
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

    public function fetch_language()
    {
        $id = $this->request->getVar('lid'); 

        $res = $this->language->where('id',$id)->first();

        if ($res > 0) {
            $data[ERROR_CODE] = 1;
            $data[ERROR_MESSAGE] = FETCH_SUCCESS;
            $data[RESPONSE] = $res;
        } else {
            $data[ERROR_CODE] = 0;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }

    public function language_json(){

        $lang_code = $this->request->getVar('language');

          $res = $this->language->where('is_delete',0)->findAll();

          $data = array();
          if($lang_code == "english"){
            $this->session->set('lang_type', "English");
            $this->session->set('lang_flag', "assets/images/flags/gb.png");
              foreach($res as $val){
                  $data[$val['label']] = $val['en_lang'];
              }
          }else if($lang_code == "franch"){
            $this->session->set('lang_type', "French");
            $this->session->set('lang_flag', "assets/images/flags/fr.png");
            foreach($res as $val){
                $data[$val['label']] = $val['fr_lang'];
            }
          }

          $this->session->remove('lang');
          $this->session->set('lang', $data);

         if($this->session->get('lang')){
             echo SUCCESS_CODE;
         }else{
             echo FAILD_CODE;
         }
         
    }
}
