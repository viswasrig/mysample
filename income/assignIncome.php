<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$associateName = $percentage ="";
$associateName_err = $percentage_err = "";
$dateOfExp = date_create()->format('Y-m-d H:i:s');
$form_data = array();
$resultData = array(); 
$associateID = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $input_associateName = trim($_POST["AssociativeID"]);
    if(empty($input_associateName)){
        $associateName_err = "Associate ID is required";
    } else {
        $associateID = (int)trim($_POST["AssociativeID"]);
    }
    $form_data['error']=$associateName_err;
    if( empty($associateName_err) ){
    $sql = "SELECT Prctg, mrkt_exp from Associate_Compensation WHERE AssociateID=?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID = (int)$associateID;
        if($stmt->execute()){
            $stmt->store_result();
            $form_data['number of rows'] = $stmt->num_rows;
            if($stmt->num_rows == 1){
                $stmt->bind_result($Prctg,$mrktExp);
                $stmt->fetch();
                $percentage = $Prctg;
                $_POST['percentage'] = $Prctg;
                $_POST['mrktExp'] = $mrktExp;
                $form_data['success'] = true;
                if($percentage == null){
                    $form_data['success'] = false;
                    $form_data['response'] = "Percentage is not found for corresponding Associate: " .$associateID . " Whose Name is " .$associateName. " . Please insert percentage Record.";
                }
            } else{
                $form_data['success'] = false;
                $form_data['response'] = "Percentage is not found for corresponding Associate: " .$associateID . " Whose Name is " .$associateName. " . Please insert percentage Record.";
            }
    }else{
        $form_data['success'] = false;
        $form_data['msg'] = "URL doesn't contain valid id parameter. Redirect to error page" .$mysqli->error;
    }
    $stmt->close();
    }else{
        $form_data['success'] = false;
        $form_data['success'] = "AssociateID is not found" .$mysqli->error;
    }
    }else{
        $form_data['success'] = false;
        $form_data['success'] = "AssociateID is not found";
    }
    if($form_data['success']){
        $tempData  = insertUpdateAssociateIncome($_POST, $mysqli);
        $form_data['success'] =$tempData['success'];
        $form_data['response'] =$tempData['response'];
        if($tempData['success']){
            $tempData = insertUpDateAssociateBalence($_POST, $mysqli);
            $form_data['success'] =$tempData['success'];
            $form_data['response'] =$tempData['response']; 
            if ($tempData['success']) {
                $receivedDate = $_POST["ReceivedDate"] ==null?null:convertDate(trim($_POST["ReceivedDate"]));
                $InvoiceID = trim($_POST["ID"]);
                $amount = trim($_POST["TotalAmount"]);
                $employeeShare = ($amount * $percentage)/100;
                $reference=null;
                /* $sql = "INSERT INTO Associate_Income 
                (AssociateID,InvoiceID,ReceivedDate,EmployerAmount, Percentage,EmployeeShare,Reference) 
                VALUES (?,?,?,?,?,?,?)"; */
                $sql ="UPDATE Invoice_Master SET IncomeAssigned='Y' WHERE ID=?";
                if($stmt = $mysqli->prepare($sql)){
                    // $stmt->bind_param("sssssss", $associateID,$InvoiceID, $receivedDate, $amount, $percentage, $employeeShare, $reference);
                    $stmt->bind_param("s",$InvoiceID);
                    if($stmt->execute()){
                        $form_data['success'] = true;
                        $form_data['response']= "Income Assigned successfully.";
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
        }
        
    }
    $mysqli->close();
}
echo json_encode($form_data);
function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}
function insertUpdateAssociateIncome($data, $mysqli) {
    $associateID = trim($data['AssociativeID']);
    $invoiceID = trim($data['ID']); 
    $AssignIncomeID = '';
    $EmployerAmount = '';
    $assocIncomeFormData = array();
    $$percentage = '';
    $sql="SELECT ID,EmployerAmount FROM Associate_Income WHERE AssociateID =? AND InvoiceID= ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("ss", $associateID,$invoiceID);
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($AssignIncomeID, $EmployerAmount);
                $stmt->fetch();
            }
        }
        $stmt -> close();
    }
    $receivedDate = $data["ReceivedDate"] != null && trim($data["ReceivedDate"]).length>0 ? convertDate(trim($data["ReceivedDate"])):trim($data["ReceivedDate"]);
    // $receivedDate = $data["ReceivedDate"] == null?null:convertDate(trim($data["ReceivedDate"]));
    $InvoiceID = trim($data["ID"]);
    $rate = trim($data["Price"]);
    $rate = empty($rate) ? 0 : floatval($rate);
    $percentage = floatval(trim($data["percentage"]));

    $mrktExp = trim($data["mrktExp"]);
    $mrktExp = empty($mrktExp) ? 0 : floatval($mrktExp);

    $noOfUnits = trim($data["NumOfUnits"]);
    $noOfUnits = empty($noOfUnits)?0:((int)$noOfUnits);
    $deductions = empty(trim($data['Deductions']))?0.0:floatval(trim($data['Deductions']));
    $amount = (($rate - $mrktExp) * $noOfUnits ) - $deductions;

    $employeeShare = ($amount * $percentage)/100;
    $reference = null;
    $EmployerAmount = empty($EmployerAmount)? 0 :((float)$EmployerAmount);

    if(empty($AssignIncomeID)){
        $sql="INSERT INTO Associate_Income 
        (AssociateID,InvoiceID,ReceivedDate,EmployerAmount, Percentage,EmployeeShare,Reference) 
        VALUES (?,?,?,?,?,?,?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssssss", $associateID,$InvoiceID, $receivedDate, $amount, $percentage, $employeeShare, $reference);
           if($stmt->execute()){
                $assocIncomeFormData['success'] = true;
                $assocIncomeFormData['response']= "Income Assigned Record successfully.";
            } else{
                $assocIncomeFormData['success'] = false;
                $assocIncomeFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
            } 
        $stmt->close();
        }else{ 
            $assocIncomeFormData['success'] = false;
            $assocIncomeFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
        }
    } else { 
        $sql="UPDATE Associate_Income SET ReceivedDate=?, EmployerAmount=?,Percentage=?, EmployeeShare=? WHERE ID=?";
        $amount = $EmployerAmount + ($rate - $mrktExp) * $noOfUnits;
        $employeeShare = ($amount * $percentage)/100;
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sssss", $receivedDate, $amount, $percentage, $employeeShare, $AssignIncomeID);
           if($stmt->execute()){
                $assocIncomeFormData['success'] = true;
                $assocIncomeFormData['response']= "Income Assigne updated record successfully.";
            } else{
                $assocIncomeFormData['success'] = false;
                $assocIncomeFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
            } 
        $stmt->close();
        }else{ 
            $assocIncomeFormData['success'] = false;
            $assocIncomeFormData['response']= "Something went wrong. Please try again later" .$mysqli->error;
        }

    }
    return $assocIncomeFormData;
}

