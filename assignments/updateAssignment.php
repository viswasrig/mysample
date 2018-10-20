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

$formData = array();
$resultData = array(); 

// Processing form data when form is submitted
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    // Validate Amount
   
   $ID = $_POST["ID"];
    
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
    
    $startDate = $_POST["startDate"] == null ? null : convertDate(trim($_POST["startDate"])) ;
    $technology = trim($_POST["technology"]);
    $invoiceTerm = trim($_POST["invoiceTerm"]);
    $paymentTerm = trim($_POST["paymentTerm"]);
    $endClientName = trim($_POST["endClientName"]);
    
    if(empty($_POST["endDate"])){
        $endDate = NULL;
    } else{
        $endDate = convertDate(trim($_POST["endDate"]));
    }
    
    // Check input errors before inserting in database
    if(empty($rate_err) && empty($unit_err)){
        // Prepare an insert statement
        $sql = "UPDATE Assignment_Master SET StartDate =?, Technology =?, Rate =?, Unit =?, InvoiceTerm =?, 
        PaymentTerm =?, EndClientName =?, EndDate =? WHERE ID =?"; 

        if($stmt = $mysqli->prepare($sql)){
            //Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssssss", $param_startDate, $param_technology, $param_rate, $param_unit, 
            $param_invoiceTerm, $param_paymentTerm, $param_endClientName, $param_endDate, $param_ID);
            // Set parameters
            $param_startDate = $startDate;
            $param_technology = $technology;
            $param_rate = $rate;
            $param_unit = $unit;
            $param_invoiceTerm = $invoiceTerm;
            $param_paymentTerm = $paymentTerm;
            $param_endDate = $endDate;
            $param_endClientName = $endClientName;
            $param_ID = $ID;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $formData['success'] = true;
                $formData['response'] = "Record successfully updated.";
            } else{
                $formData['success'] = false;
                $formData['response'] = "Something went wrong. Please try again later.". $mysqli->erro;
            }
            
        $stmt->close();
        }else{
            $formData['success'] = false;
            $formData['response'] = "Could not prepare the statement" . $mysqli->error;
        }
    }
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    
    // Prepare a select statement
    $sql = "SELECT ASM.ID, CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, CM.Name AS ClientName, ASM.StartDate,ASM.Technology, ASM.Rate, ASM.Unit, ASM.InvoiceTerm, ASM.PaymentTerm, ASM.EndClientName, ASM.EndDate FROM Assignment_Master ASM JOIN Associate_Master AM ON ASM.AssociateID = AM.ID JOIN Client_Master CM ON ASM.ClientID = CM.ID Where ASM.ID = ? AND ASM.RecStatus='A'";
    $ID = (int)trim($_GET["ID"]);
    $formData['sql'] = $sql; 
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_ID);
        $param_ID = $ID;
        
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $stmt->store_result();
            
            if($stmt->num_rows == 1){
                // bind the result to variables
                $stmt->bind_result($IDD, $fullName, $clientName, $startDate, $technology, $rate, $unit,
                 $invoiceTerm, $paymentTerm, $endClientName, $endDate);
                
             $stmt->fetch();

                $C = array();
                $C['ID'] = $IDD;
                $C['associateName'] = $fullName;
                $C['clientName'] = $clientName;
                $C['startDate'] = $startDate === null?null:date('m/d/Y',strtotime($startDate));
                $C['technology'] = $technology;
                $C['rate'] = $rate;
                $C['unit'] = $unit;
                $C['invoiceTerm'] = $invoiceTerm;
                $C['paymentTerm'] = $paymentTerm;
                $C['endClientName'] = $endClientName;
                $C['endDate'] = $endDate === null?null:date('m/d/Y',strtotime($endDate));

                $formData['success'] = true;
                $formData['response'] = $C;
               

            } else{
                $formData['success'] = false;
                $formData['response'] = "URL doesn't contain valid id parameter.";
            }
            
        } else{
           $formData['success'] = false;
           $formData['response'] = "Oops! Something went wrong. Please try again later.";
        }
     $stmt->close();
    }
    $mysqli->close();
} else{
    $formData['success'] = false;
    $formData['response'] = "URL doesn't contain id parameter.";
}

echo json_encode($formData);
function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}

?>