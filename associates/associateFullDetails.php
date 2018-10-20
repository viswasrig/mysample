<?php
$data   = urldecode(file_get_contents("php://input"));
$_POST  = json_decode($data, true);
date_default_timezone_set('America/Chicago');
//ini_set('session.save_path', '/home/content/31/7042131/html/tmp');
session_start();
require_once '../config.php';
$formData =array();
$resultData = array();
if(!empty(trim($_GET['userId']))){
    $userId = trim($_GET['userId']);
    if(!empty($_GET['type'] === "ADDR")){
        $formData = getAddressDetails($userId, $mysqli);
    }
    if(!empty($_GET['type'] === "PHONE_EMAIL")){
        $formData = getPhoneAndEmailDetails($userId, $mysqli);
    }
    // $formData['temp'] ="GET";
    $mysqli->close();
}
if(!empty(trim($_POST['userId']))){
    // $formData['temp'] ="POST";
    if(!empty(trim($_POST['PTYPE']))  && trim($_POST['PTYPE']) === "ADDR"){
        $formData = insertOrUpdateAddressDetails($_POST, $mysqli);
    }

    if(!empty(trim($_POST['PTYPE']))  && trim($_POST['PTYPE']) === "PHONE_EMAIL"){
        $formData = insertOrUpdatePhoneDetails($_POST, $mysqli);
        if( $formData['success'] ) { 
            $formData = insertOrUpdateEmailDetails($_POST, $mysqli);
        }
    }
    $mysqli->close();
}
echo json_encode($formData);  
function getPersonalDetails ($userId, $mysqli) {
    $sql = "SELECT * FROM Associate_Master WHERE ID =?";
}

function getAddressDetails($userId, $mysqli){
    $sql = "SELECT * FROM Physical_Addresses WHERE EntityID=?";
    $tempData = array();
    $tempResultData = array();
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID = (int)$userId;
        if($stmt->execute()){
            $result = $stmt->get_result();
            // $tempData['numberOfRecords'] =$result->num_rows;  
            if($result->num_rows >0 ){
                // $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                       $C = array();
                       $C['addressID'] = $row['AddressID'];
                       $C['entityID'] = $row['EntityID'];
                       $C['address1'] = $row['Address1'];
                       $C['address2'] = $row['Address2'];
                       $C['city'] = $row['City'];
                       $C['state'] = $row['State'];
                       $C['zip'] = $row['Zip'];
                       $C['country'] = $row['Country'];
                       $C['typeOfEntity'] = $row['TypeOfEntity'];
                       $C['addressType'] = $row['AddressType'];
                       $tempResultData[] = $C;
                }
                $tempData['success'] = true;
                $tempData['response'] = $tempResultData;  
            } else {
                $tempData['success'] = false;
                $tempData['response'] = [];
                $tempData['msg'] = "Error in Fetch Address Details" .$mysqli->error;
            }
            $stmt ->close();  
        } else {
            $tempData['success'] = false;
            $tempData['response'] = [];
            $tempData['msg'] = "Error in Fetch Address Details" .$mysqli->error; 
        }
    } else {
        $tempData['success'] = false;
        $tempData['response'] = [];
        $tempData['msg'] = "Error in Fetch Address Details" .$mysqli->error;
    }

    return $tempData;
}

function insertOrUpdateAddressDetails($data, $mysqli){
    $addressID = trim($data['addressID']);
    $entityID = trim($data['entityID']);
    $associateID = trim($data['ID']);
    $address1 = trim($data['address1']);
    $address2 = trim($data['address2']);
    $city = trim($data['city']);
    $state = trim($data['state']);
    $zip = trim($data['zip']);
    $country = trim($data['country']);
    $typeOfEntity = trim($data['typeOfEntity']);
    $addressType = trim($data['addressType']);
    $tempFormData = array();
    $sql = "INSERT INTO Physical_Addresses (EntityID,Address1,Address2,City,State,Zip,Country,TypeOfEntity,AddressType) VALUES(?,?,?,?,?,?,?,?,?)";                 
    if(!empty(trim($addressID)) ) { 
        $sql = "UPDATE Physical_Addresses SET Address1 =?, Address2=?,City=?,State=?,Zip=?, Country=?, TypeOfEntity=?, AddressType=? WHERE AddressID=? AND EntityID=?"; 
    }
    if($stmt = $mysqli->prepare($sql)){
        if(empty(trim($addressID)) ){
            $stmt->bind_param("sssssssss",$associateID, $address1, $address2, $city, $state,
            $zip, $country, $typeOfEntity, $addressType);
        }else{
            $stmt->bind_param("ssssssssss", $address1, $address2, $city, $state,
            $zip, $country, $typeOfEntity, $addressType,$addressID, $associateID);
        }

        if( $stmt->execute() ){
            $tempFormData['success'] = true;
            $tempFormData['response'] = empty(trim($addressID))? "Address Details are inserted" :"Address Details is updated Succefully";
        } else{
            $tempFormData['success'] = false;
            $tempFormData['response'] = "Insert Or Update Address Details " . $mysqli->error;;
        }
        $stmt ->close();
    }else{
        $tempFormData['success'] = false;
        $tempFormData['response'] = "Insert Or Update Address Details " . $mysqli->error;;
    }

   return $tempFormData;
}

