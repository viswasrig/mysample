<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
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
                $C['Date'] = $dateOfExp === null ? null: date('m/d/Y',strtotime($dateOfExp));
                $C['Amount'] = $amount;
                $C['Payee'] = $payee;
                $C['PaymentMethod'] = $paymentMethod;
                $C['Header'] = $header;
                $C['Reference'] = $reference;
                $C['Description'] = $description;
                $C['Comment'] = $comments;
                $C['AssociateName'] = $associateName;
                $C['associateID'] = $associateID;

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
echo json_encode($formData);
?>