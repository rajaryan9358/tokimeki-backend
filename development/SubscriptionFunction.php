<?php
class SubscriptionFunction
{
    protected $connection;

    public function __construct(mysqli $con)
    {
        $this->connection = $con;
    }

    public function call_service($service, $postData)
    {
        switch ($service) {
            case "getSubscriptionIscancel":
                {
                    return $this->getSubscriptionIscancel($postData);
                }
                break;
            case "addSubscription":
                {
                    return $this->addSubscription($postData);
                }
                break;
            case "RestoreSubscription":
                {
                    return $this->RestoreSubscription($postData);
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

    public function getSubscriptionIscancel($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        if ($userId == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=%s", TABLE_SUBSCRIPTION, SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("i", $userId);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                $subscribeData = fetch_assoc_all_values($selectQueryStmt);
                $d2 = new Datetime();
                $d3 = $d2->format('U');
                $currentDate = $d3 * 1000;
                $responseData = new stdClass();
                $responseData = (object) [
                    'plan_type' => $subscribeData[SUBSCRIPTION_Fields::planType],
                    'resubscribeUI' => false,
                    'plan_IsActive' => true,
                ];
                if ($subscribeData[SUBSCRIPTION_Fields::purchasedFrom] == 'iOS') {
                    $tokenDetails = $this->getIOSTokenDetails($subscribeData, false);
                    // print_r()
                    if ($tokenDetails->status == 21007) {
                        $tokenDetails = $this->getIOSTokenDetails($subscribeData, true);
                    } else if ($tokenDetails->status == 21008) {
                        $tokenDetails = $this->getIOSTokenDetails($subscribeData, false);
                    }
                    $pendingRenewalInfo = $tokenDetails->pending_renewal_info;
                    // $pendingRenewalInfo = json_decode($pendingRenewalInfo, TRUE);
                    if (sizeof($pendingRenewalInfo) > 0) {
                        $autoRenewStatus = $pendingRenewalInfo[0]->auto_renew_status;
                        if ($autoRenewStatus == 0 && $subscribeData['end_date'] >= $currentDate) {
                            $responseData = (object) [
                                'plan_type' => $subscribeData[SUBSCRIPTION_Fields::planType],
                                'resubscribeUI' => true,
                                'plan_IsActive' => false,
                            ];
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'User auto renewal is cancel!';
                            $data[DATA] = $responseData;
                            return $data;
                        } else {
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'User auto renewal is active!';
                            $data[DATA] = $responseData;
                            return $data;
                        }
                    } else {
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'User auto renewal is active!';
                        $data[DATA] = $responseData;
                        return $data;
                    }
                } else {
                    // ANDROID CODE IS HERE
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }
            } else {
                $data[STATUS] = SUCCESS;
                $data[MESSAGE] = "User is not subscibed yet!";
                return $data;
            }
        } else {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = SOMETHING_WRONG;
            return $data;
        }
    }

    public function getIOSTokenDetails($body, $isSandbox)
    {
        $url = '';
        $curl = curl_init();
        if ($isSandbox) {
            $url = 'https://sandbox.itunes.apple.com/verifyReceipt';
        } else {
            $url = 'https://buy.itunes.apple.com/verifyReceipt';
        }
        $SHARED_SECRET = APPLE_SECRET_KEY;
        $payload = [
            'receipt-data' => $body[SUBSCRIPTION_Fields::purchaseToken],
            'password' => $SHARED_SECRET,
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

    public function getIOSLatestReceipt($response)
    {
        $latestReceipt = null;
        $latest_receipt_info = $response->latest_receipt_info;
        if (is_array($latest_receipt_info)) {
            $latestReceipt = array_reduce($latest_receipt_info, function ($a, $b) {
                return @$a->purchase_date_ms > $b->purchase_date_ms ? $a : $b;
            });
            return $latestReceipt;
        } else {
            return 'Invalid purchase token';
        }
    }

    public function getAndroidTokenDetails($body)
    {

    }

    public function addSubscription($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $purchaseForm = validateObject($postData, param_purchase_form, "");
        $orderId = validateObject($postData, param_orderid, "");
        $purchaseTime = validateObject($postData, param_purchase_time, "");
        $purchaseToken = validateObject($postData, param_purchase_token, "");
        $productId = validateObject($postData, param_product_id, "");
        $price = validateObject($postData, param_price, "");
        $isTestData = validateObject($postData, param_is_testdata, "");
        $currentDate = date('Y-m-d H:i:s');
        if ($userId == "" || $purchaseForm == "" || $orderId == "" || $purchaseTime == "" || $purchaseToken == "" || $productId == "" || $price == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        $isSubscribed = false;
        $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=%s", TABLE_SUBSCRIPTION, SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
        if ($selectQueryStmt = $this->connection->prepare($query)) {
            $selectQueryStmt->bind_param("i", $userId);
            $selectQueryStmt->execute();
            $selectQueryStmt->store_result();
            if ($selectQueryStmt->num_rows > 0) {
                $isSubscribed = true;
            } else {
                $isSubscribed = false;
            }
            /**
             * IAP flow - start
             */
            $reqData = [
                'user_id' => $userId,
                'product_id' => $productId,
                'price' => $price,
                'purchased_from' => $purchaseForm,
                'order_id' => $orderId,
                'purchase_time' => $purchaseTime,
                'purchase_token' => $purchaseToken,
            ];
            $tokenDetails = [];
            if ($purchaseForm == 'iOS') {
                $tokenDetails = $this->getIOSTokenDetails($reqData, false);
            } else {
                $tokenDetails = $this->getAndroidTokenDetails($reqData);
            }
            // For iOS part
            if ($purchaseForm == 'iOS') {
                if ($tokenDetails->status == 21007) {
                    $tokenDetails = $this->getIOSTokenDetails($reqData, true);
                } else if ($tokenDetails->status == 21008) {
                    $tokenDetails = $this->getIOSTokenDetails($reqData, false);
                }

                $tokenDetails = $this->getIOSLatestReceipt($tokenDetails);
                $is_expire = '0';
                $d2 = new Datetime();
                $d3 = $d2->format('U');
                $TodayDate = $d3 * 1000;
                $is_expire = '0';
                if (gettype($tokenDetails) == 'object' && $tokenDetails->expires_date_ms <= $TodayDate) {
                    $is_expire = '1';
                }
                $planType = $tokenDetails->product_id == 'com.twoconnect30.classic' ? 'Classic' : $tokenDetails->product_id == 'com.twoconnect30.premium' ? 'Premium' : 'vip';
                $startDate = $tokenDetails->purchase_date_ms;
                $endDate = $tokenDetails->expires_date_ms;
                // $reqData['is_expire'] = $is_expire;
            } else {
                // for android
                if ($tokenDetails->status == 21007) {
                    $tokenDetails = $this->getIOSTokenDetails($reqData, true);
                } else if ($tokenDetails->status == 21008) {
                    $tokenDetails = $this->getIOSTokenDetails($reqData, false);
                }

                $tokenDetails = $this->getIOSLatestReceipt($tokenDetails);
                $is_expire = '0';
                $d2 = new Datetime();
                $d3 = $d2->format('U');
                $TodayDate = $d3 * 1000;
                $is_expire = '0';
                if (gettype($tokenDetails) == 'object' && $tokenDetails->expires_date_ms <= $TodayDate) {
                    $is_expire = '1';
                }
                $planType = $tokenDetails->product_id == 'com.twoconnect30.classic' ? 'Classic' : $tokenDetails->product_id == 'com.twoconnect30.premium' ? 'Premium' : 'vip';
                $startDate = $tokenDetails->purchase_date_ms;
                $endDate = $tokenDetails->expires_date_ms;
            }
            /**
             * IAP flow - end
             */
            $saveSuccess = false;
            // Add entry into subscriptions table or update the same
            if ($isSubscribed) {
                $update_stmt = $this->connection->prepare("Update " . TABLE_SUBSCRIPTION . " set " . SUBSCRIPTION_Fields::purchasedFrom . " = ? , " . SUBSCRIPTION_Fields::orderId . " = ? , " . SUBSCRIPTION_Fields::productId . " = ? , " . SUBSCRIPTION_Fields::purchaseTime . " = ?, " . SUBSCRIPTION_Fields::purchaseToken . " = ? ," . SUBSCRIPTION_Fields::planType . " = ? ," . SUBSCRIPTION_Fields::startDate . " = ?," . SUBSCRIPTION_Fields::endDate . " = ?, " . SUBSCRIPTION_Fields::price . " = ?, " . SUBSCRIPTION_Fields::isExpire . " = ? , " . SUBSCRIPTION_Fields::modified . " = ? where " . SUBSCRIPTION_Fields::userId . " = ? and last_insert_id(id)");
                $update_stmt->bind_param("sssssssssisi", $purchaseForm, $orderId, $productId, $purchaseTime, $purchaseToken, $planType, $startDate, $endDate, $price, $is_expire, $currentDate, $userId);
                // $update_stmt -> bind_param("sssssssssisi",$purchaseForm,$orderId,$productId,$purchaseTime,$purchaseToken,$planType,$startDate,$endDate,$price,$is_expire,$currentDate,$userId);
                if ($update_stmt->execute()) {
                    $subscribe_inserted_id = $update_stmt->insert_id;
                    $saveSuccess = true;
                    $update_stmt->close();
                } else {
                    $data[STATUS] = FAILED;
                    $data[MESSAGE] = SOMETHING_WRONG;
                    return $data;
                }
            } else {
                $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`,`%s`,`%s`,`%s`,`%s`,`%s`,`%s`,`%s`,`%s`",
                    SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::purchasedFrom, SUBSCRIPTION_Fields::orderId, SUBSCRIPTION_Fields::productId, SUBSCRIPTION_Fields::purchaseTime, SUBSCRIPTION_Fields::purchaseToken, SUBSCRIPTION_Fields::planType, SUBSCRIPTION_Fields::startDate, SUBSCRIPTION_Fields::endDate,
                    SUBSCRIPTION_Fields::price, SUBSCRIPTION_Fields::isExpire, SUBSCRIPTION_Fields::created, SUBSCRIPTION_Fields::modified, SUBSCRIPTION_Fields::isTestdata);
                $insertQuery = "INSERT INTO " . TABLE_SUBSCRIPTION . " ($insertFields) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                if ($insertSubStmt = $this->connection->prepare($insertQuery)) {
                    $insertSubStmt->bind_param("isssssssssissi", $userId, $purchaseForm, $orderId, $productId, $purchaseTime, $purchaseToken, $planType, $startDate, $endDate, $price, $is_expire, $currentDate, $currentDate, $isTestData);
                    if ($insertSubStmt->execute()) {
                        $subscribe_inserted_id = $insertSubStmt->insert_id;
                        $saveSuccess = true;
                        $insertSubStmt->close();
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = $insertSubStmt->error;
                        return $data;
                    }
                }
            }
            if ($saveSuccess == true) {
                $subscribeData = [];
                $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=%s", TABLE_SUBSCRIPTION, SUBSCRIPTION_Fields::id, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
                if ($selectQueryStmt = $this->connection->prepare($query)) {
                    $selectQueryStmt->bind_param("i", $subscribe_inserted_id);
                    $selectQueryStmt->execute();
                    $selectQueryStmt->store_result();
                    if ($selectQueryStmt->num_rows > 0) {
                        $subscribeData = fetch_assoc_all_values($selectQueryStmt);
                    }
                }
                $data[STATUS] = SUCCESS;
                $data[MESSAGE] = 'Subscription plan saved successfully.';
                $data[DATA] = $subscribeData;
                return $data;
            }

        } else {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = SOMETHING_WRONG;
            return $data;
        }
    }

    public function RestoreSubscription($postData)
    {
        $userId = validateObject($postData, param_userid, "");
        $TransctionReceipt = validateObject($postData, param_transactionreceipt, "");
        $purchaseForm = validateObject($postData, param_purchaseform, "");
        $isTestData = validateObject($postData, param_is_testdata, "");
        $price = validateObject($postData, param_price, "");
        $currentDate = date('Y-m-d H:i:s');
        if ($userId == "" || $TransctionReceipt == "" || $purchaseForm == "" || $price == "") {
            $data[STATUS] = FAILED;
            $data[MESSAGE] = DEV_ERROR;
            return $data;
        }
        // check token
        $reqData = ["purchase_token" => $TransctionReceipt];
        $tokenDetails = [];
        $CheckInDB = false;
        if ($purchaseForm == 'iOS') {
            $tokenDetails = $this->getIOSTokenDetails($reqData, false);
            if ($tokenDetails->status == 21007) {
                $tokenDetails = $this->getIOSTokenDetails($reqData, true);
            } else if ($tokenDetails->status == 21008) {
                $tokenDetails = $this->getIOSTokenDetails($reqData, false);
            }
            $LatestReceipt = $this->getIOSLatestReceipt($tokenDetails);
            $current_timestamp = time() * 1000;
            $pendingRenewalInfo = $tokenDetails->pending_renewal_info;
            if (sizeof($pendingRenewalInfo) > 0 && $pendingRenewalInfo[0]->auto_renew_status == 1 && $LatestReceipt->expires_date_ms > $current_timestamp) {
                //inapp active entry is missing in our db
                $CheckInDB = true;
            }
        } else {
            $tokenDetails = $this->getAndroidTokenDetails($reqData);
        }
        $orderId = $LatestReceipt->transaction_id;
        $productId = $LatestReceipt->product_id;
        $purchaseTime = $LatestReceipt->purchase_date;
        $planType = $LatestReceipt->product_id == 'com.twoconnect30.classic' ? 'Classic' : $LatestReceipt->product_id == 'com.twoconnect30.premium' ? 'Premium' : 'vip';
        $startDate = $LatestReceipt->purchase_date_ms;
        $endDate = $LatestReceipt->expires_date_ms;
        $is_expire = 0;
        if ($CheckInDB) {
            $query = sprintf("SELECT * FROM %s WHERE %s=? AND %s=%s", TABLE_SUBSCRIPTION, SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::isDelete, DELETE_STATUS::NOT_DELETE);
            if ($selectQueryStmt = $this->connection->prepare($query)) {
                $selectQueryStmt->bind_param("i", $userId);
                $selectQueryStmt->execute();
                $selectQueryStmt->store_result();
                if ($selectQueryStmt->num_rows > 0) {
                    $users = fetch_assoc_all_values($selectQueryStmt);
                    $isExpired = $users[SUBSCRIPTION_Fields::isExpire];
                    // update record
                    $update_stmt = $this->connection->prepare("Update " . TABLE_SUBSCRIPTION . " set " . SUBSCRIPTION_Fields::purchasedFrom . " = ? , " . SUBSCRIPTION_Fields::orderId . " = ? , " . SUBSCRIPTION_Fields::productId . " = ? , " . SUBSCRIPTION_Fields::purchaseTime . " = ?, " . SUBSCRIPTION_Fields::purchaseToken . " = ? ," . SUBSCRIPTION_Fields::planType . " = ? ," . SUBSCRIPTION_Fields::startDate . " = ?," . SUBSCRIPTION_Fields::endDate . " = ?, " . SUBSCRIPTION_Fields::price . " = ?, " . SUBSCRIPTION_Fields::isExpire . " = ? , " . SUBSCRIPTION_Fields::modified . " = ? where " . SUBSCRIPTION_Fields::userId . " = ? and last_insert_id(id)");
                    $update_stmt->bind_param("sssssssssisi", $purchaseForm, $orderId, $productId, $purchaseTime, $TransctionReceipt, $planType, $startDate, $endDate, $price, $is_expire, $currentDate, $userId);
                    // $update_stmt -> bind_param("sssssssssisi",$purchaseForm,$orderId,$productId,$purchaseTime,$purchaseToken,$planType,$startDate,$endDate,$price,$is_expire,$currentDate,$userId);
                    if ($update_stmt->execute()) {
                        $subscribe_inserted_id = $update_stmt->insert_id;
                        $saveSuccess = true;
                        $update_stmt->close();
                        $data[STATUS] = SUCCESS;
                        $data[MESSAGE] = 'Restore subscription successfully!';
                        return $data;
                    } else {
                        $data[STATUS] = FAILED;
                        $data[MESSAGE] = SOMETHING_WRONG;
                        return $data;
                    }
                } else {
                    // insert
                    $insertFields = sprintf("`%s`, `%s`, `%s`, `%s`, `%s`, `%s`,`%s`,`%s`,`%s`,`%s`,`%s`,`%s`,`%s`,`%s`",
                        SUBSCRIPTION_Fields::userId, SUBSCRIPTION_Fields::purchasedFrom, SUBSCRIPTION_Fields::orderId, SUBSCRIPTION_Fields::productId, SUBSCRIPTION_Fields::purchaseTime, SUBSCRIPTION_Fields::purchaseToken, SUBSCRIPTION_Fields::planType, SUBSCRIPTION_Fields::startDate, SUBSCRIPTION_Fields::endDate,
                        SUBSCRIPTION_Fields::price, SUBSCRIPTION_Fields::isExpire, SUBSCRIPTION_Fields::created, SUBSCRIPTION_Fields::modified, SUBSCRIPTION_Fields::isTestdata);
                    $insertQuery = "INSERT INTO " . TABLE_SUBSCRIPTION . " ($insertFields) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    if ($insertSubStmt = $this->connection->prepare($insertQuery)) {
                        $insertSubStmt->bind_param("isssssssssissi", $userId, $purchaseForm, $orderId, $productId, $purchaseTime, $TransctionReceipt, $planType, $startDate, $endDate, $price, $is_expire, $currentDate, $currentDate, $isTestData);
                        if ($insertSubStmt->execute()) {
                            $subscribe_inserted_id = $insertSubStmt->insert_id;
                            $insertSubStmt->close();
                            $data[STATUS] = SUCCESS;
                            $data[MESSAGE] = 'Restore subscription successfully!';
                            return $data;
                        } else {
                            $data[STATUS] = FAILED;
                            $data[MESSAGE] = SOMETHING_WRONG;
                            return $data;
                        }
                    }
                }
            }
        } else {
            $data[STATUS] = SUCCESS;
            $data[MESSAGE] = 'You have no active subscription!';
            return $data;
        }
    }
}
