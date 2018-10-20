<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
// Define variables and initialize with empty values
$firstName = $lastName = $userID = $passWord = "";
$firstName_err = $lastName_err = $userID_err = $password_err = "";
$dateOfExp = date_create()->format('Y-m-d H:i:s');
$form_data = array();
$resultData = array(); 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
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
    
    
        // Validate UserID
    $input_userID = trim($_POST["userID"]);
    if(empty($input_userID)){
        $userID_err = 'Please enter the User ID';     
    } else{
        $userID = $input_userID;
    }
        // Validate Payee
    $input_password = trim($_POST["password"]);
    if(empty($input_password)){
        $password_err = 'Please enter the password';     
    } else{
        $password = $input_password;
    }
    $dob = trim($_POST["dateOfBirth"]);
    $doj = trim($_POST["dateOfJoining"]);

    $middleName = trim($_POST["middleName"]);
    $dateOfBirth = $dob == null ? null : convertDate($dob);
    $ssn = trim($_POST["ssn"]);
    $employerName = trim($_POST["employerName"]);
    $dateOfJoining = $doj == null ? null : convertDate($doj);
    $associateType = trim($_POST["associateType"]);

    
    // Check input errors before inserting in database
    if(empty($firstName_err) && empty($lastName_err) && empty($userID_err) && empty($password_err)){
        // Prepare an insert statement
        $defaultRecStatus = 'A';
        $sql = "INSERT INTO Associate_Master 
        (FirstName, LastName, MiddleName, DateOfBirth, SSN, EmployerID, DateOfJoining, UserID, Password,AssociateType, RecStatus) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?)";
 
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssssssss", $param_firstName, $param_lastName, $param_middleName, $param_dateOfBirth,  
            $param_ssn, $param_employerID,  $param_dateOfJoining, $param_userID, $param_password, $param_associateType, $defaultRecStatus);

            //Determine Employer ID
            if($employerName=="E3 Global Inc")
            {
                $employerID="1";
            }else
            {
                $employerID="2";
            }
            
            
            // Set parameters
            $param_firstName = $firstName;
            $param_lastName = $lastName;
            $param_middleName = $middleName;
            $param_dateOfBirth = $dateOfBirth;
            $param_ssn = $ssn;
            $param_employerID = $employerID;
            $param_dateOfJoining = $dateOfJoining;
            $param_userID = $userID;
            $param_password = $password;
            $param_associateType = $associateType;
            
           if($stmt->execute()){
                // Records created successfully. Redirect to landing page
                //header("location: index.php");
                //exit();
                $form_data['success'] = true;
                $form_data['response']= "Records created successfully.";
            } else{
                $form_data['success'] = false;
                $form_data['response']= "Something went wrong. Please try again later.";
            } 
            
        // Close statement
        $stmt->close();
        }else{ 
            $form_data['success'] = false;
            $form_data['response']= "Something went wrong. Please try again later.";
        }
    }
    
    // Close connection
    $mysqli->close();
}
echo json_encode($form_data);

function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
   // ChromePhp::log($convertedDate);
    return $convertedDate;
}

?>