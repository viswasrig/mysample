<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
if(!empty($_GET['userId'])){
    $userId = $_GET['userId'];
    $sql = "SELECT AM.ID AS AssociateID, CONCAT(AM.FirstName,' ', AM.LastName) AS FullName,
CM.ID AS ClientID, CM.Name AS ClientName, 
ASM.ID as AssignmentID, ASM.Date,ASM.StartDate, ASM.EndDate, 
ASM.Technology, ASM.EndClientName, ASM.ReasonForEnd,
ASM.Rate, ASM.Unit, ASM.InvoiceTerm, ASM.PaymentTerm, (
    CASE 
        WHEN AC.mrkt_exp IS NULL THEN 0.0
        ELSE AC.mrkt_exp
    END) AS mrkt_exp
FROM Assignment_Master ASM
INNER JOIN Associate_Master AM ON ASM.AssociateID = AM.ID AND AM.ID =" .$userId ."
INNER JOIN Client_Master CM ON ASM.ClientID = CM.ID 
LEFT OUTER JOIN Associate_Compensation as AC on AM.ID = AC.AssociateID AND AC.RecStatus != 'D'
WHERE ASM.RecStatus= 'A' ORDER BY ASM.EndDate ASC";
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['associateID'] = $row['AssociateID'];
                $C['fullName'] = $row['FullName'];
                $C['clientID'] = $row['ClientID'];
                $C['clientName'] = $row['ClientName'];
                $C['assignmentID'] = $row['AssignmentID'];
                $C['assignStartDate'] = $row['StartDate'] == null ? null:date('m/d/Y',strtotime($row['StartDate']));
                $C['assignEndDate'] = $row['EndDate'] == null ? null:date('m/d/Y',strtotime($row['EndDate']));
                $C['technology'] = $row['Technology'];
                $C['endClientName'] = $row['EndClientName'];
                $C['reasonForEnd'] = $row['ReasonForEnd'];
                $C['rate'] = $row['Rate'];
                $C['unit'] = $row['Unit'];
                $C['assignmentDt'] = $row['Date'] == null ? null:date('m/d/Y',strtotime($row['Date']));
                $C['invoiceTerm'] = $row['InvoiceTerm'];
                $C['paymentTerm'] = $row['PaymentTerm'];
                $C['mrktExp'] = $row['mrkt_exp'];
                $C['calucatedRate'] = $row['Rate'] - $row['mrkt_exp'];

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
}
echo json_encode($formData);  
?>