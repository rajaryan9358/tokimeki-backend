<?php

class UserFunctions
{
    protected $connection;

    public function __construct(mysqli $con)
    {
        $this->connection = $con;
    }

    public function call_service($service, $postData)
    {
        switch ($service) {
            case "Login":
                {
                    return $this->login($postData);
                }
                break;
            case "Logout":
                {
                    return $this->logout($postData);
                }
                break;

            case "Signup":
                {
                    return $this->register($postData);
                }
                break;
            case "ResendCode":
                {
                    return $this->resendVerifyCode($postData);
                }
                break;
            case "UserVerification":
                {
                    return $this->UserVerification($postData);
                }
                break;
            case "Feedback":
                {
                    return $this->feedback($postData);
                }
                break;
            case "ForgetPassword":
                {
                    return $this->forgetPassword($postData);
                }
                break;
            case "EditProfileImage":
                {
                    return $this->editProfileImageOrAvtar($postData);
                }
                break;
            case "SocialLogin":
                {
                    return $this->socialLogin($postData);
                }
                break;
            case "changePassword":
                {
                    return $this->changePassword($postData);
                }
                break;
            case "updateProfile":
                {
                    return $this->updateProfileData($postData);
                }
                break;
            case "AddNickName":
                {
                    return $this->updateNickName($postData);
                }
                break;
            case "getUserAttempt":
                {
                    return $this->getUserAttempt($postData);
                }
                break;
            case "BlockUnBlockUser":
                {
                    return $this->createBlockUser($postData);
                }
                break;
            case "getBlockUser":
                {
                    return $this->getBlockUserList($postData);
                }
                break;
            case "rejectReInstateUser":
                {
                    return $this->rejectReInstateUser($postData);
                }
                break;
            case "getRejectedUser":
                {
                    return $this->getRejectedUserList($postData);
                }
                break;
            case "IsUserActive":
                {
                    return $this->checkUserIsActive($postData);
                }
                break;
            case "DeleteAccount":
                {
                    return $this->deleteAccount($postData);
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

    public function register($postData)
    {
        include "Mail.php";

        $email = validateObject($postData, param_email, "");
        $password = validateObject($postData, param_password, "");
        $firstName = validateObject($postData, param_firstname, "");
        $lastName = validateObject($postData, param_lastname, "");
        $nickName = validateObject($postData, param_nickname, "");
        $DOB = validateObject($postData, param_DOB, "");
        $maritalStatus = validateObject($postData, param_marital_status, "");
        $isTestData = validateObject($postData, param_is_testdata, "");
        $deviceType = validateObject($postData, param_device_type, "");
        $deviceToken = validateObject($postData, param_device_token, "");

        if ($email == "" || $password == "" || $firstName == "" || $lastName == "" || $nickName == "" || $DOB == "" || $maritalStatus == "" || $isTestData == "" || $deviceToken == "" || $deviceType == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s' AND %s=?", TABLE_USERS, Users_Fields::emailId, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE, Users_Fields::isTestdata);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("si", $email, $isTestData);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                $selectQueryStmt->close();
                $data[STATUS] = FAILED;
                $data[MESSAGE] = "Emailid is already in use! Try again with new email.";
                return $data;
            }
            $selectQueryStmt->close();
        }
        $security = new SecurityFunctions($this->connection);
        $encrypt = new Security();
        $generate_guid = $security->gen_uuid();
        $verifyCode = generateRandomCode(4);
        $key = "password";
        $encryptedPassword = $encrypt->encrypt($password, $key);
        $currentDate = date('Y-m-d H:i:s');
        $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`,`%s`,`%s`,`%s`,`%s`,`%s`,`%s`",
            Users_Fields::emailId, Users_Fields::password, Users_Fields::firstName, Users_Fields::lastName, Users_Fields::nickName, USERS_Fields::dateOfBirth, USERS_Fields::verificationCode, USERS_Fields::maritialStatus, Users_Fields::GUID, USERS_Fields::created, USERS_Fields::modified, Users_Fields::isTestdata);
        $insertQuery = "INSERT INTO " . TABLE_USERS . " ($insertFields) values(?,?,?,?,?,?,?,?,?,?,?,?)";
        if ($insertUserStmt = $this->connection->prepare($insertQuery)) {
            $insertUserStmt->bind_param("sssssssssssi", $email, $encryptedPassword, $firstName, $lastName, $nickName, $DOB, $verifyCode, $maritalStatus, $generate_guid, $currentDate, $currentDate, $isTestData);
            // echo $insertUserStmt->error;
            if ($insertUserStmt->execute()) {
                $user_inserted_id = $insertUserStmt->insert_id;
                $insertUserStmt->close();
                $selectQuery = sprintf("SELECT * FROM %s
                   WHERE  %s=? AND %s='%s' AND %s=?", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE, Users_Fields::isTestdata);
                if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                    $selectUserStmt->bind_param("is", $user_inserted_id, $isTestData);
                    if ($selectUserStmt->execute()) {
                        $selectUserStmt->store_result();
                        if ($selectUserStmt->num_rows > 0) {
                            $users = fetch_assoc_all_values($selectUserStmt);
                            //Update Token for user
                            $tokenData = new stdClass;
                            $tokenData->GUID = $users[Users_Fields::GUID];
                            $tokenData->userid = $users[Users_Fields::id];
                            $tokenData->device_type = $deviceType;
                            $tokenData->device_token = $deviceToken;
                            $user_token = $security->updateTokenforUser($tokenData);
                            if ($user_token[STATUS] == 1) {
                                $data[USER_TOKEN] = $user_token[USER_TOKEN];
                                $is_success = true;
                            } else {
                                $is_success = false;
                            }
                            if ($is_success == true) {
                                $mailsend = sendEmail($email, $verifyCode);
                                $data[STATUS] = SUCCESS;
                                $data[MESSAGE] = "User registered Successfully!";
                                $data[DATA] = $users;
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
        } else {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = SOMETHING_WRONG;
            return $data;
        }
    }

    public function login($postData)
    {
        $email = validateObject($postData, param_email, "");
        $password = validateObject($postData, param_password, "");
        $deviceType = validateObject($postData, param_device_type, "");
        $deviceToken = validateObject($postData, param_device_token, "");
        if ($email == "" || $password == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $encrypt = new Security();
        $key = "password";
        $encryptedPassword = $encrypt->encrypt($password, $key);
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=? AND %s='%s'", TABLE_USERS, Users_Fields::emailId, Users_Fields::password, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("ss", $email, $encryptedPassword);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                $users = fetch_assoc_all_values($selectQueryStmt);
                // if($users[USERS_Fields::isVerify] == 0){
                //     $data[STATUS] = SUCCESS;
                //     $data[MESSAGE] = "User is not verified!";
                //     return $data;
                // }
                $isActive = $users[Users_Fields::isActive];
                if ($isActive === 2) {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = "You are currently deactivated by the administrator!";
                    $data[DATA] = $users;
                    return $data;
                }
                $security = new SecurityFunctions($this->connection);
                $tokenData = new stdClass;
                $tokenData->GUID = $users[Users_Fields::GUID];
                $tokenData->userid = $users[Users_Fields::id];
                $tokenData->device_type = $deviceType;
                $tokenData->device_token = $deviceToken;
                $user_token = $security->updateTokenforUser($tokenData);
                if ($user_token[STATUS] == 1) {
                    $data[USER_TOKEN] = $user_token[USER_TOKEN];
                    $is_success = true;
                } else {
                    $is_success = false;
                }
                $selectQueryStmt->close();
                $isAttempt = false;
                // $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_ATTEMPTS ,USERATTEMPTS_Fields::userId,USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_CITY, USERCITY_Fields::userId, USERCITY_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                if ($selectStmt = $this->connection->prepare($qry)) {
                    $selectStmt->bind_param("i", $users[Users_Fields::id]);
                    $selectStmt->execute();
                    $selectStmt->store_result();
                    if ($selectStmt->num_rows > 0) {
                        $isAttempt = true;
                    }
                }
                $isQuestionAttempt = false;
                $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_ATTEMPTS, USERATTEMPTS_Fields::userId, USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                if ($selectStmt = $this->connection->prepare($qry)) {
                    $selectStmt->bind_param("i", $users[Users_Fields::id]);
                    $selectStmt->execute();
                    $selectStmt->store_result();
                    if ($selectStmt->num_rows > 0) {
                        $isQuestionAttempt = true;
                    }
                }
                $subscription_plan = new stdClass();
                $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=%s", TABLE_SUBSCRIPTION, SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                if ($selectQueryStmt = $this->connection->prepare($query)) {
                    $selectQueryStmt->bind_param("i", $users[Users_Fields::id]);
                    $selectQueryStmt->execute();
                    $selectQueryStmt->store_result();
                    if ($selectQueryStmt->num_rows > 0) {
                        $subscription_plan = fetch_assoc_all_values($selectQueryStmt);
                    }
                    $selectQueryStmt->close();
                }
                $users['isAttempt'] = $isAttempt;
                $users['isQuestionAttempt'] = $isQuestionAttempt;
                $users['subscription_plan'] = $subscription_plan;
                if ($users[USERS_Fields::isVerify] == 0) {
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "User is not verified!";
                    $data[DATA] = $users;
                    return $data;
                }
                if ($is_success == true) {
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "You are logged in successfully!";
                    $data[DATA] = $users;
                    return $data;
                }
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = "Invalid Email or password!";
                return $data;
            }
            $selectQueryStmt->close();
        }
    }

    public function logout($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $deviceType = validateObject($postData, param_device_type, "");
        $deviceToken = validateObject($postData, param_device_token, "");
        if ($userId == "" || $deviceType == "" || $deviceToken == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        if ($deviceType == 3) {
            $deviceType = 'postman';
        } else if ($deviceType == 2) {
            $deviceType = 'android';
        } else if ($deviceType == 1) {
            $deviceType = 'ios';
        }
        $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s=? AND %s=?", TABLE_APP_TOKENS, APP_TOKENS_Fields::userId, APP_TOKENS_Fields::device_token, APP_TOKENS_Fields::device_type);
        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
            $selectUserStmt->bind_param("iss", $userId, $deviceToken, $deviceType);
            if ($selectUserStmt->execute()) {
                $selectUserStmt->store_result();
                if ($selectUserStmt->num_rows > 0) {
                    $Tokens = fetch_assoc_all_values($selectUserStmt);
                    $TokenId = $Tokens[APP_TOKENS_Fields::id];
                    $dleteTokenStmt = sprintf("DELETE FROM %s WHERE %s = ?", TABLE_APP_TOKENS, APP_TOKENS_Fields::id);
                    if ($dleteTokenStmt = $this->connection->prepare($dleteTokenStmt)) {
                        $dleteTokenStmt->bind_param("i", $TokenId);
                        $dleteTokenStmt->execute();
                        $dleteTokenStmt->close();
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = "User logged out successfully";
                        return $data;
                    }
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = "Sorry we could not find any account!";
                    return $data;
                }
            }
        }
        $data[STATUS] = FAILED;
        $data[MESSAGE] = "Sorry we could not find any account!";
        return $data;
    }

    public function resendVerifyCode($postData)
    {
        include "Mail.php";
        $userId = validateObject($postData, param_userid, "");
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
                    $email = $users[Users_Fields::emailId];
                    $verifyCode = generateRandomCode(4);
                    $currentDate = date('Y-m-d H:i:s');
                    $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::verificationCode . " = ? , " . USERS_Fields::modified . " = ? where id = ?");
                    $update_stmt->bind_param("ssi", $verifyCode, $currentDate, $userId);
                    if ($update_stmt->execute()) {
                        $mailsend = sendEmail($email, $verifyCode);
                        $update_stmt->close();
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'Verification code is send to registered email!';
                        return $data;
                    }
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = 'There is no user belong to this userid!';
                    return $data;
                }
            }
        }
    }

    public function UserVerification($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $verificationCode = validateObject($postData, param_verification_code, "");
        if ($userId == "" || $verificationCode == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, USERS_Fields::verificationCode, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
            $selectUserStmt->bind_param("is", $userId, $verificationCode);
            if ($selectUserStmt->execute()) {
                $selectUserStmt->store_result();
                if ($selectUserStmt->num_rows > 0) {
                    $currentDate = date('Y-m-d H:i:s');
                    $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::isVerify . " = " . VERIFICATION_STATUS::Verified . " , " . USERS_Fields::modified . " = ? where id = ?");
                    $update_stmt->bind_param("si", $currentDate, $userId);
                    if ($update_stmt->execute()) {
                        $update_stmt->close();
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User verification successfully!';
                        return $data;
                    }
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = 'You are enter wrong verification code!';
                    return $data;
                }
            }
        }
    }

    public function feedback($postData)
    {
        include "feedbackMail.php";
        $email = validateObject($postData, param_email, "");
        $message = validateObject($postData, param_message, "");
        if ($email == "" || $message == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $mailsend = sendFeedbackEmail($email, $message);
        if ($mailsend == 'Message has been sent successfully') {
            $data[STATUS] = SUCCESS;
            $data[MESSAGE] = 'your feedback received successfully!';
            return $data;
        } else {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = SOMETHING_WRONG;
            return $data;
        }
    }

    public function forgetPassword($postData)
    {
        include "forgetPasswordMail.php";

        $email = validateObject($postData, param_email, "");
      
        if ($email == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USERS, Users_Fields::emailId, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("s", $email);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                $users = fetch_assoc_all_values($selectQueryStmt);
                $userId = $users[Users_Fields::id];
                $selectQueryStmt->close();
                $newPassword = generateRandomString(6);
                $encrypt = new Security();
                $key = "password";
                $encryptedPassword = $encrypt->encrypt($newPassword, $key);
                $mailsend = sendForgetpwdEmail($email, $newPassword);

                if ($mailsend == 'Message has been sent successfully') {
                    $currentDate = date('Y-m-d H:i:s');
                    $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::password . " = ? , " . USERS_Fields::modified . " = ? where id = ?");
                    $update_stmt->bind_param("ssi", $encryptedPassword, $currentDate, $userId);
                    if ($update_stmt->execute()) {
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'We have sent a new password on your email address!!';
                        return $data;
                    }
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }
            } else {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = 'There is a no user belong to this user!';
                return $data;
            }
            $selectQueryStmt->close();
        }
    }

    public function editProfileImageOrAvtar($postData)
    {
        $avtarName = validateValue($_POST[param_avtar], "");
        $userId = validateValue($_POST[param_userid], "");
        // $image = $_FILES['image']['name'];
        if ($userId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        if ($avtarName != "") {
            // update avtar
            $ProfileImage = null;
            $currentDate = date('Y-m-d H:i:s');
            $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::avtarName . " = ?, " . USERS_Fields::profileImage . " = ? , " . USERS_Fields::modified . " = ? where id = ?");
            $update_stmt->bind_param("sssi", $avtarName, $ProfileImage, $currentDate, $userId);
            if ($update_stmt->execute()) {
                $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                if ($selectQueryStmt = $this->connection->prepare($query)) {
                    $selectQueryStmt->bind_param("i", $userId);
                    $selectQueryStmt->execute();
                    $selectQueryStmt->store_result();
                    if ($selectQueryStmt->num_rows > 0) {
                        $users = fetch_assoc_all_values($selectQueryStmt);
                    }
                }
                $update_stmt->close();
                $data[STATUS] = SUCCESS;
                $data[MESSAGE] = 'User Profile Image updated successfully!';
                $data[DATA] = $users;
                return $data;
            } else {
                $update_stmt->close();
                $data[STATUS] = FAILED;
                $data[MESSAGE] = SOMETHING_WRONG;
                return $data;
            }
        } else {
            // upload image
            $currentDate = date('Y-m-d H:i:s');
            $target_dir = 'upload/Profile';
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $baseurl = "http://" . $host . $path . "/";
            $target_file = $target_dir . basename($_FILES['image']["name"]);
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $fileName = time() . '.' . $fileType;
            $extensions_arr = array("jpg", "jpeg", "png", "gif", "pdf");
            $productUrl = $baseurl . $target_dir . '/' . $fileName;
            if (in_array($fileType, $extensions_arr)) {
                move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . '/' . $fileName);
                $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::profileImage . " = ? , " . USERS_Fields::modified . " = ? where id = ?");
                $update_stmt->bind_param("ssi", $fileName, $currentDate, $userId);
                if ($update_stmt->execute()) {
                    $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                    if ($selectQueryStmt = $this->connection->prepare($query)) {
                        $selectQueryStmt->bind_param("i", $userId);
                        $selectQueryStmt->execute();
                        $selectQueryStmt->store_result();
                        if ($selectQueryStmt->num_rows > 0) {
                            $users = fetch_assoc_all_values($selectQueryStmt);
                        }
                    }
                    $update_stmt->close();
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = 'User Profile Image updated successfully!';
                    $data[DATA] = $users;
                    return $data;
                } else {
                    $update_stmt->close();
                    $data[STATUS] = FAILED;
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

    public function socialLogin($postData)
    {
        $email = validateObject($postData, param_email, "");
        $firstName = validateObject($postData, param_firstname, "");
        $lastName = validateObject($postData, param_lastname, "");
        $socialId = validateObject($postData, param_social_id, "");
        $socialType = validateObject($postData, param_social_type, "");
        $isTestData = validateObject($postData, param_is_testdata, "");
        $deviceType = validateObject($postData, param_device_type, "");
        $deviceToken = validateObject($postData, param_device_token, "");
        $maritalStatus = validateObject($postData, param_marital_status, "");
        if ($email == "" || $firstName == "" || $lastName == "" || $socialId == "" || $socialType == "" || $isTestData == "" || $deviceToken == "" || $deviceType == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USERS, Users_Fields::emailId, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("s", $email);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                // login
                $qry = '';
                if ($socialType == 'google') {
                    $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s=? AND %s='%s'", TABLE_USERS, Users_Fields::emailId, USERS_Fields::googleId, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                } else if ($$socialType == 'apple') {
                    $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s=? AND %s='%s'", TABLE_USERS, Users_Fields::emailId, USERS_Fields::appleId, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                }
                if ($selectStmt = $this->connection->prepare($qry)) {
                    $selectStmt->bind_param("ss", $email, $socialId);
                    $selectStmt->execute();
                    $selectStmt->store_result();
                    if ($selectStmt->num_rows > 0) {
                        $users = fetch_assoc_all_values($selectStmt);
                        $isActive = $users[Users_Fields::isActive];
                        if ($isActive === 2) {
                            $data[STATUS] = FAILED;
                            $data[MESSAGE] = "You are currently deactivated by the administrator!";
                            $data[DATA] = $users;
                            return $data;
                        }
                        $security = new SecurityFunctions($this->connection);
                        $tokenData = new stdClass;
                        $tokenData->GUID = $users[Users_Fields::GUID];
                        $tokenData->userid = $users[Users_Fields::id];
                        $tokenData->device_type = $deviceType;
                        $tokenData->device_token = $deviceToken;
                        $user_token = $security->updateTokenforUser($tokenData);
                        if ($user_token[STATUS] == 1) {
                            $data[USER_TOKEN] = $user_token[USER_TOKEN];
                            $is_success = true;
                        } else {
                            $is_success = false;
                        }
                        $selectQueryStmt->close();
                        $isAttempt = false;
                        // $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_ATTEMPTS ,USERATTEMPTS_Fields::userId,USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_CITY, USERCITY_Fields::userId, USERCITY_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectStmt = $this->connection->prepare($qry)) {
                            $selectStmt->bind_param("i", $users[Users_Fields::id]);
                            $selectStmt->execute();
                            $selectStmt->store_result();
                            if ($selectStmt->num_rows > 0) {
                                $isAttempt = true;
                            }
                        }
                        $isQuestionAttempt = false;
                        $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_ATTEMPTS, USERATTEMPTS_Fields::userId, USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectStmt = $this->connection->prepare($qry)) {
                            $selectStmt->bind_param("i", $users[Users_Fields::id]);
                            $selectStmt->execute();
                            $selectStmt->store_result();
                            if ($selectStmt->num_rows > 0) {
                                $isQuestionAttempt = true;
                            }
                        }
                        $subscription_plan = new stdClass();
                        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=%s", TABLE_SUBSCRIPTION, SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectQueryStmt = $this->connection->prepare($query)) {
                            $selectQueryStmt->bind_param("i", $users[Users_Fields::id]);
                            $selectQueryStmt->execute();
                            $selectQueryStmt->store_result();
                            if ($selectQueryStmt->num_rows > 0) {
                                $subscription_plan = fetch_assoc_all_values($selectQueryStmt);
                            }
                            $selectQueryStmt->close();
                        }
                        $users['isAttempt'] = $isAttempt;
                        $users['isQuestionAttempt'] = $isQuestionAttempt;
                        $users['subscription_plan'] = $subscription_plan;
                        if ($is_success == true) {
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = "You are logged in successfully!";
                            $data[DATA] = $users;
                            return $data;
                        }
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'You are register with different platform!';
                        return $data;
                    }
                }
            } else {
                //register
                $security = new SecurityFunctions($this->connection);
                $currentDate = date('Y-m-d H:i:s');
                $Isverify = 1;
                $generate_guid = $security->gen_uuid();
                if ($socialType == 'google') {
                    $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`,`%s`,`%s`,`%s`,`%s`",
                        Users_Fields::emailId, Users_Fields::firstName, Users_Fields::lastName, USERS_Fields::isVerify, USERS_Fields::googleId, USERS_Fields::maritialStatus, Users_Fields::GUID, USERS_Fields::created, USERS_Fields::modified, Users_Fields::isTestdata);
                } else if ($$socialType == 'apple') {
                    $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`,`%s`,`%s`,`%s`,`%s`",
                        Users_Fields::emailId, Users_Fields::firstName, Users_Fields::lastName, USERS_Fields::isVerify, USERS_Fields::appleId, USERS_Fields::maritialStatus, Users_Fields::GUID, USERS_Fields::created, USERS_Fields::modified, Users_Fields::isTestdata);
                }
                $insertQuery = "INSERT INTO " . TABLE_USERS . " ($insertFields) values(?,?,?,?,?,?,?,?,?,?)";
                if ($insertUserStmt = $this->connection->prepare($insertQuery)) {
                    if ($socialType == 'google') {
                        $insertUserStmt->bind_param("sssisssssi", $email, $firstName, $lastName, $Isverify, $socialId, $maritalStatus, $generate_guid, $currentDate, $currentDate, $isTestData);
                    } else if ($$socialType == 'apple') {
                        $insertUserStmt->bind_param("sssisssssi", $email, $firstName, $lastName, $Isverify, $socialId, $maritalStatus, $generate_guid, $currentDate, $currentDate, $isTestData);
                    }
                    if ($insertUserStmt->execute()) {
                        $user_inserted_id = $insertUserStmt->insert_id;
                        $insertUserStmt->close();
                        $selectQuery = sprintf("SELECT * FROM %s
                            WHERE  %s=? AND %s='%s' AND %s=?", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE, Users_Fields::isTestdata);
                        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                            $selectUserStmt->bind_param("is", $user_inserted_id, $isTestData);
                            if ($selectUserStmt->execute()) {
                                $selectUserStmt->store_result();
                                if ($selectUserStmt->num_rows > 0) {
                                    $users = fetch_assoc_all_values($selectUserStmt);
                                    //Update Token for user
                                    $tokenData = new stdClass;
                                    $tokenData->GUID = $users[Users_Fields::GUID];
                                    $tokenData->userid = $users[Users_Fields::id];
                                    $tokenData->device_type = $deviceType;
                                    $tokenData->device_token = $deviceToken;
                                    $user_token = $security->updateTokenforUser($tokenData);
                                    if ($user_token[STATUS] == 1) {
                                        $data[USER_TOKEN] = $user_token[USER_TOKEN];
                                        $is_success = true;
                                    } else {
                                        $is_success = false;
                                    }
                                    $subscription_plan = new stdClass();
                                    $users['isAttempt'] = false;
                                    $users['isQuestionAttempt'] = false;
                                    $users['subscription_plan'] = $subscription_plan;
                                    if ($is_success == true) {
                                        $data[STATUS] = SUCCESS;
                                        $data[MESSAGE] = "You are logged in successfully!";
                                        $data[DATA] = $users;
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
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }
            }
            $selectQueryStmt->close();
        }
    }

    public function changePassword($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $oldPassword = validateObject($postData, param_oldpassword, "");
        $newPassword = validateObject($postData, param_newpassword, "");
        if ($userId == "" || $oldPassword == "" || $newPassword == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
            $selectUserStmt->bind_param("i", $userId);
            if ($selectUserStmt->execute()) {
                $selectUserStmt->store_result();
                $key = "password";
                $encrypt = new Security();
                if ($selectUserStmt->num_rows > 0) {
                    $users = fetch_assoc_all_values($selectUserStmt);
                    $encryptedPassword = $encrypt->encrypt($oldPassword, $key);
                    $password = $users[Users_Fields::password];
                    if ($encryptedPassword != $password) {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'Your current password is not matched!';
                        return $data;
                    } else {
                        $currentDate = date('Y-m-d H:i:s');
                        $encryptedNewPassword = $encrypt->encrypt($newPassword, $key);
                        $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::password . " = ? , " . USERS_Fields::modified . " = ? where id = ?");
                        $update_stmt->bind_param("ssi", $encryptedNewPassword, $currentDate, $userId);
                        if ($update_stmt->execute()) {
                            $update_stmt->close();
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'password reset successfully!';
                            return $data;
                        } else {
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'Error occur while updating password!';
                            return $data;
                        }
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
    }

    public function updateProfileData($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $firstName = validateObject($postData, param_firstname, "");
        $lastName = validateObject($postData, param_lastname, "");
        $nickName = validateObject($postData, param_nickname, "");
        $email = validateObject($postData, param_email, "");
        if ($userId == "" || $firstName == "" || $lastName == "" || $nickName == "" || $email == "") {
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
                    $userData = fetch_assoc_all_values($selectUserStmt);
                    if ($userData[USERS_Fields::emailId] != $email) {
                        $selQuery = sprintf("SELECT * FROM %s WHERE  %s=?", TABLE_USERS, Users_Fields::emailId);
                        if ($selUserStmt = $this->connection->prepare($selQuery)) {
                            $selUserStmt->bind_param("s", $email);
                            if ($selUserStmt->execute()) {
                                $selUserStmt->store_result();
                                if ($selUserStmt->num_rows > 0) {
                                    $data[STATUS] = SUCCESS;
                                    $data[MESSAGE] = 'Emailid is already exists, please try with new one!';
                                    return $data;
                                }
                            }
                        }
                    }
                    $currentDate = date('Y-m-d H:i:s');
                    $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::firstName . " = ? , " . USERS_Fields::lastName . " = ?, " . USERS_Fields::nickName . " = ?, " . USERS_Fields::emailId . " = ? , " . USERS_Fields::modified . " = ? where id = ?");
                    $update_stmt->bind_param("sssssi", $firstName, $lastName, $nickName, $email, $currentDate, $userId);
                    if ($update_stmt->execute()) {
                        $user = [];
                        $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                            $selectUserStmt->bind_param("i", $userId);
                            if ($selectUserStmt->execute()) {
                                $selectUserStmt->store_result();
                                if ($selectUserStmt->num_rows > 0) {
                                    $user = fetch_assoc_all_values($selectUserStmt);
                                }
                            }
                        }
                        $update_stmt->close();
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User profile updated successfully!';
                        $data[DATA] = $user;
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'Error occur while updating user profile!';
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
    }

    public function updateNickName($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $nickName = validateObject($postData, param_nickname, "");
        if ($userId == "" || $nickName == "") {
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
                    $currentDate = date('Y-m-d H:i:s');
                    $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::nickName . " = ?," . USERS_Fields::modified . " = ? where id = ?");
                    $update_stmt->bind_param("ssi", $nickName, $currentDate, $userId);
                    if ($update_stmt->execute()) {
                        $user = [];
                        $isAttempt = false;
                        $isQuestionAttempt = false;
                        $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
                            $selectUserStmt->bind_param("i", $userId);
                            if ($selectUserStmt->execute()) {
                                $selectUserStmt->store_result();
                                if ($selectUserStmt->num_rows > 0) {
                                    $user = fetch_assoc_all_values($selectUserStmt);
                                    // $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_ATTEMPTS ,USERATTEMPTS_Fields::userId,USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                    $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_CITY, USERATTEMPTS_Fields::userId, USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                    if ($selectStmt = $this->connection->prepare($qry)) {
                                        $selectStmt->bind_param("i", $user[Users_Fields::id]);
                                        $selectStmt->execute();
                                        $selectStmt->store_result();
                                        if ($selectStmt->num_rows > 0) {
                                            $isAttempt = true;
                                        }
                                    }
                                    $qry = sprintf("SELECT * FROM %s WHERE %s=? AND %s='%s'", TABLE_USER_ATTEMPTS, USERATTEMPTS_Fields::userId, USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                                    if ($selectStmt = $this->connection->prepare($qry)) {
                                        $selectStmt->bind_param("i", $user[Users_Fields::id]);
                                        $selectStmt->execute();
                                        $selectStmt->store_result();
                                        if ($selectStmt->num_rows > 0) {
                                            $isQuestionAttempt = true;
                                        }
                                    }
                                }
                            }
                        }
                        $user['isAttempt'] = $isAttempt;
                        $user['isQuestionAttempt'] = $isQuestionAttempt;
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'Nick name added successfully!';
                        $data[DATA] = $user;
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = 'Error occur while updating user nickname!';
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
    }

    public function getUserAttempt($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        if ($userId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $userCity = [];
        $userCategory = [];
        $categoryIds = [];
        $userAttempts = [];
        $cityQuery = sprintf("SELECT *,user_city.id as usercid FROM %s INNER JOIN %s ON %s.%s=%s.%s WHERE  %s=? AND %s.%s='%s'", TABLE_USER_CITY, TABLE_CITY, TABLE_USER_CITY,
            USERCITY_Fields::cityId, TABLE_CITY, CITY_Fields::id, USERCITY_Fields::userId, TABLE_USER_CITY, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectUserCityStmt = $this->connection->prepare($cityQuery)) {
            $selectUserCityStmt->bind_param("i", $userId);
            if ($selectUserCityStmt->execute()) {
                $stmt_res = $selectUserCityStmt->get_result();
                if ($stmt_res->num_rows > 0) {
                    while ($row_data = $stmt_res->fetch_assoc()) {
                        array_push($userCity, $row_data);
                    }
                }
            }
        }
        $userAttemptQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USER_ATTEMPTS, USERATTEMPTS_Fields::userId, USERATTEMPTS_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectUserAttemptStmt = $this->connection->prepare($userAttemptQuery)) {
            $selectUserAttemptStmt->bind_param("i", $userId);
            if ($selectUserAttemptStmt->execute()) {
                $stmt_res = $selectUserAttemptStmt->get_result();
                if ($stmt_res->num_rows > 0) {
                    while ($row_data = $stmt_res->fetch_assoc()) {
                        array_push($categoryIds, $row_data[USERATTEMPTS_Fields::categoryId]);
                        array_push($userAttempts, $row_data);
                    }
                }
            }
        }
        if (sizeof($categoryIds) > 0) {
            $clause = implode(',', array_fill(0, count($categoryIds), '?'));
            $types = str_repeat('i', count($categoryIds));
            $params = array($types);
            foreach ($categoryIds as $key => $value) {
                $params[] = &$categoryIds[$key];
            }
            $categoryQuery = sprintf("SELECT * FROM %s WHERE  %s IN (" . $clause . ") AND %s='%s'", TABLE_CATEGORY, CATEGORY_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectUserCatStmt = $this->connection->prepare($categoryQuery)) {
                call_user_func_array(array($selectUserCatStmt, 'bind_param'), $params);
                if ($selectUserCatStmt->execute()) {
                    $stmt_res = $selectUserCatStmt->get_result();
                    if ($stmt_res->num_rows > 0) {
                        while ($row_data = $stmt_res->fetch_assoc()) {
                            array_push($userCategory, $row_data);
                        }
                    }
                }
            }
        }
        $finalResult = (object) [
            'userCity' => $userCity,
            'userCategory' => $userCategory,
            'userAttempts' => $userAttempts,
        ];
        if (sizeof($userCity) > 0 && sizeof($userAttempts) > 0 && sizeof($userCategory) > 0) {
            $data[STATUS] = SUCCESS;
            $data[MESSAGE] = 'User Attempt successfully!';
            $data[DATA] = $finalResult;
            return $data;
        } else {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEFAULT_NO_RECORD;
            return $data;
        }
    }

    public function createBlockUser($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $otherUserId = validateObject($postData, param__other_userid, "");
        $isTestData = validateObject($postData, param_is_testdata, "");
        $currentDate = date('Y-m-d H:i:s');
        if ($userId == "" || $otherUserId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=?", TABLE_USER_BLOCKLIST, USER_BLOCKLIST_Fields::userId, USER_BLOCKLIST_Fields::otherUserId);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("ii", $userId, $otherUserId);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                $Blockuser = fetch_assoc_all_values($selectQueryStmt);
                $isBlock = $Blockuser[USER_BLOCKLIST_Fields::isDelete];
                $userBlockId = $Blockuser[USER_BLOCKLIST_Fields::id];
                $isDelete = $isBlock == 0 ? 1 : 0;
                $update_stmt = $this->connection->prepare("Update " . TABLE_USER_BLOCKLIST . " set " . USER_BLOCKLIST_Fields::isDelete . " = ? , " . USER_BLOCKLIST_Fields::modified . " = ? where " . USER_BLOCKLIST_Fields::id . " = ?");
                $update_stmt->bind_param("isi", $isDelete, $currentDate, $userBlockId);
                if ($update_stmt->execute()) {
                    $update_stmt->close();
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = $isDelete == 0 ? 'User block successfully!' : 'User unblock successfully!';
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }

            } else {
                $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`",
                    USER_BLOCKLIST_Fields::userId, USER_BLOCKLIST_Fields::otherUserId, USER_BLOCKLIST_Fields::created, USER_BLOCKLIST_Fields::modified, USER_BLOCKLIST_Fields::isTestdata);
                $insertQuery = "INSERT INTO " . TABLE_USER_BLOCKLIST . " ($insertFields) values(?,?,?,?,?)";
                if ($insertUserStmt = $this->connection->prepare($insertQuery)) {
                    $insertUserStmt->bind_param("iissi", $userId, $otherUserId, $currentDate, $currentDate, $isTestData);
                    // echo $insertUserStmt->error;
                    if ($insertUserStmt->execute()) {
                        $user_inserted_id = $insertUserStmt->insert_id;
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User block successfully!';
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = SOMETHING_WRONG;
                        return $data;
                    }
                }
            }
        }
    }

    public function getBlockUserList($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $start = validateObject($postData, param_start, "");
        $limit = validateObject($postData, param_limit, "");
        if ($userId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s'", TABLE_USER_BLOCKLIST, USER_BLOCKLIST_Fields::userId, USER_BLOCKLIST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectStmt = $this->connection->prepare($query)) {
            $selectStmt->bind_param("i", $userId);
            if ($selectStmt->execute()) {
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $TotalNoOfRecords = $stmt_result->num_rows;
                }
            }
        }
        $query = sprintf("SELECT * ,user_block_list.id as bid FROM %s INNER JOIN %s ON %s.%s = %s.%s WHERE %s = ? AND %s.%s='%s' limit ?,?",
            TABLE_USER_BLOCKLIST, TABLE_USERS, TABLE_USER_BLOCKLIST, USER_BLOCKLIST_Fields::otherUserId, TABLE_USERS, USERS_Fields::id, USER_BLOCKLIST_Fields::userId, TABLE_USER_BLOCKLIST, USER_BLOCKLIST_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectStmt = $this->connection->prepare($query)) {
            $selectStmt->bind_param("iii", $userId, $start, $limit);
            if ($selectStmt->execute()) {
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $blockUser = [];
                    while ($row_data = $stmt_result->fetch_assoc()) {
                        $user['id'] = $row_data['bid'];
                        $user['userid'] = $row_data[USER_BLOCKLIST_Fields::otherUserId];
                        $user['first_name'] = $row_data[USERS_Fields::firstName];
                        $user['last_name'] = $row_data[USERS_Fields::lastName];
                        $user['nick_name'] = $row_data[USERS_Fields::nickName];
                        $user['profile_image'] = $row_data[USERS_Fields::profileImage];
                        $user['avtar_name'] = $row_data[USERS_Fields::avtarName];
                        array_push($blockUser, $user);
                    }
                    $selectStmt->close();
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "Block userlist return successfully!";
                    $data[DATA] = $blockUser;
                    $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = DEFAULT_NO_RECORDS;
                    return $data;
                }
            }
        }
    }

    public function rejectReInstateUser($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $otherUserId = validateObject($postData, param__other_userid, "");
        $isTestData = validateObject($postData, param_is_testdata, "");
        $currentDate = date('Y-m-d H:i:s');
        if ($userId == "" || $otherUserId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=?", TABLE_REJECT_MATCH, REJECT_MATCH_Fields::userId, REJECT_MATCH_Fields::otherUserId);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("ii", $userId, $otherUserId);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                // update in existing record
                $Rejectuser = fetch_assoc_all_values($selectQueryStmt);
                $isRejected = $Rejectuser[REJECT_MATCH_Fields::isDelete];
                $rejectUserId = $Rejectuser[REJECT_MATCH_Fields::id];
                $isDelete = $isRejected == 0 ? 1 : 0;
                $update_stmt = $this->connection->prepare("Update " . TABLE_REJECT_MATCH . " set " . REJECT_MATCH_Fields::isDelete . " = ? , " . REJECT_MATCH_Fields::modified . " = ? where " . REJECT_MATCH_Fields::id . " = ?");
                $update_stmt->bind_param("isi", $isDelete, $currentDate, $rejectUserId);
                if ($update_stmt->execute()) {
                    $update_stmt->close();
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = $isDelete == 0 ? 'User rejected successfully!' : 'User reInstate successfully!';
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }
            } else {
                // create new record
                $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`",
                    REJECT_MATCH_Fields::userId, REJECT_MATCH_Fields::otherUserId, REJECT_MATCH_Fields::created, REJECT_MATCH_Fields::modified, REJECT_MATCH_Fields::isTestdata);
                $insertQuery = "INSERT INTO " . TABLE_REJECT_MATCH . " ($insertFields) values(?,?,?,?,?)";
                if ($insertUserStmt = $this->connection->prepare($insertQuery)) {
                    $insertUserStmt->bind_param("iissi", $userId, $otherUserId, $currentDate, $currentDate, $isTestData);
                    // echo $insertUserStmt->error;
                    if ($insertUserStmt->execute()) {
                        $user_inserted_id = $insertUserStmt->insert_id;
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User rejected successfully!';
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = SOMETHING_WRONG;
                        return $data;
                    }
                }
            }
        }
    }

    public function getRejectedUserList($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $start = validateObject($postData, param_start, "");
        $limit = validateObject($postData, param_limit, "");
        if ($userId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s = ? AND %s='%s'", TABLE_REJECT_MATCH, REJECT_MATCH_Fields::userId, REJECT_MATCH_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectStmt = $this->connection->prepare($query)) {
            $selectStmt->bind_param("i", $userId);
            if ($selectStmt->execute()) {
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $TotalNoOfRecords = $stmt_result->num_rows;
                }
            }
        }
        $query = sprintf("SELECT * ,reject_match.id as Rid FROM %s INNER JOIN %s ON %s.%s = %s.%s WHERE %s = ? AND %s.%s='%s' limit ?,?",
            TABLE_REJECT_MATCH, TABLE_USERS, TABLE_REJECT_MATCH, REJECT_MATCH_Fields::otherUserId, TABLE_USERS, USERS_Fields::id, REJECT_MATCH_Fields::userId, TABLE_REJECT_MATCH, REJECT_MATCH_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectStmt = $this->connection->prepare($query)) {
            $selectStmt->bind_param("iii", $userId, $start, $limit);
            if ($selectStmt->execute()) {
                $stmt_result = $selectStmt->get_result();
                if ($stmt_result->num_rows > 0) {
                    $rejectUser = [];
                    while ($row_data = $stmt_result->fetch_assoc()) {
                        $user['id'] = $row_data['Rid'];
                        $user['userid'] = $row_data[REJECT_MATCH_Fields::otherUserId];
                        $user['first_name'] = $row_data[USERS_Fields::firstName];
                        $user['last_name'] = $row_data[USERS_Fields::lastName];
                        $user['nick_name'] = $row_data[USERS_Fields::nickName];
                        $user['profile_image'] = $row_data[USERS_Fields::profileImage];
                        $user['avtar_name'] = $row_data[USERS_Fields::avtarName];
                        array_push($rejectUser, $user);
                    }
                    $selectStmt->close();
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "Rejected userlist return successfully!";
                    $data[DATA] = $rejectUser;
                    $data[TOTAL_NO_RECORDS] = $TotalNoOfRecords;
                    return $data;
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = DEFAULT_NO_RECORDS;
                    return $data;
                }
            }
        }
    }

    public function checkUserIsActive($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        if ($userId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $tempData = new stdClass;
        $tempData->is_active = 1;

        $selectQuery = sprintf("SELECT * FROM %s WHERE  %s=? AND %s='%s'", TABLE_USERS, Users_Fields::id, Users_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectUserStmt = $this->connection->prepare($selectQuery)) {
            $selectUserStmt->bind_param("i", $userId);
            if ($selectUserStmt->execute()) {
                $selectUserStmt->store_result();
                if ($selectUserStmt->num_rows > 0) {
                    $user = fetch_assoc_all_values($selectUserStmt);
                    $isActive = $user[USERS_Fields::isActive];
                    $tempData->is_active = $isActive;
                    $data[STATUS] = SUCCESS;
                    $data[MESSAGE] = "check user's activness successfully!";
                    $data[DATA] = $tempData;
                    return $data;
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
    }

    public function deleteAccount($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        
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
                if ($selectUserStmt->num_rows <= 0) {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = 'User not found!';
                    return $data;
                } else {
                    $isDeleteStatus = 1;
                    $currentDate = date('Y-m-d H:i:s');
                    $update_stmt = $this->connection->prepare("Update " . TABLE_USERS . " set " . USERS_Fields::isDelete . " = ? ," . USERS_Fields::modified . " = ? where id = ?");
                    $update_stmt->bind_param("sss", $isDeleteStatus, $currentDate, $userId);
                    if ($update_stmt->execute()) {
                        $update_stmt->close();

                        /**
                         * delete and expired user token if exists in app_token tabke
                         */
                        $tokenStatus = "expired";
                        $update_stmt = $this->connection->prepare("Update " . TABLE_APP_TOKENS . " set " . APP_TOKENS_Fields::status . " = ? ," . APP_TOKENS_Fields::isDelete . " = ? where " . APP_TOKENS_Fields::userId . " = ?");
                        $update_stmt->bind_param("sss", $tokenStatus, $isDeleteStatus, $userId);
                        if ($update_stmt->execute()) {
                            $update_stmt->close();
                        }

                        /**
                         * update is_delete flag to 1 for user_point table 
                         */
                        $update_stmt = $this->connection->prepare("Update " . TABLE_USER_POINTS . " set " . USERPOINTS_Fields::isDelete . " = ? where " . USERPOINTS_Fields::userId . " = ?");
                        $update_stmt->bind_param("ss", $isDeleteStatus, $userId);
                        if ($update_stmt->execute()) {
                            $update_stmt->close();
                        }

                        /**
                         * update is_delete flag to 1 for user_request table for sender and receiver
                        */
                        $update_stmt = $this->connection->prepare("Update " . TABLE_USER_REQUEST . " set " . USERREQUEST_Fields::isDelete . " = ? where " . USERREQUEST_Fields::senderId . " = ? or " . USERREQUEST_Fields::receiverId . " = ?");
                        $update_stmt->bind_param("sss", $isDeleteStatus, $userId, $userId);
                        if ($update_stmt->execute()) {
                            $update_stmt->close();
                        }

                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User account deleted successfully!';
                        return $data;
                    } else {
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'Error occur while deleting user!';
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
}
