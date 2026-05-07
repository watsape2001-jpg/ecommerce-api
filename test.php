
<?php

header("Content-Type: application/json");

echo json_encode(array(

    "status" => "success",

    "data" => [
        array("id" => 1)
    ]

));