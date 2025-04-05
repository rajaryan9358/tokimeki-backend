<?php
/*******************************************************************************/
/*                                 Table Names                                 */
/*******************************************************************************/


define("TABLE_ADMIN_CONFIG", "admin_config");
define("TABLE_APP_TOKENS", "app_token"); 
define("TABLE_ANSWER", "answer"); 
define("TABLE_USERS", "user");
define("TABLE_CATEGORY", "category");
define("TABLE_CITY", "city");
define("TABLE_NOTIFICATION", "notification");
define("TABLE_POINTS", "points");
define("TABLE_QUESTION", "question");
define("TABLE_SUBSCRIPTION", "subscription");
define("TABLE_USER_ATTEMPTS", "user_attempts");
define("TABLE_USER_CITY", "user_city");
define("TABLE_USER_POINTS", "user_points");
define("TABLE_USER_REQUEST", "user_request");
define("TABLE_REQUEST_CITY", "request_city");
define("TABLE_CONVERSATIONS", "conversations");
define("TABLE_CHAT_MESSAGE", "chat_messages");
define("TABLE_USER_BLOCKLIST", "user_block_list");
define("TABLE_REJECT_MATCH", "reject_match");

/*******************************************************************************/
/*                              Table field names                              */
/*******************************************************************************/
abstract class APP_Config_Fields {
    const id = "id";
    const config_key = "config_key";
    const config_value = "config_value";
    const value_unit = "value_unit";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}

abstract class APP_TOKENS_Fields{
    const id = "id";
    const userId = "user_id";
    const token = "token";
    const token_type = "token_type";
    const status = "status";
    const expiry = "expiry";
    const access_count = "access_count";
    const device_token = "device_token";
    const device_type = "device_type";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class ANSWER_Fields{
    const id = "id";
    const questionId = "question_id";
    const answer = "answer";
    const answerFr = "answer_fr";
    const score = "score";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class CATEGORY_Fields{
    const id = "id";
    const categoryName = "category_name";
    const categoryNameFr = "category_name_fr";
    const parentId = "parentid";
    const score = "score";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class CITY_Fields{
    const id = "id";
    const cityName = "city_name";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class NOTIFICATION_Fields{
    const id = "id";
    const notificationType = "notification_type";
    const notificationMsg = "notification_msg";
    const receiverId = "receiver_id";
    const senderId = "sender_id";
    const readRecipients = "read_recipients";
    const notificationExpiryDate = "notification_expiry_date";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class POINTS_Fields{
    const id = "id";
    const minPoint = "min_point";
    const maxPoint = "max_point";
    const applicablePlan = "applicable_plan";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class QUESTION_Fields{
    const id = "id";
    const question = "question";
    const questionFr = "question_fr";
    const categoryId = "category_id";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class SUBSCRIPTION_Fields{
    const id = "id";
    const userId = "user_id";
    const purchasedFrom = "purchased_from";
    const orderId = "order_id";
    const productId = "product_id";
    const purchaseTime = "purchase_time";
    const purchaseToken = "purchase_token";
    const planType = "plan_type";
    const startDate = "start_date";
    const endDate = 'end_date';
    const price = "price";
    const isExpire = "is_expire";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class USERS_Fields{
    const id = "id";
    const emailId = "email_id";
    const password = "password";
    const firstName = "first_name";
    const lastName = "last_name";
    const nickName = "nick_name";
    const profileImage = "profile_image";
    const avtarName = "avtar_name";
    const dateOfBirth = "date_of_birth";
    const isVerify = "is_verify";
    const isSubscribe = "is_subscribe";
    const isActive = "is_active";
    const googleId = "google_id";
    const appleId = "apple_id";
    const verificationCode = "verification_code";
    const maritialStatus = "marital_status";
    const GUID = "guid";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class USERATTEMPTS_Fields{
    const id = "id";
    const userId = "user_id";
    const categoryId = "category_id";
    const questionId = "question_id";
    const answerId = "answer_id";
    const points = "points";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class USERCITY_Fields{
    const id = "id";
    const userId = "user_id";
    const cityId = "city_id";
    const stayPercentage = "stay_percentage";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class USERPOINTS_Fields{
    const id = "id";
    const userId = "user_id";
    const point = "point";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class USERREQUEST_Fields{
    const id = "id";
    const senderId = "sender_id";
    const receiverId = "receiver_id";
    const status = "status";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class REQUESTCITY_Fields{
    const id = "id";
    const emailId = "email_id";
    const city = "city";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}
abstract class CONVERSTIONS_Fields{
    const id = "id";
    const lastMessage = "last_message";
    const senderId = "sender_id";
    const receiverId = "receiver_id";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
    const isDeleteByReceiver = "is_delete_by_receiver";
    const isDeleteBySender = "is_delete_by_sender";
}
abstract class CHATMESSAGE_Fields{
    const id = "id";
    const conversationId = "conversation_id";
    const senderId = "sender_id";
    const receiverId = "receiver_id";
    const messageType = "message_type";
    const message = "message";
    const isRead = "is_read";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
    const isDeleteByReceiver = "is_delete_by_receiver";
    const isDeleteBySender = "is_delete_by_sender";
}
abstract class USER_BLOCKLIST_Fields{
    const id = "id";
    const userId = "userid";
    const otherUserId = "other_user_id";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}

abstract class  REJECT_MATCH_Fields
{
    const id = "id";
    const userId = "userid";
    const otherUserId = "other_user_id";
    const created = "created_date";
    const modified = "modified_date";
    const isTestdata = "is_testdata";
    const isDelete = "is_delete";
}

?>