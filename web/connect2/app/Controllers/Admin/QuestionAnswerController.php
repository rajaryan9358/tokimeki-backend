<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\QuestionModel;
use App\Models\AnswerModel;
use App\Models\categoryModel;

class QuestionAnswerController extends BaseController
{

    public function __construct()
    {
        $this->session = session();
        helper(['form', 'url']);
        $this->question = new QuestionModel();
        $this->answer = new AnswerModel();
        $this->category = new categoryModel(); 
    }

    public function index(){
        return view('Admin/question_answer_list');
    }

    public function add_question_answer()
    {

        if($this->request->getVar('id') == ""){
            $data1 = [
                "question" => $this->request->getVar('question'),
                "question_fr" => $this->request->getVar('question_fr'),
                "category_id" => $this->request->getVar('category_name'),
                "create_date"=> CURRENT_DATE,
                "modified_date"=> CURRENT_DATE,
                "is_delete" => 0,
                "is_testdata" => TESTDATA 
            ];
    
           $ques_res = $this->question->save($data1);
           $answers = $this->request->getVar('answers');
           $answers_fr = $this->request->getVar('answers_fr');
           $scores = $this->request->getVar('score');
           $question_id = $this->question->getInsertID();
    
           if($ques_res > 0){
               for($i=0;$i<count($answers);$i++){
                    if($answers[$i] != ""){
                        $ans_data = [
                                "question_id" => $question_id,
                                "answer" => $answers[$i],
                                "answer_fr" => $answers_fr[$i],
                                "score" =>  $scores[$i],
                                "create_date" => CURRENT_DATE,
                                "modified_date" => CURRENT_DATE,
                                "is_testdata" => TESTDATA
                        ];
                        $answer = $this->answer->save($ans_data);     
                    }
               }
            }
            if($answer){
                 $data[ERROR_CODE] = 1;
                 $data[ERROR_MESSAGE] = SUCCESS_MESSGE;
             }else{
                 $data[ERROR_CODE] = 0;
                 $data[ERROR_MESSAGE] = FAILD_MESSAGE;
             }

        }else{

            $data = [
                "question" => $this->request->getVar('question'),
                "question_fr" => $this->request->getVar('question_fr'),
                "modified_date"=> CURRENT_DATE
            ];

            $this->question->set($data)->where('id',$this->request->getVar('id'))->update();
            $answers = $this->request->getVar('answers');
            $answers_fr = $this->request->getVar('answers_fr');
            $scores = $this->request->getVar('score');
            $ans_id = $this->request->getVar('ans_id');
            for ($i=0;$i<count($answers);$i++){
                if($answers[$i] != ""){
                    $exist_check = $this->answer->where('id',$ans_id[$i])->first();

                    if(empty($exist_check)){
                        $ans_data = [
                            "question_id" => $this->request->getVar('id'),
                            "answer" => $answers[$i],
                            "answer_fr" => $answers_fr[$i],
                            "score" =>  $scores[$i],
                            "create_date" => CURRENT_DATE,
                            "modified_date" => CURRENT_DATE,
                            "is_testdata" => TESTDATA
                        ];
                        $answer = $this->answer->save($ans_data);    
                    }else{
                        $ans_data = [
                            "answer" => $answers[$i],
                            "answer_fr" => $answers_fr[$i],
                            "score" =>  $scores[$i],
                            "modified_date" => CURRENT_DATE,
                        ];
                        $answer = $this->answer->set($ans_data)->where('id',$ans_id[$i])->update();
                    }
                }

            }

            if($answer){
                $data[ERROR_CODE] = 1;
                $data[ERROR_MESSAGE] = SUCCESS_MESSGE;
            }else{
                $data[ERROR_CODE] = 0;
                $data[ERROR_MESSAGE] = FAILD_MESSAGE;
            }
        }

        echo json_encode($data);
    }

