<?php
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
$sql = "SELECT * FROM Roles_Master";
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['RoleName'] = $row['RoleName'];
                $C['RoleDesc'] = $row['RoleDesc'];
                $C['Status'] = $row['Status'];
                $resultData[] = $C;
            }
            $result->free();
        }
        $formData['success'] = true ;
        $formData['response'] =  $resultData;
    }else{
        $formData['success'] = false ;
        $formData['response'] =  $resultData;
        $formData['msg']="ERROR: Could not able to execute $sql. " . $mysqli->error;
    }
$mysqli->close();
echo json_encode($formData);  
?>