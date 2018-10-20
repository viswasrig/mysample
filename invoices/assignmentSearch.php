<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
 // ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$form_data = array();
$resultData = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){  
        $sql = "SELECT CM.Name AS ClientName, ASM.ID AS ID, ASM.RATE AS Price, ASM.Unit as Unit,  ASM.PaymentTerm as PaymentTerm, ASM.InvoiceTerm as InvoiceTerm
                FROM Assignment_Master ASM JOIN Client_Master CM ON ASM.ClientID = CM.ID
                AND ASM.ENDDATE IS NULL AND ASM.ASSOCIATEID = " .$_POST["query"];  
        if($result = $mysqli->query($sql)){  
            if($result->num_rows > 0){   
                while($row = mysqli_fetch_array($result)){
                    $C = array();
                    $C['ClientName'] = $row["ClientName"];
                    $C['ID'] = $row["ID"];
                    $C['Price'] = $row["Price"];
                    $C['Unit'] = $row["Unit"];
                    $C['PaymentTerm'] = $row['PaymentTerm'];
                    $C['invoiceTerm'] = $row['InvoiceTerm'];
                    $resultData[] = $C;
                   // $output = $row["ClientName"] .'*' .$row["ID"] .'*' .$row["Price"] .'*' .$row["Unit"] .'*' .$row["PaymentTerm"];
                }
            }
 } 
 $mysqli->close();

$form_data['success'] = true;
$form_data['response'] = $resultData;
echo json_encode($form_data);
}
 ?>  
