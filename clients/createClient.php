<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
// Include config file
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
 
// Define variables and initialize with empty values
$name = "";
$name_err = "";
$dateCreated = date_create()->format('Y-m-d H:i:s');
// Processing form data when form is submitted
$form_data = array();
$resultData = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){
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
    $defaultRecStatus = 'A';
    if(empty($name_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO Client_Master 
        (Name, Type, LineOfBusiness, FEIN, FAXNumber, WebsiteURL, DateCreated, RecStatus) 
        VALUES (?,?,?,?,?,?,?,?)";
 
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssssss", $param_name, $param_type, $param_lob, $param_fein, $param_fax, $param_url, $param_dateCreated, $defaultRecStatus);

            // Set parameters
            $param_name = $name;
            $param_type = $type;
            $param_lob = $lob;
            $param_fein = $fein;
            $param_fax = $fax;
            $param_url = $url;
            $param_dateCreated = $dateCreated;
                       
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
        $form_data['response']= "".$name_err;
    }
    // Close connection
    $mysqli->close();
}
echo json_encode($form_data);

function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
    return $convertedDate;
}
?>