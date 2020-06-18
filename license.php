<?php
$keys = readJson("keys.json");

include('Crypt/RSA.php');

if(isset($_GET["serial"]) && isset($_GET["hwid"])){
    
    $serial = $_GET["serial"];
    $hwid = $_GET["hwid"];
	$hwid = str_replace("-", "+",$hwid);
	$hwid = str_replace("_", "/",$hwid);
    
    if(reedemKey($serial, $hwid)) echo signHWID(base64_decode($hwid));
    else echo "serial_invalid";
    
}else echo "noargs";
http_response_code(200);

function signHWID($hwid){
	$rsa = new Crypt_RSA();
	$rsa->loadKey('PUT_RSAPRIVKEY_HERE_IN_XML_FORMAT');
	$rsa->setHash('sha256');
	$rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
    $signature = $rsa->sign($hwid);
	return base64_encode($signature);
}

function reedemKey($key, $hwid64){
    global $keys;
    if(!isset($keys[$key])){
        return false;
    }else if($keys[$key]["used"] == "yes"){
        if($keys[$key]["usedBy"] == $hwid64){
            $keys[$key] = array(
                "used" => "yes",
                "useTime" => time(),
                "usedBy" => $hwid64
            );
            writeJson("keys.json", $keys);
            return true;
        }
        
		return false;
    }else{
        $keys[$key] = array(
            "used" => "yes",
            "useTime" => time(),
            "usedBy" => $hwid64
        );
        writeJson("keys.json", $keys);
        return true;
    }
}

function writeJson($file, $content){
    $fp = fopen($file, 'w');
    fwrite($fp, json_encode($content, JSON_PRETTY_PRINT));
    fclose($fp);
}

function readJson($fileName){
    $fileContents = @file_get_contents($fileName);
    if($fileContents == FALSE) return array();
    else return json_decode($fileContents, true);
}

?>