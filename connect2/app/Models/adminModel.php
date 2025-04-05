<?php 
namespace App\Models;
use CodeIgniter\Model;

class adminModel extends Model
{
        
        protected $table = 'admin';
        protected $primaryKey = 'id';    
        protected $allowedFields = ['id','user_name','email_id','password','profile_pic','contact_no','dob','address1','address2','is_delete','is_testdata'];

}


