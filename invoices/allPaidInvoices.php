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
$sql = "SELECT IM.ID, IM.AltID, IM.Date, AM.ID as AssociativeID, CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, CM.Name AS ClientName, 
IM.Price*IM.NumOfUnits AS Amount, IM.ReceivedAmount, IM.ReceivedDate, IM.Deductions, IM.Price, IM.NumOfUnits
FROM Invoice_Master IM
JOIN Assignment_Master ASM ON IM.AssignmentID = ASM.ID AND (IM.RecStatus='P' && IM.ReceivedDate IS NOT NULL && IM.IncomeAssigned IS NULL)
JOIN Associate_Master AM ON ASM.AssociateID = AM.ID
JOIN Client_Master CM ON ASM.ClientID = CM.ID";
 // $form_data['sql'] = $sql;
if($result = $mysqli->query($sql)){
    if($result->num_rows > 0){
        while($row = $result->fetch_array()){
            $rowData = array();
            $rowData['ID'] = $row['ID'];
            $rowData['AltID'] = $row['AltID'];
            $rowData['AssociativeID'] = $row['AssociativeID'];
            $rowData['Date'] = $row['Date'] ===null?null:date('m/d/Y',strtotime($row['Date']));
            $rowData['FullName'] = $row['FullName'];
            $rowData['ClientName'] = $row['ClientName'];
            $rowData['TotalAmount'] = $row['Amount'];
            $rowData['Deductions'] = $row['Deductions'];
            $rowData['Price'] = $row['Price'];
            $rowData['NumOfUnits'] = $row['NumOfUnits'];
            $rowData['ReceivedAmount'] = $row['ReceivedAmount'];
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
echo json_encode($form_data);
?>