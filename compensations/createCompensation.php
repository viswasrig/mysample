<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
// Define variables and initialize with empty values
$associateName = $percentage = $mrktExp="";
$associateName_err = $percentage_err =$mrktExp_err="";
$dateOfExp = date_create()->format('Y-m-d H:i:s');
$form_data = array();
$resultData = array(); 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate associate Name
    $input_associateName = trim($_POST["associateName"]);
    if(empty($input_associateName)){
        $associateName_err = "Please Enter the Associate Name.";
     } else{
         $associateID = (int)trim($_POST["associateID"]);
        $associateName = $input_associateName;
    }
    // Validate Percentage
    $input_percentage = trim($_POST["percentage"]);
    if(empty($input_percentage)){
        $percentage_err = "Please Enter the Percentage.";
     } else{
        $percentage = $input_percentage;
    }

    $input_mrktExp = trim($_POST["mrktExp"]);
    if(empty($input_mrktExp)){
        $mrktExp_err = "Please Enter the Percentage.";
     } else{
        $mrktExp = $input_mrktExp;
    }
    // Check input errors before inserting in database
    if(empty($associateName_err) && empty($percentage_err)){
        // Prepare an insert statement
        $defaultRecStatus = 'A';
        $sql = "INSERT INTO Associate_Compensation 
        (AssociateID, Prctg, mrkt_exp, RecStatus) 
        VALUES (?,?,?,?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssss", $associateID,$percentage,$mrktExp,$defaultRecStatus);
           if($stmt->execute()){
                $form_data['success'] = true;
                $form_data['response']= "Record created successfully.";
            } else{
                $form_data['success'] = false;
                $form_data['response']= "Something went wrong. Please try again later.";
            } 
        $stmt->close();
        }else{ 
            $form_data['success'] = false;
            $form_data['response']= "Something went wrong. Please try again later.";
        }
    }
    $mysqli->close();
}
echo json_encode($form_data);
function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}
?>