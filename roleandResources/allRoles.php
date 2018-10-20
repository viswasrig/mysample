<?php
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
$sql = "SELECT RM . * ,RAM.ID as RID,RAM.RoleID,RAM.AssociateID, RAM.Status, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS FullName
    FROM Roles_Master AS RM
    INNER JOIN Role_Associate_Map AS RAM ON RAM.RoleID = RM.ID
    INNER JOIN Associate_Master AS AM ON RAM.AssociateID = AM.ID
    AND RAM.Status IS NOT NULL";
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['RoleName'] = $row['RoleName'];
                $C['RoleDesc'] = $row['RoleDesc'];
                $C['AssociateID'] = $row['AssociateID'];
                $C['FullName'] = $row['FullName'];
                $C['Status'] = $row['Status'];
                $C['RoleID'] = $row['RoleID'];
                $C['RID'] = $row['RID'];
                $C['RoleAssociatedStatus'] = '';
                if($row['Status'] && $row['Status'] == "A"){
                    $C['RoleAssociatedStatus'] = 'Active';
                }else if($row['Status'] && $row['Status'] == "D"){
                    $C['RoleAssociatedStatus'] = 'Not Active';
                }
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
echo json_encode($formData);  
?>