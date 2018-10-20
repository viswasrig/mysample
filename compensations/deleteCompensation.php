<?php
date_default_timezone_set('America/Chicago');
// Include config file
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
// Processing form data when form is submitted
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
$DateCreated = date_create()->format('Y-m-d');
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    $ID = (int)$_POST["ID"];
    $user = (int)$_POST["userId"];
    $sql = "UPDATE Associate_Compensation SET RecStatus='D', ModifiedBy='" .$user. "', ModifiedDate='".$DateCreated."' WHERE ID=" .$ID;
    // $formData['sql']= $sql;
    if($result = $mysqli->query($sql)){
        $formData['success'] = true;
        $formData['response'] = "Successfully deleted record";
    } else{
        $formData['success'] = false;
        $formData['response'] = "Something went wrong. Please try again later." .$mysqli->error;
    }
   if($formData['success']){
    $DateCreated = date_create()->format('Y-m-d H:i:s');
    $trackerInsertQuery = "INSERT INTO Application_Tracker 
                    (AssociateID, RecID, TrgTable, Action,UpdateDate) 
                    VALUES (?,?,?,?,?)";
                    $param_table = 'Associate_Compensation';
                    $param_action = 'Deleted';
                    $reopenDate = $DateCreated;
                    $userId = (int)$_POST['userId'];
    if($stmt = $mysqli->prepare($trackerInsertQuery)){
        $stmt->bind_param("sssss", $user, $ID,$param_table, $param_action,$reopenDate);
        if($stmt->execute()){
            $formData['TrackerMsg']= "Successfully Inserted";
        } else{
            $formData['TrackerMsg']= "Something went wrong. Please try again later." .$mysqli->error;
        }
        $stmt->close();
    }
   } 
}else{
    $formData['success'] = false; 
    $formData['response'] = "ID is not found";
}
$mysqli->close();
echo json_encode($formData);
?>