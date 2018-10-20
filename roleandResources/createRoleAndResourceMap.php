<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$RoleName = $userId = $ResourceID = $RoleID="";
$RoleID_err = $ResourceID_err = $userId_err= "";
$dateOfExp = date_create()->format('Y-m-d H:i:s');
$createdDate = date_create()->format('Y-m-d');
$form_data = array();
$resultData = array(); 
$RoleName = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $input_ResourceID = trim($_POST["ResourceID"]);
    if(empty($input_ResourceID)){
        $ResourceID_err = "Resource Name is required";
    } else {
        $ResourceID = $input_ResourceID;
    }

    $input_RoleID = trim($_POST["RoleID"]);
    if(empty($input_RoleID)){
        $RoleID_err = "Role Name is required";
    } else {
        $RoleID = $input_RoleID;
    }

    $input_userId = trim($_POST["userId"]);
    if(empty($input_userId)){
        $userId_err = "User ID is required";
    } else {
        $userId = $input_userId;
    }

    $RecStatus = "A";
    $form_data['error']="" .$ResourceID_err. "" .$RoleID_err. "" .$userId_err;
    if( empty($ResourceID_err) && empty($RoleID_err) && empty($userId_err)){
        $sql = "INSERT INTO Role_Resource_Map (RoleID,ResourceID,Status,CreatedBy,CreatedDate) VALUES (?,?,?,?,?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssss", $RoleID, $ResourceID,$RecStatus,$userId,$createdDate);
           if($stmt->execute()){
                $form_data['success'] = true;
                $form_data['response']= "Role and Resource Map inserted successfully.";
            } else{
                $form_data['success'] = false;
                $form_data['response']= "Role and Resource Map insert failed, due to " . $mysqli->error;
            } 
        $stmt->close();
        }else{ 
            $form_data['success'] = false;
            $form_data['response']= "Something went wrong. Please try again later " . $mysqli->error;
        }
    }
    $mysqli->close();
}
echo json_encode($form_data);
function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}
?>