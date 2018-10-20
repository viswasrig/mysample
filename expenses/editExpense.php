<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
 
// Define variables and initialize with empty values
$amount = $payee = $paymentMethod = $header = $ID = "";
$amount_err = $payee_err = $paymentMethod_err = $header_err = "";
// echo "Entity ID : " . $_SESSION["EntityID"];
// Processing form data when form is submitted
$formData = array();
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    $formData['response'] = '';
    // Validate Amount
    $ID = $_POST["ID"];
    
    $input_amount = trim($_POST["amount"]);
    if(empty($input_amount)){
        $formData['success'] = false;
        $formData['response'] = "amount";
        $amount_err = "Please Enter the amount.";
     } else{
        $amount = $input_amount;
    }
    
    // Validate Payee
    $input_payee = trim($_POST["payee"]);
    if(empty($input_payee)){
        $formData['success'] = false;
        $formData['response'] =  "" .$formData['response']. ", Payee Name";
        $payee_err = 'Please enter the Payee Name';     
    } else{
        $payee = $input_payee;
    }
        // Validate Payment Method
    $input_paymentMethod = trim($_POST["paymentMethod"]);
    if(empty($input_paymentMethod)){
        $formData['success'] = false;
        $formData['response'] = "" .$formData['response']. ", Payment Method"; 
        $paymentMethod_err = 'Please enter the Payment Method';     
    } else{
        $paymentMethod = $input_paymentMethod;
    }
        // Validate Payee
    $input_header = trim($_POST["header"]);
    if(empty($input_header)){
        $formData['success'] = false;
        $formData['response'] = "" .$formData['response']. ", Expense Header";
        $headerr_err = 'Please enter the Expense Header';     
    } else{
        $header = $input_header;
    }
    
    $dateOfExpense = trim($_POST["expenseDate"]) === null?null:convertDate(trim($_POST["expenseDate"]));
    $referenceID = trim($_POST["associateID"]);
    $reference = trim($_POST["associateName"]);
    $description = trim($_POST["description"]);
    $comments = trim($_POST["comments"]);
    $userId = trim($_POST["userId"]);
    $userId = (int)($userId);
    $RecStatus = 'U';

    
    // Check input errors before inserting in database
    if(empty($amount_err) && empty($payee_err) && empty($paymentMethod_err) && empty($header_err)){
        
        $fData = insertUpDateAssociateBalence($_POST, $mysqli);
        if($fData['success']){
            // Prepare an insert statement
            $sql = "UPDATE Expenses SET Date =?, Amount =?, Payee =?, PaymentMethod =?, Header =?, Reference =?, Description =?, Comments =?, refID=?, RecStatus=? WHERE ID =?"; 
            if($stmt = $mysqli->prepare($sql)){
                //Bind variables to the prepared statement as parameters
                $stmt->bind_param("sssssssssss", $param_dateOfExp, $param_amount, $param_payee, $param_paymentMethod, $param_header, $param_reference, $param_description, $param_comments, $param_refID, $param_RecStatus, $param_ID);
                // Set parameters
                $param_dateOfExp = $dateOfExpense;
                $param_amount = $amount;
                $param_payee = $payee;
                $param_paymentMethod = $paymentMethod;
                $param_header = $header;
                $param_reference = $reference;
                $param_description = $description;
                $param_comments = $comments;
                $param_ID = (int)$ID;
                $param_refID = (int)$referenceID;
                $param_RecStatus = $RecStatus;

                // Attempt to execute the prepared statement
                if( $stmt->execute()){
                    // Records created successfully. Redirect to landing page
                    $formData['success'] = true;
                    $formData['response'] = "Record is updated successfully.";
                } else{
                    $formData['success'] = false;
                    $formData['response'] = "Something went wrong. Please try again later.";
                }
            
                // Close statement
                $stmt->close();
            }else{ 
                $formData['success'] = false;
                $formData['response'] = "Could not prepare the statement";
            }    
        }else{
            $formData['success'] = false;
            $formData['response'] = $fData['response'];
        }
    }else{
        $formData['success'] = false;
        $formData['response'] = "Please enter " .$formData['response']."";
    }
    
    // Close connection
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    
    // Prepare a select statement
    $sql = "SELECT EX.ID,EX.Date,EX.Amount,EX.Payee,EX.PaymentMethod,EX.Header,EX.Reference,EX.Description,EX.Comments,
    CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, AM.ID AS AssociateID
    FROM Expenses as EX inner join Associate_Master as AM ON AM.ID = EX.refID AND EX.ID = ? AND EX.RecStatus != 'D'";
    $ID =(int) trim($_GET["ID"]);
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_ID);
        
        // Set parameters
        $param_ID = $ID;
        
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $stmt->store_result();
            
            if($stmt->num_rows == 1){
                // bind the result to variables
                $C = array();
                
                $stmt->bind_result($IID,$dateOfExp, $amount, $payee, $paymentMethod, $header, $reference,$description, $comments,$associateName,$associateID);
                $stmt->fetch();

                $C['ID'] = $IID;
                $C['expenseDate'] = $dateOfExp === null ? null: date('m/d/Y',strtotime($dateOfExp));
                $C['amount'] = $amount;
                $C['payee'] = $payee;
                $C['paymentMethod'] = $paymentMethod;
                $C['header'] = $header;
                $C['ref'] = $reference;
                $C['description'] = $description;
                $C['comments'] = $comments;
                $C['associateName'] = $associateName;
                $C['associateID'] = $associateID;
                $C['originalAmount'] = $amount;

                $formData['success'] = true;
                $formData['response'] = $C;
            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                $formData['success'] = false;
                $formData['response'] = "URL doesn't contain valid id parameter";
            }
        } else{
            $formData['success'] = false;
            $formData['response'] = "Oops! Something went wrong. Please try again later";
        }
    // Close statement    
    $stmt->close();
    }
    // Close connection
    $mysqli->close();
} else{
    // URL doesn't contain id parameter. Redirect to error page
    $formData['success'] = false;
    $formData['response'] = "Oops! Something went wrong. Please try again later";
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
    $originalAmount = empty(trim($data['originalAmount']))?0:floatval(trim($data['originalAmount']));
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
        $TotalExpenses = $TotalExpenses + $amount - $originalAmount;
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