function insertUpDateAssociateBalence($data, $mysqli){
    $associateID = trim($data['AssociativeID']);
    $invoiceID = trim($data['ID']); 
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
    $receivedDate = $data["ReceivedDate"] ==null?null:convertDate(trim($data["ReceivedDate"]));
    $InvoiceID = trim($data["ID"]);
    
    $rate = trim($data["Price"]);
    $rate = empty($rate) ? 0 : floatval($rate);
    $percentage = floatval(trim($data['percentage']));

    $mrktExp = trim($data["mrktExp"]);
    $mrktExp = empty($mrktExp) ? 0 : floatval($mrktExp);

    $noOfUnits = trim($data["NumOfUnits"]);
    $noOfUnits = empty($noOfUnits) ? 0 : ((int)$noOfUnits);
    $deductions = empty(trim($data['Deductions']))?0.0:floatval(trim($data['Deductions']));
    $amount = (($rate - $mrktExp) * $noOfUnits ) - $deductions;
    
    $employeeShare = ($amount * $percentage)/100;
    $reference = null;
    $EmployerAmount = empty($EmployerAmount)? 0 :floatval($EmployerAmount);
    
    $TotalIncome = empty(trim($TotalIncome))? 0 :floatval(trim($TotalIncome));
    $TotalExpenses = empty(trim($TotalExpenses))?0:floatval(trim($TotalExpenses));
    $balence = 0;

    if(empty($AssignIncomeID)){
        $TotalIncome = $amount;
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
        if( ((int)$TotalIncome) > 0){
            $TotalIncome = $TotalIncome + $employeeShare;
        }else{
            $TotalIncome = $TotalIncome + $amount;
        }
        
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