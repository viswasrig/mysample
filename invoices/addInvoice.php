<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
include '../ChromePhp.php';
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
//session.save_path = '/home/content/31/7042131/html/tmp';
session_start();
require_once '../config.php';
$associativeName ='';
$clientName ='';
$form_data = array();
$resultData = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $invoiceDate = $_POST['invoiceDate']?convertDate($_POST['invoiceDate']):null ;
    $associativeName = $_POST['associativeName'];
    $clientName = $_POST['cname'];
    $rate = $_POST['rate'];
    $unit = $_POST['unit'];
    $noOfUnits = $_POST['noOfUnits'];
    $amount = $_POST['amount'];
    $deductions = $_POST['deductions'];
    $type = $_POST['type'];
    $altID = $_POST['qbinvNumber'];
    $dueDt = $_POST['dueDate']?convertDate($_POST['dueDate']):null;
    $fromDate = $_POST['fromDate']?convertDate($_POST['fromDate']):null;
    $toDate = $_POST['toDate']?convertDate($_POST['toDate']):null;
    $comments = $_POST['comments'];
    $assignmentID = trim($_POST["assignmentID"]);
   
    /*$altID = trim($_POST["qInvNum"]);
    $assignmentID = trim($_POST["assignmentID"]);
    $date = trim($_POST["startDate"]);
    $type = trim($_POST["type"]);
    $unit = trim($_POST["unit"]);
    $fromDate = trim($_POST["fromDate"]);
    $toDate = trim($_POST["toDate"]);
    $deductions = trim($_POST["deductions"]);
    $dueDate = trim($_POST["dueDate"]);
    $comments = trim($_POST["comments"]); */

    $input_associateName = trim($_POST["associativeName"]);
    // Validate Associate ID
   
    if(empty($input_associateName)){
        $associateName_err = "No Valid Associate Name.";
     } else{
        $associativeName = $input_associateName;
    }
    
    // Validate ClientID
    $input_clientName = trim($_POST["cname"]);
    if(empty($input_clientName)){
        $clientName_err = 'No Valid Client Name';     
    } else{
        $clientName = $input_clientName;
    }
        // Validate Rate
    $input_rate = trim($_POST["rate"]);
    if(empty($input_rate)){
        $rate_err = 'Please enter the Rate';     
    }
        // Validate Unit
    $input_numOfUnits = trim($_POST["noOfUnits"]);
    if(empty($input_numOfUnits)){
        $numOfUnits_err = 'Please enter the Unit of Work';     
    }
    $defaultStatus = 'N';
    if(empty($associateName_err) && empty($clientname_err) && empty($rate_err) && empty($numOfUnits_err)){ 
        $sql = "INSERT INTO Invoice_Master 
        (AltID, Date, Type, AssignmentID, Price, Unit, NumOfUnits, FromDate, ToDate,  Deductions, DueDate, Comments, RecStatus) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssssssssss", $altID, $invoiceDate, $type, $assignmentID, $rate,
            $unit, $noOfUnits, $fromDate,$toDate, $deductions, $dueDt, $comments,$defaultStatus);
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                 // Records created successfully. Redirect to landing page
                 $form_data['success']= true;
                 $form_data['msg']= "Successfully Inserted";
            } else{
                $form_data['success']= false;
                $form_data['msg']= "Something went wrong. Please try again later." .$mysqli->error;
            }
        }
        // Close statement
        $stmt->close();
    } else{
        $form_data['success']= false;
        $form_data['msg']= $associateName_err .$clientname_err .$rate_err .$numOfUnits_err;
    }
    $mysqli->close();
}
echo json_encode($form_data);

function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
   // ChromePhp::log($convertedDate);
    return $convertedDate;
}
?>