    public function category_list()
    {

        $res = $this->category->select('category_name,category_name_fr,id')->where('id NOT IN ( select category_id from question where is_delete = 0 )')
                                                          ->where('is_delete',0)
                                                          ->findAll();

        if(!empty($res)){
            $data[ERROR_CODE] = SUCCESS_CODE;
            $data[ERROR_MESSAGE] = FETCH_SUCCESS;
            $data[RESPONSE] = $res;
        }else{
            $data[ERROR_CODE] = FAILD_CODE;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }

    public function fetchCategoryQuestionList(){

        ## Read value
        $draw = $_POST['draw'];
        $row = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue =   $_POST['search']['value'];// Search value

        if($searchValue == '')
            $search = " where q.is_delete = 0 ";
        else
            $search = " where  q.is_delete = 0  AND q.question LIKE '%$searchValue%' OR q.question_fr LIKE '%$searchValue%' OR c.category_name LIKE '%$searchValue%'   ";

        ## Total number of records without filtering
        $res = $this->question->select("count(*) as allcount")->where('is_delete',0)->first();
        
        $totalRecords = $res['allcount'];

        ## Total number of record with filtering
        $records = $this->question->query("select count(*) as allcount from question as q join category as c on c.id = q.category_id  $search ");
        $res  = $records->getResult();
        $totalRecordwithFilter = $res[0]->allcount;
       // echo $row,$rowperpage;
      
        ## Fetch records

        if($columnName == "")
            $order = " order by q.id DESC";
        else
            $order = " order by $columnName $columnSortOrder ";

        $categoryQuery = $this->category->query("select q.*,c.category_name,c.id as cat_id from question as q join category as c on c.id = q.category_id $search $order limit $row,$rowperpage");
        $res  = $categoryQuery->getResult();
        $lang_session = $this->session->get('lang');
        $data = array();
        foreach($res as $key){
          
            $action = "<td class='text-center' ><ul class='icons-list'><li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='icon-menu9'></i></a><ul class='dropdown-menu dropdown-menu-right'><li><a href='#' data-toggle='modal' data-target='#modal-login' onclick='edit_que_ans(".$key-> id .")'><i class='icon-pencil4'></i>". lang('Validation.edit')."</a></li><li><a href='#' data-toggle='modal' data-target='#modal_default' onclick='delete_question(".$key-> id.")'><i class='icon-trash'></i>". lang('Validation.delete')."</a></li></ul></li></ul></td>";
            $data[] = array(
                "question" => $key->question,
                "question_fr" => $key->question_fr, 
                "category_name" => $key-> category_name,
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



    public function delete_question()
    {
       $res = $this->question->set(['is_delete' => 1 ])->where('id',$this->request->getVar("id"))->update();

        if($res > 0){
            $this->answer->set(['is_delete' => 1 ])->where('question_id',$this->request->getVar("id"))->update();
            $data[ERROR_CODE] = 1;
            $data[ERROR_MESSAGE] = DELETE_MESSAGE;
        }else{
            $data[ERROR_CODE] = 0;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }

    public function fetch_que_ans_list(){

        $id = $this->request->getVar('id');
        $que_data = $this->question->select("question.*, c.category_name")->table("question")->join('category as c','c.id = question.category_id')->where(['question.id' =>$id ,'question.is_delete' => 0 ])->first();

        if(!empty($que_data)){
            $ans_data = $this->answer->where(['question_id' =>$id ,'is_delete' =>0 ])->findAll();
            $que_data['ans_data'] = $ans_data;

            if(!empty($ans_data)){
                $data[ERROR_CODE] = SUCCESS_CODE;
                $data[ERROR_MESSAGE] = SUCCESS_MESSGE;
                $data[RESPONSE] = $que_data;
            }else{
                $data[ERROR_CODE] = FAILD_CODE;
                $data[ERROR_MESSAGE] = FAILD_MESSAGE;
            }
           echo json_encode($data);
        }
    }

    public function delete_answer()
    {

        $res = $this->answer->set(['is_delete' => 1 ])->where('id',$this->request->getVar("id"))->update();

        if($res > 0){
            $data[ERROR_CODE] = 1;
            $data[ERROR_MESSAGE] = DELETE_MESSAGE;
        }else{
            $data[ERROR_CODE] = 0;
            $data[ERROR_MESSAGE] = FAILD_MESSAGE;
        }
        echo json_encode($data);
    }
}