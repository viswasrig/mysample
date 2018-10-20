<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$resultData = array();
$conditional = " AND EXP.RecTransformed IS NULL AND EXP.RecStatus != 'D'";
if($_GET['type'] == 'D'){
    $conditional = " AND (EXP.RecTransformed = 'Y' or EXP.RecStatus = 'D')";
}
// Attempt select query execution
$sql = "SELECT EXP.ID, EXP.Amount, EXP.RecStatus,EXP.RecTransformed, AM.ID AS AssociateID, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS FullName
FROM Expenses AS EXP
INNER JOIN Associate_Master AS AM ON ( EXP.refID = AM.ID
AND EXP.refID IS NOT NULL 
AND EXP.refID !=1085" .$conditional." ) ORDER BY ID DESC";
if($result = $mysqli->query($sql)){
    if($result->num_rows > 0){
        while($row = $result->fetch_array()){
            $C =  array();
            $C['ID'] = $row['ID'];
            $C['Amount'] = $row['Amount'];
            $C['AssociateID'] = $row['AssociateID'];
            $C['FullName'] = $row['FullName'];
            $C['recStatus'] = $row['RecStatus'];
            $C['RecTransformed'] = $row['RecTransformed'];
            $resultData[] = $C;
        }
        $result->free();
        $formData['success'] = true ;
        $formData['response'] =  $resultData;    
    }else{
        $formData['success'] = false;
        $formData['response'] = "No Record found".$mysqli->error;
    }
}else{
    $formData['success'] = false;
    $formData['response'] = "No Record found" .$mysqli->error;;
}
$mysqli->close();

echo json_encode($formData);
?>