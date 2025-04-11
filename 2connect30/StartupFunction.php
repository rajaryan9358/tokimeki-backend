<?php
    class StartupFunctions
    {
        protected $connection;
    
        function __construct(mysqli $con)
        {
            $this->connection = $con;
        }

        public function call_service($service, $postData)
        {
            switch ($service) {
                case "GetAllCity":
                    {
                        return $this->featchAllCity();
                    }
                    break;
                case "GetAllCategory":
                    {
                        return $this->featchAllCategory();
                    }
                    break;
                case "AddUserCity":
                    {
                        return $this->AddUserCity($postData);
                    }
                    break;
                case "GetQuestionAnswerList":
                    {
                        return $this->getQuestionAnswerList($postData);
                    }
                    break;
                case "AddUserAttempts":
                    {
                        return $this->AddUserAttempts($postData);
                    }
                    break;
                case "RequestCity":
                    {
                        return $this->AddRequestCity($postData);
                    }
                    break;
                case "updateUserAttempts":
                    {
                        return $this->updateUserAttempt($postData);
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

        public function featchAllCity() 
        {
            $selectQuery = sprintf("SELECT * FROM %s WHERE %s='%s'", TABLE_CITY, CITY_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($selectQuery)) {
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $cityData=[];
                        while($row_data = $stmt_result->fetch_assoc()) {  
                            $city['id']=$row_data['id'];
                            $city['city_name']=$row_data['city_name'];
                            $city['created_date']=$row_data['created_date'];
                            $city['modified_date']=$row_data['modified_date'];
                            $city['is_testdata']=$row_data['is_testdata'];
                            $city['is_delete']=$row_data['is_delete'];
                            array_push($cityData,$city);
                        }
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'City return successfully!';
                        $data[DATA] = $cityData;
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = DEFAULT_NO_RECORDS;
                        return $data;
                    }
                }
            }
        }

        public function featchAllCategory() {
            $selectQuery = "
                SELECT 
                    c.*, 
                    (SELECT COUNT(*) 
                     FROM " . TABLE_QUESTION . " q 
                     WHERE q." . QUESTION_Fields::categoryId . " = c.id 
                     AND q." . QUESTION_Fields::isDelete . " = '" . DELETE_STATUS::NOT_DELETE . "'
                    ) AS question_count 
                FROM " . TABLE_CATEGORY . " c 
                WHERE c." . CATEGORY_Fields::isDelete . " = '" . DELETE_STATUS::NOT_DELETE . "'";
        
            if ($selectStmt = $this->connection->prepare($selectQuery)) {
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        $categoryData = [];
                        while ($row_data = $stmt_result->fetch_assoc()) {
                            $city['id'] = $row_data['id'];
                            $city['category_name'] = $row_data['category_name'];
                            $city['category_name_fr'] = $row_data['category_name_fr'];
                            $city['parentid'] = $row_data['parentid'];
                            $city['score'] = $row_data['score'];
                            $city['created_date'] = $row_data['created_date'];
                            $city['modified_date'] = $row_data['modified_date'];
                            $city['is_testdata'] = $row_data['is_testdata'];
                            $city['is_delete'] = $row_data['is_delete'];
                            $city['question_count'] = $row_data['question_count']; // 👈 New field
                            array_push($categoryData, $city);
                        }
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'Category return successfully!';
                        $data[DATA] = $categoryData;
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = DEFAULT_NO_RECORDS;
                        return $data;
                    }
                }
            }
        }

        public function AddUserCity($postData)
        {
            $userId = validateObject($postData,param_userid, "");
            $cityArr = validateObject($postData,param_city, "");
            $isTestData = validateObject($postData,param_is_testdata, "");
            if ($userId == "" || $cityArr == "") {
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
            $cityStore = false;
            $cityUpdate = false;
            $cityDelete = false;
            $isUpdate = false;
            $oldUserCity = [];
            $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USER_CITY, USERCITY_Fields::userId, USERCITY_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("i", $userId);
                if ($selectUserStmt->execute()) {
                    $stmt_result = $selectUserStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row_data = $stmt_result->fetch_assoc()) {
                            $oldCity['id']=$row_data['id'];
                            $oldCity['city_id']=$row_data['city_id'];
                            array_push($oldUserCity, $oldCity);
                        }
                        $isUpdate = true;
                    }
                }
            }
            $deleteCityId = [];
            $editCityId = [];
            foreach ($cityArr as $k => $city) {
                $cityId = $city->city_id;
                $percentage = $city->percentage;
                $currentDate = date('Y-m-d H:i:s');
                if(array_search($cityId, array_column($oldUserCity, 'city_id')) !== False) {
                    array_push($editCityId,$cityId);
                    $query = "Update ". TABLE_USER_CITY ." set ".USERCITY_Fields::stayPercentage." = ?, ".USERCITY_Fields::modified." = ? where ".USERCITY_Fields::userId." = ? AND ".USERCITY_Fields::cityId." = ?";
                    $update_stmt = $this->connection->prepare($query);
                    $update_stmt->bind_param("isii",$percentage,$currentDate,$userId,$cityId);
                    if ($update_stmt->execute())
                    {
                        $update_stmt->close();
                        $cityUpdate = true;
                    }
                    $cityStore = true;
                } else {
                    $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`", 
                    USERCITY_Fields::userId,USERCITY_Fields::cityId,USERCITY_Fields::stayPercentage,USERCITY_Fields::created,USERCITY_Fields::modified,USERCITY_Fields::isTestdata);
                    $insertQuery = "INSERT INTO " . TABLE_USER_CITY . " ($insertFields) values(?,?,?,?,?,?)";
                    if ($insertStmt = $this->connection->prepare($insertQuery)) {
                        $insertStmt->bind_param("iiissi", $userId,$cityId,$percentage,$currentDate,$currentDate, $isTestData);
                        if ($insertStmt->execute()) {
                            $cityStore = true;
                        }
                    }
                    else {
                        $cityStore = false;
                    }
                    $cityUpdate = true;
                }
            }
            foreach($oldUserCity as $oldCity){
                if(in_array($oldCity['city_id'], $editCityId) == false){
                    array_push($deleteCityId,$oldCity['id']);
                }
            }
            // delete old city
            if(sizeof($deleteCityId) > 0){
                $clause = implode(',', array_fill(0, count($deleteCityId), '?'));
                $types = str_repeat('i', count($deleteCityId));
                $params = array($types);
                foreach ($deleteCityId as $key => $value) {
                    $params[] = &$deleteCityId[$key];
                }
                $query = "Update ". TABLE_USER_CITY ." set ".USERCITY_Fields::isDelete." = ".DELETE_STATUS::IS_DELETE." where id IN (".$clause.")";
                $update_stmt = $this->connection->prepare($query);
                call_user_func_array(array($update_stmt, 'bind_param'), $params);
                if ($update_stmt->execute())
                {
                    $cityDelete = true;
                    $update_stmt->close();
                }
            } else {
                $cityDelete = true;
            }
            if($cityStore == true && $cityDelete == true && $cityUpdate == true){
                $data[STATUS] = SUCCESS;
                $data[MESSAGE] = 'User city store successfully!';
                return $data;
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;
            }
        }

        public function getQuestionAnswerList($postData){
            $categoryId = validateObject($postData,param_categoryid, "");
            if (sizeof($categoryId) == 0) {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $clause = implode(',', array_fill(0, count($categoryId), '?'));
            $types = str_repeat('i', count($categoryId));
            $params = array($types);
            foreach ($categoryId as $key => $value) {
            $params[] = &$categoryId[$key];
            }
            $fields = ''.TABLE_QUESTION.'.'.QUESTION_Fields::id.' as qid,'.QUESTION_Fields::question.','.QUESTION_Fields::questionFr.','.QUESTION_Fields::categoryId.','.TABLE_QUESTION.'.'.QUESTION_Fields::created.','.TABLE_QUESTION.'.'.QUESTION_Fields::modified.','.TABLE_QUESTION.'.'.QUESTION_Fields::isDelete.','.TABLE_QUESTION.'.'.QUESTION_Fields::isTestdata.',
            '.TABLE_ANSWER.'.'.ANSWER_Fields::id.' AS aid,'.ANSWER_Fields::questionId.','.ANSWER_Fields::answer.', '.ANSWER_Fields::answerFr.' ,'.TABLE_ANSWER.'.'.ANSWER_Fields::score.',
            '.TABLE_CATEGORY.'.'.CATEGORY_Fields::id.' AS cid, '.CATEGORY_Fields::categoryName.', '.TABLE_CATEGORY.'.'.CATEGORY_Fields::score.' AS category_score';
            if($request = $this->connection->prepare('SELECT '.$fields.' FROM '.TABLE_QUESTION.' 
            INNER JOIN '.TABLE_ANSWER.' ON '.TABLE_QUESTION.'.'.QUESTION_Fields::id.' = '.TABLE_ANSWER.'.'.ANSWER_Fields::questionId.'
            INNER JOIN '.TABLE_CATEGORY.' ON '.TABLE_QUESTION.'.'.QUESTION_Fields::categoryId.' = '.TABLE_CATEGORY.'.'.CATEGORY_Fields::id.'
            WHERE '.QUESTION_Fields::categoryId.' IN ('.$clause.') AND '.TABLE_QUESTION.'.'.QUESTION_Fields::isDelete.'= '.DELETE_STATUS::NOT_DELETE.'')) {
                call_user_func_array(array($request, 'bind_param'), $params);
                $request->execute();
                $stmt_result = $request->get_result();
                if ($stmt_result->num_rows > 0) {
                    $questionData=[];
                    $answerArr = [];
                    $oldqid = "";
                    while($row_data = $stmt_result->fetch_assoc()) { 
                        $answer['id']=$row_data['aid'];
                        $answer['question_id']=$row_data['question_id'];
                        $answer['answer']=$row_data['answer'];
                        $answer['answer_fr']=$row_data['answer_fr'];
                        $answer['score']=$row_data['score'];
                        array_push($answerArr,$answer);
                        if($row_data['qid'] != $oldqid || $oldqid == ""){
                            $question['id']=$row_data['qid'];
                            $question['question']=$row_data['question'];
                            $question['question_fr']=$row_data['question_fr'];
                            $question['category_id']=$row_data['category_id'];
                            $question['category_name']=$row_data['category_name'];
                            $question['created_date']=$row_data['created_date'];
                            $question['modified_date']=$row_data['modified_date'];
                            $question['is_testdata']=$row_data['is_testdata'];
                            $question['is_delete']=$row_data['is_delete'];
                            $question['category_score']=$row_data['category_score'];
                            array_push($questionData,$question);
                        }
                        $oldqid = $row_data['qid'];
                    }
                    $newansArr = [];
                    foreach ($questionData as $value) {
                        $temp = [];
                        foreach($answerArr as $que){
                            if($que['question_id'] == $value['id']){
                                array_push($temp,$que);
                            }
                        }
                        array_push($newansArr,$temp);
                    }
                    foreach ($questionData as $key => $arr) {
                        $questionData[$key]["answers"] = $newansArr[$key];
                    }
                    $finalResult = [];
                    foreach ($categoryId as $key => $value) {
                        foreach($questionData as $question){
                            if($value == $question['category_id']){
                                array_push($finalResult, $question);
                            }
                        }
                    }
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = 'Question answer return successfully';
                    $data[DATA] = $finalResult;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = DEFAULT_NO_RECORDS;
                    return $data;
                }
            }
        }

        public function AddUserAttempts($postData){
            $userId = validateObject($postData,param_userid, "");
            $categories = validateObject($postData,param_category, "");
            $isTestData = validateObject($postData,param_is_testdata, "");
            if ($userId == "" || $isTestData == "" || count($categories) == 0) {
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
            if(count($categories) > 0){
                $userAttempt = false;
                $userPoint = 0;
                foreach($categories as $cat){
                    $categoryId = $cat->categoryid;
                    $questionId = $cat->questionid;
                    $answerId = $cat->answerid;
                    $point = $cat->category_score + $cat->answer_score;
                    $userPoint = $userPoint+$point;
                    $currentDate = date('Y-m-d H:i:s');
                    $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`,`%s`, `%s`, `%s`, `%s`", USERATTEMPTS_Fields::userId,USERATTEMPTS_Fields::categoryId,USERATTEMPTS_Fields::questionId,USERATTEMPTS_Fields::answerId,USERATTEMPTS_Fields::points, USERATTEMPTS_Fields::created,USERATTEMPTS_Fields::modified,USERATTEMPTS_Fields::isTestdata);
                    $insertQuery = "INSERT INTO " . TABLE_USER_ATTEMPTS . " ($insertFields) values(?,?,?,?,?,?,?,?)";
                    if ($insertStmt = $this->connection->prepare($insertQuery)) {
                        $insertStmt->bind_param("iiiiissi", $userId,$categoryId,$questionId,$answerId,$point,$currentDate,$currentDate, $isTestData);
                        if ($insertStmt->execute()) {
                            $insertStmt->close();
                            $userAttempt = true;
                        }
                    } else {
                        $userAttempt = false;
                    }
                }
                if($userAttempt == true){
                    $insertField = sprintf("`%s`, `%s`, `%s`, `%s`,`%s`", USERPOINTS_Fields::userId,USERPOINTS_Fields::point, USERATTEMPTS_Fields::created,USERATTEMPTS_Fields::modified,USERATTEMPTS_Fields::isTestdata);
                    $insertQry = "INSERT INTO " . TABLE_USER_POINTS . " ($insertField) values(?,?,?,?,?)";
                    if ($insStmt = $this->connection->prepare($insertQry)) {
                        $insStmt->bind_param("iissi", $userId,$userPoint,$currentDate,$currentDate, $isTestData);
                        if ($insStmt->execute()) {
                            $insStmt->close();
                        }
                    }
                    $selectQuery = sprintf("SELECT * FROM %s WHERE %s='%s'", TABLE_POINTS, POINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                    if ($selectStmt = $this->connection->prepare($selectQuery)) {
                        if ($selectStmt->execute()) {
                            $stmt_result = $selectStmt->get_result();
                            if ($stmt_result->num_rows > 0) {
                                $pointData=[];
                                while($row_data = $stmt_result->fetch_assoc()) {  
                                    $pnt['id']=$row_data['id'];
                                    $pnt['min_point']=$row_data['min_point'];
                                    $pnt['max_point']=$row_data['max_point'];
                                    $pnt['applicable_plan']=$row_data['applicable_plan'];
                                    $pnt['is_testdata']=$row_data['is_testdata'];
                                    $pnt['is_delete']=$row_data['is_delete'];
                                    array_push($pointData,$pnt);
                                }
                            }
                        }
                    }
                    foreach($pointData as $key => $element){
                        $selQuery = sprintf("SELECT COUNT(*) AS count FROM %s WHERE %s BETWEEN ? AND ? AND %s != ? AND %s='%s'", TABLE_USER_POINTS, USERPOINTS_Fields::point, USERPOINTS_Fields::userId ,USERPOINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectStmt = $this->connection->prepare($selQuery)) {
                            $selectStmt->bind_param("iii", $element['min_point'],$element['max_point'],$userId);
                            $selectStmt->execute();
                            $selectStmt->store_result();
                            if ($selectStmt->num_rows > 0) {
                                $countData = fetch_assoc_all_values($selectStmt);
                                $count = $countData['count'];
                                if($element['applicable_plan'] == 'High'){
                                    $pointData[$key]['MemberCount'] = $count;
                                } else if($element['applicable_plan'] == 'Medium'){
                                    $pointData[$key]['MemberCount'] = $count;
                                } else if($element['applicable_plan'] == 'Low'){
                                    $pointData[$key]['MemberCount'] = $count;
                                }
                            }
                        }
                    }
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = 'Match count return successfully!';
                    $data[DATA] = $pointData;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }
            }
        }

        public function updateUserAttempt($postData){
            $userId = validateObject($postData,param_userid, "");
            $categories = validateObject($postData,param_category, "");
            $isTestData = validateObject($postData,param_is_testdata, "");
            if ($userId == "" || $isTestData == "" || count($categories) == 0) {
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
            // get old userattempt 
            $oldUserAttemptId=[];
            $Query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_ATTEMPTS, USERATTEMPTS_Fields::userId, USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectStmt = $this->connection->prepare($Query)) {
                $selectStmt->bind_param("i", $userId);
                if ($selectStmt->execute()) {
                    $stmt_result = $selectStmt->get_result();
                    if ($stmt_result->num_rows > 0) {
                        while($row_data = $stmt_result->fetch_assoc()) {
                            array_push($oldUserAttemptId,$row_data['id']);
                        }
                    }
                }
            }
            $userAttempt = false;
            $userPoint = 0;
            foreach($categories as $k => $cat){
                $categoryId = $cat->categoryid;
                $questionId = $cat->questionid;
                $answerId = $cat->answerid;
                $point = $cat->category_score + $cat->answer_score;
                $userPoint = $userPoint+$point;
                $currentDate = date('Y-m-d H:i:s');
                $update_stmt = $this->connection->prepare ("Update ". TABLE_USER_ATTEMPTS ." set ".USERATTEMPTS_Fields::categoryId." = ?, ".USERATTEMPTS_Fields::questionId." = ?, ".USERATTEMPTS_Fields::answerId." = ?, ".USERATTEMPTS_Fields::points." = ? , ".USERATTEMPTS_Fields::modified." = ? where id = ?");
                $update_stmt->bind_param("iiiisi",$categoryId,$questionId,$answerId,$point,$currentDate,$oldUserAttemptId[$k]);
                if ($update_stmt ->execute())
                {
                    $userAttempt = true;
                } else {
                    $userAttempt = false;
                }
            }
            if($userAttempt == true){
                $userpointId = '';
                $selQuery = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s'", TABLE_USER_POINTS, USERPOINTS_Fields::userId ,USERPOINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                if ($selectStmt = $this->connection->prepare($selQuery)) {
                    $selectStmt->bind_param("i", $userId);
                    $selectStmt->execute();
                    $selectStmt->store_result();
                    if ($selectStmt->num_rows > 0) {
                        $userpoints = fetch_assoc_all_values($selectStmt);
                        $userpointId = $userpoints[USERPOINTS_Fields::id];
                    }
                }
                $updatepoint_stmt = $this->connection->prepare("Update ". TABLE_USER_POINTS ." set ".USERPOINTS_Fields::point." = ?, ".USERPOINTS_Fields::modified." = ? where id = ?");
                $updatepoint_stmt->bind_param("isi",$userPoint,$currentDate,$userpointId);
                if ($updatepoint_stmt ->execute())
                {
                    $selectQuery = sprintf("SELECT * FROM %s WHERE %s='%s'", TABLE_POINTS, POINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                    if ($selectStmt = $this->connection->prepare($selectQuery)) {
                        if ($selectStmt->execute()) {
                            $stmt_result = $selectStmt->get_result();
                            if ($stmt_result->num_rows > 0) {
                                $pointData=[];
                                while($row_data = $stmt_result->fetch_assoc()) {  
                                    $pnt['id']=$row_data['id'];
                                    $pnt['min_point']=$row_data['min_point'];
                                    $pnt['max_point']=$row_data['max_point'];
                                    $pnt['applicable_plan']=$row_data['applicable_plan'];
                                    $pnt['is_testdata']=$row_data['is_testdata'];
                                    $pnt['is_delete']=$row_data['is_delete'];
                                    array_push($pointData,$pnt);
                                }
                            }
                        }
                    }
                    foreach($pointData as $key => $element){
                        $selQuery = sprintf("SELECT COUNT(*) AS count FROM %s WHERE %s BETWEEN ? AND ? AND %s != ? AND %s='%s'", TABLE_USER_POINTS, USERPOINTS_Fields::point, USERPOINTS_Fields::userId ,USERPOINTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectStmt = $this->connection->prepare($selQuery)) {
                            $selectStmt->bind_param("iii", $element['min_point'],$element['max_point'],$userId);
                            $selectStmt->execute();
                            $selectStmt->store_result();
                            if ($selectStmt->num_rows > 0) {
                                $countData = fetch_assoc_all_values($selectStmt);
                                $count = $countData['count'];
                                if($element['applicable_plan'] == 'High'){
                                    $pointData[$key]['MemberCount'] = $count;
                                } else if($element['applicable_plan'] == 'Medium'){
                                    $pointData[$key]['MemberCount'] = $count;
                                } else if($element['applicable_plan'] == 'Low'){
                                    $pointData[$key]['MemberCount'] = $count;
                                }
                            }
                        }
                    }
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = 'User Attempt updated successfully!';
                    $data[DATA] = $pointData;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }
            }


        }

        public function AddRequestCity($postData){
            $email = validateObject($postData,param_email, "");
            $city = validateObject($postData,param_city, "");
            $isTestData = validateObject($postData,param_is_testdata, "");
            if ($email == "" || $isTestData == "" ||  $city == "") {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = DEV_ERROR;
                return $data;
            }
            $currentDate = date('Y-m-d H:i:s');

            $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_CITY, CITY_Fields::cityName, CITY_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                $selectUserStmt->bind_param("s", $city);
                if ($selectUserStmt->execute()) {
                    $selectUserStmt->store_result();
                    if ($selectUserStmt->num_rows == 1) {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'This city is alrady in our list!';
                        return $data;
                    } else {
                        $selQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s=? AND %s='%s'", TABLE_REQUEST_CITY, REQUESTCITY_Fields::emailId, REQUESTCITY_Fields::city, REQUESTCITY_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectRequestCityStmt = $this->connection->prepare($selQuery)) {
                            $selectRequestCityStmt->bind_param("ss",$email,$city);
                            if ($selectRequestCityStmt->execute()) {
                                $selectRequestCityStmt->store_result();
                                if ($selectRequestCityStmt->num_rows == 1) {
                                    $data[STATUS] = FAILED;
                                    $data[MESSAGE] = 'This city is alrady added in our other city list!';
                                    return $data;
                                } else {
                                    $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`", REQUESTCITY_Fields::emailId, REQUESTCITY_Fields::city, REQUESTCITY_Fields::created,REQUESTCITY_Fields::modified,REQUESTCITY_Fields::isTestdata);
                                    $insertQuery = "INSERT INTO " . TABLE_REQUEST_CITY . " ($insertFields) values(?,?,?,?,?)";
                                    if ($insertStmt = $this->connection->prepare($insertQuery)) {
                                        $insertStmt->bind_param("ssssi", $email,$city,$currentDate,$currentDate, $isTestData);
                                        if ($insertStmt->execute()) {
                                            $insertStmt->close();
                                            $data[STATUS] = SUCCESS;
                                            $data[MESSAGE] = 'your request received successfully!';
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
    }
?>