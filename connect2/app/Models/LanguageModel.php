<?php 
namespace App\Models;
use CodeIgniter\Model;

class LanguageModel extends Model
{     
    protected $table = 'language_specification';
    protected $primaryKey = 'id';    
    protected $allowedFields = ['id','label','en_lang','fr_lang','created_date','modified_date','is_delete','is_testdata'];
}
