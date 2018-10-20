<?php
date_default_timezone_set('America/Chicago');
// Include config file
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
// Processing form data when form is submitted
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    $ID = (int)$_POST["ID"];
    // $formData['id']= $ID;
    $sql = "UPDATE Expenses SET RecStatus='D' WHERE ID=" .$ID;
    // $formData['sql']= $sql;
    if($result = $mysqli->query($sql)){
        $formData['success'] = true;
        $formData['response'] = "Successfully Deleted Record";
    } else{
        $formData['success'] = false;
        $formData['response'] = "SQL Exception " .$mysqli->error;
    }
    $mysqli->close();
}else{
    $formData['success'] = false; 
    $formData['response'] = "ID is not found";
}
echo json_encode($formData);

?>