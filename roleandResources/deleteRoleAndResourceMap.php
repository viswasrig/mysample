<?php
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
if(isset($_POST["RRID"]) && !empty($_POST["RRID"])){
    $ID = (int)$_POST["RRID"];
    $sql = "UPDATE Role_Resource_Map SET Status='D' WHERE ID=" .$ID;
    if($result = $mysqli->query($sql)){
        $formData['success'] = true;
        $formData['response'] = "Successfully Deleted Record";
    } else{
        $formData['success'] = false;
        $formData['response'] = "Something went wrong. Please try again later." .$mysqli->error;
    }

    if($formData['success']) { 
        $DateCreated = date_create()->format('Y-m-d H:i:s');
        $trackerInsertQuery = "INSERT INTO Application_Tracker 
                        (AssociateID, RecID, TrgTable, Action,UpdateDate) 
                        VALUES (?,?,?,?,?)";
                        $param_table = 'Role_Resource_Map';
                        $param_action = 'Delete Record';
                        $reopenDate = $DateCreated;
                        $userId = (int)$_POST["userId"];
        if($stmt = $mysqli->prepare($trackerInsertQuery)){
            $stmt->bind_param("sssss", $userId, $ID,$param_table, $param_action,$reopenDate);
            if($stmt->execute()){
                $form_data['TrackerMsg']= "Successfully Inserted";
            } else{
                $form_data['TrackerMsg']= "Something went wrong. Please try again later." .$mysqli->error;
            }
            $stmt->close();
        }
    }
    $mysqli->close();
}else{
    $formData['success'] = false; 
    $formData['response'] = "ID is not found";
}
echo json_encode($formData);
?>