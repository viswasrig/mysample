<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
 
// Define variables and initialize with empty values
$formData = array();
$formData['data'] = $_POST;
// Processing form data when form is submitted
if(isset($_POST["ID"]) && !empty($_POST["ID"])){
    // Validate Amount
    $ID = (int)$_POST["ID"];
    
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please Enter the name of the Client.";
     } else{
        $name = $input_name;
    }
    

    $type = trim($_POST["type"]);
    $lob = trim($_POST["lineOfBusiness"]);
    $fein = trim($_POST["fein"]);
    $fax = trim($_POST["fax"]);
    $url = trim($_POST["url"]);
    
    // Check input errors before inserting in database
    if(empty($name_err)){
        // Prepare an insert statement
        $sql = "UPDATE Client_Master SET Name =?, Type =?, LineOfBusiness =?, FEIN =?, FAXNumber =?, WebsiteURL =? WHERE ID =?"; 

        if($stmt = $mysqli->prepare($sql)){
            //Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssss", $param_name, $param_type, $param_lob, $param_fein, $param_fax, $param_url, $param_ID);
            // Set parameters
            $param_name = $name;
            $param_type = $type;
            $param_lob = $lob;
            $param_fein = $fein;
            $param_fax = $fax;
            $param_url = $url;
            $param_ID = $ID;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records created successfully. Redirect to landing page
                $formData['success'] = true;
                $formData['response'] = "Record is updated successfully";
                
            } else{
                $formData['success'] = false;
                $formData['response'] = "Something went wrong. Please try again later.";
            }
            
        // Close statement
        $stmt->close();
        } else { 
            $formData['success'] = false;
                $formData['response'] = "Could not prepare the statement";
        }
    }
    // Close connection
    $mysqli->close();
}else if(isset($_GET["ID"]) && !empty(trim($_GET["ID"]))){
    // Prepare a select statement
    $sql = "SELECT ID, Name, Type, LineOfBusiness, FEIN, FAXNumber, WebsiteURL, DateCreated FROM Client_Master Where ID = ? AND RecStatus='A'";
    $ID = (int)trim($_GET["ID"]);
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
                $stmt->bind_result($IDD, $name, $type, $lob, $fein, $fax, $url,$dateCreated);
                $stmt->fetch();
                $C = array();
                $C['ID'] = $IDD;
                $C['name'] = $name;
                $C['type'] = $type;
                $C['lineOfBusiness'] = $lob;
                $C['fein'] = $fein;
                $C['fax'] = $fax;
                $C['url'] = $url;
                $C['createdDate'] = $dateCreated;
                $formData['success'] = true;
                $formData['response'] = $C;
            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                $formData['success'] = false;
                $formData['response'] = "URL doesn't contain valid id parameter";
            }
            
        } else{
            $formData['success'] = false;
            $formData['response'] = "Oops! Something went wrong. Please try again later.";
        }
    // Close statement    
    $stmt->close();
    }
    // Close connection
    $mysqli->close();
} else{
    $formData['success'] = false;
    $formData['response'] = "Oops! Something went wrong. Please try again later.";
}
echo json_encode($formData);
?>