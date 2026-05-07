<?php

// ==========================================================
//  Copyright Reserved Wael Wael Abo Hamza (Course Ecommerce)
// ==========================================================
 //define("MB", 1048576);
 include_once "config.php";



// ملاحظة للمهندس محمد: تم إزالة أسطر PHPMailer لأننا نستخدم الآن Brevo API عبر curl مباشرة.

function filterRequest($requestname)
{
    if(isset($_POST[$requestname])){

        return htmlspecialchars(strip_tags($_POST[$requestname]));

    }

    $data = json_decode(file_get_contents("php://input"), true);

    if(isset($data[$requestname])){

        return htmlspecialchars(strip_tags($data[$requestname]));

    }

    return "";
}

function getAllData($table, $where = null, $values = null)
{
    global $con;

    if ($where == null) {
        $stmt = $con->prepare("SELECT * FROM $table");
    } else {
        $stmt = $con->prepare("SELECT * FROM $table WHERE $where");
    }

    $stmt->execute($values);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = $stmt->rowCount();

    if ($count > 0) {

        echo json_encode(array(
            "status" => "success",
            "data" => $data
        ));

    } else {

        echo json_encode(array(
            "status" => "failure"
        ));

    }

    return $count;
}

function insertData($table, $data, $json = true)
{
    global $con;
    foreach ($data as $field => $v)
        $ins[] = ':' . $field;
    $ins = implode(',', $ins);
    $fields = implode(',', array_keys($data));
    $sql = "INSERT INTO $table ($fields) VALUES ($ins)";

    $stmt = $con->prepare($sql);
    foreach ($data as $f => $v) {
        $stmt->bindValue(':' . $f, $v);
    }
    $stmt->execute();
    $count = $stmt->rowCount();
    if ($json == true) {
        if ($count > 0) {
            echo json_encode(array("status" => "success"));
        } else {
            echo json_encode(array("status" => "failure"));
        }
    }
    return $count;
}



function updateData($table, $data, $where, $json = true)
{
            #-----[ لدالة الخاصة ب تحديث البيانات في قاعدة البيانات ]-----

    global $con;
    $cols = array();
    $vals = array();

    foreach ($data as $key => $val) {
        $vals[] = "$val";
        $cols[] = "`$key` =  ? ";
    }
    $sql = "UPDATE $table SET " . implode(', ', $cols) . " WHERE $where";

    $stmt = $con->prepare($sql);
    $stmt->execute($vals);
    $count = $stmt->rowCount();
    if ($json == true) {
        if ($count > 0) {
            echo json_encode(array("status" => "success"));
        } else {
            echo json_encode(array("status" => "failure"));
        }
    }
    return $count;
}

function deleteData($table, $where, $json = true)
{
        #-----[ لدالة الخاصة ب حذف البيانات من قاعدة البيانات ]-----

    global $con;
    $stmt = $con->prepare("DELETE FROM $table WHERE $where");
    $stmt->execute();
    $count = $stmt->rowCount();
    if ($json == true) {
        if ($count > 0) {
            echo json_encode(array("status" => "success"));
        } else {
            echo json_encode(array("status" => "failure"));
        }
    }
    return $count;
}


function imageUpload($imageRequest)
{
    global $msgError;
    $imagename  = rand(1000, 10000) . $_FILES[$imageRequest]['name'];
    $imagetmp   = $_FILES[$imageRequest]['tmp_name'];
    $imagesize  = $_FILES[$imageRequest]['size'];
    $allowExt   = array("jpg", "png", "gif", "mp3", "pdf");
    $strToArray = explode(".", $imagename);
    $ext        = end($strToArray);
    $ext        = strtolower($ext);

    if (!empty($imagename) && !in_array($ext, $allowExt)) {
        $msgError = "EXT";
    }
    if ($imagesize > 2 * MB) {
        $msgError = "size";
    }
    if (empty($msgError)) {
        move_uploaded_file($imagetmp,  "../upload/" . $imagename);
        return $imagename;
    } else {
        return "fail";
    }
}



function deleteFile($dir, $imagename)
{
    if (file_exists($dir . "/" . $imagename)) {
        unlink($dir . "/" . $imagename);
    }
}

function checkAuthenticate()
{
    if (isset($_SERVER['PHP_AUTH_USER'])  && isset($_SERVER['PHP_AUTH_PW'])) {
        if ($_SERVER['PHP_AUTH_USER'] != "wael" ||  $_SERVER['PHP_AUTH_PW'] != "wael12345") {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Page Not Found';
            exit;
        }
    } else {
        exit;
    }

    // End 
}

// ... (يمكنك الإبقاء على دوال updateData و deleteData كما هي)

function printFailure($message = "none") 
{
    echo json_encode(array("status" => "failure" , "message" => $message));
}

// الدالة الناجحة التي أثبتت فعاليتها في اختبارك الأخير
function sendEmail($to, $title, $body)
{
    $url = 'https://api.brevo.com/v3/smtp/email';

    $data = array(

        "sender" => array(

            "name"  => BREVO_SENDER_NAME,

            "email" => BREVO_SENDER_EMAIL

        ),

        "to" => array(

            array(

                "email" => $to

            )

        ),

        "subject" => $title,

        "htmlContent" => "<html><body dir='rtl'>$body</body></html>"

    );

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(

        'api-key: ' . BREVO_API_KEY,

        'Content-Type: application/json'

    ));

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return ($httpCode == 201);
}