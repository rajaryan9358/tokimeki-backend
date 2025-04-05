<?php
// print array with format
function pr($arr = null, $exit = 1, $append_text = null)
{
    if ($arr != null) {
        echo "<pre>";
        if ($arr != null)
            echo $append_text;

        print_r($arr);

        if ($exit == 1)
            exit;
    }
}


function validateValue($value, $placeHolder)
{
    $value = strlen($value) > 0 ? $value : $placeHolder;
    return $value;
}

function validatePostValue($key, $placeHolder)
{
    $value = isset($_POST[$key]) ? $_POST[$key] : $placeHolder;
    return $value;
}

function validateObject($object, $key, $placeHolder)
{

    if (isset($object->$key)) {
        return $object->$key;
    } else {
        return $placeHolder;
    }
}

function encryptPassword($str)
{

    $qEncoded = md5($str);

    return ($qEncoded);
}

function decryptPassword($str)
{
    $qDecoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(ENCRYPTION_KEY), base64_decode($str), MCRYPT_MODE_CBC, md5(md5(ENCRYPTION_KEY))), "\0");
    return ($qDecoded);
}

function json_validate($string)
{
    if (is_string($string)) {
        @json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
    return false;
}

function getDefaultDate()
{
    return date("Y-m-d H:i:s", time());
}

function generatePassword($password)
{
    $cost = 10;

    $saltPassword = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
    $saltPassword = sprintf("$2a$%02d$", $cost) . $saltPassword;

    $finalHashPassword = crypt($password, $saltPassword);

    return $finalHashPassword;
}

function matchPassword($userPassword, $dbPassword)
{
    if (crypt($userPassword, $dbPassword) == $dbPassword)
        return 1;
    else
        return 0;
}

function matchStringValue($str1, $str2)
{
    if (strcmp($str1, $str2))
        return 1;
    else
        return 0;
}


function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function generateRandomCode($length)
{
    $numbers = range('0', '9');
    $final_array = array_merge($numbers);
    $randomString = "";
    while ($length--) {
        $key = array_rand($final_array);
        $randomString .= $final_array[$key];
    }
    return $randomString;
}

function copy_value($v)
{
    return $v;
}

function fetch_assoc_all_values($stmt)
{
    if ($stmt->num_rows > 0) {
        $result = array();
        $md = $stmt->result_metadata();
        $params = array();
        while ($field = $md->fetch_field()) {
            $params[] = &$result[$field->name];
        }
        call_user_func_array(array($stmt, 'bind_result'), $params);
        if ($stmt->fetch())
            return $result;
    }

    return null;
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function errorLogFunction($error_message)
{
    $log_file = date("F_j_Y") . '_log.txt';
    $file = 'error_log_' . date("Y_m_d") . '.txt';
    $current = @file_get_contents($file);
    $current = "\n----------------------------\n";
    //$current .= basename(__FILE__) .'/LogFile/'. "\n----------------------------\n";
    $current .= '/LogFile/' . "\n----------------------------\n";
    $current .= "Date := " . date("Y-m-d H:i:s") . "\n----------------------------\n";
    $current .= $error_message;
    $current .= (microtime(true)) - time() . " seconds elapsed\n\n";
    // Write the contents back to the file

    file_put_contents(dirname(__FILE__) . './LogFile/' . $file, $current, FILE_APPEND);
}

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return round(($miles * 1.609344), 2);
    } else if ($unit == "N") {
        return round(($miles * 0.8684), 2);
    } else {
        return round($miles, 2);
    }
}

//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";


/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
/*::                                                                         :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles                                   :*/
/*::                  'K' is kilometers (default)                            :*/
/*::                  'N' is nautical miles                                  :*/
/*::                                                                         :*/
/*::	distance(32.96, -96.80, 29.46, -98.53, "M") . " Miles<br>";			 :*/
/*::	distance(32.96, -96.80, 29.46, -98.53, "K") . " Kilometers<br>";	 :*/
/*::	distance(32.96, -96.80, 29.46, -98.53, "N") . " Nautical Miles<br>"; :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/

function distancefromlocation($lat1, $lon1, $lat2, $lon2, $unit)
{
    if ($lat1 != '' && $lon1 != '' && $lat2 != '' && $lon2 != '' && $unit != '') {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    } else {

        return false;
    }
}

function fetch($stmt)
{
    $array = array();

    if ($stmt instanceof mysqli_stmt) {
        $variables = array();
        $data = array();

        /* get resultset for metadata */
        $meta = $stmt->result_metadata();

        while ($field = $meta->fetch_field())
            $variables[] = &$data[$field->name]; // pass by reference

        call_user_func_array(array($stmt, 'bind_result'), $variables);

        $i = 0;
        while ($stmt->fetch()) {
            $array[$i] = array();
            foreach ($data as $k => $v)
                $array[$i][$k] = $v;
            $i++;
            // don't know why, but when I tried $array[] = $data, I got the same one result in all rows
        }
    } else if ($stmt instanceof mysqli_result) {
        while ($row = $stmt->fetch_assoc())
            $array[] = $row;
    }
    return $array;
}

