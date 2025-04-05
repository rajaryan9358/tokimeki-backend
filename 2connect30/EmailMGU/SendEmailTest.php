<?php

//include 'config.php';   
include 'class.phpmailer.php';


$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";   //charset=iso-8859-1
$headers .= 'From: Drop Receipt' . "\r\n";
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->CharSet = 'UTF-8';
$mail->Host = "smtp.gmail.com";
$mail->SMTPDebug = false; 

$mail->SMTPAuth = true;
$mail->Port = 465; // Or 587 465


$mail->Username = "nomorehotdogs123@gmail.com"; // GMAIL username
$mail->Password = "wsvvhtdxnadmubjy"; // GMAIL password


$mail->SMTPSecure = 'ssl';
$mail->From = "nomorehotdogs123@gmail.com";
$mail->FromName = "2Conenct30";
$mail->Subject = "Test email";
$mail->Body = "This is test email";


$userEmailId = "mgu@narola.email";

$mail->addAddress($userEmailId);
if ($mail->send()) {
    echo "sucsess";
    return true;
} else {
    echo $mail->ErrorInfo;
    echo "Failes";
    return false;
}
//echo $mail->ErrorInfo;
//$post['message'] = $mail->ErrorInfo;
$res = false;

/*
try {
    date_default_timezone_set('Asia/Calcutta');
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: NIPL App' . "\r\n";

    $subject = 'Welcome from Test App';

    $mail = new PHPMailer();
    $mail->IsSMTP(); // telling the class to use SMTP
    //$mail->Host = "mail.yourdomain.com"; // SMTP server
    $mail->SMTPDebug = false; // enables SMTP debug information (for testing)
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth = true; // enable SMTP authentication
    $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
    $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
    $mail->Port = 465; // set the SMTP port for the GMAIL server


    $mail->Username = "yobodfitness@gmail.com";
    $mail->Password = "U&~2|JS%Elez^^HiJk%E";

    $from = "yobodfitness@gmail.com";
    $to = "nr@narola.email";

    $mail->SetFrom($from, "Test" . ' Team');

    $mail->Subject = $subject;
    //$mail->MsgHTML($content);
    $mail->IsHTML(true);
    $mail->Body = "body";


    $address = $to;

    $mail->AddAddress($address);
    $res = $mail->Send();
} catch (phpmailerException $e) {
}
*/

?>