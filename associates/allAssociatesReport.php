<?php
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
$conditional = "AM.DateOfLeaving IS NULL AND AM.RecStatus !='D'";
if($_GET['type'] == 'D'){
    $conditional = "AM.DateOfLeaving IS NOT NULL OR AM.RecStatus ='D'";
}else if($_GET['type']=='ALL'){
    $conditional ='';
} else if($_GET['type']=='USERID') { 
    $conditional = $_GET['userId'];
}

$sql = "SELECT AM.ID, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS FullName, AM.AssociateType, IF( AB.Balance IS NOT NULL , AB.Balance, 0 ) AS Balance, AM.RecStatus, AM.DateOfLeaving
FROM Associate_Master AS AM
LEFT OUTER JOIN Associate_Balances AS AB ON AM.ID = AB.AssociateID
LEFT OUTER JOIN Assignment_Master AS ASM ON ASM.AssociateID = AM.ID
GROUP BY AM.ID HAVING " .$conditional. " ORDER BY FullName ASC";

if($_GET['type'] == 'ALL'){
    $sql = "SELECT AM.ID, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS FullName, AM.AssociateType, IF( AB.Balance IS NOT NULL , AB.Balance, 0 ) AS Balance, AM.RecStatus, AM.DateOfLeaving
    FROM Associate_Master AS AM
    LEFT OUTER JOIN Associate_Balances AS AB ON AM.ID = AB.AssociateID
    LEFT OUTER JOIN Assignment_Master AS ASM ON ASM.AssociateID = AM.ID
    GROUP BY AM.ID ORDER BY FullName DESC";
} else if($_GET['type']=='USERID'){
    $sql = "SELECT AM.ID, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS FullName, AM.AssociateType, IF( AB.Balance IS NOT NULL , AB.Balance, 0 ) AS Balance, AM.RecStatus, AM.DateOfLeaving
    FROM Associate_Master AS AM
    LEFT OUTER JOIN Associate_Balances AS AB ON AM.ID = AB.AssociateID
    LEFT OUTER JOIN Assignment_Master AS ASM ON ASM.AssociateID = AM.ID
    GROUP BY AM.ID
    HAVING AM.ID =" .$conditional. "
    ORDER BY FullName DESC ;";
}

    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while($row = $result->fetch_array()){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['FullName'] = $row['FullName'];
                $C['AssociateType'] = $row['AssociateType'];
                $C['Balance'] = $row['Balance'];
                $C['recStatus'] = $row['RecStatus'];
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