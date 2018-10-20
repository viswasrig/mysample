<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$RoleName = $RoleDesc ="";
$RoleName_err = $RoleDesc_err = "";
$dateOfExp = date_create()->format('Y-m-d H:i:s');
$form_data = array();
$resultData = array(); 
$RoleName = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $input_RoleName = trim($_POST["RoleName"]);
    if(empty($input_RoleName)){
        $RoleName_err = "Role Name is required";
    } else {
        $RoleName = $input_RoleName;
    }

    $input_RoleDesc = trim($_POST["RoleDesc"]);
    if(empty($input_RoleDesc)){
        $RoleDesc_err = "Role Description is required";
    } else {
        $RoleDesc = $input_RoleDesc;
    }
    $form_data['error']="" .$RoleName_err. "" .$RoleDesc_err;
    if( empty($RoleName_err) && empty($RoleDesc_err) ){
        $sql = "INSERT INTO Roles_Master (RoleName,RoleDesc) VALUES (?,?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ss", $RoleName,$RoleDesc);
           if($stmt->execute()){
                $form_data['success'] = true;
                $form_data['response']= "Role inserted successfully.";
            } else{
                $form_data['success'] = false;
                $form_data['response']= "Something went wrong. Please try again later " . $mysqli->error;
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