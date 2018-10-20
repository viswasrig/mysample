<?php
date_default_timezone_set('America/Chicago');
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$dateCreated = date_create()->format('Y-m-d H:i:s');
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    // Validate Amount
    $ID =(int) $_POST["ID"];
    $RecStatus = 'N';
    $sql = "UPDATE Invoice_Master SET ReceivedDate=null,RecStatus='" .$RecStatus ."', Comments='" .$comments ."' WHERE ID =" .$ID; 
    if($result = $mysqli->query($sql)){
        $formData['success'] = true;
        $formData['response'] = "Successfully updated Record, Record is Opened";
    } else{
        $formData['success'] = false;
        $formData['response'] = "Something went wrong. Please try again later." .$mysqli->error;
    }
    
$trackerInsertQuery = "INSERT INTO Application_Tracker 
    (AssociateID, RecID, TrgTable, Action,UpdateDate) 
    VALUES (?,?,?,?,?)";
$param_table = 'Invoice_Master';
$param_action = 'Reopen';
$reopenDate = $dateCreated;
$userId = (int)$_POST['userId'];

if($stmt = $mysqli->prepare($trackerInsertQuery)){
    $stmt->bind_param("sssss", $userId, $ID,$param_table, $param_action,$reopenDate);
    // Attempt to execute the prepared statement
    if($stmt->execute()){
            $form_data['TrackerMsg']= "Successfully Inserted";
    } else{
        $form_data['TrackerMsg']= "Something went wrong. Please try again later." .$mysqli->error;
    }
}
    $mysqli->close();
}
echo json_encode($formData);
?>