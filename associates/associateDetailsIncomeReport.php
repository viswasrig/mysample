<?php
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
require_once '../config.php';
$formData =array();
$resultData = array();
$sql = "";
$formData =array();
$resultData = array();
$conditional = "";
if(!empty($_GET['ID'])) {
    $sql = "SELECT AI.*, IM.Price,IM.Unit, IM.NumOfUnits, IM.FromDate, IM.ToDate, IM.ReceivedDate, IM.ReceivedAmount, ASM.ClientID, ASM.InvoiceTerm, ASM.PaymentTerm, ASM.ClientID, CM.Name AS ClientName
    FROM Associate_Income AS AI
    LEFT OUTER JOIN Invoice_Master AS IM ON AI.InvoiceID = IM.ID
    LEFT OUTER JOIN Assignment_Master AS ASM ON ASM.AssociateID = AI.AssociateID
    LEFT OUTER JOIN Client_Master AS CM ON ASM.ClientID = CM.ID
    GROUP BY AI.InvoiceID HAVING AI.AssociateID =" .$_GET['ID'];
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while($row = $result->fetch_array()){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['AssociateID'] = $row['AssociateID'];
                $C['InvoiceID'] = $row['InvoiceID'];
                $C['EmployerAmount'] = $row['EmployerAmount'];
                $C['Percentage'] = $row['Percentage'];
                $C['EmployeeShare'] = $row['EmployeeShare'];
                $C['Reference'] = $row['Reference'];
                $C['Price'] = $row['Price'];
                $C['Unit'] = $row['Unit'];
                $C['NumOfUnits'] = $row['NumOfUnits'];
                $C['FromDate'] = $row['FromDate'] == null ? null:date('m/d/Y',strtotime($row['FromDate']));
                $C['ToDate'] = $row['ToDate'] ==null ? null: date('m/d/Y',strtotime($row['ToDate']));
                $C['ReceivedDate'] = $row['ReceivedDate'] == null ? null : date('m/d/Y',strtotime($row['ReceivedDate']));
                $C['ReceivedAmount'] = $row['ReceivedAmount'];
                $C['ClientID'] = $row['ClientID'];
                $C['InvoiceTerm'] = $row['InvoiceTerm'];
                $C['PaymentTerm'] = $row['PaymentTerm'];
                $C['ClientName'] = $row['ClientName'];
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