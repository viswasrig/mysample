<?php
date_default_timezone_set('America/Chicago');
// Include config file
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
// Processing form data when form is submitted
$formData = array();
// Processing form data when form is submitted
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    $tempForm =checkPercentageExistsToAssociate($_POST, $mysqli);
    if($tempForm['success']){
        $formData['success'] = $tempForm['success'];
        $formData['response'] = $tempForm['response'];
        $_POST['percentage'] = $tempForm['percentage'];
        $_POST['mrktExp'] = $tempForm['mrktExp'];
        $tempForm = insertUpdateAssociateIncome($_POST, $mysqli);
        $formData['success'] = $tempForm['success'];
        $formData['response'] = $tempForm['response'];
        if($tempForm['success']){
            $tempForm = insertUpDateAssociateBalence($_POST, $mysqli);
            $formData['success'] = $tempForm['success'];
            $formData['response'] = $tempForm['response'];
        }
        if($tempForm['success']){
            $tempForm = updateInvoiceReceivePayment($_POST, $mysqli);
            $formData['success'] = $tempForm['success'];
            $formData['response'] = $tempForm['response'];
        }
    }else{
        $formData['success'] = $tempForm['success'];
        $formData['response'] = $tempForm['response'];
        $formData['percentage'] = $tempForm['percentage'];
    }
    // Close connection
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    
    // Prepare a select statement
    $sql = "SELECT IM.ID, IM.AltID, IM.Date, CONCAT(AM.FirstName,' ', AM.LastName) AS FullName, CM.Name AS ClientName, 
    IM.Price, ASM.Unit, IM.NumOfUnits, IM.Price*IM.NumOfUnits AS Amount, IM.Type, IM.Deductions, IM.DueDate,IM.FromDate, IM.toDate, IM.Comments, IM.ReceivedAmount, IM.ReceivedDate, AM.ID as associateID
    FROM Invoice_Master IM
    JOIN Assignment_Master ASM ON IM.AssignmentID = ASM.ID
    JOIN Associate_Master AM ON ASM.AssociateID = AM.ID
    JOIN Client_Master CM ON ASM.ClientID = CM.ID Where IM.ID = ?";
    $ID = trim($_GET["ID"]);
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID =(int)$ID;
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($ID, $altID, $date, $associateName, $clientName, $rate, $unit, $numOfUnits, 
                $amount, $type, $deductions,$dueDate,$fromDate,$toDate,$comments, $rAmount, $rDate, $associateID);
                
                $stmt->fetch();
                $C=array();
                $C['ID'] = $ID;
                $C['altID'] = $altID;
                $C['invoiceDate'] = $date == null ? null:date('m/d/Y',strtotime($fromDate));
                $C['associateName'] = $associateName;
                $C['cname'] = $clientName;
                $C['rate'] = $rate;
                $C['unit'] = $unit;
                $C['noOfUnits'] = $numOfUnits;
                $C['amount'] = $amount;
                $C['type'] = $type;
                $C['deductions'] =$deductions;
                //$C['fromDate'] = $fromDate;
                $C['associateID'] = $associateID;
                $C['fromDate'] =$fromDate == null ? null:date('m/d/Y',strtotime($fromDate));
                $C['toDate'] =$toDate == null?null:date('m/d/Y',strtotime($toDate));
                $C['dueDate'] = $dueDate == null? null:date('m/d/Y',strtotime($dueDate)); 
                $C['comments'] = $comments;
                $C['receivedAmount'] = $rAmount;
                $C['receivedDate'] = $rDate === null ? null: date('m/d/Y',strtotime($rDate));
                $formData['success'] = true;
                $formData['response'] = $C;
            } else{
               $formData['success'] = false;
                $formData['msg'] = "URL doesn't contain valid id parameter. Redirect to error page";
            }
            
        } else{
            $formData['success'] = false;
            $formData['msg'] = "Oops! Something went wrong. Please try again later.";
        }
    // Close statement    
    $stmt->close();
    }
    // Close connection
    $mysqli->close();
} else{
    $formData['success'] = false;
   $formData['msg'] = "URL doesn't contain id parameter. Redirect to error page";
}
echo json_encode($formData);
function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}