/*
 *
 * if ($data_query_stmt = $connection->prepare($dataQuery)) {
            $data_query_stmt->execute();
            $data_query_stmt->store_result();
            if ($data_query_stmt->num_rows > 0) {

//                while($$val=fetch_assoc_all_values($data_query_stmt))
//                {
//                  $ar=array();
//                    $a['user-id']=$ar['id'];
//
//                }
                $rows = fetch($data_query_stmt);
                $data_query_stmt->close();
 * */

function createThumbnailImage($originalImage, $imagename)
{
    list($w, $h) = getimagesize($originalImage);
    $sourceRatio = $w / $h;
    $imagePathS = USER_THUMB_IMAGE . $imagename;
    $resizeObj = new resize($originalImage);
    $resizeObj->resizeImage(110, 110, 'exact');
    $resizeObj->saveImage($imagePathS, 100);
}

function uploadSingleImageWithMultipart($upload_key, $file_name, $folder_path)
{
    //  $uploadDirImg = $_SERVER['DOCUMENT_ROOT'] .
    $uploadDir = "." . $folder_path;
    $uploadFile = $uploadDir . $file_name;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    } else {
        chmod($uploadDir, 0777);
    }
    if ($upload_key['name'] != '') {
        if ($upload_key['error'] > 0) {
            $status = FAILED;
            $message = "Invalid file";
        } else {
            if (($upload_key['error'] < 24000000)) {
                $status = FAILED;
                $message = "Media size exceeds than 24 MB.";
            } else {
                if ($upload_key["error"] > 0) {
                    $message = $upload_key["error"];
                    $status = FAILED;
                } else {
                    if (move_uploaded_file($upload_key['tmp_name'], $uploadFile)) {
                        $status = SUCCESS;
                        $message = "Image Uploaded Successfully.";
                    } else {
                        $status = FAILED;
                        $message = "Failed to upload Image";
                    }
                }
            }
        }
    }
    $data[STATUS] = $status;
    $data[MESSAGE] = $message;
    return $data;
}

/***
 * @param mysqli $connection
 * @param $userId
 * @return array
 */

function getUserInfoFromId(mysqli $connection, $userId)
{
    $deleteStatus = DELETE_STATUS::NOT_DELETE;
    $testData = TEST_DATA;

    $selectQuery = "SELECT u.*  FROM " . TABLE_USERS . " u  WHERE u.id=? AND u.is_delete=? AND u.is_testdata=?";
    $stmt = $connection->prepare($selectQuery);
    $stmt->bind_param("iii", $userId, $deleteStatus, $testData);
    $userInfo = null;
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($val = fetch_assoc_all_values($stmt)) {
            unset($val['device_type']);
            unset($val['is_testdata']);
            unset($val['created_date']);
            unset($val['modified_date']);

            $val['profile_pic'] = getUserProfilePic($val['profile_pic'], $val['login_type']);

            $userInfo = $val;
        }
    }
    return $userInfo;
}

function getUserProfilePic($profilePic, $loginType)
{
    if ($loginType == "NORMAL") {
        return URL_PROFILE_PIC . $profilePic;
    } else {
        return $profilePic;
    }
}


function fileUploadCodeToMessage($code)
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
            break;
        case UPLOAD_ERR_PARTIAL:
            $message = "The uploaded file was only partially uploaded";
            break;
        case UPLOAD_ERR_NO_FILE:
            $message = "No file was uploaded";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $message = "Missing a temporary folder";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $message = "Failed to write file to disk";
            break;
        case UPLOAD_ERR_EXTENSION:
            $message = "File upload stopped by extension";
            break;

        default:
            $message = "Unknown upload error";
            break;
    }
    return $message;
}

function fwrite_stream($fp, $string)
{
    for ($written = 0; $written < strlen($string); $written += $fwrite) {
        $fwrite = fwrite($fp, substr($string, $written));
        if ($fwrite === false) {
            return $written;
        }
    }
    return $written;
}


