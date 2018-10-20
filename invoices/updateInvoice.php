<?php
date_default_timezone_set('America/Chicago');
// Include config file
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
// Processing form data when form is submitted
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
// Define variables and initialize with empty values
$associateName = $clientName = $rate = $numOfUnits = "";
$associateName_err = $clientName_err = $rate_err = $numOfUnitst_err = "";
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
    $input_numOfUnits = trim($_POST["noOfUnits"]);
    if(empty($input_numOfUnits)){
    $numOfUnits_err = 'Please enter the Unit of Work';     
    } else{
    $numOfUnits = $input_numOfUnits;
    }

    $altID = trim($_POST["qbinvNumber"]);
    $date = $_POST["invoiceDate"] ===null ?null:convertDate(trim($_POST["invoiceDate"]));
    $type = trim($_POST["type"]);
    $unit = trim($_POST["unit"]);
    $fromDate = $_POST["fromDate"] === null ? null:convertDate(trim($_POST["fromDate"]));
    $toDate = $_POST["toDate"] === null ? null:convertDate(trim($_POST["toDate"]));
    $deductions = trim($_POST["deductions"]);
    $dueDate = $_POST["dueDate"] === null ? null:convertDate(trim($_POST["dueDate"]));
    $comments = trim($_POST["comments"]);
    $RecStatus = 'U';

    // Check input errors before inserting in database
    if(empty($rate_err) && empty($numOfUnits_err)){
    // Prepare an insert statement
    $sql = "UPDATE Invoice_Master SET AltID='" .$altID ."', Date='" .$date ."', Type='" .$type ."', price=" .$rate 
    .", Unit='" .$unit ."', numOfUnits=" .$numOfUnits .", FromDate='" .$fromDate ."', ToDate='" .$toDate 
    ."', deductions=" .$deductions .", DueDate='" .$dueDate ."', Comments='" .$comments ."', RecStatus='" .$RecStatus
    ."' WHERE ID=" .$ID; 
    
        //echo $sql;
        // Attempt to execute the prepared statement
            if($result = $mysqli->query($sql)){
            //header("location: index.php");
            //exit();
            $formData['success'] = true;
            $formData['response'] = "Successfully updated Record";
        } else{
            $formData['success'] = false;
            $formData['response'] = "Something went wrong. Please try again later." .$mysqli->error;
        }
    
    }
    // Close connection
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    // Prepare a select statement
    $sql = "SELECT IM.ID, IM.AltID, IM.Date, CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, CM.Name AS ClientName,IM.type, 
    IM.Price, IM.Unit, IM.NumOfUnits, IM.Price*IM.NumOfUnits AS Amount, IM.Deductions, IM.FromDate, IM.toDate, IM.DueDate, IM.Comments,
    ASM.PaymentTerm,ASM.InvoiceTerm
    FROM Invoice_Master IM
    JOIN Assignment_Master ASM ON IM.AssignmentID = ASM.ID
    JOIN Associate_Master AM ON ASM.AssociateID = AM.ID
    JOIN Client_Master CM ON ASM.ClientID = CM.ID Where IM.ID = ?";
    $ID = trim($_GET["ID"]);
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_ID);
        
        // Set parameters
        $param_ID = (int)$ID;
        
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $stmt->store_result();
            $formData['number of rows'] = $stmt->num_rows;
            if($stmt->num_rows == 1){
                // bind the result to variables
                $stmt->bind_result($ID, $altID, $date, $associateName, 
                $clientName,$type, $rate, $unit, $numOfUnits, 
                $amount, $deductions, $fromDate, $toDate, $dueDate, $comments,$paymentTerm,$invoiceTerm);
                
                $stmt->fetch();

                $C=array();
                $C['ID'] = $ID;
                $C['altID'] = $altID;
                $C['qbinvNumber'] = $altID;
                $C['invoiceDate'] = $date === null ? null: date('m/d/Y',strtotime($date));
                $C['associateName'] = $associateName;
                $C['cname'] = $clientName;
                $C['rate'] = $rate;
                $C['unit'] = $unit;
                $C['noOfUnits'] = $numOfUnits;
                $C['type'] = $type;
                $C['deductions'] = $deductions ===null?0.0:$deductions;
                $C['amount'] = $amount;
                $C['fromDate'] =$fromDate == null ? null:date('m/d/Y',strtotime($fromDate));
                $C['toDate'] =$toDate == null?null:date('m/d/Y',strtotime($toDate));
                $C['dueDate'] = $dueDate == null? null:date('m/d/Y',strtotime($dueDate)); 
                $C['comments'] = $comments;
                $C['paymentTerm'] = $paymentTerm;
                $C['invoiceTerm'] = $invoiceTerm;

                $formData['success'] = true;
                $formData['response'] = $C;
            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                //header("location: error.php");
                //exit();
                $formData['success'] = false;
                $formData['msg'] = "URL doesn't contain valid id parameter. Redirect to error page";
            }
            
        } else{
            $formData['success'] = false;
            $formData['msg'] = "Oops! Something went wrong. Please try again later.";
            //echo "Oops! Something went wrong. Please try again later.";
        }
    // Close statement    
    $stmt->close();
    }

    
    // Close connection
    $mysqli->close();
} else{
    // URL doesn't contain id parameter. Redirect to error page
   // header("location: error.php");
   // exit();
   $formData['success'] = false;
   $formData['msg'] = "URL doesn't contain id parameter. Redirect to error page";
}
echo json_encode($formData);

function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
   // ChromePhp::log($convertedDate);
    return $convertedDate;
}
?>