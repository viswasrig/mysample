<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$resultData = array();

if(isset($_GET["ID"]) && !empty($_GET["ID"])){
    $ID = $_GET["ID"];
    // Attempt select query execution
$sql = "SELECT EX.ID, CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, EX.Date, EX.Amount, EX.Payee, EX.PaymentMethod,
EX.Header, EX.Description, EX.Comments 
FROM Expenses EX
INNER JOIN Associate_Master AM ON EX.EnteredBy = AM.ID AND EX.RecStatus != 'D'
Where EX.EnteredBy = '" .$ID."' ORDER BY Date DESC";
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
}else{
    $formData['success'] = false;
    $formData['response'] = "No ID found";
}
echo json_encode($formData);
?>