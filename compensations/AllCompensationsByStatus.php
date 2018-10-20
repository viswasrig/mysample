<?php
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
$condition = "";
if(!empty($_GET["RECSTATUS"])){ 
    if($_GET["RECSTATUS"] == "AU"){
        $condition = "RecStatus= 'A' OR RecStatus= 'U'";
    }
    $sql = "SELECT AC.*, CONCAT(AM.FirstName, ' ', AM.LastName) as FullName FROM Associate_Compensation as AC 
    LEFT OUTER JOIN Associate_Master as AM on AC.AssociateID = AM.ID GROUP BY AC.ID HAVING " .$condition. " ORDER BY FullName ASC";
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['AssociateID'] = $row['AssociateID'];
                $C['FullName'] = $row['FullName'];
                $C['Prctg'] = $row['Prctg'];
                $C['mrktExp'] = $row['mrkt_exp'];
                $C['ModifiedDate'] = $row['ModifiedDate'] == null ? null:date('m/d/Y',strtotime($row['ModifiedDate']));
                $C['ModifiedBy'] = $row['ModifiedBy'];
                $C['RecStatus'] = $row['RecStatus'];
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
}else{
    $formData['success'] = false ;
    $formData['response'] = "RecStatus is not found in URL";
}
$mysqli->close();
echo json_encode($formData);  
?>