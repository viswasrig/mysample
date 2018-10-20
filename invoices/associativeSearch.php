<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$form_data = array();
$resultData = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $queryParam = $_POST["query"];
    if($queryParam != ""){ 
        $sql = "SELECT * FROM Associate_Master WHERE (FirstName LIKE '%".$queryParam ."%' OR LastName LIKE '%".$queryParam ."%') AND DateOfLeaving IS NULL";  
        if($result = $mysqli->query($sql)){
            $form_data['sql'] = $sql; 
            $form_data['number of rows'] = $result->num_rows; 
            if($result->num_rows > 0){      
                while($row = mysqli_fetch_array($result)){
                    $C = array();
                    $C['ID'] = $row["ID"];
                    $C['FirstName'] = $row["FirstName"];
                    $C['LastName'] = $row["LastName"];
                    $resultData[] = $C;
                    //$output .= '<li id=' .$row["ID"] .'>'.$row["FirstName"] .' ' .$row["LastName"] .'</li>';
                }  
             }  
        }
    }
        
 } 
$mysqli->close();
$form_data['success'] = true;
$form_data['response'] = $resultData;
 echo json_encode($form_data);
 ?>  