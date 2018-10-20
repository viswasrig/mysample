<?php
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
$sql = "SELECT ID, FirstName, MiddleName, LastName, AssociateType, CONCAT(FirstName,' ',LastName) AS FullName FROM Associate_Master WHERE DateOfLeaving IS NULL AND RecStatus !='D' ORDER BY FirstName ASC";
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while($row = $result->fetch_array()){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['FirstName'] = $row['FirstName'];
                $C['MiddleName'] = $row['MiddleName'];
                $C['LastName'] = $row['LastName'];
                $C['AssociateType'] = $row['AssociateType'];
                $C['FullName'] = $row['FullName'];
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