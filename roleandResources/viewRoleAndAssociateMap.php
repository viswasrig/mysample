<?php
// ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
if ( isset($_GET["RID"]) && !empty(trim($_GET["RID"])) ) {
    $ID = trim($_GET["RID"]);
    
    $sql = "SELECT RM. * , RAM.ID AS RID, RAM.AssociateID, RAM.RoleID, RAM.Status, RAM.CreatedBy, RAM.CreatedDate, RAM.ModifiedBy, RAM.ModifiedDate, CONCAT( AM.FirstName,  ' ', AM.LastName ) AS AssociateName, CONCAT( AMM.FirstName,  ' ', AMM.LastName ) AS CreatedByName, CONCAT( AMMM.FirstName,  ' ', AMMM.LastName ) AS ModifiedByName
    FROM Roles_Master AS RM
    LEFT OUTER JOIN Role_Associate_Map AS RAM ON ( RAM.RoleID = RM.ID ) 
    LEFT OUTER JOIN Associate_Master AS AM ON ( RAM.AssociateID = AM.ID ) 
    LEFT OUTER JOIN Associate_Master AS AMM ON ( RAM.CreatedBy IS NOT NULL 
    AND AMM.ID = RAM.CreatedBy ) 
    LEFT OUTER JOIN Associate_Master AS AMMM ON ( RAM.ModifiedBy IS NOT NULL 
    AND AMMM.ID = RAM.ModifiedBy ) 
    GROUP BY RoleName
    HAVING STATUS IS NOT NULL 
    AND RID =" .$ID;
    if($result = $mysqli->query($sql)){
        if($result->num_rows > 0){
            while( $row = $result->fetch_array() ){
                $C = array();
                $C['ID'] = $row['ID'];
                $C['RoleName'] = $row['RoleName'];
                $C['RoleDesc'] = $row['RoleDesc'];
                $C['RID'] = $row['RID'];
                $C['RoleID'] = $row['RoleID'];
                $C['AssociateID'] = $row['AssociateID'];
                $C['AssociateName'] = $row['AssociateName'];
                $C['Status'] = $row['Status'];
                $C['CreatedBy'] = $row['CreatedBy'];
                $C['CreatedDate'] = $row['CreatedDate'] == null?null:date('m/d/Y',strtotime($row['CreatedDate']));
                $C['ModifiedBy'] = $row['ModifiedBy'];
                $C['ModifiedDate'] = $row['ModifiedDate'] == null?null:date('m/d/Y',strtotime($row['ModifiedDate']));
                $C['CreatedByName'] = $row['CreatedByName'];
                $C['ModifiedByName'] = $row['ModifiedByName'];
                $C['RoleAssociateStatus'] = '';
                if($row['Status'] && $row['Status'] == "A"){
                    $C['RoleAssociateStatus'] = 'Active';
                }else if($row['Status'] && $row['Status'] == "D"){
                    $C['RoleAssociateStatus'] = 'Not Active';
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