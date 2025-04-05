<?php
    include_once 'FCM.php';
    class Notification
    {
        protected $connection;
    
        function __construct(mysqli $con)
        {
            $this->connection = $con;
        }

        public function call_service($service, $postData)
        {
            switch ($service) {
                case "getNotificationList":
                    {
                        return $this->getNotificationList($postData);
                    }
                    break;
                default:
                    {
                        $data[DATA] = 'No Service Found';
                        $data[MESSAGE] = $_REQUEST['Service'];
                        return $data;
                    }
                break;
            }
        }

        public function getNotificationList($postData)
        {
            $userId = validateObject($postData,param_userid, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $currentDate = date('Y-m-d');
            if ($userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $TotalNoOfNotification = 0;
            $query = sprintf("SELECT * FROM %s WHERE %s = ? AND %s > ? AND %s='%s'",TABLE_NOTIFICATION,NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::notificationExpiryDate,NOTIFICATION_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("is", $userId,$currentDate);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $TotalNoOfNotification = $stmt_result->num_rows;
                    }
                }
            }
           $NotificationList = [];
           $SelectedField = sprintf("%s.%s,%s,%s,%s,%s,%s,%s.%s,%s.%s,%s.%s,%s.%s,%s,%s,%s",TABLE_NOTIFICATION,NOTIFICATION_Fields::id,NOTIFICATION_Fields::notificationType,NOTIFICATION_Fields::notificationMsg,NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::senderId,NOTIFICATION_Fields::notificationExpiryDate,TABLE_NOTIFICATION,NOTIFICATION_Fields::created,TABLE_NOTIFICATION,NOTIFICATION_Fields::modified,TABLE_NOTIFICATION, NOTIFICATION_Fields::isDelete,TABLE_NOTIFICATION,NOTIFICATION_Fields::isTestdata,USERS_Fields::firstName,USERS_Fields::lastName,USERS_Fields::nickName);
           $query = sprintf("SELECT $SelectedField FROM %s INNER JOIN %s ON %s.%s = %s.%s WHERE %s = ? AND %s > ? AND %s.%s='%s' ORDER BY %s.%s DESC LIMIT ?,?",TABLE_NOTIFICATION,TABLE_USERS,TABLE_NOTIFICATION,
            NOTIFICATION_Fields::senderId,TABLE_USERS,USERS_Fields::id,NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::notificationExpiryDate,TABLE_NOTIFICATION,NOTIFICATION_Fields::isDelete,DELETE_STATUS::NOT_DELETE,TABLE_NOTIFICATION,NOTIFICATION_Fields::created);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("isii", $userId,$currentDate,$start,$limit);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $notificationIds = [];
                        $modifiedDate = date('Y-m-d H:i:s');
                        while($row_data = $stmt_result->fetch_assoc()) {
                            // $msg = $row_data[USERS_Fields::firstName].' '.$row_data[USERS_Fields::lastName].' '.$row_data[NOTIFICATION_Fields::notificationMsg];
                            $msg = $row_data[USERS_Fields::nickName].' '.$row_data[NOTIFICATION_Fields::notificationMsg];
                            $notify['id']=$row_data['id'];
                            $notify['notification_type']=$row_data[NOTIFICATION_Fields::notificationType];
                            $notify['notification_msg']=$msg;
                            $notify['receiver_id']=$row_data[NOTIFICATION_Fields::receiverId];
                            $notify['sender_id']=$row_data[NOTIFICATION_Fields::senderId];
                            $notify['notification_expiry_date']=$row_data[NOTIFICATION_Fields::notificationExpiryDate];
                            $notify['created_date']=$row_data[NOTIFICATION_Fields::created];
                            $notify['modified_date']=$row_data[NOTIFICATION_Fields::modified];
                            $notify['is_testdata']=$row_data[NOTIFICATION_Fields::isTestdata];
                            $notify['is_delete']=$row_data[NOTIFICATION_Fields::isDelete];
                            array_push($NotificationList,$notify);
                            // array_push($notificationIds,$row_data['id']);
                            $update_stmt = $this->connection->prepare("Update ". TABLE_NOTIFICATION ." set ".NOTIFICATION_Fields::readRecipients." = ? , ".NOTIFICATION_Fields::modified." = ? where ".NOTIFICATION_Fields::id." = ?");
                            $update_stmt -> bind_param("ssi",$userId,$modifiedDate,$row_data['id']);
                            $update_stmt ->execute();
                        }
                        // update Receiption
                        
                        // $clause = implode(',', array_fill(0, count($notificationIds), '?'));
                        // $types = str_repeat('i', count($notificationIds));
                        // $params = array($types);
                        // foreach ($notificationIds as $key => $value) {
                        //     $params[] = &$notificationIds[$key];
                        // }
                        // $update_stmt = $this->connection->prepare("Update ". TABLE_NOTIFICATION ." set ".NOTIFICATION_Fields::readRecipients." = ? , ".NOTIFICATION_Fields::modified." = ? where ".NOTIFICATION_Fields::id." IN (".$clause.")");
                        // $update_stmt->execute(array(
                        //     NOTIFICATION_Fields::readRecipients => $userId, 
                        //     NOTIFICATION_Fields::modified => $modifiedDate,
                        //     NOTIFICATION_Fields::id => $params));
                        // $update_stmt -> bind_param("ss",$userId,$modifiedDate);
                        // call_user_func_array(array($update_stmt, 'bind_param'), $params);
                        // if ($update_stmt ->execute())
                        // {
                        //     echo 'if-->';
                        //     echo $update_stmt->error;
                        //     $update_stmt->close();
                        // } else {
                        //     echo 'err-->';
                        //     echo $update_stmt->error;
                        // }
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'Notification return successfully!';
                        $data[DATA] = $NotificationList;
                        $data[TOTAL_NO_RECORDS] = $TotalNoOfNotification;
                        return $data;  
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = DEFAULT_NO_RECORDS;
                        return $data;  
                    }
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;  
                }
            }
        }

        public function createAcceptNotification($senderId,$receiverId,$isTestData){
            // echo $senderId.'----',$receiverId;
            $currentDate = date('Y-m-d H:i:s');
            $push_msg = 'accept your request';
            $sixMonth = date('Y-m-d', strtotime("+6 months", strtotime($currentDate)));;
            $Token = [];
            $senderName = '';
            $notificationType = 'accept_request';
            //get token
            $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_APP_TOKENS,APP_TOKENS_Fields::userId, APP_TOKENS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("i", $receiverId);
                $selectStmt->execute();
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    while($row_data = $stmt_result->fetch_assoc()) {
                        if($row_data[APP_TOKENS_Fields::device_token] != null && $row_data[APP_TOKENS_Fields::device_token] != '123456'){
                            array_push($Token,$row_data[APP_TOKENS_Fields::device_token]);
                        }
                    }
                    // $requestarr = fetch_assoc_all_values($selectStmt);
                    // $Token = [$requestarr[APP_TOKENS_Fields::device_token]];
                }
            }
            //get sender user name
            $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USERS,USERS_Fields::id, USERS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("i", $senderId);
                $selectStmt->execute();
                $selectStmt->store_result();
                if ($selectStmt->num_rows > 0) {
                    $users = fetch_assoc_all_values($selectStmt);
                    // $senderName = $users[USERS_Fields::firstName].' '.$users[USERS_Fields::lastName];
                    $senderName = $users[USERS_Fields::nickName];
                }
            }
            $Message = $senderName.' '.$push_msg;
            $sendNotify = new FCM();
            $finalarray = array(
                'title'=>'Accept Request',
                'body'=> $Message
            );
            $dataArray = [];
            $sendPushNotification = $sendNotify->send_fcm_notify($Token, true, $finalarray, $dataArray);
            $res = json_decode($sendPushNotification, true);
            if($res['success'] > 0){
                // store notification
                $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`,`%s`,`%s`", NOTIFICATION_Fields::notificationType,NOTIFICATION_Fields::notificationMsg,
                NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::senderId,NOTIFICATION_Fields::notificationExpiryDate,NOTIFICATION_Fields::created,NOTIFICATION_Fields::modified,NOTIFICATION_Fields::isTestdata);
                $insertQuery = "INSERT INTO " . TABLE_NOTIFICATION . " ($insertFields) values(?,?,?,?,?,?,?,?)";
                if ($insertReqStmt = $this->connection->prepare($insertQuery)) {
                    $insertReqStmt->bind_param("sssssssi", $notificationType, $push_msg, $receiverId, $senderId, $sixMonth, $currentDate, $currentDate, $isTestData);
                    $insertReqStmt->execute();
                    // echo $insertReqStmt->error;
                    $insertReqStmt->close();
                    // echo 'success';
                    //success
                } else {
                    // echo 'error';
                    //error
                }
            }
        }

        public function createSentNotification($senderId,$receiverId,$isTestData) {
            $currentDate = date('Y-m-d H:i:s');
            $push_msg = 'sent you request';
            $sixMonth = date('Y-m-d', strtotime("+6 months", strtotime($currentDate)));;
            $Token = [];
            $senderName = '';
            $notificationType = 'sent_request';
            //get token
            $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_APP_TOKENS,APP_TOKENS_Fields::userId, APP_TOKENS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("i", $receiverId);
                $selectStmt->execute();
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    while($row_data = $stmt_result->fetch_assoc()) {
                        if($row_data[APP_TOKENS_Fields::device_token] != null && $row_data[APP_TOKENS_Fields::device_token] != '123456'){
                            array_push($Token,$row_data[APP_TOKENS_Fields::device_token]);
                        }
                    }
                    // $requestarr = fetch_assoc_all_values($selectStmt);
                    // $Token = [$requestarr[APP_TOKENS_Fields::device_token]];
                }
            }
            //get sender user name
            $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USERS,USERS_Fields::id, USERS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("i", $senderId);
                $selectStmt->execute();
                $selectStmt->store_result();
                if ($selectStmt->num_rows > 0) {
                    $users = fetch_assoc_all_values($selectStmt);
                    // $senderName = $users[USERS_Fields::firstName].' '.$users[USERS_Fields::lastName];
                    $senderName = $users[USERS_Fields::nickName];
                }
            }
            $Message = $senderName.' '.$push_msg;
            $sendNotify = new FCM();
            $finalarray = array(
                'title'=>'Sent Request',
                'body'=> $Message
            );
            $dataArray = [];
            $sendPushNotification = $sendNotify->send_fcm_notify($Token, true, $finalarray, $dataArray);
            $res = json_decode($sendPushNotification, true);
            if($res['success'] > 0){
                // store notification
                $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`,`%s`,`%s`", NOTIFICATION_Fields::notificationType,NOTIFICATION_Fields::notificationMsg,
                NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::senderId,NOTIFICATION_Fields::notificationExpiryDate,NOTIFICATION_Fields::created,NOTIFICATION_Fields::modified,NOTIFICATION_Fields::isTestdata);
                $insertQuery = "INSERT INTO " . TABLE_NOTIFICATION . " ($insertFields) values(?,?,?,?,?,?,?,?)";
                if ($insertReqStmt = $this->connection->prepare($insertQuery)) {
                    $insertReqStmt->bind_param("sssssssi", $notificationType, $push_msg, $receiverId, $senderId, $sixMonth, $currentDate, $currentDate, $isTestData);
                    $insertReqStmt->execute();
                    // echo $insertReqStmt->error;
                    $insertReqStmt->close();
                    //success
                } else {
                    // echo 'error';
                    //error
                }
            }
        }

        // $tokenStmtData = ['cyTMMkfUzk_LrkcJEE62J0:APA91bHuorUx-S2cONYPnuIAhaxeR8U0yv0FDdErSRHEhgsjnjMVlZM0ddXgU-Sd-J2fdgz6J3iJUl7rpflQxAZoZNHzhxfE_SdV1zVDcGHp-oYFLs9wLdr3Af5_wYI-ZEkp3uJ-d4rL'];
            // $finalarray = array(
            //     'title'=>'Test',
            //     'body'=>'This is just for testing.'
            // );
            // $sendNotify = new FCM();
            // $dataArray = [];
            // $sendPush = $sendNotify->send_fcm_notify($tokenStmtData, true, $finalarray, $dataArray);
            // exit;
    }
?>