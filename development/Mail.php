<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
 
    function sendEmail($email,$verificationCode){
        require_once "vendor/autoload.php";
    
        $mail = new PHPMailer;
        try {
        //Server settings
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
//            $mail->Username   = 'nomorehotdogs123@gmail.com';                     // SMTP username
//            $mail->Password   = 'wsvvhtdxnadmubjy';
            $mail->Username   = 'twoconnect30@gmail.com';                     // SMTP username
            $mail->Password   = 'ryfenpycapvrpkvs';                                 // SMTP password
            $mail->Port       = 587;    

            //Recipients
            $mail->setFrom('twoconnect30@gmail.com', 'Raphael');
            $mail->addAddress($email, 'Joe User');     // Add a recipient
        
            // // Attachments
            // $mail->addAttachment('upload/PassportProfile/'.$passportImage);         // Add attachments
            // $mail->addAttachment('upload/Document/'.$bankDocumentUrl);    // Optional name
        
            // Content
            $mail->isHTML(true);                                  
            $mail->Subject = 'Verify your email';
            $mail->Body = "
                <html xmlns='http://www.w3.org/1999/xhtml' style='font-family: Helvetica Neue, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;'>
                <head>
                    <title>Confirm email</title>
                    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
                
                    <style type='text/css'>
                        .container {
                            width: 100%;
                            max-width: 1140px;
                            padding-right: 15px;
                            padding-left: 15px;
                            margin-right: auto;
                            margin-left: auto;
                        }
                
                        .email-box {
                            background-color: #f4f4f4;
                            padding: 100px 0;
                            text-align: center;
                            font-family: 'Raleway', sans-serif;
                        }
                
                        .email-txt h2 {
                            margin: 60px 0 30px;
                            font-size: 55px;
                            color: #333333;
                            font-weight: 800;
                            line-height: 64px;
                            font-family: 'Raleway', sans-serif;
                        }
                
                        .email-txt p {
                            font-size: 25px;
                            color: #333333;
                            font-weight: 400;
                            line-height: 36px;
                            font-family: 'Raleway', sans-serif;
                            max-width: 100%;
                            /* width: 500px; */
                            margin: 0 auto 30px;
                        }
                
                        .email-logo {
                            margin: 10px 0;
                        }
                
                        .email-logo img {
                            max-width: 100%;
                            width: 20%;
                        }
                
                        .email-page {
                            margin: 90px 0;
                        }
                
                        body {
                            margin: 0;
                        }
                    </style>
                </head>
                
                <body>
                    <div class='email-page'>
                        <div class='container'>
                            <div class='email-box'>
                                <div class='email-logo'>
                                    <img src='https://tokimeki.ca/2connect30/Image/Logo.png' style='height: 100px; width: 98px; margin-bottom: 5px;' />
                                </div>
                                <div class='email-txt'>
                                    <p>Hi!</p>
                                    <p>Here is the confirmation code for your online form:</p>
                                    <h2>$verificationCode</h2>
                                    <p>All you have to do is copy the confirmation code and paste it to your form to complete the email verification process.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            ";
            if(!$mail->Send())
            {
                $data = "Mail Error:".$mail->ErrorInfo;
            }
            else
            {
                $data = "Message has been sent successfully";
            }
        } catch (Exception $e) {
            $data =  "Mailer Error: " . $mail->ErrorInfo;
        }
        return $data;
    }
?>