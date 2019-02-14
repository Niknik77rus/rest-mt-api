<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// get database connection
include_once '../include/config.php';
include_once 'database.php';
 
// instantiate product object
include_once 'command.php';

ini_set("allow_url_fopen", 1);

$database = new Database();
$db = $database->getConnection();
 
$apicall = new ApiCall($db);
 

    $apicall->uniqueid = '43210';
    $apicall->groupid = 'client-Juniper';
       
    #var_dump($apicall->check_uid_exist());
    #var_dump($apicall->check_uid_approved());
    #var_dump($apicall->get_current_ip());
    #var_dump($apicall->get_groupid());
    #var_dump($apicall->compare_ip());
    var_dump($apicall->ros_change_ip());

 
?>