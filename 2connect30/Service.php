<?php

include_once 'Config.php';
include_once 'ConstantValues.php';
include_once 'TableVars.php';
include_once 'HelperFunctions.php';
include_once 'SecurityFunctions.php';

$post_body = file_get_contents('php://input');
if ($_REQUEST['Service'] != "addSubscription") {
    $post_body = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($post_body));
}
// $post_body = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($post_body));
$reqData[] = json_decode($post_body);
$postData = $reqData[0];

$debug = 0;
$status = "";

switch ($_REQUEST['Service']) {
    /*********************  Auth Functions ******************************/
    case "Login":
    case "Logout":
    case "Signup":
    case "ResendCode":
    case "UserVerification":
    case "Feedback":
    case "ForgetPassword":
    case "EditProfileImage":
    case "SocialLogin":
    case "changePassword":
    case "updateProfile":
    case "AddNickName":
    case "getUserAttempt":
    case "BlockUnBlockUser":
    case "getBlockUser":
    case "rejectReInstateUser":
    case "getRejectedUser":
    case "IsUserActive":
    case "DeleteAccount":
        {
            $isSecure = '';
            if ($_REQUEST['Service'] == 'EditProfileImage') {
                $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($_POST['access_key'], $_POST['secret_key'], $_POST['device_type'], $_POST['device_token']);
            } else {
                $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($postData->access_key, $postData->secret_key, $postData->device_type, $postData->device_token);
            }
            if ($isSecure == NO) {
                $data[STATUS] = MALICIOUS_SOURCE_STATUS;
                $data[MESSAGE] = MALICIOUS_SOURCE;
            } elseif ($isSecure == ERROR) {
                $data[STATUS] = FAILED;
                $data[MESSAGE] = TOKEN_ERROR;
            } else {
                include_once 'UserFunctions.php';
                $user = new UserFunctions($GLOBALS['con']);
                $data = $user->call_service($_REQUEST['Service'], $postData);
                if ($isSecure != YES && $isSecure != '') {
                    if ($isSecure['key'] == "Temp") {
                        $data['tempToken'] = $isSecure['value'];
                    } else {
                        $data['userToken'] = $isSecure['value'];
                    }
                }
            }
        }
        break;

    /********************  Security Section ********************************/
    case "GetAllCity":
    case "RequestCity":
    case "GetAllCategory":
    case "AddUserCity":
    case "GetQuestionAnswerList":
    case "AddUserAttempts":
    case "updateUserAttempts":
        $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($postData->access_key, $postData->secret_key, $postData->device_type, $postData->device_token);
        if ($isSecure == NO) {
            $data[STATUS] = MALICIOUS_SOURCE_STATUS;
            $data[MESSAGE] = MALICIOUS_SOURCE;
        } elseif ($isSecure == ERROR) {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = TOKEN_ERROR;
        } else {
            include_once 'StartupFunction.php';
            $user = new StartupFunctions($GLOBALS['con']);
            $data = $user->call_service($_REQUEST['Service'], $postData);
            if ($isSecure != YES && $isSecure != '') {
                if ($isSecure['key'] == "Temp") {
                    $data['tempToken'] = $isSecure['value'];
                } else {
                    $data['userToken'] = $isSecure['value'];
                }
            }
        }
        break;

    /********************  user request Section ********************************/
    case "userDetails":
    case "sendRequest":
    case "getReceivedRequest":
    case "getSendRequest":
    case "acceptRejectRequest":
    case "getUserMtachList":
    case "getMyConnection":
    case "getUserDetailsById":
    case "cancelRequest":
        $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($postData->access_key, $postData->secret_key, $postData->device_type, $postData->device_token);
        if ($isSecure == NO) {
            $data[STATUS] = MALICIOUS_SOURCE_STATUS;
            $data[MESSAGE] = MALICIOUS_SOURCE;
        } elseif ($isSecure == ERROR) {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = TOKEN_ERROR;
        } else {
            include_once 'UserRequestFunction.php';
            $user = new UserRequestFunction($GLOBALS['con']);
            $data = $user->call_service($_REQUEST['Service'], $postData);
            if ($isSecure != YES && $isSecure != '') {
                if ($isSecure['key'] == "Temp") {
                    $data['tempToken'] = $isSecure['value'];
                } else {
                    $data['userToken'] = $isSecure['value'];
                }
            }
        }
        break;
    /********************  Notification Section ********************************/
    case "getNotificationList":
        $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($postData->access_key, $postData->secret_key, $postData->device_type, $postData->device_token);
        if ($isSecure == NO) {
            $data[STATUS] = MALICIOUS_SOURCE_STATUS;
            $data[MESSAGE] = MALICIOUS_SOURCE;
        } elseif ($isSecure == ERROR) {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = TOKEN_ERROR;
        } else {
            include_once 'Notification.php';
            $notification = new Notification($GLOBALS['con']);
            $data = $notification->call_service($_REQUEST['Service'], $postData);
            if ($isSecure != YES && $isSecure != '') {
                if ($isSecure['key'] == "Temp") {
                    $data['tempToken'] = $isSecure['value'];
                } else {
                    $data['userToken'] = $isSecure['value'];
                }
            }
        }
        break;

    /********************  chat Section ********************************/
    case "getConversationList":
    case "createGetChat":
    case "getMessageList":
    case "chatmsgFileupload":
    case "deleteConversation":
        if ($_REQUEST['Service'] == 'chatmsgFileupload') {
            $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($_POST['access_key'], $_POST['secret_key'], $_POST['device_type'], $_POST['device_token']);
        } else {
            $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($postData->access_key, $postData->secret_key, $postData->device_type, $postData->device_token);
        }
        if ($isSecure == NO) {
            $data[STATUS] = MALICIOUS_SOURCE_STATUS;
            $data[MESSAGE] = MALICIOUS_SOURCE;
        } elseif ($isSecure == ERROR) {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = TOKEN_ERROR;
        } else {
            include_once 'ChatFunction.php';
            $chat = new ChatFunction($GLOBALS['con']);
            $data = $chat->call_service($_REQUEST['Service'], $postData);
            if ($isSecure != YES && $isSecure != '') {
                if ($isSecure['key'] == "Temp") {
                    $data['tempToken'] = $isSecure['value'];
                } else {
                    $data['userToken'] = $isSecure['value'];
                }
            }
        }
        break;

    /********************  subscription function Section ********************************/
    case "getSubscriptionIscancel":
    case "addSubscription":
    // case "updateSubscriptions":
    case "RestoreSubscription":
        // if($_REQUEST['Service'] == 'updateSubscriptions') {
        // $isSecure = YES;
        // } else {
        $isSecure = (new SecurityFunctions($GLOBALS['con']))->checkForSecurityNew($postData->access_key, $postData->secret_key, $postData->device_type, $postData->device_token);
        // }
        if ($isSecure == NO) {
            $data[STATUS] = MALICIOUS_SOURCE_STATUS;
            $data[MESSAGE] = MALICIOUS_SOURCE;
        } elseif ($isSecure == ERROR) {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = TOKEN_ERROR;
        } else {
            include_once 'SubscriptionFunction.php';
            $subscription = new SubscriptionFunction($GLOBALS['con']);
            $data = $subscription->call_service($_REQUEST['Service'], $postData);
            if ($isSecure != YES && $isSecure != '') {
                if ($isSecure['key'] == "Temp") {
                    $data['tempToken'] = $isSecure['value'];
                } else {
                    $data['userToken'] = $isSecure['value'];
                }
            }
        }
        break;
    /********************  Security Section ********************************/

    case "RefreshToken":
    case "UpdateTokenForUser":
    case "ExpiredAllTokenofUser":
    case "TestEncryption":
        {
            include_once 'SecurityFunctions.php';
            $security = new SecurityFunctions($GLOBALS['con']);
            $data = $security->call_service($_REQUEST['Service'], $postData);
        }
        break;

    default:
        $data[STATUS] = 0;
        $data[MESSAGE] = "Service not found.";
}

header('Content-type: application/json');

echo json_encode($data);
mysqli_close($GLOBALS['con']);
