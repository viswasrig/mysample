<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
 
// Define variables and initialize with empty values
$associateName = $clientName = $rate = $unit = "";
$associateName_err = $clientName_err = $rate_err = $unit_err = "";
$dateCreated = date_create()->format('Y-m-d H:i:s'); 
// Processing form data when form is submitted
$form_data = array();
$resultData = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){
     $input_associateID = trim($_POST["associateID"]);
    // Validate Associate ID
   
    if(empty($input_associateID)){
        $associateID_err = "No Valid Associate Name.";
     } else{
        $associateID = $input_associateID;
    }
    
    // Validate ClientID
    $input_clientID = trim($_POST["clientID"]);
    if(empty($input_clientID)){
        $clientID_err = 'No Valid Client Name';     
    } else{
        $clientID = $input_clientID;
    }
        // Validate Rate
    $input_rate = trim($_POST["rate"]);
    if(empty($input_rate)){
        $rate_err = 'Please enter the Rate';     
    } else{
        $rate = $input_rate;
    }
        // Validate Unit
    $input_unit = trim($_POST["unit"]);
    if(empty($input_unit)){
        $unit_err = 'Please enter the Unit of Work';     
    } else{
        $unit = $input_unit;
    }
    
    $startDate = $_POST["startDate"] === null?null:convertDate(trim($_POST["startDate"]));
    $technology = trim($_POST["technology"]);
    $invoiceTerm = trim($_POST["invoiceTerm"]);
    $paymentTerm = trim($_POST["paymentTerm"]);
    $endClientName = trim($_POST["endClientName"]);
    $defaultRecStatus = 'A';
    if(empty($associateID_err) && empty($clientID_err) && empty($rate_err) && empty($unit_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO Assignment_Master 
        (AssociateID, ClientID, Date, StartDate, Technology, Rate, Unit, InvoiceTerm, PaymentTerm, EndClientName, RecStatus) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?)";
 
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssssssss", $param_associateID, $param_clientID, $param_date, $param_startDate,
            $param_technology, $param_rate, $param_unit, $param_invoiceTerm, $param_paymentTerm, $param_endClientName, $defaultRecStatus);

            // Set parameters
            $param_associateID = (int)$associateID;
            $param_clientID = (int)$clientID;
            $param_date = $dateCreated;
            $param_startDate = $startDate;
            $param_technology = $technology;
            $param_rate = $rate;
            $param_unit = $unit;
            $param_invoiceTerm = $invoiceTerm;
            $param_paymentTerm = $paymentTerm;
            $param_endClientName = $endClientName;
                       
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records created successfully. Redirect to landing page
                $form_data['success']= true;
                $form_data['response']= "Successfully Inserted";
            } else{
                $form_data['success']= false;
                $form_data['response']= "Something went wrong. Please try again later." .$mysqli->error;
            }
        }
         
        // Close statement
        $stmt->close();
    }else{ 
        $form_data['success']= false;
        $form_data['response']= "".$associateID_err ." ".$clientID_err ." ".rate_err ." ".$unit_err;
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