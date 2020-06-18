<?php

$adminuser = "admin";
$adminpassword = "changepass";

$keys = readJson("keys.json");

if(authAdmin()){
    if(isset($_GET["action"])){
        switch($_GET["action"]){
            case "info":
                $used = 0;
                $total = count($keys);

                foreach($keys as $key=>$lul){
                    if($lul["used"] == "yes"){
                        $used++;
                    }
                }
                
                echo "Total Keys: ".$total."<br>";
                echo "Used Keys: ".$used."<br>";
                echo "Unused Keys: ".($total - $used)."<br>";
                echo "<a href='./admin.php'>Back</a><br>";
                return;
            case "genkey":
                echo "Key:<br>";
                echo genKey();
                echo "<br><br>";
                echo "<a href='./admin.php'>Back</a><br>";
                return;
            case "resetForm":
                echo '<form name="form" method="get" action="admin.php">';
                echo '<input type="hidden" id="action" name="action" value="resetKey">';
                echo 'Key: <input type="text" name="key" id="key" ><br/>';
                echo '<input type="submit" value="Reset">';
                echo '</form>';
                echo "<a href='./admin.php'>Back</a><br>";
                return;
            case "resetKey":
                if(isset($_GET["key"]) && $_GET["key"] != null){
                    if(isset($keys[$_GET["key"]])){
                        resetKey($_GET["key"]);
                        echo "Successfully reset Key [".$_GET["key"]."]<br>";
                        echo "<a href='./admin.php'>Back</a><br>";
                    }
                }else{
                    echo "no key<br>";
                    echo "<a href='./admin.php'>Back</a><br>";
                }
                return;
            case "keylist":
                date_default_timezone_set('MET');
                foreach($keys as $key=>$lul){
                    echo $key." = used: ".$lul["used"]."<br>";
                    if($lul["used"] == "yes"){
                        echo "useTimeUnix: ".$lul["useTime"]."<br>";
                        echo "useTime: ".date('d-m-Y H:i:s', $lul["useTime"])."<br>";
                        echo "useTimeFull: ".date('l d M Y H:i:s', $lul["useTime"])."<br>";
                        echo "used by (hwid-b64): ".$lul["usedBy"]."<br>";
                        
                    }
					echo "<br>";
                }
                if(sizeof($keys) == 0) echo "No Keys yet.<br>";
                echo "<a href='./admin.php'>Back</a><br>";
                return;
            default:
                break;
        }
    }
    
    echo "Actions<br>";
    echo "<a href='./admin.php?action=info'>Info</a><br>";
    echo "<a href='./admin.php?action=keylist'>Key List</a><br>";
    echo "<a href='./admin.php?action=genkey'>Generate Key</a><br>";
    echo "<a href='./admin.php?action=resetForm'>Reset Key</a><br>";
    
    
}else{
	echo "unauthorized";
	http_response_code(403);
}

function genKey(){
    global $keys;
    $base = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $key = "";
    for ($i = 0; $i < 16; $i++) {
        $key .= $base[random_int(0, 61)];
    }
    if(isset($keys[$key])){
        return genKey();
    }
    $keys[$key] = array("used" => "no");
    writeJson("keys.json", $keys);
    return $key;
}

function resetKey($key){
    global $keys;
    
    $keys[$key] = array("used" => "no");
    writeJson("keys.json", $keys);
    return $key;
}

function authAdmin(){
	global $adminuser, $adminpassword;
	while(!($_SERVER['PHP_AUTH_USER'] == $adminuser && $_SERVER['PHP_AUTH_PW'] == $adminpassword)){
		header('WWW-Authenticate: Basic realm="License Server Administration"');
		header('HTTP/1.0 401 Unauthorized');
		return false;
		exit("Unauthorized!");
	}
	return true;
}

function writeJson($file, $content){
    $fp = fopen($file, 'w');
    fwrite($fp, json_encode($content, JSON_PRETTY_PRINT));
    fclose($fp);
}

function readJson($fileName){
    $fileContents = @file_get_contents($fileName);
    if($fileContents == FALSE){
        return array();
    }else{
        return json_decode($fileContents, true);
    }
}
?>