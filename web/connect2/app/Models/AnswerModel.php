<?php 
namespace App\Models;
use CodeIgniter\Model;

class AnswerModel extends Model
{     
        protected $table = 'answer';
        protected $primaryKey = 'id';    
        protected $allowedFields = ['id','question_id','answer','answer_fr','score','created_date','modified_date','is_testdata','is_delete'];
}