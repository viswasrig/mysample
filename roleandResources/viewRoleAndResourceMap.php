<?php
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
if ( isset($_GET["RRID"]) && !empty(trim($_GET["RRID"])) ) {
    $ID = trim($_GET["RRID"]);

    $sql = "SELECT RM. * , RRM.ID AS RRID, RRM.ResourceID, RRM.RoleID, ROL.RoleName, ROL.RoleDesc, RRM.Status, RRM.CreatedBy, RRM.CreatedDate, RRM.ModifiedBy, RRM.ModifiedDate, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS CreatedByName, CONCAT( AMM.FirstName,  ' ', AMM.LastName ) AS ModifiedByName
FROM Resource_Master AS RM
LEFT OUTER JOIN Role_Resource_Map AS RRM ON ( RRM.ResourceID = RM.ID ) 
LEFT OUTER JOIN Roles_Master AS ROL ON ( ROL.ID = RRM.RoleID ) 
LEFT OUTER JOIN Associate_Master AS AM ON ( RRM.CreatedBy IS NOT NULL 
AND AM.ID = RRM.CreatedBy ) 
LEFT OUTER JOIN Associate_Master AS AMM ON ( RRM.ModifiedBy IS NOT NULL 
AND AMM.ID = RRM.ModifiedBy ) 
GROUP BY RRID 
HAVING RRM.Status IS NOT NULL AND RRID=" .$ID;
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['ResourceName'] = $row['ResourceName'];
                $C['ResorceDesc'] = $row['ResorceDesc'];
                $C['RRID'] = $row['RRID'];
                $C['RoleID'] = $row['RoleID'];
                $C['ResourceID'] = $row['ResourceID'];
                $C['Status'] = $row['Status'];
                $C['RoleDesc'] = $row['RoleDesc'];
                $C['RoleName'] = $row['RoleName'];
                $C['CreatedBy'] = $row['CreatedBy'];
                $C['CreatedDate'] = $row['CreatedDate'] == null?null:date('m/d/Y',strtotime($row['CreatedDate']));
                $C['ModifiedBy'] = $row['ModifiedBy'];
                $C['ModifiedDate'] = $row['ModifiedDate'] == null?null:date('m/d/Y',strtotime($row['ModifiedDate']));
                $C['CreatedByName'] = $row['CreatedByName'];
                $C['ModifiedByName'] = $row['ModifiedByName'];
                $C['ResourceRoleStatus'] = '';
                if($row['Status'] && $row['Status'] == "A"){
                    $C['ResourceRoleStatus'] = 'Active';
                }else if($row['Status'] && $row['Status'] == "D"){
                    $C['ResourceRoleStatus'] = 'Not Active';
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
}

$mysqli->close();
echo json_encode($formData);

function convertDate($originalDate){
    $convertedDate = date("Y-m-d", strtotime($originalDate));
   // ChromePhp::log($convertedDate);
    return $convertedDate;
}
?>