<?php 
namespace App\Models;
use CodeIgniter\Model;

class QuestionModel extends Model
{     
        protected $table = 'question';
        protected $primaryKey = 'id';    
        protected $allowedFields = ['id','question','question_fr','category_id','created_date','modified_date','is_delete','is_testdata'];
}