function callAPIASync($api, $content)
{
    $protocol = "tcp";
    $host = HOST_URL;
    $port = 80;
    $path = WS_PATH . "/MobileAppService.php?Service=$api";
    $timeout = 5;


    try {
        # Open our socket to the API Server.
        $socket = fsockopen($protocol . "://" . $host, $port,
            $errno, $errstr, $timeout);

        # Create the request body, and make the request.
        $req = "";
        $req .= "POST " . $path . " HTTP/1.1\r\n";
        $req .= "Host: " . $host . "\r\n";
        $req .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $req .= "Cache-Control: no-cache\r\n";
        $req .= "Content-length: " . strlen($content) . "\r\n";
        $req .= "\r\n";
        $req .= $content;


        fwrite($socket, $req);
        fread($socket,26);
        fclose($socket);

    } catch (Exception $e) {
        error_log("Error {$e->getCode()} : {$e->getMessage()}");
    }
}


function createImageThumbnail($uploadFile, $fileName, $uploadDirImg)
{


    $width = 250;
    $height = 250;

    // Get new dimensions
    list($width_orig, $height_orig) = getimagesize($uploadFile);

    $ratio_orig = $width_orig / $height_orig;

    if ($width / $height > $ratio_orig) {
        $width = $height * $ratio_orig;
    } else {
        $height = $width / $ratio_orig;
    }


    if (!is_dir($uploadDirImg)) {
        mkdir($uploadDirImg, 0777, true);
    }


    $thumbImgName = getThumbNameFromFileName($fileName);


    $ffmpeg = 'C:/FFMPEG/bin/ffmpeg'; //Europa Server

    $thumbFile = SERVER_FEED_IMAGE_PATH . SERVER_FEED_THUMB_PATH . $thumbImgName;

    $cmd = "$ffmpeg -i $uploadFile -vf scale=w=$width:h=$height -crf 1 $thumbFile";
    shell_exec($cmd);
    return $thumbImgName;

}


function createVideoThumbnail($uploadFile, $fileName, $uploadDirImg)
{

    if (!is_dir($uploadDirImg)) {
        mkdir($uploadDirImg, 0777, true);
    }

    $ffmpeg = 'C:/FFMPEG/bin/ffmpeg';

    $thumbImgName = getThumbNameFromFileName($fileName);
    $thumbFile = SERVER_FEED_VIDEO_PATH . SERVER_FEED_THUMB_PATH . getThumbNameFromFileName($thumbImgName);
    $size = "250x250";
    $cmd = "$ffmpeg -i $uploadFile -y -vcodec mjpeg -vframes 1 -an -f rawvideo -s $size $thumbFile";

    shell_exec($cmd);

    return $thumbImgName;

}

function getThumbNameFromFileName($fileName)
{
    $nameFile = explode(".", $fileName);
    return $nameFile[0] . ".jpg";
}

function addEntryInNotificationTable(mysqli $connection, $arrayNotificationValues, &$isQuerySuccess)
{

    $isTestData = TEST_DATA;
    $currentDate = getDefaultDate();
    $insertNotification = "INSERT INTO " . TABLE_NOTIFICATION . " (sender_id,receiver_id,notification_type_id,notification_type,
    notification_text,created_date,is_testdata) VALUES(?,?,?,?,?,?,?)";
    $insertStmtNotification = $connection->prepare($insertNotification);


    foreach ($arrayNotificationValues as $notificationObj) {

        $senderId = $notificationObj['sender_id'];
        $receiverId = $notificationObj['receiver_id'];
        $notificationTypeId = $notificationObj['notification_type_id'];
        $notificationType = $notificationObj['notification_type'];
        $notificationText = $notificationObj['notification_text'];
        $insertStmtNotification->bind_param('iiiissi', $senderId, $receiverId, $notificationTypeId, $notificationType,
            $notificationText, $currentDate, $isTestData);
        if ($isQuerySuccess && $insertStmtNotification->execute()) {
            $isQuerySuccess = true;

            echo $insertStmtNotification->error;
        } else {
            echo $insertStmtNotification->error;
            $isQuerySuccess = false;
            break;
        }

    }


}

function printQuery($sql, $params)
{
    for ($i = 0; $i < count($params); $i++) {

        if (is_string($params[$i])) {
            $par = "'" . $params[$i] . "'";
            $sql = preg_replace('/\?/', $par, $sql, 1);
        } else {

            $sql = preg_replace('/\?/', $params[$i], $sql, 1);
        }
    }
    return $sql;
}
?>
