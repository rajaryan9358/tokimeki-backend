<?php 
namespace App\Models;
use CodeIgniter\Model;

class categoryModel extends Model
{     
    protected $table = 'category';
    protected $primaryKey = 'id';    
    protected $allowedFields = ['id','category_name','category_name_fr','parentid','score','created_date','modified_date','is_delete','is_testdata'];
}


