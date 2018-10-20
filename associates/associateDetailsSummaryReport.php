<?php
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
require_once '../config.php';
$formData =array();
$resultData = array();
$sql = "";
$formData =array();
$resultData = array();
$conditional = "";
if(!empty($_GET['ID'])) {
    $sql = "SELECT * FROM Associate_Balances WHERE AssociateID =" .$_GET['ID'];
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while($row = $result->fetch_array()){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['AssociateID'] = $row['AssociateID'];
                $C['TotalIncome'] = $row['TotalIncome'];
                $C['TotalExpenses'] = $row['TotalExpenses'];
                $C['Balance'] = $row['Balance'];
                $C['Reference'] = $row['Reference'];
                $resultData[] = $C;
            }
            $result->free();
        }
        $formData['success'] = true ;
        $formData['response'] =  $resultData;
    }else{
        $formData['success'] = false ;
        $formData['response'] =  $resultData;
        $formData['msg']="ERROR: Could not able to execute $sql. " . $mysqli->error;
    }
    $mysqli->close();
}
echo json_encode($formData);
?>