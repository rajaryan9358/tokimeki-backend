const { Console } = require('console');
const express = require('express');
const app = express();
const server = require('http').createServer(app)
const port = process.env.PORT || 9563
const { Server } = require("socket.io");
const io = new Server(server);
var users = [];
log4js = require('log4js'),
mysql = require('mysql');
FCM = require('fcm').FCM;
var logger = log4js.getLogger('cervApp');

var pool = mysql.createPool({
    connectionLimit: 10,
    host: 'localhost',
    user: 'root',
    password: '2c0nN3c$T#0%&&',
    database: 'twoconnect30app',
    charset: 'utf8mb4'
});

app.get('/', (req, res) => {
    res.status(200).send('<h1>Socket Demo</h1>');
});

const FCM_KEY = "AAAAkrsjwuA:APA91bGLy2RgvwP3azvVwqCT_6ANm80QsUsokfmzCQnOJrL1FWZHxboqvc4DdA-HRA49eVnB7L8N8AN5GiZho_OJVaB4oTEhqMUad73dHaiGSUFsdHIc51y_ySTi4nQyvE7RNCFlv3-K";
const NOTIFICATION_TITLE = "2Connect30";
const NOTIFICATION_MESSAGE = "New message received!!";

io.on('connection', (socket) => {
    console.log('Some client connected');

    socket.on('disconnect', () => {
        console.log('Disconnect socket');
        console.log(socket.user_id);
        var userAvailable = isInArray(socket.user_id, users);
        if (userAvailable == true) {
            deleteFromArray(users, socket.user_id);
        }
    });

    socket.on('JoinSocket', function (data, callback) {
        logger.level = 'debug';
        logger.debug("Join socket");
        if (typeof data.id === "undefined") {
            console.log("Please pass user id");
        } else {
            socket.join(data.id);
            socket.user_id = data.id;
            if (users.length <= 0) {
                console.log("**** First User ****--->" + data.id);
                users.push(data.id);
            } else {
                var userAvailable = isInArray(data.id, users);
                if (userAvailable == false) {
                    console.log("**** New  User ****--->" + data.id);
                    users.push(data.id);
                }
            }
            if (typeof callback === "function") {
                callback({ status: SUCCESS });
            }
        }
        console.log(users, 'avalible user');
    });

    socket.on('ReadMessage', function (data, callback) {
        console.log(data, "====>>Read Message<<<<<====")
        var is_delete = 0;
        var updateReadMessage = "UPDATE " + 'chat_messages' + " SET is_read=1 WHERE conversation_id = " + data.conversation_id + " AND receiver_id = " + data.receiver_id + " AND is_delete=" + is_delete + "";

        executeQuery(updateReadMessage, [data.conversation_id, data.receiver_id], function (err, result, fields) {
            if (err) {
                logger.error(new Error().stack + err);
                throw err;
            }
        });
    });

    socket.on('SendMessage', function (data, callback) {
        var created_date = getCurrentUTCDateTime();
        var is_delete = 0;
        var messageType = data.message_type;
        // TO CHECK IF CHAT IS ALREDY EXIST OR NOT
        var chatInfo = "";
        if(data.conversation_id == 0){
            var chatInfo = "SELECT *, id as conversation_id FROM " + 'conversations' +
            " WHERE sender_id = " + data.sender_id + " AND receiver_id = "+data.receiver_id+" AND is_delete = 0 AND is_testdata=" + data.isTestData + "";
        } else {
            var chatInfo = "SELECT *,id as conversation_id FROM " + 'conversations' +
            " WHERE id = " + data.conversation_id + " AND is_delete = 0 AND is_testdata=" + data.isTestData + "";
        }
        executeQuery(chatInfo, '', function (err, chatResult, fields) {
            if (err) {
                logger.error(new Error().stack + err);
                throw err;
            } else {
                if (typeof chatResult !== 'undefined' && chatResult.length > 0) {
                    let converstionId = data.conversation_id == 0 ? chatResult[0].conversation_id : data.conversation_id;
                    let isDeleteSender = chatResult[0].is_delete_by_sender;
                    let isDelReceiver = chatResult[0].is_delete_by_receiver;
                    // update conversation is delete sender/ receiver
                    var updateIsDelete = "UPDATE " + 'conversations' + " SET is_delete_by_sender = ?, is_delete_by_receiver = ?, modified_date = '" + created_date + "' WHERE id = " + converstionId;
                    executeQuery(updateIsDelete, [0,0], function (err, result1, fields) {
                        if (err) {
                            logger.error(new Error().stack + err);
                            throw err;
                        }
                    });
                    // let converstionId = data.conversation_id;
                    // console.log('in if cht_msg');
                    // // TO INSERT MESSAGE INTO CHAT_MESSAGE TABLE
                    var insertExistChat = "INSERT INTO " + 'chat_messages' + " (conversation_id, sender_id, receiver_id, message_type, message, created_date, is_testdata) " +
                        "VALUES (?,?,?,?,?,?,?)";

                    executeQuery(insertExistChat, [converstionId, data.sender_id, data.receiver_id, messageType, data.message, created_date, data.isTestData], function (err, result1, fields) {
                        if (err) {
                            logger.error(new Error().stack + err);
                            throw err;
                        }
                        data.message_id = result1.insertId;
                        finalObj = {
                            conversation_id: stringToInt(converstionId),
                            message_id: stringToInt(data.message_id),
                            sender_id: stringToInt(data.sender_id),
                            receiver_id: stringToInt(data.receiver_id),
                            message_type: messageType,
                            message: data.message,
                            created_date: created_date,
                            is_read: 0,
                            is_testdata: stringToInt(data.isTestData)
                        }

                        if (typeof callback === "function") {
                            callback(finalObj);
                        }
                        var userAvailable = isInArray(data.receiver_id, users);
                        if (userAvailable === true && data.message != '') {
                            // TO CALL RECIVE EVENT IF RECEIVER IS ONLINE
                            io.in(data.receiver_id).emit("ReceiveMessage", finalObj);
                            io.in(data.receiver_id).emit("UpdateChatList", finalObj);
                            io.in(data.sender_id).emit("UpdateChatList", finalObj);
                            console.log('message send if');
                            var chatInfo = "SELECT device_token FROM " + 'app_token' +
                                " WHERE user_id = " + data.receiver_id + " AND is_delete = 0";
                            executeQuery(chatInfo, '', function (err, chatResult, fields) {
                                if (err) {
                                    logger.error(new Error().stack + err);
                                    throw err;
                                } else {
                                    if (typeof chatResult !== 'undefined' && chatResult.length > 0) {
                                        let regIdsArr = []
                                        chatResult.forEach(element => {
                                            if(element['device_token'] != '' && element['device_token'] != '123456'){
                                                regIdsArr.push(element['device_token'])
                                            }
                                        });
                                        sendNotification(converstionId, regIdsArr)
                                    }
                                }
                            })
                        } else {
                            // var chatInfo = "SELECT device_token FROM " + 'app_token' +
                            //     " WHERE user_id = " + data.receiver_id + " AND is_delete = 0 AND is_testdata=" + data.isTestData + "";
                            // executeQuery(chatInfo, '', function (err, chatResult, fields) {
                            //     if (err) {
                            //         logger.error(new Error().stack + err);
                            //         throw err;
                            //     } else {
                            //         if (typeof chatResult !== 'undefined' && chatResult.length > 0) {
                            //             let regIdsArr = []
                            //             chatResult.forEach(element => {
                            //                 regIdsArr.push(element['device_token'])
                            //             });
                            //             sendNotification(converstionId, regIdsArr)
                            //         }
                            //     }
                            // })
                        }

                    });

                    //TO UPDATE LAST MESSAGE IN CONVERSATION TABLE
                    var updateLastMessage = "UPDATE " + 'conversations' + " SET last_message = ?, modified_date = '" + created_date + "' WHERE id = " + converstionId;
                    executeQuery(updateLastMessage, [data.message], function (err, result1, fields) {
                        if (err) {
                            logger.error(new Error().stack + err);
                            throw err;
                        }
                    });
                } else {
                    // console.log('in else conversation and msg');
                    // TO INSERT NEW RECORDS IN CONVERSATION TABLE
                    var insertNewChat = "INSERT INTO conversations (last_message, sender_id, receiver_id, created_date, modified_date, is_testdata) " +
                        "VALUES (?,?,?,?,?,?)";

                    executeQuery(insertNewChat, [data.message, data.sender_id, data.receiver_id, created_date, created_date, data.isTestData], function (err, result, fields) {
                        if (err) {
                            logger.error(new Error().stack + err);
                            throw err;
                        }
                        var tempId = result.insertId;
                        data.created_date = created_date;
                        // TO INSERT MESSAGE INTO CHAT_MESSAGE TABLE
                        var insertExistChat = "INSERT INTO " + 'chat_messages' + " (conversation_id, sender_id, receiver_id, message_type, message, created_date, is_testdata) " +
                            "VALUES (?,?,?,?,?,?,?)";

                        executeQuery(insertExistChat, [tempId, data.sender_id, data.receiver_id, messageType, data.message, created_date, data.isTestData], function (err, result1, fields) {
                            if (err) {
                                logger.error(new Error().stack + err);
                                throw err;
                            }
                            data.message_id = result1.insertId;
                            finalObj = {
                                conversation_id: data.conversation_id == 0 ? stringToInt(tempId) :stringToInt(data.conversation_id),
                                message_id: stringToInt(data.message_id),
                                sender_id: stringToInt(data.sender_id),
                                receiver_id: stringToInt(data.receiver_id),
                                message_type: messageType,
                                message: data.message,
                                created_date: created_date,
                                is_read: 0,
                                is_testdata: stringToInt(data.isTestData)
                            }

                            if (typeof callback === "function") {
                                callback(finalObj);
                            }
                            var userAvailable = isInArray(data.receiver_id, users);
                            if (userAvailable === true && data.message != '') {
                                // TO CALL RECIVE EVENT IF RECEIVER IS ONLINE
                                io.in(data.receiver_id).emit("ReceiveMessage", finalObj);
                                io.in(data.receiver_id).emit("UpdateChatList", finalObj);
                                io.in(data.sender_id).emit("UpdateChatList", finalObj);
                            } else {
                                console.log('message send else');
                                var chatInfo = "SELECT device_token FROM " + 'app_token' +
                                " WHERE user_id = " + data.receiver_id + " AND is_delete = 0";
                                executeQuery(chatInfo, '', function (err, chatResult, fields) {
                                    if (err) {
                                        logger.error(new Error().stack + err);
                                        throw err;
                                    } else {
                                        if (typeof chatResult !== 'undefined' && chatResult.length > 0) {
                                            let regIdsArr = []
                                            chatResult.forEach(element => {
                                                if(element['device_token'] != '' && element['device_token'] != '123456'){
                                                    regIdsArr.push(element['device_token'])
                                                }
                                            });
                                            const converstionId = data.conversation_id == 0 ? stringToInt(tempId) :stringToInt(data.conversation_id);
                                            sendNotification(converstionId, regIdsArr)
                                        }
                                    }
                                })
                            }

                        });
                    })

                }
            }
        })

    });

    function isInArray(user, userArray) {
        //return userArray.indexOf(user) > -1;
        var length = userArray.length;
        for (var i = 0; i < length; i++) {
            if (userArray[i] == user)
                return true;
        }
        return false;
    }
    function deleteFromArray(my_array, element) {
        position = my_array.indexOf(element);
        my_array.splice(position, 1);
    }
    function getCurrentUTCDateTime() {
        return new Date().toISOString().replace(/T/, ' ').// replace T with a space
            replace(/\..+/, '');
    }
    function stringToInt($string) {
        return parseInt($string);
    }
    function executeQuery(sql, parma, sql_rescponce_callack) {
        pool.getConnection(function (err, connection) {
            if (err) {
                logger.error(sql + '  : getConnection THROW :' + err);
                return;
            }
            var query = connection.query(sql, parma, sql_rescponce_callack);
            if (typeof query === "undefined") {

            } else {
                query.on('error', function (err) {
                    logger.error(sql + ' : query FROM :' + err);
                    throw err;
                });
                query.on('end', function () {
                    connection.release();
                });
            }
        });
    }
    function sendNotification(converstionId, regIDsArr) {
        const axios = require('axios');
        var notification = {
            'title': NOTIFICATION_TITLE,
            'body': NOTIFICATION_MESSAGE
        };
        var extraData = {
            "converstionId": converstionId
        };
        var notification_body = {
            'notification': notification,
            'registration_ids': regIDsArr,
            'data': extraData
        }
        axios({
            method: 'post',
            url: 'https://fcm.googleapis.com/fcm/send',
            'headers': {
                // replace authorization key with your key
                'Authorization': 'key=' + FCM_KEY,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(notification_body)
        }).then(function (response) {
            return true;
          })
          .catch(function (error) {
            return false;
        });
    }
});

server.listen(port, () => {
    console.log(`Server running on port: ${port}`)
});