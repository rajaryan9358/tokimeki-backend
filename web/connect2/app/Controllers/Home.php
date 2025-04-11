<?php

namespace App\Controllers;
use App\Libraries\Phpmailer;
class Home extends BaseController
{
    public function index()
    {
        // print_r($_SESSION);
        // die();
        return view('landing_page/index');
    }
    public function contact()
    {
        return view('landing_page/contact_us');
    }
   public function send_email() {
    //print_r($_POST);
    $session = session();
    $session->set('email_sent',1);
        $email_id = "sna@narola.email";
        $html='';
        $html.='<html>';
        $html.='<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 50%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}
#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  color: black;
}
</style>
</head>
<body>';
        $html.='<table id="customers">';
        $html.='<tr><th colspan="2">Contact Us Form Details</th></tr>';
        $html.='<tr><th>Name</th><td>'.$_POST['name'].'</td></tr>';
        $html.='<tr><th>Email</th><td>'.$_POST['email'].'</td></tr>';
        $html.='<tr><th>Phone</th><td>'.$_POST['phone'].'</td></tr>';
        $html.='<tr><th>Message</th><td>'.$_POST['message'].'</td></tr>';
        $html.='</table>';
        $html.='</html>';

        $email = \Config\Services::email();
        $email->setFrom('narolareactnative@gmail.com', 'Tokimeki.com');
        $email->setTo($email_id);

        $email->setSubject('Contact Us From Details');
        $email->setMessage($html);

        if ($email->send()) 
        {
             $session->set('email_sent',1);
            // redirect(base_url());
           echo "Email sent";
            return  $this->response->redirect(base_url());
        } 
        else 
        {
            echo $email->printDebugger();
        }
    }

}
