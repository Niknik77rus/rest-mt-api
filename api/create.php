<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
    include_once '../include/config.php';
    include_once 'database.php';
    include_once 'command.php';

    $database = new Database();
    $db = $database->getConnection(); 
    $apicall = new ApiCall($db);
 
    $postData = file_get_contents('php://input');
    $clearedPostData = str_replace('\\', '\\\\', $postData);
    $data = json_decode($clearedPostData, true);

    if( 
        $data['MAC'] &&     
        $data['Hostname'] &&  
        $data['UniqueID'] &&
        $data['WindowsUser']) {
            $apicall->mac = $data['MAC'];
            $apicall->uniqueid = $data['UniqueID'];
            $apicall->hostname = $data['Hostname'];
            $apicall->windowsuser = $data['WindowsUser'];
            $apicall->approved = '0';
 
        if ($apicall->check_uid_exist()) {
            if ($apicall->check_uid_approved()) { 
                http_response_code(202);
                echo json_encode(array("message" => "UniqueID exist and approved"));
            }
        else {
            http_response_code(403);
            echo json_encode(array("message" => "UniqueID is not approved"));
        }
    }

    elseif (!$apicall->check_uid_exist()) {
        if($apicall->create_uid()){
            http_response_code(201);
            echo json_encode(array("message" => "Apicall was created."));
        }
    
        else{
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create apicall. DEBUG please"));
        }
    }
    else {
        echo 'Undefined case! Alarm!!!';
    }
    } 
    
    else{
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create apicall. Data is incomplete."));
    }


    if ( !$apicall->compare_ip() && !$apicall->ros_check_iplist_entry()) {
        $apicall->ros_change_ip(); 
        $apicall->change_current_ip();
    }       

?>