<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
 
// Define variables and initialize with empty values
$firstName = $lastName = "";
$firstName_err = $lastName_err = "";
$form_data = array();
$resultData = array(); 
// Processing form data when form is submitted
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    // Validate Amount
    $ID = $_POST["ID"];
    
          // Validate First Name
    $input_firstName = trim($_POST["firstName"]);
    if(empty($input_firstName)){
        $firstName_err = "Please Enter the First Name.";
     } else{
        $firstName = $input_firstName;
    }
    
    // Validate Last Name
    
    $input_lastName = trim($_POST["lastName"]);
    if(empty($input_lastName)){
        $lastName_err = "Please Enter the Last Name.";
     } else{
        $lastName = $input_lastName;
    }
    
    $middleName = trim($_POST["middleName"]);
    $dob = trim($_POST["dateOfBirth"]);
    $doj = trim($_POST["dateOfJoining"]);
    $dol = trim($_POST["dateOfLeaving"]);
    $dateOfBirth = $dob == null ? null : convertDate($dob);
    //$employerID = trim($_POST["employerID"]);
    $ssn = trim($_POST["ssn"]);
    $dateOfJoining = $doj == null ? null : convertDate($doj);
    $associateType = trim($_POST["associateType"]);
    $dateOfLeaving = $dol == null ? null : convertDate($dol);
    $reason = trim($_POST["reason"]);

    
    // Check input errors before inserting in database
    if(empty($firstName_err) && empty($lastName_err)){
        // Prepare an insert statement
        $sql = "UPDATE Associate_Master SET FirstName =?, LastName =?, MiddleName =?, DateOfBirth =?,  
        SSN =?, DateOfJoining =?, AssociateType =? , DateOfLeaving =?, ReasonForLeaving =? WHERE ID =?"; 

        if($stmt = $mysqli->prepare($sql)){
            //Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssssssss", $param_firstName, $param_lastName, $param_middleName, $param_dateOfBirth,
            $param_ssn, $param_dateOfJoining, $param_associateType, $param_dateOfLeaving, $param_reason, $param_ID);
            // Set parameters
             $param_firstName = $firstName;
            $param_lastName = $lastName;
            $param_middleName = $middleName;
            $param_dateOfBirth = $dateOfBirth;
            $param_ssn = $ssn;
            $param_dateOfJoining = $dateOfJoining;
            $param_associateType = $associateType;
            $param_dateOfLeaving = $dateOfLeaving;
            $param_reason = $reason;
            $param_ID = $ID;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records created successfully. Redirect to landing page
                // header("location: index.php");
                // exit();
                $form_data['success'] = true;
                $form_data['response'] = "Record is updated Succefully.";
            } else{
                $form_data['success'] = false;
                $form_data['response'] = "Something went wrong. Please try again later." . $mysqli->error;;
                // echo "Something went wrong. Please try again later." . $mysqli->error;
            }
            
        // Close statement
        $stmt->close();
        }
        // echo "Could not prepare the statement" . $mysqli->error;
    }
    
    // Close connection
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    
    // Prepare a select statement
    $sql = "SELECT AM.ID, AM.FirstName, AM.LastName, AM.MiddleName, AM.DateOfBirth, AM.userID, AM.Password,
     CM.Name, AM.SSN, AM.DateOfJoining, AM.AssociateType, AM.DateOfLeaving, AM.ReasonForLeaving
            FROM Associate_Master AM
            JOIN Company_Master CM ON AM.EmployerID = CM.ID
            Where AM.ID = ?";
    $ID = trim($_GET["ID"]);
    // $form_data['ID'] = $ID;
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_ID);
        // Set parameters
        $param_ID = (int)$ID;
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                // bind the result to variables
                $stmt->bind_result($IDD, $firstName, $lastName, $middleName, $dateOfBirth, 
                $userID,$pwd,$employerName, $ssn, $dateOfJoining, 
                $associateType, $dateOfLeaving, $reason);
                $stmt->fetch();
                
                $C = array();
                $C['ID'] = $IDD;
                $C['firstName'] = $firstName;
                $C['middleName'] = $middleName;
                $C['lastName'] = $lastName;
                $C['dateOfBirth'] = $dateOfBirth == null ? null:date('m/d/Y',strtotime($dateOfBirth));
                // $C['userID'] = $userID;
                // $C['password'] = $pwd;
                $C['employerName'] = $employerName;
                $C['ssn'] = $ssn;
                $C['dateOfJoining'] = $dateOfJoining == null ? null:date('m/d/Y',strtotime($dateOfJoining));
                $C['associateType'] = $associateType;
                $C['dateOfLeaving'] = $dateOfLeaving == null ? null:date('m/d/Y',strtotime($dateOfLeaving));
                $C['reason'] = $reason;
                
                $form_data['success'] = true;
                $form_data['response'] = $C;

            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                //  header("location: error.php");
                $form_data['success'] = false;
                $form_data['response'] = "URL doesn't contain valid id parameter.";
            }
            
        } else{
            $form_data['success'] = false;
            $form_data['response'] = "Oops! Something went wrong. Please try again later.";
        }
    // Close statement    
    $stmt->close();
    }
    // Close connection
    $mysqli->close();
} else{
    $form_data['success'] = false;
    $form_data['response'] = "Oops! Something went wrong. Please try again later.";
}
echo json_encode($form_data);
function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
   // ChromePhp::log($convertedDate);
    return $convertedDate;
}
?>