function insertOrUpdatePhoneDetails($data,$mysqli){
    $phoneID = trim($data['phoneID']);
    $entityID = trim($data['entityID']);
    $associateID = trim($data['ID']);
    $phone = trim($data['phone']);
    $MobileKey = "Mobile";
    $typeOfEntity = empty(trim($data['typeOfEntity']))?'client':trim($data['typeOfEntity']);
    $tempFormData = array();
    $CLIENT = "client";
    $CURRENT = "current";
    $sql = "INSERT INTO ElectronicComm (Type,Value,EntityID,EntityType,CommType) values(?,?,?,?,?)";                 
    if(!empty(trim($phoneID)) ) { 
        $sql = "UPDATE ElectronicComm SET Value =?, EntityType=?,CommType=? WHERE ID=? AND EntityID=?"; 
    }
    if($stmt = $mysqli->prepare($sql)){
        if(empty(trim($phoneID)) ){
            $stmt->bind_param("sssss",$MobileKey,$phone,$associateID,$typeOfEntity,$CURRENT);
        }else{
            $stmt->bind_param("sssss", $phone, $typeOfEntity, $CURRENT, $phoneID, $associateID);
        }

        if( $stmt->execute() ){
            $tempFormData['success'] = true;
            $tempFormData['response'] = empty(trim($phoneID))? "Phone Details are inserted" :"Phone Details is updated Succefully";
        } else{
            $tempFormData['success'] = false;
            $tempFormData['response'] = "Insert Or Update Phone Details " . $mysqli->error;;
        }
        $stmt ->close(); 
    }else{
        $tempFormData['success'] = false;
        $tempFormData['response'] = "Insert Or Update Phone Details " . $mysqli->error;;
    }

   return $tempFormData;
}

function insertOrUpdateEmailDetails($data, $mysqli){
    $emailID = trim($data['emailID']);
    $entityID = trim($data['entityID']);
    $associateID = trim($data['ID']);
    $email = trim($data['email']);
    $EmailKey = "Email";
    $CLIENT = "client";
    $typeOfEntity = empty(trim($data['typeOfEntity']))?$CLIENT:trim($data['typeOfEntity']);
    $tempFormData = array();
    $CURRENT = "current";
    $sql = "INSERT INTO ElectronicComm (Type,Value,EntityID,EntityType,CommType) values(?,?,?,?,?)";                 
    if(!empty(trim($emailID)) ) { 
        $sql = "UPDATE ElectronicComm SET Value =?, EntityType=?,CommType=? WHERE ID=? AND EntityID=?"; 
    }
    if($stmt = $mysqli->prepare($sql)){
        if(empty(trim($emailID)) ){
            $stmt->bind_param("sssss",$EmailKey,$email,$associateID,$typeOfEntity,$CURRENT);
        }else{
            $stmt->bind_param("sssss", $email, $typeOfEntity, $CURRENT, $emailID, $associateID);
        }

        if( $stmt->execute() ){
            $tempFormData['success'] = true;
            $tempFormData['response'] = empty(trim($emailID))? "Email Details are inserted" :"Email Details is updated Succefully";
        } else{
            $tempFormData['success'] = false;
            $tempFormData['response'] = "Insert Or Update Email Details " . $mysqli->error;;
        }
        $stmt ->close(); 
    }else{
        $tempFormData['success'] = false;
        $tempFormData['response'] = "Insert Or Update Email Details " . $mysqli->error;;
    }

   return $tempFormData;
}

function getPhoneAndEmailDetails($userId,$mysqli){ 
    $sql = "SELECT * FROM ElectronicComm WHERE EntityID=?";
    $tempData = array();
    $tempResultData = array();
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_ID);
        $param_ID = (int)$userId;
        if($stmt->execute()){
            $result = $stmt->get_result();
            // $tempData['numberOfRecords'] =$result->num_rows;
            if($result->num_rows >0 ){
                // $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                       $C = array();
                       $C['type'] = $row['Type'];
                     if($row['Type'] === 'Email'){
                        $C["eType"] = $row['Type']; 
                        $C['emailID'] = $row['ID'];
                        $C['email'] = $row['Value'];
                     }else{
                        $C["pType"] = $row['Type']; 
                        $C['phoneID'] = $row['ID'];
                        $C['phone'] = $row['Value'];
                     }
                       $C['entityID'] = $row['EntityID'];
                       $C['typeOfEntity'] = $row['EntityType'];
                       $C['CommonType'] = $row['CommType'];
                       $tempResultData[] = $C;
                }
                $tempData['success'] = true;
                $tempData['response'] = $tempResultData;  
            } else {
                $tempData['success'] = false;
                $tempData['response'] = [];
                $tempData['msg'] = "Error in Fetch Email and Phone Details" .$mysqli->error;
            }
            $stmt ->close();  
        } else {
            $tempData['success'] = false;
            $tempData['response'] = [];
            $tempData['msg'] = "Error in Fetch Email and Phone Details" .$mysqli->error; 
        }
    } else {
        $tempData['success'] = false;
        $tempData['response'] = [];
        $tempData['msg'] = "Error in Fetch Email and Phone Details" .$mysqli->error;
    }
    return $tempData;
}
?>