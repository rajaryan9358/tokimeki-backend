<?php
    include_once 'Config.php';
    include_once 'HelperFunctions.php';
    include_once 'ConstantValues.php';
    include_once 'TableVars.php';

    $connection = $GLOBALS['con'];
    $tomorrow_timestamp = strtotime('tomorrow');
    $is_expire = 0;

    $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s<=? AND %s='%s'", TABLE_SUBSCRIPTION ,SUBSCRIPTION_Fields::isExpire, SUBSCRIPTION_Fields::endDate, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
    if ($selectQueryStmt = $connection->prepare($query)) {
        $selectQueryStmt->bind_param("is",$is_expire,$tomorrow_timestamp );
        $selectQueryStmt->execute();
        $stmt_res = $selectQueryStmt->get_result();
        if ($stmt_res->num_rows > 0) {
            $updateSuccess = false;
            while($rowData = $stmt_res->fetch_assoc()) {
                if($rowData[SUBSCRIPTION_Fields::purchasedFrom] == 'iOS'){
                    $tokenDetails = getIOSTokenDetails($rowData, false);
                    if ($tokenDetails->status == 21007) {
                        $tokenDetails = getIOSTokenDetails($rowData, true);
                    } else if ($tokenDetails->status == 21008) {
                        $tokenDetails = getIOSTokenDetails($rowData, false);
                    }
                    $tokenDetails = getIOSLatestReceipt($tokenDetails);
                    $result = updateSubscriptionStatus($connection,$tokenDetails, $rowData);
                    $updateSuccess = true;
                } else {
                    $tokenDetails =getAndroidTokenDetails($rowData);
                }
            }
            if($updateSuccess){
                // echo 'Updated successfully iOS.';
            }
        }
        else {
            // echo 'No subscriptions found';
        }
    }

    function getIOSTokenDetails($body, $isSandbox){
        $url = '';
        $curl = curl_init();
        if ($isSandbox) {
            $url = 'https://sandbox.itunes.apple.com/verifyReceipt';
        } else {
            $url = 'https://buy.itunes.apple.com/verifyReceipt';
        }
        $SHARED_SECRET = APPLE_SECRET_KEY;
        $payload = [
            'receipt-data'=> $body[SUBSCRIPTION_Fields::purchaseToken],
            'password'=> $SHARED_SECRET,
        ];
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
         ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result);
    }

    function getIOSLatestReceipt($response){
        $latestReceipt = null;
        $latest_receipt_info = $response->latest_receipt_info;           
        if (is_array($latest_receipt_info)) {
            $latestReceipt = array_reduce($latest_receipt_info, function ($a, $b) {
                return @$a->purchase_date_ms > $b->purchase_date_ms ? $a : $b ;
            });
            return $latestReceipt;
        } else {
            return 'Invalid purchase token';
        }
    }

    function getAndroidTokenDetails($body){

    } 

    function updateSubscriptionStatus($connection,$tokenDetails, $rowData){
        $is_expire = 0;
        $product_id = $tokenDetails->product_id;
        $current_timestamp = time()*1000;
        $currentDate = date('Y-m-d H:i:s');
        $userId = $rowData[SUBSCRIPTION_Fields::userId];
        if ($rowData[SUBSCRIPTION_Fields::purchasedFrom] == 'iOS' && $tokenDetails->expires_date_ms <= $current_timestamp) {
            $is_expire = 1;
            $product_id = null;
        } else if ($rowData[SUBSCRIPTION_Fields::purchasedFrom] == 'Android' && $tokenDetails->expiryTimeMillis <= $current_timestamp) {
            $is_expire = 1;
            $product_id = null;
        }
        $start_date = $rowData[SUBSCRIPTION_Fields::purchasedFrom] == 'iOS' ? $tokenDetails->purchase_date_ms : $tokenDetails->startTimeMillis;
        $end_date = $rowData[SUBSCRIPTION_Fields::purchasedFrom] == 'iOS' ? $tokenDetails->expires_date_ms : $tokenDetails->expiryTimeMillis;
        $update_stmt = $connection->prepare ("Update ". TABLE_SUBSCRIPTION ." set ".SUBSCRIPTION_Fields::startDate." = ?,".SUBSCRIPTION_Fields::endDate." = ?,".SUBSCRIPTION_Fields::isExpire." = ?,".SUBSCRIPTION_Fields::productId." = ? , ".SUBSCRIPTION_Fields::modified." = ? where ".SUBSCRIPTION_Fields::userId." = ? AND ".SUBSCRIPTION_Fields::isDelete." = ".DELETE_STATUS::NOT_DELETE."");
        $update_stmt -> bind_param("ssissi",$start_date,$end_date,$is_expire,$product_id,$currentDate,$userId);
        if ($update_stmt ->execute())
        {
            $update_stmt->close();
            return true;
        } else {
            return false;
        }
    }
?>