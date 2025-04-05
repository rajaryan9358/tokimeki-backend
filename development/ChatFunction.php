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
            $userId = validateObject($postData,param_userid, "");
            $start = validateObject($postData,param_start, "");
            $limit = validateObject($postData,param_limit, "");
            $isDebug = validateObject($postData, "isdebug", false);
            if ($userId == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $TotalNumberOfChatConverstion = 0;
            $ConversationList = [];
            $query = sprintf("SELECT * FROM %s WHERE (%s = ? AND %s = '%s' OR %s = ? AND %s = '%s') AND %s='%s' ORDER BY %s DESC ",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::senderId, CONVERSTIONS_Fields::isDeleteBySender,DELETE_STATUS::NOT_DELETE,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDeleteByReceiver,DELETE_STATUS::NOT_DELETE,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CONVERSTIONS_Fields::created);
            if($selectStmt = $this->connection->prepare($query)){
                $selectStmt->bind_param("ii", $userId,$userId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $TotalNumberOfChatConverstion = $stmt_result->num_rows;
                    }
                    $selectStmt->close();
                }
            }
            $isRead = 0;
            // echo $qry = sprintf("SELECT * FROM %s WHERE (%s = ? OR %s = ?) AND %s='%s' ORDER BY %s DESC  limit ?,?",TABLE_CONVERSATIONS,CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CONVERSTIONS_Fields::created);
            // $qry = sprintf("SELECT c.*, COUNT(m.id) AS unreadmsg FROM conversations AS c LEFT JOIN chat_messages AS m ON c.id = m.conversation_id AND m.is_read = 0 WHERE (c.sender_id = 19 OR c.receiver_id = 19) AND c.is_delete='0' ORDER BY c.created_date DESC limit 0,10");
            // echo $qry = sprintf("SELECT c.*, COUNT(m.id) AS unreadMessageCount FROM %s AS c LEFT JOIN %s AS m ON c.%s = m.%s AND m.%s = ? WHERE (c.%s = ? OR c.%s = ?) AND c.%s='%s' ORDER BY c.%s DESC limit ?,?",
            //     TABLE_CONVERSATIONS,TABLE_CHAT_MESSAGE,CONVERSTIONS_Fields::id,CHATMESSAGE_Fields::conversationId,CHATMESSAGE_Fields::isRead,CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDelete,DELETE_STATUS::NOT_DELETE,CONVERSTIONS_Fields::created);

            $qry = sprintf("SELECT c.*,m.unreadMessageCount FROM %s AS c 
left join (select count(*) as unreadMessageCount, %s from %s WHERE %s = ? AND %s != ? group by %s) AS m ON c.%s = m.%s 
 left join %s ub on ((ub.%s = ? and ub.%s = c.%s) or (ub.%s = ? and ub.%s = c.%s)
  or (ub.%s = ? and ub.%s = c.%s) or (ub.%s = ? and ub.%s = c.%s))
WHERE (c.%s = ? AND %s = '%s' OR c.%s = ? AND %s = '%s') AND c.%s='0'   AND ub.%s = '1' ORDER BY c.%s DESC limit ?,?",
            TABLE_CONVERSATIONS,CHATMESSAGE_Fields::conversationId,TABLE_CHAT_MESSAGE,CHATMESSAGE_Fields::isRead,CHATMESSAGE_Fields::senderId,
                CHATMESSAGE_Fields::conversationId,CONVERSTIONS_Fields::id,CHATMESSAGE_Fields::conversationId,
                TABLE_USER_BLOCKLIST,USER_BLOCKLIST_Fields::userId,USER_BLOCKLIST_Fields::otherUserId,CONVERSTIONS_Fields::senderId,
                USER_BLOCKLIST_Fields::otherUserId,USER_BLOCKLIST_Fields::userId,CONVERSTIONS_Fields::senderId,
                USER_BLOCKLIST_Fields::otherUserId,USER_BLOCKLIST_Fields::userId,CONVERSTIONS_Fields::receiverId,
                USER_BLOCKLIST_Fields::userId,USER_BLOCKLIST_Fields::otherUserId,CONVERSTIONS_Fields::receiverId,
                CONVERSTIONS_Fields::senderId,CONVERSTIONS_Fields::isDeleteBySender,DELETE_STATUS::NOT_DELETE,CONVERSTIONS_Fields::receiverId,CONVERSTIONS_Fields::isDeleteByReceiver,                 DELETE_STATUS::NOT_DELETE,CONVERSTIONS_Fields::isDelete,USER_BLOCKLIST_Fields::isDelete, CONVERSTIONS_Fields::created);

                if($selectConversationStmt = $this->connection->prepare($qry)){
                $selectConversationStmt->bind_param("iiiiiiiiii", $isRead,$userId,$userId,$userId,$userId,$userId,$userId,$userId,$start,$limit);
                if ($selectConversationStmt->execute()) {
                    $stmt_result = $selectConversationStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row_data = $stmt_result->fetch_assoc()) {
                            $newUserId = '';
                            if($row_data[CONVERSTIONS_Fields::senderId] == $userId){
                                $newUserId = $row_data[CONVERSTIONS_Fields::receiverId];
                            } else {
                                $newUserId = $row_data[CONVERSTIONS_Fields::senderId];
                            }
                            $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? ", TABLE_USERS, Users_Fields::id);
                            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                                $selectUserStmt->bind_param("i", $newUserId);
                                if ($selectUserStmt->execute()) {
                                    $selectUserStmt->store_result();
                                    if ($selectUserStmt->num_rows > 0) {
                                        $users = fetch_assoc_all_values($selectUserStmt);
                                    }
                                }
                            }
                            $con['id']=$row_data[CONVERSTIONS_Fields::id];
                            $con['last_message']=$row_data[CONVERSTIONS_Fields::lastMessage];
                            $con['sender_id']=$row_data[CONVERSTIONS_Fields::senderId];
                            $con['receiver_id']=$row_data[CONVERSTIONS_Fields::receiverId];
                            $con['created_date']=$row_data[CONVERSTIONS_Fields::created];
                            $con['modified_date']=$row_data[CONVERSTIONS_Fields::modified];
                            $con['is_testdata']=$row_data[CONVERSTIONS_Fields::isTestdata];
                            $con['is_delete']=$row_data[CONVERSTIONS_Fields::isDelete];
                            $con['userid']=$users[USERS_Fields::id];
                            $con['nick_name']=$users[USERS_Fields::nickName];
                            $con['avtar_name']=$users[USERS_Fields::avtarName];
                            $con['profile_image']=$users[USERS_Fields::profileImage];
                            $con['is_user_delete']=$users[USERS_Fields::isDelete];
                            $con['unreadMessageCount']=$row_data['unreadMessageCount'];
                            array_push($ConversationList,$con);
                        }
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'ConverstionList return successfully!';
                        $data[DATA] = $ConversationList;
                        $data[TOTAL_NO_RECORDS] = $TotalNumberOfChatConverstion;
                        return $data;  
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = DEFAULT_NO_RECORDS;
                        return $data;  
                    }
                    $selectConversationStmt->close();
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