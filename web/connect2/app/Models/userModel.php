<?php 
namespace App\Models;
use CodeIgniter\Model;

class userModel extends Model
{
        
        protected $table = 'user';
        protected $primaryKey = 'id';    
        protected $allowedFields = ['id','email_id','first_name','last_name','nick_name','profile_image','avtar_name','date_of_birth','is_verify','is_subscribe','is_active','google_id','apple_id','verification_code','marital_status','guid','created_date','is_delete'];

}


