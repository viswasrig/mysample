<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$amount = $payee = $paymentMethod = $header = "";
$amount_err = $payee_err = $paymentMethod_err = $header_err = "";
$dateOfExp = date_create()->format('Y-m-d H:i:s'); 
$formData = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $formData['response'] = '';
    $tempData = insertUpDateAssociateBalence($_POST,$mysqli);
    if ( $tempData['success'] ) { 
        $sql = "UPDATE Expenses SET RecTransformed = ? WHERE ID = ?";
        $ID = $_POST['ID'];
        $RecTransformed = "Y";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ss", $param_RecTransformed, $param_ID);

            $param_RecTransformed = $RecTransformed;
            $param_ID = $ID;

            if( $stmt->execute()){
                $formData['success'] = true;
                $formData['response'] = "Expense record transformed successfully";
            } else{
                $formData['success'] = false;
                $formData['response'] = "Something went wrong. Please try again later" .$mysqli->error;
            }
            $stmt->close();
        }else{
            $formData['success'] = false;
            $formData['response'] = "Something went wrong. Please try again later" .$mysqli->error;
        }

    }else{
        $formData['success'] = $tempData['success'];
        $formData['response'] = $tempData['response'];
    }
    $mysqli->close(); 
}
function insertUpDateAssociateBalence($data, $mysqli){
    $associateID = trim($data['AssociateID']);
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
    $amount = trim($data['Amount']);
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

echo json_encode($formData);
?>

