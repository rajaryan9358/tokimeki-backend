<?php

class FCM
{
    function send_fcm_notify($reg_id, $isNotificationType,$payload,$dataArray)
    {
        define("FIREBASE_API_KEY", "AAAAkrsjwuA:APA91bGLy2RgvwP3azvVwqCT_6ANm80QsUsokfmzCQnOJrL1FWZHxboqvc4DdA-HRA49eVnB7L8N8AN5GiZho_OJVaB4oTEhqMUad73dHaiGSUFsdHIc51y_ySTi4nQyvE7RNCFlv3-K");
        define("FIREBASE_FCM_URL", "https://fcm.googleapis.com/fcm/send");
        // $reg_id = ['d-wjYGsfBUornJ5lHA9FJH:APA91bEEVvDBk7LNLU9FxX6oC6k6zzA3ny3GWt2yOHl9X7YYg0yFrung5f9WnwH52WCjiDy5RjWqfz9IKlIs3R8FH9sTC48scuaunRBL60Q_0NsWCkw0HWf9gvPlOpD5YCUmXM5pm_Of'];
        $fields=array();
        //$isNotificationType is Boolean value
        // if($isNotificationType){

            if(is_array($reg_id)){
                $fields['registration_ids'] = $reg_id;

            }else{
                $fields['to'] = $reg_id;
            }

            $fields['priority'] =  "high";
            $fields['notification'] =  $payload;
            // $fields['data'] = $dataArray;

        // }
        // else{

        //     if(is_array($reg_id)){
        //         $fields['registration_ids'] = $reg_id;

        //     }else{
        //         $fields['to'] = $reg_id;
        //     }

        //     $fields['priority'] =  "high";
        //     $fields['data'] =  $payload;
        // }

        $headers = array(
            'Authorization: key=' . FIREBASE_API_KEY,
            'Content-Type: application/json'
        );
        //echo  json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, FIREBASE_FCM_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Problem occurred: ' . curl_error($ch));
        }

        curl_close($ch);
        return $result;
    }
}

?>