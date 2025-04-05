<?php 
namespace App\Models;
use CodeIgniter\Model;

class AppTokenModel extends Model
{     
        protected $table = 'app_token';
        protected $primaryKey = 'id';    
        protected $allowedFields = ['id','user_id','token','token_type','status','expiry','access_count','device_token','device_type','created_date','modified_date','is_delete','is_testdata'];
}