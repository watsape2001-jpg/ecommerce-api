<?php

include "config.php";

//================================================
// Headers
//================================================

header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Headers: *");

header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

header("Content-Type: application/json; charset=UTF-8");

//================================================
// الاتصال بقاعدة البيانات
//================================================

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;

$options = array(

    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"

);

try {

    $con = new PDO($dsn , DB_USER , DB_PASS , $options);

    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include "functions.php";

} catch (PDOException $e) {

    echo json_encode(array(

        "status" => "failure",

        "message" => $e->getMessage()

    ));

}

