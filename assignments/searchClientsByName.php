<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData = array();
$resultData = array();
if(isset($_POST["query"])){  
        $sql = "SELECT * FROM Client_Master WHERE Name LIKE '%".$_POST["query"] ."%'";  
        if($result = $mysqli->query($sql)){  
            if($result->num_rows > 0){      
                while($row = mysqli_fetch_array($result)){
                    $C = array();
                    $C['ID'] = $row['ID'];
                    $C['Name'] = $row['Name'];
                    $resultData[] = $C;  
                }  
             }  
        }
 } 
 $mysqli->close();
 $formData['success'] = true;
 $formData['response'] = $resultData;
 echo json_encode($formData);
 ?>  