function checkPercentageExistsToAssociate($item, $mysqli){
    $formPercentage = array();
    $associateID = trim($item['associateID']);
    $name = $item['associateName'];
    $Prctg = '';
    $sql = "SELECT Prctg, mrkt_exp from Associate_Compensation WHERE AssociateID=?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID = (int)$associateID;
        if($stmt->execute()){
            $stmt->store_result();
            $formPercentage['number of rows'] = $stmt->num_rows;
            if($stmt->num_rows == 1){
                $stmt->bind_result($Prctg,$mrktExp);
                $stmt->fetch();

                $formPercentage['success'] = true;
                $formPercentage['percentage'] = $Prctg;
                $formPercentage['mrktExp'] = $mrktExp;
                if($Prctg == null){
                    $formPercentage['success'] = false;
                    $formPercentage['response'] = "Percentage is not found for corresponding Associate: " .$associateID . " Whose Name is " .$name. " . Please insert percentage Record.";
                }
            }else{
                $formPercentage['success'] = false;
                $formPercentage['response'] = "Percentage is not found for corresponding Associate: " .$associateID . " Whose Name is " .$name. " . Please insert percentage Record.";
            }
    }else{
        $formPercentage['success'] = false;
        $formPercentage['response'] = "URL doesn't contain valid id parameter. Redirect to error page" .$mysqli->error;
    }
    $stmt->close();
    }else{
        $formPercentage['success'] = false;
        $formPercentage['success'] = "SQL Query Exception" .$mysqli->error;
    }
    return $formPercentage;
}

function insertUpdateAssociateIncome($data, $mysqli) {
    $associateID = trim($data['associateID']);
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
    $receivedDate = $data["receivedDate"] != null && trim($data["receivedDate"]).length>0 ? convertDate(trim($data["receivedDate"])):trim($data["receivedDate"]);
    // $receivedDate = $data["ReceivedDate"] == null?null:convertDate(trim($data["ReceivedDate"]));
    $InvoiceID = trim($data["ID"]);
    $rate = trim($data["rate"]);
    $rate = empty($rate) ? 0 : floatval($rate);
    $percentage = floatval(trim($data["percentage"]));

    $mrktExp = trim($data["mrktExp"]);
    $mrktExp = empty($mrktExp) ? 0 : floatval($mrktExp);

    $noOfUnits = trim($data["noOfUnits"]);
    $noOfUnits = empty($noOfUnits)?0:((int)$noOfUnits);
    $deductions = empty(trim($data['deductions']))?0.0:floatval(trim($data['deductions']));
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
    $associateID = trim($data['associateID']);
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
    
    $rate = trim($data["rate"]);
    $rate = empty($rate) ? 0 : floatval($rate);
    $percentage = floatval(trim($data['percentage']));

    $mrktExp = trim($data["mrktExp"]);
    $mrktExp = empty($mrktExp) ? 0 : floatval($mrktExp);

    $noOfUnits = trim($data["noOfUnits"]);
    $noOfUnits = empty($noOfUnits) ? 0 : ((int)$noOfUnits);
    $deductions = empty(trim($data['deductions']))?0.0:floatval(trim($data['deductions']));
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

function updateInvoiceReceivePayment($data,$mysqli) { 
    $ID = $data["ID"];
    $invoiceFormData = array();
    $receivedDate = $data["receivedDate"] != null && trim($data["receivedDate"]).length>0 ? convertDate(trim($data["receivedDate"])):trim($data["receivedDate"]);
    $receivedAmount = trim($data["receivedAmount"])?floatval(trim($data["receivedAmount"])):trim($data["receivedAmount"]);
    $paymentMethod = trim($data["paymentMethod"]);
    $paymentRef = trim($data["paymentRef"]);
    $orginalAmount = trim($data["orginalAmount"])?floatval(trim($data["orginalAmount"])):trim($data["orginalAmount"]);
    $comments = trim($data["comments"]);
    $deductions = empty(trim($data["deductions"]))?0.0:floatval(trim($data["deductions"]));
    $RecStatus = $receivedAmount === ($orginalAmount - $deductions)? 'P': 'I';
    $invoiceFormData['modifiedValues']="receivedAmount: " .$receivedAmount ."orginalAmount: " .$orginalAmount;
    $IncomeAssigned = $RecStatus == 'P' ?'Y':null;
    // Prepare an update statement PaymentMethod='" .$paymentMethod ."', PaymentRef='" .$paymentRef ."'
        $sql = "UPDATE Invoice_Master SET  ReceivedDate='" .$receivedDate ."', ReceivedAmount=" .$receivedAmount 
        .", RecStatus='" .$RecStatus ."', IncomeAssigned='" .$IncomeAssigned . "', Comments='" .$comments ."' WHERE ID =" .$ID; 
        if($result = $mysqli->query($sql)){
            $invoiceFormData['success'] = true;
            $invoiceFormData['response'] = "Payment successfully updated";

        } else{
            $invoiceFormData['success'] = false;
            $invoiceFormData['response'] = "Something went wrong. Please try again later." .$mysqli->error;
        }
        return $invoiceFormData;
}

?>
