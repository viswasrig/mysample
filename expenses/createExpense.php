<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
 
// Define variables and initialize with empty values
$amount = $payee = $paymentMethod = $header = "";
$amount_err = $payee_err = $paymentMethod_err = $header_err = "";
$dateOfExp = date_create()->format('Y-m-d H:i:s'); 
// Processing form data when form is submitted
$formData = array();
// $formData['data'] = $_POST;
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $formData['response'] = '';
    // Validate Amount
    $input_amount = trim($_POST["amount"]);
    if(empty($input_amount)){
        $formData['success'] = false;
        $formData['response'] = "amount";
        $amount_err = "Please enter Amount";
     } else{
        $amount = $input_amount;
    }
    
    // Validate Payee
    $input_payee = trim($_POST["payee"]);
    if(empty($input_payee)){
        $formData['success'] = false;
        $formData['response'] =  "" .$formData['response']. ", Payee Name";
        $payee_err = "Please enter Payee";
    } else{
        $payee = $input_payee;
    }
        // Validate Payment Method
    $input_paymentMethod = trim($_POST["paymentMethod"]);
    if(empty($input_paymentMethod)){
        $formData['success'] = false;
        $formData['response'] = "" .$formData['response']. ", Payment Method";  
        $paymentMethod_err = "Please enter PaymentMethod";
         // echo json_encode($formData);  
    } else{
        $paymentMethod = $input_paymentMethod;
    }
        // Validate Payee
    $input_header = trim($_POST["header"]);
    if(empty($input_header)){
        $formData['success'] = false;
        $formData['response'] = "" .$formData['response']. ", Expense Header"; 
        $header_err = "Please enter Payment Header";
    } else{
        $header = $input_header;
    }

    $dateOfExpense = trim($_POST["expenseDate"]) === null?null:convertDate(trim($_POST["expenseDate"]));
    $referenceID = trim($_POST["associateID"]);
    $description = trim($_POST["description"]);
    $comments = trim($_POST["comments"]);
    $userId = trim($_POST["userId"]);
    $userId = (int)($userId);
    $RecStatus = 'N';
    $RecTransformed = 'Y';
    $Reference = trim($_POST["associateName"]);
    // Check input errors before inserting in database
    if(empty($amount_err) && empty($payee_err) && empty($paymentMethod_err) && empty($header_err)){
        // Prepare an insert statement
        $fData = insertUpDateAssociateBalence($_POST, $mysqli);
      if($fData['success']){
            $sql = "INSERT INTO Expenses 
        (EnteredBy, refID, Date, Amount, Payee, PaymentMethod, Header, Reference, Description, Comments,RecStatus, RecTransformed) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
 
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssssssssss", $param_EnteredBy, $param_referenceID, $param_dateOfExp, $param_amount, $param_payee,
            $param_paymentMethod, $param_header, $param_Reference, $param_description, $param_comments, $param_RecStatus, $param_RecTransformed);

            // Set parameters 
            $param_EnteredBy = $userId;
            $param_dateOfExp = $dateOfExpense;
            $param_amount = $amount;
            $param_payee = $payee;
            $param_paymentMethod = $paymentMethod;
            $param_header = $header;
            $param_Reference = $Reference;
            $param_referenceID = $referenceID;
            $param_description = $description;
            $param_comments = $comments;
            $param_RecStatus = $RecStatus;
            $param_RecTransformed = $RecTransformed;           
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records created successfully. Redirect to landing page
                $formData['success'] = true;
                $formData['response'] = "Records created successfully.";
            } else{
                //echo "Something went wrong. Please try again later.";
                $formData['success'] = false;
                $formData['response'] = "Something went wrong. Please try again later.";
            }
        }
        // Close statement
        $stmt->close();
        }else{
            $formData['success'] = false;
            $formData['response'] = $fData['response'];
    }
        
    } else { 
        $formData['response'] = "Please enter " .$formData['response']."";
    }
    // Close connection
    $mysqli->close();
}

function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
   // ChromePhp::log($convertedDate);
    return $convertedDate;
}
echo json_encode($formData);

function insertUpDateAssociateBalence($data, $mysqli){
        $associateID = trim($data['associateID']);
        $AssignIncomeID = '';
        $TotalIncome = '';
        $TotalExpenses = '';
        $assocBalFormData = array();
    
        $sql="SELECT ID,TotalIncome, TotalExpenses FROM Associate_Balances WHERE AssociateID =?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $associateID);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $stmt->bind_result($AssignIncomeID, $TotalIncome,$TotalExpenses);
                    $stmt->fetch();
                }
            }
            $stmt -> close();
        }
        $amount = trim($data['amount']);
        $TotalIncome = empty(trim($TotalIncome))? 0 :floatval(trim($TotalIncome));
        $TotalExpenses = empty(trim($TotalExpenses))?0:floatval(trim($TotalExpenses));
        $balence = 0;
    
        if(empty($AssignIncomeID)){
            $TotalExpenses = $TotalExpenses + $amount;
            $balence = $TotalIncome - $TotalExpenses;
            $sql="INSERT INTO Associate_Balances 
            (AssociateID,TotalIncome,TotalExpenses,Balance, Reference) 
            VALUES (?,?,?,?,?)";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("sssss", $associateID,$TotalIncome, $TotalExpenses, $balence, $reference);
               if($stmt->execute()){
                    $assocBalFormData['success'] = true;
                    $assocBalFormData['response']= "Associate Balence Record inserted successfully.";
                } else{
                    $assocBalFormData['success'] = false;
                    $assocBalFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
                } 
            $stmt->close();
            }else{ 
                $assocBalFormData['success'] = false;
                $assocBalFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
            }
        } else { 
            $sql="UPDATE Associate_Balances SET TotalIncome=?, TotalExpenses=?,Balance=? WHERE ID=?";
            $TotalExpenses = $TotalExpenses + $amount;
            $balence = $TotalIncome - $TotalExpenses;
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("ssss", $TotalIncome, $TotalExpenses, $balence, $AssignIncomeID);
               if($stmt->execute()){
                    $assocBalFormData['success'] = true;
                    $assocBalFormData['response']= "Associate Balence Record updated successfully.";
                } else{
                    $assocBalFormData['success'] = false;
                    $assocBalFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
                } 
            $stmt->close();
            }else{ 
                $assocBalFormData['success'] = false;
                $assocBalFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
            }
         
        }
        return $assocBalFormData;
}
    
?>