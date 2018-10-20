<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
 
// Define variables and initialize with empty values
$associateName = $percentage = $mrktExp ="";
$associateName_err = $percentage_err = $mrktExp_Err="";
$dateOfExp = date_create()->format('Y-m-d');
$form_data = array();
$resultData = array(); 
// Processing form data when form is submitted
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    // Validate Amount
    $ID = $_POST["ID"];
    $ID = (int)$ID;
    // Validate First Name
    $input_associateName = trim($_POST["associateName"]);
    if(empty($input_associateName)){
        $associateName_err = "Please Enter the Associate Name.";
    } else{
        $associateID = trim($_POST["associateID"]);
        $associateName = $input_associateName;
    }
    // Validate Last Name
    $input_percentage = trim($_POST["percentage"]);
    if(empty($input_percentage)){
        $percentage_err = "Please Enter the Percentage.";
    } else{
        $percentage = $input_percentage;
    }
    $userId_err = "";
    $userId = "";
    $input_userId = trim($_POST["userId"]);
    if(empty($input_userId)){
        $userId_err = "User ID is not found";
    } else{
        $userId = $input_userId;
    }
    $input_mrktExp = trim($_POST["mrktExp"]);
    if(empty($input_mrktExp)){
        $mrktExp_Err = "Mrkt Exp is not found";
    } else{
        $mrktExp = $input_mrktExp;
    }

    $form_data['error'] = "" .$percentage_err. "" .$associateName_err. "" .$userId_err."";
    // Check input errors before inserting in database
    if(empty($associateName_err) && empty($percentage_err) && empty($userId_err) && empty($mrktExp_Err)){
        $sql = "UPDATE Associate_Compensation SET Prctg=?, mrkt_exp =?, RecStatus='U', ModifiedBy=?, ModifiedDate=? WHERE ID =?"; 
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssss", $percentage,$mrktExp, $userId, $dateOfExp, $ID);
            if($stmt->execute()){
                $form_data['success'] = true;
                $form_data['response'] = "Record is updated succefully.";
            } else{
                $form_data['success'] = false;
                $form_data['response'] = "Something went wrong. Please try again later." . $mysqli->error;;
            }
        $stmt->close();
        }else{
            $form_data['success'] = false;
            $form_data['response'] = "Something went wrong. Please try again later." . $mysqli->error;;
        }
        if($form_data['success']){
            $DateCreated = date_create()->format('Y-m-d H:i:s');
            $trackerInsertQuery = "INSERT INTO Application_Tracker 
                            (AssociateID, RecID, TrgTable, Action,UpdateDate) 
                            VALUES (?,?,?,?,?)";
                            $param_table = 'Associate_Compensation';
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
    }else{
        $form_data['success'] = false;
        $form_data['response'] = "Something went wrong. Please try again later." . $mysqli->error;
    }
    // Close connection
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    // Prepare a select statement
    $sql = "SELECT AC. * , CONCAT( AM.FirstName,  ' ', AM.LastName ) AS associateName, CONCAT( AMM.FirstName,  ' ', AMM.LastName ) AS ModifiedName
    FROM Associate_Compensation AS AC
    LEFT OUTER JOIN Associate_Master AS AM ON AM.ID = AC.AssociateID
    LEFT OUTER JOIN Associate_Master AS AMM ON AMM.ID = AC.ModifiedBy
    OR AC.ModifiedBy IS NULL 
    GROUP BY AC.ID
    HAVING AC.ID =?";
    $ID = trim($_GET["ID"]);
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID = (int)$ID;
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($IDD, $associateID,$Prctg, $mrktExp, $ModifiedBy, $ModifiedDate, $RecStatus, $associateName, $ModifiedName);
                $stmt->fetch();
                $C = array();
                $C['ID'] = $IDD;
                $C['associateID'] = $associateID;
                $C['percentage'] = $Prctg;
                $C['mrktExp'] = $mrktExp;
                $C['ModifiedBy'] = $ModifiedBy;
                $C['ModifiedDate'] = $ModifiedDate == null ? null:date('m/d/Y',strtotime($ModifiedDate));
                $C['RecStatus'] = $RecStatus;
                $C['associateName'] = $associateName;
                $C['ModifiedName'] = $ModifiedName;
                
                $form_data['success'] = true;
                $form_data['response'] = $C;

            } else{
                $form_data['success'] = false;
                $form_data['response'] = "URL doesn't contain valid id parameter.";
            }
            
        } else{
            $form_data['success'] = false;
            $form_data['response'] = "Oops! Something went wrong. Please try again later.";
        }
    $stmt->close();
    }
    $mysqli->close();
} else{
    $form_data['success'] = false;
    $form_data['response'] = "Oops! Something went wrong. Please try again later.";
}
echo json_encode($form_data);
function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}
?>