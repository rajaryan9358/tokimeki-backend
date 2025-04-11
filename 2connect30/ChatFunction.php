<?php
    class ChatFunction
    {
        protected $connection;
    
        function __construct(mysqli $con)
        {
            $this->connection = $con;
            $this->connection-> set_charset("utf8mb4");
        }

        public function call_service($service, $postData)
        {
            switch ($service) {
                case "getConversationList":
                    {
                        return $this->getConversationList($postData);
                    }
                    break;
                case "createGetChat":
                    {
                        return $this->createGetChat($postData);
                    }
                    break;
                case "getMessageList":
                    {
                        return $this->getMessageList($postData);
                    }
                    break;
                case "chatmsgFileupload": 
                    {
                        return $this->chatmsgFileupload($postData);
                    }
                    break;
                case "deleteConversation":
                    {
                        return $this->deleteConversation($postData);
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

        public function getConversationList($postData){
            $userId = validateObject($postData, param_userid, "");
            $start = validateObject($postData, param_start, "");
            $limit = validateObject($postData, param_limit, "");
            $isDebug = validateObject($postData, "isdebug", false);
        
            if ($userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
        
            $TotalNumberOfChatConverstion = 0;
            $ConversationList = [];
        
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? AND %s = '%s' OR %s = ? AND %s = '%s') AND %s='%s' ORDER BY %s DESC",
                TABLE_CONVERSATIONS,
                CONVERSTIONS_Fields::senderId, CONVERSTIONS_Fields::isDeleteBySender, DELETE_STATUS::NOT_DELETE,
                CONVERSTIONS_Fields::receiverId, CONVERSTIONS_Fields::isDeleteByReceiver, DELETE_STATUS::NOT_DELETE,
                CONVERSTIONS_Fields::isDelete, DELETE_STATUS::NOT_DELETE,
                CONVERSTIONS_Fields::created
            );
        
        
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("ii", $userId, $userId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $TotalNumberOfChatConverstion = $stmt_result->num_rows;
                    }
                    $selectStmt->close();
                }
            }
        
            // New query without unreadMessageCount
            $qry = sprintf("SELECT c.* FROM %s AS c 
                    LEFT JOIN %s ub ON (
                        (ub.%s = ? AND ub.%s = c.%s) OR
                        (ub.%s = ? AND ub.%s = c.%s) OR
                        (ub.%s = ? AND ub.%s = c.%s) OR
                        (ub.%s = ? AND ub.%s = c.%s)
                    ) AND ub.%s = '1'
                    WHERE 
                        (c.%s = ? AND c.%s = '%s' OR c.%s = ? AND c.%s = '%s') 
                        AND c.%s = '0' 
                    ORDER BY c.%s DESC 
                    LIMIT ?,?",
                    TABLE_CONVERSATIONS,
                    TABLE_USER_BLOCKLIST,
                    USER_BLOCKLIST_Fields::userId, USER_BLOCKLIST_Fields::otherUserId, CONVERSTIONS_Fields::senderId,
                    USER_BLOCKLIST_Fields::otherUserId, USER_BLOCKLIST_Fields::userId, CONVERSTIONS_Fields::senderId,
                    USER_BLOCKLIST_Fields::otherUserId, USER_BLOCKLIST_Fields::userId, CONVERSTIONS_Fields::receiverId,
                    USER_BLOCKLIST_Fields::userId, USER_BLOCKLIST_Fields::otherUserId, CONVERSTIONS_Fields::receiverId,
                    USER_BLOCKLIST_Fields::isDelete,
                    CONVERSTIONS_Fields::senderId, CONVERSTIONS_Fields::isDeleteBySender, DELETE_STATUS::NOT_DELETE,
                    CONVERSTIONS_Fields::receiverId, CONVERSTIONS_Fields::isDeleteByReceiver, DELETE_STATUS::NOT_DELETE,
                    CONVERSTIONS_Fields::isDelete,
                    CONVERSTIONS_Fields::created
                );
        
        
            if ($selectConversationStmt = $this->connection->prepare($qry)) {
                $selectConversationStmt->bind_param("iiiiiiii", $userId, $userId, $userId, $userId, $userId, $userId, $start, $limit);
        
                if ($selectConversationStmt->execute()) {
                    $stmt_result = $selectConversationStmt->get_result();
        
                    if ($stmt_result->num_rows > 0) {
                        while($row_data = $stmt_result->fetch_assoc()) {
                            $newUserId = ($row_data[CONVERSTIONS_Fields::senderId] == $userId) ? $row_data[CONVERSTIONS_Fields::receiverId] : $row_data[CONVERSTIONS_Fields::senderId];
        
                            $selectQuery = sprintf("SELECT * FROM %s WHERE %s = ?", TABLE_USERS, Users_Fields::id);
                            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                                $selectUserStmt->bind_param("i", $newUserId);
                                if ($selectUserStmt->execute()) {
                                    $selectUserStmt->store_result();
                                    if ($selectUserStmt->num_rows > 0) {
                                        $users = fetch_assoc_all_values($selectUserStmt);
                                    }
                                }
                            }
        
                            $con = [];
                            $con['id'] = $row_data[CONVERSTIONS_Fields::id];
                            $con['sender_id'] = $row_data[CONVERSTIONS_Fields::senderId];
                            $con['receiver_id'] = $row_data[CONVERSTIONS_Fields::receiverId];
                            $con['created_date'] = $row_data[CONVERSTIONS_Fields::created];
                            $con['modified_date'] = $row_data[CONVERSTIONS_Fields::modified];
                            $con['is_testdata'] = $row_data[CONVERSTIONS_Fields::isTestdata];
                            $con['is_delete'] = $row_data[CONVERSTIONS_Fields::isDelete];
        
                            $con['userid'] = $users[USERS_Fields::id];
                            $con['nick_name'] = $users[USERS_Fields::nickName];
                            $con['avtar_name'] = $users[USERS_Fields::avtarName];
                            $con['profile_image'] = $users[USERS_Fields::profileImage];
                            $con['is_user_delete'] = $users[USERS_Fields::isDelete];
        
                            array_push($ConversationList, $con);
                        }
        
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'ConverstionList returned successfully!';
                        $data[DATA] = $ConversationList;
                        $data[TOTAL_NO_RECORDS] = $TotalNumberOfChatConverstion;
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
        

        public function getMessageList($postData){
            $conversionId = validateObject($postData,param_converstionid, "");
            $userId = validateObject($postData,param_userid, "");
            // $start = validateObject($postData,param_start, "");
            // $limit = validateObject($postData,param_limit, "");
            if ($conversionId == "" || $userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $TotalNumberOfMessage = 0;
            $MessagesList = [];
            $query = sprintf("SELECT * FROM %s WHERE %s = ?",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::id);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("i", $conversionId);
                if ($selectStmt->execute()) {
                    // $stmt_result = $selectStmt->get_result();
                    $selectStmt->store_result();
                    if ($selectStmt->num_rows > 0) {
                        $converstionData = fetch_assoc_all_values($selectStmt);
                        $sender = $converstionData[CONVERSTIONS_Fields::senderId];
                        $receiver = $converstionData[CONVERSTIONS_Fields::receiverId];
                        if($sender == $userId){
                            $query = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s' AND %s='%s' ORDER BY %s ASC ",TABLE_CHAT_MESSAGE,CHATMESSAGE_Fields::conversationId,CHATMESSAGE_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::isDeleteBySender,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::created);
                            if($selectStmt = $this->connection->prepare($query)){
                                $selectStmt->bind_param("i", $conversionId);
                                if ($selectStmt->execute()) {
                                    $stmt_result = $selectStmt->get_result();
                                    if ($stmt_result->num_rows > 0) {
                                        $TotalNumberOfMessage = $stmt_result->num_rows;
                                    }
                                    $selectStmt->close();
                                }
                            }
                            $selectQuery = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s' AND %s='%s' ORDER BY %s ASC ",TABLE_CHAT_MESSAGE,CHATMESSAGE_Fields::conversationId,CHATMESSAGE_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::isDeleteBySender,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::created);
                        } else if($receiver == $userId){
                            $query = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s' AND %s='%s' ORDER BY %s ASC ",TABLE_CHAT_MESSAGE,CHATMESSAGE_Fields::conversationId,CHATMESSAGE_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::isDeleteByReceiver,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::created);
                            if($selectStmt = $this->connection->prepare($query)){
                                $selectStmt->bind_param("i", $conversionId);
                                if ($selectStmt->execute()) {
                                    $stmt_result = $selectStmt->get_result();
                                    if ($stmt_result->num_rows > 0) {
                                        $TotalNumberOfMessage = $stmt_result->num_rows;
                                    }
                                    $selectStmt->close();
                                }
                            }
                            $selectQuery = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s' AND %s='%s' ORDER BY %s ASC ",TABLE_CHAT_MESSAGE,CHATMESSAGE_Fields::conversationId,CHATMESSAGE_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::isDeleteByReceiver,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::created);
                        }
                        if($selStmt = $this->connection->prepare($selectQuery)){
                            $selStmt->bind_param("i", $conversionId);
                            if ($selStmt->execute()) {
                                $stmt_result = $selStmt->get_result();
                                if ($stmt_result->num_rows > 0) {
                                    while($row_data = $stmt_result->fetch_assoc()) {
                                        // print_r($row_data);
                                        // exit;
                                        $msg['id']=$row_data[CHATMESSAGE_Fields::id];
                                        $msg['conversation_id']=$row_data[CHATMESSAGE_Fields::conversationId];
                                        $msg['sender_id']=$row_data[CHATMESSAGE_Fields::senderId];
                                        $msg['receiver_id']=$row_data[CHATMESSAGE_Fields::receiverId];
                                        $msg['message_type']=$row_data[CHATMESSAGE_Fields::messageType];
                                        $msg['message']=$row_data[CHATMESSAGE_Fields::message];
                                        $msg['is_read']=$row_data[CHATMESSAGE_Fields::isRead];
                                        $msg['created_date']=$row_data[CHATMESSAGE_Fields::created];
                                        $msg['modified_date']=$row_data[CHATMESSAGE_Fields::modified];
                                        $msg['is_testdata']=$row_data[CHATMESSAGE_Fields::isTestdata];
                                        $msg['is_delete']=$row_data[CHATMESSAGE_Fields::isDelete];
                                        array_push($MessagesList,$msg);
                                    }
                                    $data[STATUS] = SUCCESS;
                                    $data[MESSAGE] = 'MessageList return successfully!';
                                    $data[DATA] = $MessagesList;
                                    $data[TOTAL_NO_RECORDS] = $TotalNumberOfMessage;
                                    return $data;
                                } else {
                                    $data[STATUS] = FAILED;
                                    $data[MESSAGE] = DEFAULT_NO_RECORDS;
                                    return $data;  
                                }
                                $selStmt->close();
                            } else {
                                $data[STATUS] = FAILED;
                                $data[MESSAGE] = SOMETHING_WRONG;
                                return $data;  
                            }
                        }
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

        public function chatmsgFileupload($postData){
            $target_dir = 'upload/ChatMessage';
            // $host  = $_SERVER['HTTP_HOST'];
            // $path   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            // $baseurl = "http://" . $host . $path . "/";
            $target_file = $target_dir . basename($_FILES['attachfile']["name"]);
            $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            $fileName = time().'.'.$fileType;
            $extensions_arr = array("jpg","jpeg","png","gif","pdf");
            // $productUrl = $baseurl. $target_dir .'/'. $fileName;
            if(in_array($fileType,$extensions_arr)) { 
                move_uploaded_file($_FILES['attachfile']['tmp_name'],$target_dir.'/'.$fileName);
                $ImageData = [];
                $file['file_name']=$fileName;
                $file['file_type']='image';
                array_push($ImageData,$file);
                $data[STATUS] = SUCCESS;
                $data[MESSAGE] = 'File uploaded successfully!';
                $data[DATA] = $ImageData;
                return $data;
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = 'FileType is not matched!';
                return $data;
            }
        }

        public function createGetChat($postData) {
            $uid = validateObject($postData, 'uid', "");
            $userId = validateObject($postData, 'userid', "");
        
            if ($uid == "" || $userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
        
            $query = "SELECT * FROM " . TABLE_CONVERSATIONS . " 
                      WHERE (".CONVERSTIONS_Fields::senderId." = ? AND ".CONVERSTIONS_Fields::receiverId." = ?)
                         OR (".CONVERSTIONS_Fields::senderId." = ? AND ".CONVERSTIONS_Fields::receiverId." = ?)";
        
            if ($stmt = $this->connection->prepare($query)) {
                $stmt->bind_param("iiii", $uid, $userId, $userId, $uid);
                if ($stmt->execute()) {
                    $stmt_result = $stmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $conversationData = $stmt_result->fetch_assoc();
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = "Conversation already exists";
                        $data['conversation'] = $conversationData;
                        $stmt->close();
                        return $data;
                    }
                }
                $stmt->close();
            }
        
            // Conversation does not exist, insert new
            $createdAt = date('Y-m-d H:i:s');
            $insertQuery = "INSERT INTO " . TABLE_CONVERSATIONS . " 
                            (". CONVERSTIONS_Fields::lastMessage. ", " . CONVERSTIONS_Fields::senderId . ", " . CONVERSTIONS_Fields::receiverId . ", " . CONVERSTIONS_Fields::created . ", " . CONVERSTIONS_Fields::modified . ", ". CONVERSTIONS_Fields::isTestdata. ") 
                            VALUES (?, ?, ?, ?,?,?)";
        
            if ($insertStmt = $this->connection->prepare($insertQuery)) {
                $lastMessage = "";
                $isTestdata = 1;
                $insertStmt->bind_param("siissi", $lastMessage , $uid, $userId, $createdAt, $createdAt,$isTestdata);
                if ($insertStmt->execute()) {
                    $conversationId = $insertStmt->insert_id;
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "New conversation created";
                    $data['conversation'] = [
                        CONVERSTIONS_Fields::id => $conversationId,
                        CONVERSTIONS_Fields::lastMessage => $$lastMessage,
                        CONVERSTIONS_Fields::senderId => $uid,
                        CONVERSTIONS_Fields::receiverId => $userId,
                        CONVERSTIONS_Fields::created => $createdAt,
                        CONVERSTIONS_Fields::modified => $createdAt
                    ];
                    $insertStmt->close();
                    return $data;
                } else {
                    $insertStmt->close();
                }
            }
        
            // echo "Prepare Error: " . $this->connection->error . PHP_EOL;

            $data[STATUS] = FAILED;
            $data[MESSAGE] = 'Failed to create conversation';
            return $data;
        }

        public function deleteConversation($postData) {
            $conversionId = validateObject($postData,param_converstionid, "");
            $userId = validateObject($postData,param_userid, "");
            if ($conversionId == "" || $userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $query = sprintf("SELECT * FROM %s WHERE %s = ?",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::id);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("i", $conversionId);
                if ($selectStmt->execute()) {
                    // $stmt_result = $selectStmt->get_result();
                    $selectStmt->store_result();
                    $isDelete = 1;
                    $modifiedDate = date('Y-m-d H:i:s');
                    $conversionIdArr = [];
                    if ($selectStmt->num_rows > 0) {
                        $converstionData = fetch_assoc_all_values($selectStmt);
                        $sender = $converstionData[CONVERSTIONS_Fields::senderId];
                        $receiver = $converstionData[CONVERSTIONS_Fields::receiverId];
                        $selectQuery = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s' ORDER BY %s ASC ",TABLE_CHAT_MESSAGE,CHATMESSAGE_Fields::conversationId,CHATMESSAGE_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CHATMESSAGE_Fields::created);
                        if($selStmt = $this->connection->prepare($selectQuery)){
                            $selStmt->bind_param("i", $conversionId);
                            if ($selStmt->execute()) {
                                $stmt_result = $selStmt->get_result();
                                if ($stmt_result->num_rows > 0) {
                                    while($row_data = $stmt_result->fetch_assoc()) {
                                        array_push($conversionIdArr,$row_data[CHATMESSAGE_Fields::id]);
                                    }
                                }
                            }
                        }
                        $clause = implode(',', array_fill(0, count($conversionIdArr), '?'));
                        $types = str_repeat('i', count($conversionIdArr));
                        $params = array($types);
                        foreach ($conversionIdArr as $key => $value) {
                            $params[] = &$conversionIdArr[$key];
                        }
                        if($sender == $userId){
                            $update_stmt = $this->connection->prepare("Update ". TABLE_CONVERSATIONS ." set ".CONVERSTIONS_Fields::isDeleteBySender." = ? , ".CONVERSTIONS_Fields::modified." = ? where ".CONVERSTIONS_Fields::id." = ?");
                            $update_stmt -> bind_param("isi",$isDelete,$modifiedDate,$conversionId);
                            $update_stmt ->execute();
                            
                            // $update_stmt_msg = $this->connection->prepare("Update ".TABLE_CHAT_MESSAGE." set ".CHATMESSAGE_Fields::isDeleteBySender." = ?, ".CHATMESSAGE_Fields::modified." = ? WHERE ".CHATMESSAGE_Fields::id." IN (".$clause.") ");
                            // $update_stmt_msg -> bind_param("is",$isDelete,$modifiedDate);
                            // $update_stmt_msg ->execute();
                            $query = "Update ". TABLE_CHAT_MESSAGE ." set ".CHATMESSAGE_Fields::isDeleteBySender." = ".DELETE_STATUS::IS_DELETE." where id IN (".$clause.")";
                            $update_stmt_msg = $this->connection->prepare($query);
                            call_user_func_array(array($update_stmt_msg, 'bind_param'), $params);
                            if ($update_stmt_msg->execute())
                            {
                                $update_stmt_msg->close();
                            }
                            
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'Conversation deleted successfully!';
                            return $data;
                        } else if($receiver == $userId){
                            $update_stmt = $this->connection->prepare("Update ". TABLE_CONVERSATIONS ." set ".CONVERSTIONS_Fields::isDeleteByReceiver." = ? , ".CONVERSTIONS_Fields::modified." = ? where ".CONVERSTIONS_Fields::id." = ?");
                            $update_stmt -> bind_param("isi",$isDelete,$modifiedDate,$conversionId);
                            $update_stmt ->execute();
                            $query = "Update ". TABLE_CHAT_MESSAGE ." set ".CHATMESSAGE_Fields::isDeleteByReceiver." = ".DELETE_STATUS::IS_DELETE." where id IN (".$clause.")";
                            $update_stmt_msg = $this->connection->prepare($query);
                            call_user_func_array(array($update_stmt_msg, 'bind_param'), $params);
                            if ($update_stmt_msg->execute())
                            {
                                $update_stmt_msg->close();
                            }
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'Conversation deleted successfully!';
                            return $data;
                        } else {
                            $data[STATUS] = FAILED;
                            $data[MESSAGE] = 'Something went wrong!';
                            return $data;
                        }
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'There is a no conversation belong to this COId';
                        return $data;  
                    }
                    $selectStmt->close();
                }
            }
        }
    }
?>