<?php
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
$ResourceID = $RoleID = $userId = "";
$ResourceID_err = $RoleID_err = $userId_err = "";
$modifiedDate = date_create()->format('Y-m-d');
if(isset($_POST["RRID"]) && !empty($_POST["RRID"])){
    // Validate Amount
    $ID = $_POST["RRID"];
    
    // Validate
    $RecStatus = 'A';
    $input_ResourceID = trim($_POST["ResourceID"]);
    if(empty($input_ResourceID)){
        $ResourceID_err = "Resource Name is required";
    } else {
        $ResourceID = $input_ResourceID;
    }
    $input_RoleID = trim($_POST["RoleID"]);
    if(empty($input_RoleID)){
        $RoleID_err = "Role Name is required";
    } else {
        $RoleID = $input_RoleID;
    }
    $input_userId = trim($_POST["userId"]);
    if(empty($input_userId)){
        $userId_err = "User ID is required";
    } else {
        $userId = $input_userId;
    }
    $param_ID = $ID;
    if(empty($ResourceID_err) && empty($RoleID_err) && empty($userId_err)){
         $sql = "UPDATE Role_Resource_Map SET ResourceID=?, ModifiedBy=?, ModifiedDate=? WHERE ID=?"; 
        if($stmt = $mysqli->prepare($sql)){
            $formData['params'] = "" .$ResourceID. " " .$userId . " " .$modifiedDate. " " .$param_ID;
            $stmt->bind_param("ssss", $ResourceID, $userId, $modifiedDate, $param_ID);
            if( $stmt->execute()){
                $formData['success'] = true;
                $formData['response'] = "Successfully updated Record";
            }else{
                $formData['success'] = false;
                $formData['response'] = "Update record failed " . $mysqli->error;
            }
            $stmt-> close();

            if($formData['success']) { 
                $DateCreated = date_create()->format('Y-m-d H:i:s');
                $trackerInsertQuery = "INSERT INTO Application_Tracker 
                                (AssociateID, RecID, TrgTable, Action,UpdateDate) 
                                VALUES (?,?,?,?,?)";
                                $param_table = 'Role_Resource_Map';
                                $param_action = 'update Record';
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
        } else{
            $formData['success'] = false;
            $formData['response'] = "Something went wrong. Please try again later." .$mysqli->error;
        }
    }else{
        $formData['success'] = false;
        $formData['response'] = "Validation error, Please send ProperData";
    }
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    $sql = "SELECT ID, RoleID,ResourceID,Status FROM Role_Resource_Map WHERE ID=?";
    $ID = trim($_GET["ID"]);
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID = (int)$ID;
        if($stmt->execute()){
            $stmt->store_result();
            $formData['number of rows'] = $stmt->num_rows;
            if($stmt->num_rows == 1){
                $stmt->bind_result($RRID, $RoleID, $ResourceID, $Status);
                $stmt->fetch();

                $C=array();
                $C['RRID'] = $RRID;
                $C['RoleID'] = $RoleID;
                $C['ResourceID'] = $ResourceID;
                $C['Status'] = $Status;

                $formData['success'] = true;
                $formData['response'] = $C;
            } else{
                $formData['success'] = false;
                $formData['msg'] = "URL doesn't contain valid id parameter. Redirect to error page";
            }
            
        } else{
            $formData['success'] = false;
            $formData['msg'] = "Oops! Something went wrong. Please try again later.";
        }
    $stmt->close();
    }

    
    $mysqli->close();
} else{
   $formData['success'] = false;
   $formData['msg'] = "URL doesn't contain id parameter. Redirect to error page";
}
echo json_encode($formData);

function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}
?>