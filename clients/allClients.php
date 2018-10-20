<?php

// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();

$sql = "SELECT * FROM Client_Master WHERE RecStatus= 'A' ORDER BY ID DESC";
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['clientName'] = $row['Name'];
                $C['clientType'] = $row['Type'];
                $C['lineOfBusiness'] = $row['LineOfBusiness'];
                $C['fein'] = $row['FEIN'];
                $C['createdDate'] = $row['DateCreated'];
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