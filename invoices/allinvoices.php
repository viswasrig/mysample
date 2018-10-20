<?php
date_default_timezone_set('America/Chicago');
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$form_data = array();
$resultData = array();
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
// $form_data['data'] = $_GET["iType"];
if(isset($_GET["iType"]) && !empty(trim($_GET["iType"]))){
 $ConditionalQuery = "";
$iType = $_GET["iType"];
 if($iType === "C"){
    $ConditionalQuery = "AND (IM.RecStatus='P')";
 }else if ($iType === "D") {
    $ConditionalQuery = "AND (IM.RecStatus='D')";
 }else{
    $ConditionalQuery = "AND (IM.RecStatus='N' OR IM.RecStatus='I' or IM.RecStatus='U')";
 }
 //$form_data['ConditionalQuery'] = $ConditionalQuery;


 // $sql = "SELECT * FROM Invoice_Master ORDER BY ID ASC";
$sql = "SELECT IM.ID, IM.AltID, IM.Date, CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, CM.Name AS ClientName, 
IM.Price*IM.NumOfUnits AS Amount, IM.DueDate, IM.ReceivedDate
FROM Invoice_Master IM
JOIN Assignment_Master ASM ON IM.AssignmentID = ASM.ID " . $ConditionalQuery . "
JOIN Associate_Master AM ON ASM.AssociateID = AM.ID
JOIN Client_Master CM ON ASM.ClientID = CM.ID";
 // $form_data['sql'] = $sql;
if($result = $mysqli->query($sql)){
    if($result->num_rows > 0){
        while($row = $result->fetch_array()){
            $rowData = array();
            $rowData['ID'] = $row['ID'];
            $rowData['AltID'] = $row['AltID'];
            $rowData['Date'] = $row['Date'] ===null?null:date('m/d/Y',strtotime($row['Date']));
            $rowData['FullName'] = $row['FullName'];
            $rowData['ClientName'] = $row['ClientName'];
            $rowData['Amount'] = $row['Amount'];
            $rowData['DueDate'] = $row['DueDate'] ===null?null:date('m/d/Y',strtotime($row['DueDate']));
            $rowData['ReceivedDate'] = $row['ReceivedDate'] ===null?null:date('m/d/Y',strtotime($row['ReceivedDate']));

            $resultData[] = $rowData; 
        }
        $result->free();
    }
    $form_data['success'] = true;
    $form_data['response'] = $resultData;
}else{
    $form_data['success'] = false;
    $form_data['response'] = $resultData;
}
$mysqli->close();
}

echo json_encode($form_data);
?>