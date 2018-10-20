<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$ResourceName = $ResourceDesc ="";
$ResourceName_err = $ResourceDesc_err = "";
$dateOfExp = date_create()->format('Y-m-d H:i:s');
$form_data = array();
$resultData = array(); 
$ResourceName = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $input_ResourceName = trim($_POST["ResourceName"]);
    if(empty($input_ResourceName)){
        $ResourceName_err = "Resource Name is required";
    } else {
        $ResourceName = $input_ResourceName;
    }

    $input_ResourceDesc = trim($_POST["ResourceDesc"]);
    if(empty($input_ResourceDesc)){
        $ResourceDesc_err = "Resource Description is required";
    } else {
        $ResourceDesc = $input_ResourceDesc;
    }
    $form_data['error']="" .$ResourceName_err. " " .$ResourceDesc_err;
    if( empty($ResourceName_err) && empty($ResourceDesc_err) ){
        $sql = "INSERT INTO Resource_Master (ResourceName,ResorceDesc) VALUES (?,?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ss", $ResourceName,$ResourceDesc);
           if($stmt->execute()){
                $form_data['success'] = true;
                $form_data['response']= "Resource inserted successfully.";
            } else{
                $form_data['success'] = false;
                $form_data['response']= "Inserting record is failed due to " . $mysqli->error;
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