<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();

$sql = "SELECT CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, CM.Name AS ClientName, ASM.ID as ID, ASM.Date,ASM.StartDate,
ASM.Rate, ASM.Unit, ASM.InvoiceTerm, ASM.PaymentTerm
FROM Assignment_Master ASM
JOIN Associate_Master AM ON ASM.AssociateID = AM.ID
JOIN Client_Master CM ON ASM.ClientID = CM.ID WHERE EndDate IS NULL AND ASM.RecStatus= 'A' ORDER BY ASM.ID DESC";
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['fullName'] = $row['FullName'];
                $C['clientName'] = $row['ClientName'];
                $C['assignStartDate'] = $row['StartDate'] == null ? null:date('m/d/Y',strtotime($row['StartDate']));
                $C['rate'] = $row['Rate'];
                $C['unit'] = $row['Unit'];
                $C['assignmentDt'] = $row['Date'] == null ? null:date('m/d/Y',strtotime($row['Date']));
                $C['invoiceTerm'] = $row['InvoiceTerm'];
                $C['paymentTerm'] = $row['PaymentTerm'];

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