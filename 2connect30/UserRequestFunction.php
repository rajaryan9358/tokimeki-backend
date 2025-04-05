<?php
    include_once 'Notification.php';
    class UserRequestFunction
    {
        protected $connection;
    
        function __construct(mysqli $con)
        {
            $this->connection = $con;
        }

        public function call_service($service, $postData)
        {
            switch ($service) {
                case "userDetails":
                    {
                        return $this->getUserDetailsById($postData);
                    }
                    break;
                case "sendRequest":
                    {
                        return $this->sendUserRequest($postData);
                    }
                    break;
                case "getReceivedRequest":
                    {
                        return $this->getListOfReceivedRequest($postData);
                    }   
                    break;
                case "getSendRequest":
                    {
                        return $this->getListOfSendRequest($postData);
                    }
                    break;
                case "acceptRejectRequest":
                    {
                        return $this->AcceptRejectRequest($postData);
                    }
                    break;
                case "getUserMtachList":
                    {
                        return $this->getUserMatchList($postData);
                    }
                    break;
                case "getMyConnection":
                    {
                        return $this->getMyConnection($postData);
                    }
                    break;
                case "getUserDetailsById":
                    {
                        return $this->getOtherUserDetailsById($postData);
                    }
                    break;
                case "cancelRequest":
                    {
                        return $this->cancelRequestByUserId($postData);
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

        public function getUserDetailsById($postData){
            $userId = validateObject($postData,param_userid, "");
            if ($userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("i", $userId);
                if ($selectUserStmt->execute()) {
                    $selectUserStmt->store_result();
                    if ($selectUserStmt->num_rows > 0) {
                        $users = fetch_assoc_all_values($selectUserStmt);
                        $subscription_plan = new stdClass();
                        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=%s", TABLE_SUBSCRIPTION , SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectQueryStmt = $this->connection->prepare($query)) {
                            $selectQueryStmt->bind_param("i", $users[Users_Fields::id]);
                            $selectQueryStmt->execute();
                            $selectQueryStmt->store_result();
                            if ($selectQueryStmt->num_rows > 0) {
                                $subscription_plan = fetch_assoc_all_values($selectQueryStmt);
                            }
                            $selectQueryStmt->close();
                        }
                        $users['subscription_plan'] = $subscription_plan;
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User details get successfully!';
                        $data[DATA] = $users;
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'There is no user belong to this userid!';
                        return $data;
                    }
                }
            }
            else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;
            }
        }

        public function sendUserRequest($postData){
            $senderId = validateObject($postData,param_senderId, "");
            $receiverId = validateObject($postData,param_receiverId, "");
            $isTestData = validateObject($postData,param_is_testdata, "");
            if ($senderId == "" || $receiverId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $currentDate = date('Y-m-d H:i:s');
            $status = "pending";
            // check for already sent request
            $selectQuery = sprintf("SELECT * FROM %s WHERE %s=? AND %s=? AND %s=? AND %s='%s'", TABLE_USER_REQUEST, USERREQUEST_Fields::senderId, USERREQUEST_Fields::receiverId, USERREQUEST_Fields::status, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("iis", $senderId,$receiverId,$status);
                if ($selectUserStmt->execute()) {
                    $selectUserStmt->store_result();
                    if ($selectUserStmt->num_rows > 0) {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'You have already sent request to this user!';
                        return $data;
                    } else {    

                        $selectQuery = sprintf("SELECT * FROM %s WHERE %s=? AND %s=?", TABLE_USER_REQUEST, USERREQUEST_Fields::senderId, USERREQUEST_Fields::receiverId);
                        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                            $selectUserStmt->bind_param("ii", $senderId,$receiverId);
                            if ($selectUserStmt->execute()) {
                                $selectUserStmt->store_result();
                                if ($selectUserStmt->num_rows > 0) {
                                    $usersRequest = fetch_assoc_all_values($selectUserStmt);
                                    $isDel = $usersRequest[USERREQUEST_Fields::isDelete];
                                    $isDelete = 0;
                                    $userRequestId = $usersRequest[USERREQUEST_Fields::id];
                                    if($isDel == 1){
                                        $update_stmt = $this ->connection -> prepare ("Update ". TABLE_USER_REQUEST ." set ".USERREQUEST_Fields::isDelete." = ? , ".USERREQUEST_Fields::modified." = ? where ".USERREQUEST_Fields::id." = ?");
                                        $update_stmt -> bind_param("isi",$isDelete,$currentDate,$userRequestId);
                                        if ($update_stmt ->execute())
                                        {
                                            $usersRequest[USERREQUEST_Fields::isDelete] = $isDelete;
                                            $update_stmt->close();
                                            $data[STATUS] = SUCCESS;
                                            $data[MESSAGE] = 'User request sent successfully!';
                                            $data[DATA] = $usersRequest;
                                            return $data;
                                        } else {
                                            $data[STATUS] = FAILED;
                                            $data[MESSAGE] = SOMETHING_WRONG;
                                            return $data;
                                        }
                                    } else {
                                        // update with pending status
                                        $update_stmt = $this ->connection -> prepare ("Update ". TABLE_USER_REQUEST ." set ".USERREQUEST_Fields::status." = ? , ".USERREQUEST_Fields::isDelete." = ? , ".USERREQUEST_Fields::modified." = ? where ".USERREQUEST_Fields::id." = ?");
                                        $update_stmt -> bind_param("sisi",$status,$isDelete,$currentDate,$userRequestId);
                                        if ($update_stmt ->execute())
                                        {
                                            $usersRequest[USERREQUEST_Fields::isDelete] = $isDelete;
                                            $usersRequest[USERREQUEST_Fields::status] = $status;
                                            $update_stmt->close();
                                            $data[STATUS] = SUCCESS;
                                            $data[MESSAGE] = 'User request sent successfully!';
                                            $data[DATA] = $usersRequest;
                                            return $data;
                                        } else {
                                            $data[STATUS] = FAILED;
                                            $data[MESSAGE] = SOMETHING_WRONG;
                                            return $data;
                                        }
                                    }
                                } else {
                                    // insert new record
                                    $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`", 
                                    USERREQUEST_Fields::senderId,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::status,USERREQUEST_Fields::created,USERREQUEST_Fields::modified,USERREQUEST_Fields::isTestdata);
                                    $insertQuery = "INSERT INTO " . TABLE_USER_REQUEST . " ($insertFields) values(?,?,?,?,?,?)";
                                    if ($insertReqStmt = $this->connection->prepare($insertQuery)) {
                                        $insertReqStmt->bind_param("iisssi", $senderId, $receiverId, $status, $currentDate, $currentDate, $isTestData);
                                        if ($insertReqStmt->execute()) {
                                            $userRequest_inserted_id = $insertReqStmt->insert_id;
                                            $insertReqStmt->close();
                                            //get user request data
                                            $usersRequest = [];
                                            $selectQuery = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s' AND %s=?", TABLE_USER_REQUEST, USERREQUEST_Fields::id,Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE,USERREQUEST_Fields::isTestdata);
                                            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                                                $selectUserStmt->bind_param("ii", $userRequest_inserted_id,$isTestData);
                                                if ($selectUserStmt->execute()) {
                                                    $selectUserStmt->store_result();
                                                    if ($selectUserStmt->num_rows > 0) {
                                                        $usersRequest = fetch_assoc_all_values($selectUserStmt);
                                                        // sent notification
                                                        $acceptNotification = new Notification($GLOBALS['con']);
                                                        $sentAcceptNotification = $acceptNotification->createSentNotification($senderId,$receiverId,$isTestData);
                                                        $data[STATUS] = SUCCESS;
                                                        $data[MESSAGE] = "User request sent successfully!";
                                                        $data[DATA] = $usersRequest;
                                                        return $data;
                                                    }
                                                }
                                            }
                                        
                                        } else {
                                            $data[STATUS] = FAILED;
                                            // $data[MESSAGE] = $insertReqStmt->error;
                                            $data[MESSAGE] = SOMETHING_WRONG;
                                            return $data;
                                        }
                                    } else {
                                        $data[STATUS] = FAILED;
                                        $data[MESSAGE] = SOMETHING_WRONG;
                                        return $data;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            
            
        }

        public function getListOfReceivedRequest($postData){
            $userId = validateObject($postData,param_userid, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $status = 'pending';
            $TotalNoOfRecords = 0;
            if ($userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            //get no of records
            $query = sprintf("SELECT * FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s=? AND %s.%s='%s'", TABLE_USER_REQUEST ,TABLE_USERS, USERREQUEST_Fields::senderId, TABLE_USERS,USERS_Fields::id,
            USERREQUEST_Fields::receiverId, USERREQUEST_Fields::status, TABLE_USER_REQUEST, USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("is", $userId,$status);
                $selectStmt->execute();
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $TotalNoOfRecords = $stmt_result->num_rows;
                }
            }

            $query = sprintf("SELECT *,user_request.id as requestId FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s=? AND %s.%s='%s' limit ?,?", TABLE_USER_REQUEST ,TABLE_USERS, USERREQUEST_Fields::senderId, TABLE_USERS,USERS_Fields::id,
            USERREQUEST_Fields::receiverId, USERREQUEST_Fields::status, TABLE_USER_REQUEST, USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("isii", $userId,$status,$start,$limit);
                $selectStmt->execute();
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $receiverUser = [];
                    while($row_data = $stmt_result->fetch_assoc()) {  
                        $userCity = '';
                        $qry = sprintf("SELECT *, MAX(%s) FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s.%s='%s'", USERCITY_Fields::stayPercentage, TABLE_USER_CITY, TABLE_CITY,
                        USERCITY_Fields::cityId,TABLE_CITY,CITY_Fields::id,USERCITY_Fields::userId ,TABLE_USER_CITY,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectcityStmt = $this->connection->prepare($qry)) {
                            $selectcityStmt->bind_param("i", $row_data['receiver_id']);
                            $selectcityStmt->execute();
                            $selectcityStmt->store_result();
                            if ($selectcityStmt->num_rows > 0) {
                                $userMaxPerCity = fetch_assoc_all_values($selectcityStmt);
                                $userCity = $userMaxPerCity[CITY_Fields::cityName];
                            }
                            $selectcityStmt->close();
                        }
                        $receiver['requestid']=$row_data['requestId'];
                        $receiver['userid']=$row_data['id'];
                        $receiver['first_name']=$row_data['first_name'];
                        $receiver['last_name']=$row_data['last_name'];
                        $receiver['nick_name']=$row_data['nick_name'];
                        $receiver['profile_image']=$row_data['profile_image'];
                        $receiver['avtar_name']=$row_data['avtar_name'];
                        $receiver['city_name']=$userCity;
                        array_push($receiverUser,$receiver);
                    }
                    $selectStmt->close();
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "Received request return successfully.";
                    $data[DATA] = $receiverUser;
                    $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = DEFAULT_NO_RECORDS;
                    return $data;
                }
                $selectStmt->close();
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data; 
            }
        }

        public function getListOfSendRequest($postData){

            $userId = validateObject($postData,param_userid, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $status = 'pending';
            $TotalNoOfRecords = 0;
            if ($userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            //get no of records
            $query = sprintf("SELECT * FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s=? AND %s.%s='%s'", TABLE_USER_REQUEST ,TABLE_USERS, USERREQUEST_Fields::receiverId, TABLE_USERS,USERS_Fields::id,
            USERREQUEST_Fields::senderId, USERREQUEST_Fields::status, TABLE_USER_REQUEST ,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("is", $userId,$status);
                $selectStmt->execute();
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $TotalNoOfRecords = $stmt_result->num_rows;
                }
            }

            $query = sprintf("SELECT *, user_request.id as requestid FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s=? AND %s.%s='%s' limit ?,?", TABLE_USER_REQUEST ,TABLE_USERS, USERREQUEST_Fields::receiverId, TABLE_USERS,USERS_Fields::id,
            USERREQUEST_Fields::senderId,USERREQUEST_Fields::status , TABLE_USER_REQUEST,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("isii", $userId,$status,$start,$limit);
                $selectStmt->execute();
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $receiverUser = [];
                    while($row_data = $stmt_result->fetch_assoc()) {
                        $userCity = '';
                        $qry = sprintf("SELECT *, MAX(%s) FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s.%s='%s'", USERCITY_Fields::stayPercentage, TABLE_USER_CITY, TABLE_CITY,
                        USERCITY_Fields::cityId,TABLE_CITY,CITY_Fields::id,USERCITY_Fields::userId ,TABLE_USER_CITY,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectcityStmt = $this->connection->prepare($qry)) {
                            $selectcityStmt->bind_param("i", $row_data['sender_id']);
                            $selectcityStmt->execute();
                            $selectcityStmt->store_result();
                            if ($selectcityStmt->num_rows > 0) {
                                $userMaxPerCity = fetch_assoc_all_values($selectcityStmt);
                                $userCity = $userMaxPerCity[CITY_Fields::cityName];
                            }
                            $selectcityStmt->close();
                        }
                        $receiver['userid']=$row_data['id'];
                        $receiver['requestid']=$row_data['requestid'];
                        $receiver['first_name']=$row_data['first_name'];
                        $receiver['last_name']=$row_data['last_name'];
                        $receiver['nick_name']=$row_data['nick_name'];
                        $receiver['profile_image']=$row_data['profile_image'];
                        $receiver['avtar_name']=$row_data['avtar_name'];
                        $receiver['city_name']=$userCity;
                        array_push($receiverUser,$receiver);
                    }
                    $selectStmt->close();
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "Received request return successfully.";
                    $data[DATA] = $receiverUser;
                    $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = DEFAULT_NO_RECORDS;
                    return $data;
                }
                $selectStmt->close();
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data; 
            }
        }

        public function AcceptRejectRequest($postData){
            $requestId = validateObject($postData,param_requestid, "");
            $requestType = validateObject($postData,param_request_type, "");
            $isTestData = validateObject($postData,param_is_testdata, "");
            if ($requestId == "" || $requestType == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $currentDate = date('Y-m-d H:i:s');
            $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_REQUEST,USERREQUEST_Fields::id, USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($query)) {
                $selectStmt->bind_param("i", $requestId);
                $selectStmt->execute();
                $selectStmt->store_result();
                if ($selectStmt->num_rows > 0) {
                    $requestarr = fetch_assoc_all_values($selectStmt);
                    $senderId = $requestarr[USERREQUEST_Fields::receiverId];
                    $receiverId = $requestarr[USERREQUEST_Fields::senderId];
                    $update_stmt = $this ->connection -> prepare ("Update ". TABLE_USER_REQUEST ." set ".USERREQUEST_Fields::status." = ? , ".USERREQUEST_Fields::modified." = ? where ".USERREQUEST_Fields::id." = ?");
                    $update_stmt -> bind_param("ssi",$requestType,$currentDate,$requestId);
                    if ($update_stmt ->execute())
                    {
                        $update_stmt->close();
                        if($requestType == 'accept'){
                            //send notification for accept
                            $acceptNotification = new Notification($GLOBALS['con']);
                            $sentAcceptNotification = $acceptNotification->createAcceptNotification($senderId,$receiverId,$isTestData);
                        }
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'user request '.$requestType.' successfully!';
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = SOMETHING_WRONG;
                        return $data;
                    }
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = "There is no request belong to this id!";
                    return $data;
                }
            }
        }

        public function getUserMatchList($postData)
        {
            $userId = validateObject($postData,param_userid, "");
            $subscribePlan = validateObject($postData,param_subscribe_plan, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $isTestData = validateObject($postData,param_is_testdata, "");
            $TotalNoOfRecords = 0;
            $unreadNotificationCount = 0;
            if ($userId == "" || $subscribePlan == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            // check user is there or not
            $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("i", $userId);
                if ($selectUserStmt->execute()) {
                    $selectUserStmt->store_result();
                    if ($selectUserStmt->num_rows == 0) {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'There is no user belong to this userid!';
                        return $data;
                    }
                }
            }
            // get user max and min point as per plan
            $planPointQry = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s'", TABLE_USER_POINTS, USERPOINTS_Fields::userId,USERPOINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectPointStmt = $this->connection->prepare($planPointQry)) {
                $selectPointStmt->bind_param("i", $userId);
                if ($selectPointStmt->execute()) {
                    $selectPointStmt->store_result();
                    if ($selectPointStmt->num_rows > 0) {
                        $userPoints = fetch_assoc_all_values($selectPointStmt);
                        $userPoint = $userPoints[USERPOINTS_Fields::point];
                        $qry =  sprintf("SELECT * FROM %s WHERE  %s <= ? AND %s >= ? AND %s='%s'",TABLE_POINTS, POINTS_Fields::minPoint,POINTS_Fields::maxPoint,POINTS_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
                        if ($selectPointStmt = $this->connection->prepare($qry)) {
                            $selectPointStmt->bind_param("ii", $userPoint,$userPoint);
                            if ($selectPointStmt->execute()) {
                                $selectPointStmt->store_result();
                                if ($selectPointStmt->num_rows != 0) {
                                    $points = fetch_assoc_all_values($selectPointStmt);
                                    $minPoint = $points[POINTS_Fields::minPoint];
                                    $maxPoint = $points[POINTS_Fields::maxPoint];  
                                    $userPlan = $points[POINTS_Fields::applicablePlan];  
                                } else {
                                    // manual condition
                                    $minPoint = 0;
                                    $maxPoint = 0;
                                    $userPlan = 'High';  
                                    if($userPoint > 46){
                                        $minPoint = 36;
                                        $maxPoint = 46;  
                                    }
                                    else if($userPoint >=36 && $userPoint <= 46){
                                        $minPoint = 36;
                                        $maxPoint = 46;  
                                    } else if($userPoint >=25 && $userPoint <= 35){
                                        $minPoint = 25;
                                        $maxPoint = 35;  
                                    } else if($userPoint >=14 && $userPoint <= 24){
                                        $minPoint = 14;
                                        $maxPoint = 24;  
                                    }
                                }
                            }
                        } else {
                            $data[STATUS] = FAILED;
                            $data[MESSAGE] = SOMETHING_WRONG;
                            return $data;  
                        }
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = SOMETHING_WRONG;
                        return $data;  
                    }
                    // get Total Number of records
                    $query = sprintf("SELECT %s.*,%s.%s FROM %s INNER JOIN %s ON %s.%s = %s.%s LEFT JOIN %s ON %s.%s = %s.%s AND %s.%s = ? AND %s.%s='%s' 
                    LEFT JOIN %s AS ur_sender ON ur_sender.%s = %s.%s AND ur_sender.%s = ? AND ur_sender.%s = '%s' LEFT JOIN %s AS ur_receiver ON ur_receiver.%s = %s.%s AND ur_receiver.%s = ? AND ur_receiver.%s = '%s'
                    WHERE %s.%s BETWEEN ? AND ? AND %s.%s='%s' AND %s.%s = ? AND %s.%s != ? AND %s.%s IS NULL AND ur_sender.%s IS NULL AND ur_receiver.%s IS NULL ORDER BY %s.%s DESC",
                    TABLE_USERS,TABLE_USER_POINTS,USERPOINTS_Fields::point,TABLE_USERS,TABLE_USER_POINTS,TABLE_USER_POINTS,USERPOINTS_Fields::userId,TABLE_USERS,USERS_Fields::id,
                    TABLE_REJECT_MATCH,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::otherUserId,TABLE_USERS,USERS_Fields::id,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::userId,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::isDelete,DELETE_STATUS::NOT_DELETE,
                    TABLE_USER_REQUEST,USERREQUEST_Fields::receiverId,TABLE_USERS,USERS_Fields::id,USERREQUEST_Fields::senderId,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE,TABLE_USER_REQUEST,USERREQUEST_Fields::senderId,TABLE_USERS,USERS_Fields::id,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE,
                    TABLE_USER_POINTS,USERPOINTS_Fields::point,TABLE_USERS,USERS_Fields::isDelete, DELETE_STATUS::NOT_DELETE,TABLE_USERS,USERS_Fields::isTestdata,TABLE_USERS,USERS_Fields::id,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::id,USERREQUEST_Fields::id, USERREQUEST_Fields::id,TABLE_USERS,USERS_Fields::created);
                    if($selectStmt = $this->connection->prepare($query)){
                        $selectStmt->bind_param("iiiiiii", $userId,$userId,$userId,$minPoint,$maxPoint,$isTestData,$userId);
                        if ($selectStmt->execute()) {
                            $stmt_result = $selectStmt->get_result();
                            $TotalNoOfRecords = $stmt_result->num_rows;
                        } else {
                            $data[STATUS] = FAILED;
                            $data[MESSAGE] = SOMETHING_WRONG;
                            return $data;  
                        }
                    }
                    $query = sprintf("SELECT %s.*,%s.%s,ur_sender.%s FROM %s INNER JOIN %s ON %s.%s = %s.%s LEFT JOIN %s ON %s.%s = %s.%s AND %s.%s = ? AND %s.%s='%s' 
                    LEFT JOIN %s AS ur_sender ON ur_sender.%s = %s.%s AND ur_sender.%s = ? AND ur_sender.%s = '%s' LEFT JOIN %s AS ur_receiver ON ur_receiver.%s = %s.%s AND ur_receiver.%s = ? AND ur_receiver.%s = '%s'
                    WHERE %s.%s BETWEEN ? AND ? AND %s.%s='%s' AND %s.%s = ? AND %s.%s != ? AND %s.%s IS NULL AND ur_sender.%s IS NULL AND ur_receiver.%s IS NULL ORDER BY %s.%s DESC limit ?,?",
                    TABLE_USERS,TABLE_USER_POINTS,USERPOINTS_Fields::point,USERREQUEST_Fields::status,TABLE_USERS,TABLE_USER_POINTS,TABLE_USER_POINTS,USERPOINTS_Fields::userId,TABLE_USERS,USERS_Fields::id,
                    TABLE_REJECT_MATCH,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::otherUserId,TABLE_USERS,USERS_Fields::id,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::userId,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::isDelete,DELETE_STATUS::NOT_DELETE,
                    TABLE_USER_REQUEST,USERREQUEST_Fields::receiverId,TABLE_USERS,USERS_Fields::id,USERREQUEST_Fields::senderId,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE,TABLE_USER_REQUEST,USERREQUEST_Fields::senderId,TABLE_USERS,USERS_Fields::id,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE,
                    TABLE_USER_POINTS,USERPOINTS_Fields::point,TABLE_USERS,USERS_Fields::isDelete, DELETE_STATUS::NOT_DELETE,TABLE_USERS,USERS_Fields::isTestdata,TABLE_USERS,USERS_Fields::id,TABLE_REJECT_MATCH,REJECT_MATCH_Fields::id,USERREQUEST_Fields::id, USERREQUEST_Fields::id,TABLE_USERS,USERS_Fields::created);
                    if($selectStmt = $this->connection->prepare($query)){
                        $selectStmt->bind_param("iiiiiiiii", $userId,$userId,$userId,$minPoint,$maxPoint,$isTestData,$userId,$start,$limit);
                        if ($selectStmt->execute()) {
                            $stmt_result = $selectStmt->get_result();
                            if ($stmt_result->num_rows > 0) {
                                $users = [];
                                while($row_data = $stmt_result->fetch_assoc()) {
                                    $userCity = '';
                                    $qry = sprintf("SELECT *, MAX(%s) FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s.%s='%s'", USERCITY_Fields::stayPercentage, TABLE_USER_CITY, TABLE_CITY,
                                    USERCITY_Fields::cityId,TABLE_CITY,CITY_Fields::id,USERCITY_Fields::userId ,TABLE_USER_CITY,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                    if ($selectcityStmt = $this->connection->prepare($qry)) {
                                        $selectcityStmt->bind_param("i", $row_data[USERS_Fields::id]);
                                        $selectcityStmt->execute();
                                        $selectcityStmt->store_result();
                                        if ($selectcityStmt->num_rows > 0) {
                                            $userMaxPerCity = fetch_assoc_all_values($selectcityStmt);
                                            $userCity = $userMaxPerCity[CITY_Fields::cityName];
                                        }
                                        $selectcityStmt->close();
                                    } // usercity
                                     // get conversation for user
                                     $conversationsArr = [];
                                     $convo_query = sprintf("SELECT * FROM %s WHERE %s = ? or %s = ? AND %s = '%s'",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
                                     if ($selectConvoStmt = $this->connection->prepare($convo_query)) {
                                         $selectConvoStmt->bind_param("ii", $row_data[USERS_Fields::id],$row_data[USERS_Fields::id]);
                                         $selectConvoStmt->execute();
                                         $stmt_res = $selectConvoStmt->get_result();
                                        if ($stmt_res->num_rows > 0) {
                                            $checkSenderId = [];
                                            $checkReceiverId = [];
                                            while($convo = $stmt_res->fetch_assoc()) {
                                                if($convo[CONVERSTIONS_Fields::senderId] == $row_data[USERS_Fields::id]){
                                                    array_push($checkSenderId,$convo);
                                                }
                                                if($convo[CONVERSTIONS_Fields::receiverId] == $row_data[USERS_Fields::id]){
                                                    array_push($checkReceiverId,$convo);
                                                }
                                            }
                                            if(sizeof($checkSenderId) > 0){
                                                foreach ($checkSenderId as $value) {
                                                    if($value['receiver_id'] == $userId && $value['sender_id'] == $row_data[USERS_Fields::id]){
                                                        array_push($conversationsArr,$value);
                                                    }
                                                }
                                            }
                                            if(sizeof($checkReceiverId) > 0){
                                                foreach ($checkReceiverId as $value) {
                                                    if($value['sender_id'] == $userId && $value['receiver_id'] == $row_data[USERS_Fields::id]){
                                                        array_push($conversationsArr,$value);
                                                    }
                                                }
                                            }
                                        }
                                        $selectConvoStmt->close();
                                    } // conversation
                                    $matchUser['userid']=$row_data[USERS_Fields::id];
                                    $matchUser['point']=$row_data[USERPOINTS_Fields::point];
                                    $matchUser['email_id']=$row_data[USERS_Fields::emailId];
                                    $matchUser['first_name']=$row_data[USERS_Fields::firstName];
                                    $matchUser['last_name']=$row_data[USERS_Fields::lastName];
                                    $matchUser['nick_name']=$row_data[USERS_Fields::nickName];
                                    $matchUser['profile_image']=$row_data[USERS_Fields::profileImage];
                                    $matchUser['avtar_name']=$row_data[USERS_Fields::avtarName];
                                    $matchUser['date_of_birth']=$row_data[USERS_Fields::dateOfBirth];
                                    $matchUser['marital_status']=$row_data[USERS_Fields::maritialStatus];
                                    $matchUser['city_name']=$userCity;
                                    $matchUser['request_status']=$row_data[USERS_Fields::maritialStatus];
                                    $matchUser['conversation']=$conversationsArr;
                                    $matchUser['userPlan']=$userPlan;
                                    array_push($users,$matchUser);
                                }
                                $TodayDate = date('Y-m-d');
                                $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s > ? AND %s IS NULL AND %s='%s'", TABLE_NOTIFICATION, NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::notificationExpiryDate,NOTIFICATION_Fields::readRecipients,NOTIFICATION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                if ($selectcityStmt = $this->connection->prepare($qry)) {
                                    $selectcityStmt->bind_param("ss",$userId,$TodayDate);
                                    $selectcityStmt->execute();
                                    $selectcityStmt->store_result();
                                    if ($selectcityStmt->num_rows > 0) {
                                        $unreadNotificationCount = $selectcityStmt->num_rows;
                                    }
                                }
                                $data[STATUS] = SUCCESS;
                                $data[MESSAGE] = "Match userlist get successfully!";
                                $data[DATA] = $users;
                                $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                                $data['UnreadNotificationCount'] = $unreadNotificationCount;
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
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;  
            }
        }

        public function getUserMatchListOld2($postData)
        {
            $userId = validateObject($postData,param_userid, "");
            $subscribePlan = validateObject($postData,param_subscribe_plan, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $TotalNoOfRecords = 0;
            $unreadNotificationCount = 0;
            if ($userId == "" || $subscribePlan == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("i", $userId);
                if ($selectUserStmt->execute()) {
                    $selectUserStmt->store_result();
                    if ($selectUserStmt->num_rows == 0) {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'There is no user belong to this userid!';
                        return $data;
                    }
                }
            }
            $userContact = [];
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) AND %s='%s'",TABLE_USER_REQUEST,USERREQUEST_Fields::senderId,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("ii", $userId,$userId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row = $stmt_result->fetch_assoc()) {
                            array_push($userContact,$row);
                        }
                    }
                }
            }
            $RejectContact = [];
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) AND %s='%s'",TABLE_REJECT_MATCH,REJECT_MATCH_Fields::userId,REJECT_MATCH_Fields::otherUserId,REJECT_MATCH_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("ii", $userId,$userId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row = $stmt_result->fetch_assoc()) {
                            array_push($RejectContact,$row);
                        }
                    }
                }
            }
            $planPointQry = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s'", TABLE_USER_POINTS, USERPOINTS_Fields::userId,USERPOINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectPointStmt = $this->connection->prepare($planPointQry)) {
                $selectPointStmt->bind_param("i", $userId);
                if ($selectPointStmt->execute()) {
                    $selectPointStmt->store_result();
                    if ($selectPointStmt->num_rows > 0) {
                        $userPoints = fetch_assoc_all_values($selectPointStmt);
                        $userPoint = $userPoints[USERPOINTS_Fields::point];
                        $minPoint = 0;
                        $maxPoint = 0;
                        if($userPoint > 46){
                            $minPoint = 36;
                            $maxPoint = 46;  
                        }
                        else if($userPoint >=36 && $userPoint <= 46){
                            $minPoint = 36;
                            $maxPoint = 46;  
                        } else if($userPoint >=25 && $userPoint <= 35){
                            $minPoint = 25;
                            $maxPoint = 35;  
                        } else if($userPoint >=14 && $userPoint <= 24){
                            $minPoint = 14;
                            $maxPoint = 24;  
                        }
                        $query = sprintf("SELECT * FROM %s INNER JOIN %s ON %s.%s = %s.%s WHERE %s BETWEEN ? AND ? AND %s != ? AND %s.%s='%s' ORDER BY %s.%s DESC",TABLE_USER_POINTS,TABLE_USERS,TABLE_USER_POINTS,
                        USERPOINTS_Fields::userId,TABLE_USERS,USERCITY_Fields::id,USERPOINTS_Fields::point,USERPOINTS_Fields::userId,TABLE_USER_POINTS,USERPOINTS_Fields::isDelete,DELETE_STATUS::NOT_DELETE,TABLE_USER_POINTS,USERPOINTS_Fields::created);
                        if($selectStmt = $this->connection->prepare($query)){
                            $selectStmt->bind_param("iii", $minPoint,$maxPoint,$userId);
                            if ($selectStmt->execute()) {
                                $stmt_result = $selectStmt->get_result();
                                if ($stmt_result->num_rows > 0) {
                                    $NoOfRecords = 0;
                                    while($row_data = $stmt_result->fetch_assoc()) {
                                        $currentContact = [];
                                        $currentRejectContact = [];
                                        if(sizeof($userContact) > 0){
                                            foreach ($userContact as $contact) {
                                                $contactIndex = array_search($row_data['user_id'], $contact);
                                                if ($contactIndex !== false) {
                                                    $currentContact = $contact;
                                                    break;
                                                }
                                            }
                                        }
                                        $reqStatus = sizeof($currentContact) > 0 ? $currentContact['status'] : 'idel';
                                        if(sizeof($RejectContact) > 0){
                                            foreach ($RejectContact as $contact) {
                                                $contactIndex = array_search($row_data['user_id'], $contact);
                                                if ($contactIndex !== false) {
                                                    $currentRejectContact = $contact;
                                                    break;
                                                }
                                            }
                                        }
                                        if($reqStatus != 'accept' && sizeof($currentContact) == 0 && sizeof($currentRejectContact) == 0){
                                            $NoOfRecords++;
                                        }
                                    }
                                    $TotalNoOfRecords = $NoOfRecords;
                                }
                            }
                        }
                        $query = sprintf("SELECT * FROM %s INNER JOIN %s ON %s.%s = %s.%s WHERE %s BETWEEN ? AND ? AND %s != ? AND %s.%s='%s' ORDER BY %s.%s DESC limit ?,?",
                        TABLE_USER_POINTS,TABLE_USERS,TABLE_USER_POINTS,USERPOINTS_Fields::userId,TABLE_USERS,USERCITY_Fields::id,USERPOINTS_Fields::point,USERPOINTS_Fields::userId,TABLE_USER_POINTS,USERPOINTS_Fields::isDelete,DELETE_STATUS::NOT_DELETE,TABLE_USER_POINTS,USERPOINTS_Fields::created);
                        if($selectStmt = $this->connection->prepare($query)){
                            $selectStmt->bind_param("iiiii", $minPoint,$maxPoint,$userId,$start,$limit);
                            if ($selectStmt->execute()) {
                                $stmt_result = $selectStmt->get_result();
                                if ($stmt_result->num_rows > 0) {
                                    $users = [];
                                    while($row_data = $stmt_result->fetch_assoc()) {
                                        $userCity = '';
                                        $currentContact = [];
                                        $currentRejectContact = [];
                                        $qry = sprintf("SELECT *, MAX(%s) FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s.%s='%s'", USERCITY_Fields::stayPercentage, TABLE_USER_CITY, TABLE_CITY,
                                        USERCITY_Fields::cityId,TABLE_CITY,CITY_Fields::id,USERCITY_Fields::userId ,TABLE_USER_CITY,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                        if ($selectcityStmt = $this->connection->prepare($qry)) {
                                            $selectcityStmt->bind_param("i", $row_data['user_id']);
                                            $selectcityStmt->execute();
                                            $selectcityStmt->store_result();
                                            if ($selectcityStmt->num_rows > 0) {
                                                $userMaxPerCity = fetch_assoc_all_values($selectcityStmt);
                                                $userCity = $userMaxPerCity[CITY_Fields::cityName];
                                            }
                                            $selectcityStmt->close();
                                        } // usercity
                                        if(sizeof($userContact) > 0){
                                            foreach ($userContact as $contact) {
                                                $contactIndex = array_search($row_data['user_id'], $contact);
                                                if ($contactIndex !== false) {
                                                    $currentContact = $contact;
                                                    break;
                                                }
                                            }
                                            // echo array_search($row_data['user_id'],$userContact);
                                        }
                                        if(sizeof($RejectContact) > 0){
                                            foreach ($RejectContact as $contact) {
                                                $contactIndex = array_search($row_data['user_id'], $contact);
                                                if ($contactIndex !== false) {
                                                    $currentRejectContact = $contact;
                                                    break;
                                                }
                                            }
                                        }
                                        // get conversation for user
                                        $conversationsArr = [];
                                        $convo_query = sprintf("SELECT * FROM %s WHERE %s = ? or %s = ? AND %s = '%s'",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
                                        if ($selectConvoStmt = $this->connection->prepare($convo_query)) {
                                            $selectConvoStmt->bind_param("ii", $row_data['user_id'],$row_data['user_id']);
                                            $selectConvoStmt->execute();
                                            $stmt_res = $selectConvoStmt->get_result();
                                            if ($stmt_res->num_rows > 0) {
                                                $checkSenderId = [];
                                                $checkReceiverId = [];
                                                while($convo = $stmt_res->fetch_assoc()) {
                                                    if($convo[CONVERSTIONS_Fields::senderId] == $row_data['user_id']){
                                                        array_push($checkSenderId,$convo);
                                                    }
                                                    if($convo[CONVERSTIONS_Fields::receiverId] == $row_data['user_id']){
                                                        array_push($checkReceiverId,$convo);
                                                    }
                                                }
                                                if(sizeof($checkSenderId) > 0){
                                                    foreach ($checkSenderId as $value) {
                                                        if($value['receiver_id'] == $userId && $value['sender_id'] == $row_data['user_id']){
                                                            array_push($conversationsArr,$value);
                                                        }
                                                    }
                                                }
                                                if(sizeof($checkReceiverId) > 0){
                                                    foreach ($checkReceiverId as $value) {
                                                        if($value['sender_id'] == $userId && $value['receiver_id'] == $row_data['user_id']){
                                                            array_push($conversationsArr,$value);
                                                        }
                                                    }
                                                }
                                            }
                                            $selectConvoStmt->close();
                                        } // conversation
                                        $reqStatus = sizeof($currentContact) > 0 ? $currentContact['status'] : 'idel';
                                        if($reqStatus != 'accept' && sizeof($currentContact) == 0 && sizeof($currentRejectContact) == 0){
                                            $matchUser['userid']=$row_data['user_id'];
                                            $matchUser['point']=$row_data['point'];
                                            $matchUser['email_id']=$row_data['email_id'];
                                            $matchUser['first_name']=$row_data['first_name'];
                                            $matchUser['last_name']=$row_data['last_name'];
                                            $matchUser['nick_name']=$row_data['nick_name'];
                                            $matchUser['profile_image']=$row_data['profile_image'];
                                            $matchUser['avtar_name']=$row_data['avtar_name'];
                                            $matchUser['date_of_birth']=$row_data['date_of_birth'];
                                            $matchUser['marital_status']=$row_data['marital_status'];
                                            $matchUser['city_name']=$userCity;
                                            $matchUser['request_status']=$reqStatus;
                                            $matchUser['conversation']=$conversationsArr;
                                            array_push($users,$matchUser);
                                        }
                                    }
                                    $TodayDate = date('Y-m-d');
                                    $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s > ? AND %s IS NULL AND %s='%s'", TABLE_NOTIFICATION, NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::notificationExpiryDate,NOTIFICATION_Fields::readRecipients,NOTIFICATION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                    if ($selectcityStmt = $this->connection->prepare($qry)) {
                                        $selectcityStmt->bind_param("ss",$userId,$TodayDate);
                                        $selectcityStmt->execute();
                                        $selectcityStmt->store_result();
                                        if ($selectcityStmt->num_rows > 0) {
                                            $unreadNotificationCount = $selectcityStmt->num_rows;
                                        }
                                    }

                                    $data[STATUS] = SUCCESS;
                                    $data[MESSAGE] = "Match userlist get successfully!";
                                    $data[DATA] = $users;
                                    $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                                    $data['UnreadNotificationCount'] = $unreadNotificationCount;
                                    return $data;
                                }
                            }
                        }
                    }
                }
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;  
            }
        }

        public function getOtherUserDetailsById($postData){
            $userId = validateObject($postData,param_userid, "");
            $LoginUserId = validateObject($postData,param_login_userid, "");
            if ($userId == "" || $LoginUserId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $userContact = [];
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) AND %s='%s'",TABLE_USER_REQUEST,USERREQUEST_Fields::senderId,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("ii", $LoginUserId,$LoginUserId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row = $stmt_result->fetch_assoc()) {
                            array_push($userContact,$row);
                        }
                    }
                }
            }
            $fields = ''.Users_Fields::id.' as userid,'.Users_Fields::avtarName.','.Users_Fields::appleId.','.Users_Fields::dateOfBirth.','.Users_Fields::emailId.','.Users_Fields::firstName.','.Users_Fields::lastName.','.Users_Fields::nickName.',
            '.Users_Fields::googleId.','.Users_Fields::profileImage.','.Users_Fields::isActive.','.Users_Fields::isVerify.','.Users_Fields::isSubscribe.','.Users_Fields::maritialStatus.','.Users_Fields::created.','.Users_Fields::modified.','.Users_Fields::isDelete.','.Users_Fields::isTestdata.'';
            $selectQuery = sprintf("SELECT %s FROM %s WHERE  %s=? AND %s='%s'", $fields, TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("i", $userId);
                if ($selectUserStmt->execute()) {
                    $selectUserStmt->store_result();
                    if ($selectUserStmt->num_rows > 0) {
                        $users = fetch_assoc_all_values($selectUserStmt);

                        //get user city 
                        $userCity = '';
                        $qry = sprintf("SELECT *, MAX(%s) FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s.%s='%s'", USERCITY_Fields::stayPercentage, TABLE_USER_CITY, TABLE_CITY,
                        USERCITY_Fields::cityId,TABLE_CITY,CITY_Fields::id,USERCITY_Fields::userId ,TABLE_USER_CITY,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectcityStmt = $this->connection->prepare($qry)) {
                            $selectcityStmt->bind_param("i", $users['userid']);
                            $selectcityStmt->execute();
                            $selectcityStmt->store_result();
                            if ($selectcityStmt->num_rows > 0) {
                                $userMaxPerCity = fetch_assoc_all_values($selectcityStmt);
                                $userCity = $userMaxPerCity[CITY_Fields::cityName];
                            }
                            $selectcityStmt->close();
                        }
                        // request status
                        $currentContact = [];
                        if(sizeof($userContact) > 0){
                            foreach ($userContact as $contact) {
                                $contactIndex = array_search($users['userid'], $contact);
                                if ($contactIndex !== false) {
                                    $currentContact = $contact;
                                    break;
                                }
                            }
                        }
                        //conversation
                        $conversationsArr = [];
                        $convo_query = sprintf("SELECT * FROM %s WHERE %s = ? or %s = ? AND %s = '%s'",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
                        if ($selectConvoStmt = $this->connection->prepare($convo_query)) {
                            $selectConvoStmt->bind_param("ii", $users['userid'],$users['userid']);
                            $selectConvoStmt->execute();
                            $stmt_res = $selectConvoStmt->get_result();
                            if ($stmt_res->num_rows > 0) {
                                $checkSenderId = [];
                                $checkReceiverId = [];
                                while($convo = $stmt_res->fetch_assoc()) {
                                    if($convo[CONVERSTIONS_Fields::senderId] == $users['userid']){
                                        array_push($checkSenderId,$convo);
                                    }
                                    if($convo[CONVERSTIONS_Fields::receiverId] == $users['userid']){
                                        array_push($checkReceiverId,$convo);
                                    }
                                }
                                if(sizeof($checkSenderId) > 0){
                                    foreach ($checkSenderId as $value) {
                                        if($value['receiver_id'] == $LoginUserId && $value['sender_id'] == $users['userid']){
                                            array_push($conversationsArr,$value);
                                        }
                                    }
                                }
                                if(sizeof($checkReceiverId) > 0){
                                    foreach ($checkReceiverId as $value) {
                                        if($value['sender_id'] == $LoginUserId && $value['receiver_id'] == $users['userid']){
                                            array_push($conversationsArr,$value);
                                        }
                                    }
                                }
                            }
                            $selectConvoStmt->close();
                        }

                        $users[CITY_Fields::cityName] = $userCity;
                        $users['request_status']=sizeof($currentContact) > 0 ? $currentContact['status'] : 'idel';
                        $users['conversation']=$conversationsArr;
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User details get successfully!';
                        $data[DATA] = $users;
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'There is no user belong to this userid!';
                        return $data;
                    }
                }
            }
            else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;
            }
        }

        public function getUserMatchListOld($postData)
        {
            $userId = validateObject($postData,param_userid, "");
            $subscribePlan = validateObject($postData,param_subscribe_plan, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $TotalNoOfRecords = 0;
            $unreadNotificationCount = 0;
            if ($userId == "" || $subscribePlan == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $userContact = [];
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) AND %s='%s'",TABLE_USER_REQUEST,USERREQUEST_Fields::senderId,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("ii", $userId,$userId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row = $stmt_result->fetch_assoc()) {
                            array_push($userContact,$row);
                        }
                    }
                }
            }
            $planPointQry = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s'", TABLE_POINTS, POINTS_Fields::applicablePlan,POINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectPointStmt = $this->connection->prepare($planPointQry)) {
                $selectPointStmt->bind_param("s", $subscribePlan);
                if ($selectPointStmt->execute()) {
                    $selectPointStmt->store_result();
                    if ($selectPointStmt->num_rows > 0) {
                        $points = fetch_assoc_all_values($selectPointStmt);
                        $minPoint = $points[POINTS_Fields::minPoint];
                        $maxPoint = $points[POINTS_Fields::maxPoint];
                        // Total record
                        $query = sprintf("SELECT * FROM %s INNER JOIN %s ON %s.%s = %s.%s WHERE %s BETWEEN ? AND ? AND %s != ? AND %s.%s='%s' ORDER BY %s.%s DESC",TABLE_USER_POINTS,TABLE_USERS,TABLE_USER_POINTS,
                        USERPOINTS_Fields::userId,TABLE_USERS,USERCITY_Fields::id,USERPOINTS_Fields::point,USERPOINTS_Fields::userId,TABLE_USER_POINTS,USERPOINTS_Fields::isDelete,DELETE_STATUS::NOT_DELETE,TABLE_USER_POINTS,USERPOINTS_Fields::created);
                        if($selectStmt = $this->connection->prepare($query)){
                            $selectStmt->bind_param("iii", $minPoint,$maxPoint,$userId);
                            if ($selectStmt->execute()) {
                                $stmt_result = $selectStmt->get_result();
                                if ($stmt_result->num_rows > 0) {
                                    $TotalNoOfRecords = $stmt_result->num_rows;
                                }
                            }
                        }

                        $query = sprintf("SELECT * FROM %s INNER JOIN %s ON %s.%s = %s.%s WHERE %s BETWEEN ? AND ? AND %s != ? AND %s.%s='%s' ORDER BY %s.%s DESC limit ?,?",
                        TABLE_USER_POINTS,TABLE_USERS,TABLE_USER_POINTS,USERPOINTS_Fields::userId,TABLE_USERS,USERCITY_Fields::id,USERPOINTS_Fields::point,USERPOINTS_Fields::userId,TABLE_USER_POINTS,USERPOINTS_Fields::isDelete,DELETE_STATUS::NOT_DELETE,TABLE_USER_POINTS,USERPOINTS_Fields::created);
                        if($selectStmt = $this->connection->prepare($query)){
                            $selectStmt->bind_param("iiiii", $minPoint,$maxPoint,$userId,$start,$limit);
                            if ($selectStmt->execute()) {
                                $stmt_result = $selectStmt->get_result();
                                if ($stmt_result->num_rows > 0) {
                                    $users = [];
                                    while($row_data = $stmt_result->fetch_assoc()) {
                                        $userCity = '';
                                        $currentContact = [];
                                        $qry = sprintf("SELECT *, MAX(%s) FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s.%s='%s'", USERCITY_Fields::stayPercentage, TABLE_USER_CITY, TABLE_CITY,
                                        USERCITY_Fields::cityId,TABLE_CITY,CITY_Fields::id,USERCITY_Fields::userId ,TABLE_USER_CITY,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                        if ($selectcityStmt = $this->connection->prepare($qry)) {
                                            $selectcityStmt->bind_param("i", $row_data['user_id']);
                                            $selectcityStmt->execute();
                                            $selectcityStmt->store_result();
                                            if ($selectcityStmt->num_rows > 0) {
                                                $userMaxPerCity = fetch_assoc_all_values($selectcityStmt);
                                                $userCity = $userMaxPerCity[CITY_Fields::cityName];
                                            }
                                            $selectcityStmt->close();
                                        }
                                        if(sizeof($userContact) > 0){
                                            foreach ($userContact as $contact) {
                                                $contactIndex = array_search($row_data['user_id'], $contact);
                                                if ($contactIndex !== false) {
                                                    $currentContact = $contact;
                                                    break;
                                                }
                                            }
                                            // echo array_search($row_data['user_id'],$userContact);
                                        }
                                        // get converstion for user
                                        $conversationsArr = [];
                                        $convo_query = sprintf("SELECT * FROM %s WHERE %s = ? or %s = ? AND %s = '%s'",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
                                        if ($selectConvoStmt = $this->connection->prepare($convo_query)) {
                                            $selectConvoStmt->bind_param("ii", $row_data['user_id'],$row_data['user_id']);
                                            $selectConvoStmt->execute();
                                            $stmt_res = $selectConvoStmt->get_result();
                                            if ($stmt_res->num_rows > 0) {
                                                $checkSenderId = [];
                                                $checkReceiverId = [];
                                                while($convo = $stmt_res->fetch_assoc()) {
                                                    if($convo[CONVERSTIONS_Fields::senderId] == $row_data['user_id']){
                                                        array_push($checkSenderId,$convo);
                                                    }
                                                    if($convo[CONVERSTIONS_Fields::receiverId] == $row_data['user_id']){
                                                        array_push($checkReceiverId,$convo);
                                                    }
                                                }
                                                if(sizeof($checkSenderId) > 0){
                                                    foreach ($checkSenderId as $value) {
                                                        if($value['receiver_id'] == $userId && $value['sender_id'] == $row_data['user_id']){
                                                            array_push($conversationsArr,$value);
                                                        }
                                                    }
                                                }
                                                if(sizeof($checkReceiverId) > 0){
                                                    foreach ($checkReceiverId as $value) {
                                                        if($value['sender_id'] == $userId && $value['receiver_id'] == $row_data['user_id']){
                                                            array_push($conversationsArr,$value);
                                                        }
                                                    }
                                                }
                                            }
                                            $selectConvoStmt->close();
                                        }
                                        $matchUser['user_id']=$row_data['user_id'];
                                        $matchUser['point']=$row_data['point'];
                                        $matchUser['email_id']=$row_data['email_id'];
                                        $matchUser['first_name']=$row_data['first_name'];
                                        $matchUser['last_name']=$row_data['last_name'];
                                        $matchUser['nick_name']=$row_data['nick_name'];
                                        $matchUser['profile_image']=$row_data['profile_image'];
                                        $matchUser['avtar_name']=$row_data['avtar_name'];
                                        $matchUser['date_of_birth']=$row_data['date_of_birth'];
                                        $matchUser['marital_status']=$row_data['marital_status'];
                                        $matchUser['city_name']=$userCity;
                                        $matchUser['request_status']=sizeof($currentContact) > 0 ? $currentContact['status'] : 'idel';
                                        $matchUser['conversation']=$conversationsArr;
                                        array_push($users,$matchUser);
                                    }
                                    
                                    $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s IS NULL AND %s='%s'", TABLE_NOTIFICATION, NOTIFICATION_Fields::receiverId,NOTIFICATION_Fields::readRecipients,NOTIFICATION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                    if ($selectcityStmt = $this->connection->prepare($qry)) {
                                        $selectcityStmt->bind_param("s",$userId);
                                        $selectcityStmt->execute();
                                        $selectcityStmt->store_result();
                                        if ($selectcityStmt->num_rows > 0) {
                                            $unreadNotificationCount = $selectcityStmt->num_rows;
                                        }
                                    }

                                    $data[STATUS] = SUCCESS;
                                    $data[MESSAGE] = "Match userlist get successfully!";
                                    $data[DATA] = $users;
                                    $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                                    $data['UnreadNotificationCount'] = $unreadNotificationCount;
                                    return $data;  
                                }
                                else {
                                    $data[STATUS] = FAILED;
                                    $data[MESSAGE] = DEFAULT_NO_RECORDS;
                                    return $data;  
                                }
                            }
                        } else {
                            $data[STATUS] = FAILED;
                            $data[MESSAGE] = SOMETHING_WRONG;
                            return $data;  
                        }
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'There is no user belong to this userid!';
                        return $data;
                    }
                }
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;  
            }
            // SELECT COUNT(*) AS count FROM user_points WHERE point BETWEEN ? AND ? AND user_id != 24 AND is_delete=0
        }

        function findObjectById($id,$array){
            foreach ( $array as $element ) {
                if ( $id == $element['userid'] ) {
                    return 'true';
                } else if($id == $element['other_user_id']){
                    return 'true';
                } 
            }
            return 'false';
        }

        public function getMyConnection($postData){
            $userId = validateObject($postData,param_userid, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $status = 'accept';
            $TotalNoOfRecords = 0;
            $BlockUsers = [];
            if ($userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }

            $qry = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) AND %s='%s'",TABLE_USER_BLOCKLIST,USER_BLOCKLIST_Fields::userId, USER_BLOCKLIST_Fields::otherUserId,USER_BLOCKLIST_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($qry)){
                $selectStmt->bind_param("ii", $userId,$userId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row_data = $stmt_result->fetch_assoc()) {
                            array_push($BlockUsers,$row_data);
                        }
                    }
                }
            }
            // print_r($BlockUsers);
            // echo '<br>';
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) And %s = ? AND %s='%s'",TABLE_USER_REQUEST,USERREQUEST_Fields::senderId,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::status,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("iis", $userId,$userId,$status);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $TotalNoOfRecords = 0;
                        // $TotalNoOfRecords = $stmt_result->num_rows;
                        while($row_data = $stmt_result->fetch_assoc()) {
                            $uid = '';
                            if($row_data['sender_id'] == $userId){
                                $uid = $row_data['receiver_id'];
                            } else {
                                $uid = $row_data['sender_id'];
                            }
                            $isBlock = $this->findObjectById($uid, $BlockUsers);
                            if($isBlock == 'false'){
                                $TotalNoOfRecords++;
                            }
                        }
                    }
                }
            }
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) AND %s = ? AND %s='%s' limit ?,?",TABLE_USER_REQUEST,USERREQUEST_Fields::senderId,USERREQUEST_Fields::receiverId,USERREQUEST_Fields::status,USERREQUEST_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("iisii", $userId,$userId,$status,$start,$limit);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $myContact = [];
                        while($row_data = $stmt_result->fetch_assoc()) {
                            $uid = '';
                            if($row_data['sender_id'] == $userId){
                                $uid = $row_data['receiver_id'];
                            } else {
                                $uid = $row_data['sender_id'];
                            }
                            $users = [];
                            $qry = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s'",TABLE_USERS,USERS_Fields::id,USERS_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
                            if($selectUserStmt = $this->connection->prepare($qry)){
                                $selectUserStmt->bind_param("i", $uid);
                                if ($selectUserStmt->execute()) {
                                    $selectUserStmt->store_result();
                                    if ($selectUserStmt->num_rows > 0) {
                                        $users = fetch_assoc_all_values($selectUserStmt);
                                        $selectUserStmt->close();
                                    }
                                }
                            }
                            $userCity = '';
                            $qry = sprintf("SELECT *, MAX(%s) FROM %s INNER JOIN %s ON %s = %s.%s WHERE %s=? AND %s.%s='%s'", USERCITY_Fields::stayPercentage, TABLE_USER_CITY, TABLE_CITY,
                            USERCITY_Fields::cityId,TABLE_CITY,CITY_Fields::id,USERCITY_Fields::userId ,TABLE_USER_CITY,USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                            if ($selectcityStmt = $this->connection->prepare($qry)) {
                                $selectcityStmt->bind_param("i", $uid);
                                $selectcityStmt->execute();
                                $selectcityStmt->store_result();
                                if ($selectcityStmt->num_rows > 0) {
                                    $userMaxPerCity = fetch_assoc_all_values($selectcityStmt);
                                    $userCity = $userMaxPerCity[CITY_Fields::cityName];
                                }
                                $selectcityStmt->close();
                            }

                            // get converstion for user
                            $conversationsArr = [];
                            $convo_query = sprintf("SELECT * FROM %s WHERE %s = ? or %s = ? AND %s = '%s'",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE);
                            if ($selectConvoStmt = $this->connection->prepare($convo_query)) {
                                $selectConvoStmt->bind_param("ii",$uid,$uid);
                                $selectConvoStmt->execute();
                                $stmt_res = $selectConvoStmt->get_result();
                                if ($stmt_res->num_rows > 0) {
                                    $checkSenderId = [];
                                    $checkReceiverId = [];
                                    while($convo = $stmt_res->fetch_assoc()) {
                                        if($convo[CONVERSTIONS_Fields::senderId] == $uid){
                                            array_push($checkSenderId,$convo);
                                        }
                                        if($convo[CONVERSTIONS_Fields::receiverId] == $uid){
                                            array_push($checkReceiverId,$convo);
                                        }
                                    }
                                    if(sizeof($checkSenderId) > 0){
                                        foreach ($checkSenderId as $value) {
                                            if($value['receiver_id'] == $userId && $value['sender_id'] == $uid){
                                                array_push($conversationsArr,$value);
                                            }
                                        }
                                    }
                                    if(sizeof($checkReceiverId) > 0){
                                        foreach ($checkReceiverId as $value) {
                                            if($value['sender_id'] == $userId && $value['receiver_id'] == $uid){
                                                array_push($conversationsArr,$value);
                                            }
                                        }
                                    }
                                }
                                $selectConvoStmt->close();
                            }


                            $matchUser['userid']=$users[USERS_Fields::id];
                            $matchUser['first_name']=$users[USERS_Fields::firstName];
                            $matchUser['last_name']=$users[USERS_Fields::lastName];
                            $matchUser['nick_name']=$users[USERS_Fields::nickName];
                            $matchUser['profile_image']=$users[USERS_Fields::profileImage];
                            $matchUser['avtar_name']=$users[USERS_Fields::avtarName];
                            $matchUser['city_name']=$userCity;
                            $matchUser['conversation']=$conversationsArr;
                            $isBlock = $this->findObjectById($uid, $BlockUsers);
                            if($isBlock == 'false'){
                                array_push($myContact,$matchUser);
                            }
                        }
                        $selectStmt->close();
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = "My contact return successfully!";
                        $data[DATA] = $myContact;
                        $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                        return $data;  
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = DEFAULT_NO_RECORDS;
                        return $data;  
                    }
                }
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;  
            }
        }

        public function cancelRequestByUserId($postData){
            $RequestId = validateObject($postData,param_requestid, "");
            if ($RequestId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }

            // check user request is exists or not.
            $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USER_REQUEST, USERREQUEST_Fields::id, USERREQUEST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("i", $RequestId);
                if ($selectUserStmt->execute()) {
                    $selectUserStmt->store_result();
                    if ($selectUserStmt->num_rows > 0) {
                        $isDelete = 1;
                        $currentDate = date('Y-m-d H:i:s');
                        $update_stmt = $this ->connection -> prepare ("Update ". TABLE_USER_REQUEST ." set ".USERREQUEST_Fields::isDelete." = ? , ".USERREQUEST_Fields::modified." = ? where ".USERREQUEST_Fields::id." = ?");
                        $update_stmt -> bind_param("isi",$isDelete,$currentDate,$RequestId);
                        if ($update_stmt ->execute())
                        {
                            $update_stmt->close();
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'Request cancel successfully!';
                            return $data;
                        } else {
                            $data[STATUS] = FAILED;
                            $data[MESSAGE] = SOMETHING_WRONG;
                            return $data;  
                        }
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'There is no user request belong to this requestid!';
                        return $data;
                    }
                }
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;
            }
        }
    }
?>