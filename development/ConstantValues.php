<?php
/*******************************************************************************/
/*                                 Api Names                                   */
/*******************************************************************************/
// Users
define("Register", 'register');
define("Login", 'login');
define("ForgotPwd", 'forgotPassword');
define("Logout", 'logout');
define("SocialLogin", 'socialLogin');
define("DeleteAccount", 'deleteAccount');


/*******************************************************************************/
/*                                Request Parameters                           */
/*******************************************************************************/
define("param_offset", "offset");
define("param_userid", "userid");
define("param_login_userid", "loginUserId");
define("param_email", "email");
define("param_password", "password");
define("param_firstname", "first_name");
define("param_lastname", "last_name");
define("param_nickname", "nick_name");
define("param_DOB", "date_of_birth");
define("param_marital_status", "marital_status");
define("param_google_id", "google_id");
define("param_is_testdata", "isTestData");
define("param_device_type", "device_type");
define("param_device_token", "device_token");
define("param_guid", "guid");
define("param_verification_code", "verificationCode");
define("param_message", "message");
define("param_avtar", "avtar");
define("param_social_type", "social_type");
define("param_social_id", "social_id");
define("param_city", "city");
define("param_categoryid", "category_ids");
define("param_questions", "questions");
define("param_category", "category");
define("param_oldpassword", "oldpassword");
define("param_newpassword", "newpassword");
define("param_senderId", "senderid");
define("param_receiverId", "receiverid");
define("param_start", "start");
define("param_limit", "limit");
define("param_requestid", "request_id");
define("param_request_type", "request_type");
define("param_subscribe_plan", "subscribe_plan");
define("param_converstionid", "conversionid");
define("param__other_userid", "other_user_id");
define("param_purchase_form", "purchase_form");
define("param_orderid", "order_id");
define("param_purchase_time", "purchase_time");
define("param_purchase_token", "purchase_token");
define("param_product_id", "product_id");
define("param_price", "price");
define("param_transactionreceipt", "transactionreceipt");
define("param_purchaseform", "purchaseform");

/*******************************************************************************/
/*                                Response Parameters                           */
/*******************************************************************************/

define("MESSAGE", "message");
define("STATUS", "status");
define("DATA", "data");
define("SUCCESS", "1");
define("FAILED", "0");
define("NODATA", "2");
define("USER", "user");
define("USER_TOKEN", "userToken");
define("EMAIL_NOT_EXISTS", "Email is not exist");
define("DEFAULT_NO_RECORDS", "No Records Found.");
define("TOTAL_NO_RECORDS", "TotalNoOfRecords");
/*******************************************************************************/
/*                                Constant Enums                               */
/*******************************************************************************/

abstract class DELETE_STATUS
{
    const IS_DELETE = 1;
    const NOT_DELETE = 0;
}

abstract class API_STATUS
{
    const SUCCESS = 1;
    const FAILED = 0;
}
abstract class VERIFICATION_STATUS
{
    const Verified = 1;
    const Not = 0;
}

/*******************************************************************************/
/*                                APP Constant                                 */
/*******************************************************************************/

define("APP_NAME", "TOKIMEKIApp");
define("LIMIT_OFFSET_CONTACT", 15);
define("LIMIT_OFFSET_REQUEST", 10);
define("TEST_DATA", 1);
define("YES", 'yes');
define("NO", 'no');

define("ERROR",  'error');
define("MALICIOUS_SOURCE_STATUS","failed");
define("MALICIOUS_SOURCE","Malicious source detected");
define("TOKEN_ERROR","Please ensure that security token is supplied in your request.");
define("DEFAULT_NO_RECORD","No Data Found.");
/*******************************************************************************/
/*                                Messages                                     */
/*******************************************************************************/

define("SOMETHING_WRONG", "Something went wrong. Please try again!");
define("INVALID_PASSWORD", "Please enter a valid password");
define("PASSWORD_UPDATED", "Password is updated");
define("DEV_ERROR", "Please ensure that data supplied in your request.");

/*******************************************************************************/
/*                                FCM Account                                */
/*******************************************************************************/

define("FCM_SERVERKEY","");
define("APPLE_SECRET_KEY","1637fb939b954f82ad0c899340e4f317");
?>