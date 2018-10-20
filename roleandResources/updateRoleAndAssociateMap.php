<?php
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
$associateID = $RoleID = $userId = "";
$associateID_err = $RoleID_err = $userId_err = "";
$modifiedDate = date_create()->format('Y-m-d');
if(isset($_POST["RID"]) && !empty($_POST["RID"])){
    // Validate Amount
    $ID = $_POST["RID"];
    
    // Validate
    $RecStatus = 'A';
    $input_associateID = trim($_POST["associateID"]);
    if(empty($input_associateID)){
        $associateID_err = "Associate Name is required";
    } else {
        $associateID = $input_associateID;
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
    if(empty($associateID_err) && empty($RoleID_err) && empty($userId_err)){
         $sql = "UPDATE Role_Associate_Map SET RoleID=?, ModifiedBy=?, ModifiedDate=? WHERE ID=?"; 
        if($stmt = $mysqli->prepare($sql)){
            $formData['params'] = "" .$RoleID. " " .$userId . " " .$modifiedDate. " " .$param_ID;
            $stmt->bind_param("ssss", $RoleID, $userId, $modifiedDate, $param_ID);
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
                                $param_table = 'Role_Associate_Map';
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
    $ID = trim($_GET["ID"]);
    $sql = "SELECT RM . * ,RAM.ID AS RID ,RAM.RoleID as RoleID, RAM.AssociateID,  RAM.Status, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS FullName
    FROM Roles_Master AS RM
    INNER JOIN Role_Associate_Map AS RAM ON RAM.RoleID = RM.ID
    INNER JOIN Associate_Master AS AM ON RAM.AssociateID = AM.ID
    AND RAM.Status =  'A' AND  RAM.ID=?";
    
    // $formData['sql'] = $sql;
    // $formData['ID'] = $ID;
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID = (int)$ID;
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($ID, $RoleName, $RoleDesc, $RID, $RoleID, $AssociateID, 
                $Status,$FullName);
                $stmt->fetch();
                $C=array();
                $C['ID'] = $ID;
                $C['associateID'] = $AssociateID;
                $C['associateName'] = $FullName;
                $C['RID'] = $RID;
                $C['RoleID'] = $RoleID;
                $C['RoleName'] = $RoleName;
                $C['RoleDesc'] = $RoleDesc;
                $C['Status'] = $Status;

                $formData['success'] = true;
                $formData['response'] = $C;
            } else{
                $formData['success'] = false;
                $formData['msg'] = "URL doesn't contain valid id parameter " . $mysqli->error;
            }
            
        } else{
            $formData['success'] = false;
            $formData['msg'] = "Oops! Something went wrong. Please try again later " . $mysqli->error;
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