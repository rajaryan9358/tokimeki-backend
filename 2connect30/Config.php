<?php

//echo phpinfo();

include_once 'Logger.php';
$logger = new Logger();
date_default_timezone_set('UTC');

$server = "localhost";
$user = "root";
$password = '2c0nN3c$T#0%&&';
$dbname = 'twoconnect30app';

global $con;
$con = mysqli_connect($server, $user, $password,$dbname);

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else
{
   // echo "connected successfully";
    mysqli_select_db($con, $dbname);
    mysqli_set_charset($con,"utf8");
}
?>
