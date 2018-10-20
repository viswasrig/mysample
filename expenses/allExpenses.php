<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$resultData = array();

// Attempt select query execution
$sql = "(
    SELECT EX.ID,  '' AS FullName, EX.refID, EX.EnteredBy, EX.Date, EX.Amount, EX.Payee, EX.PaymentMethod, EX.Header, EX.Description, EX.Comments, EX.RecStatus, EX.Reference
    FROM Expenses EX
    WHERE (
    EX.refID IS NULL
    )
    )
    UNION ALL (
    
    SELECT EX.ID, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS FullName, EX.refID, EX.EnteredBy, EX.Date, EX.Amount, EX.Payee, EX.PaymentMethod, EX.Header, EX.Description, EX.Comments, EX.RecStatus, EX.Reference
    FROM Expenses EX
    JOIN Associate_Master AM ON ( EX.refID = AM.ID )
    )
    ORDER BY DATE";
if($result = $mysqli->query($sql)){
    if($result->num_rows > 0){
        while($row = $result->fetch_array()){
            $C =  array();
            $C['ID'] = $row['ID'];
            $C['Date'] = $row['Date'] === null ?null:date('m/d/Y', strtotime($row['Date']));
            $C['Amount'] = $row['Amount'];
            $C['Payee'] = $row['Payee'];
            $C['PaymentMethod'] = $row['PaymentMethod'];
            $C['Header'] = $row['Header'];
            $C['FullName'] = $row['FullName'];
            $C['Description'] = $row['Description'];
            $C['Comments'] = $row['Comments'];
            $C['recStatus'] = $row['RecStatus'];
            $C['refID'] = $row['refID'];
            $C['EnteredBy'] = $row['EnteredBy'];
            $C['Reference'] = $row['Reference'];
            $resultData[] = $C;
        }
        $result->free();
        $formData['success'] = true ;
        $formData['response'] =  $resultData;    
    }else{
        $formData['success'] = false;
        $formData['response'] = "No Record found";
    }
}else{
    $formData['success'] = false;
    $formData['response'] = "No Record found";
}
$mysqli->close();

echo json_encode($formData);
?>