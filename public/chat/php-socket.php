<?php
require_once "/var/www/html/projects/introjuicer/vendor/autoload.php";
// require_once "C:\wamp64\www/introjuicer/vendor/autoload.php";
// use DateTime;
// use DateTimeZone;
define("SECURE",true);
define("PORT","3013");
if (SECURE== true){
    define('HOST_NAME',"introjuicer.iapplabz.co.in");
    // define('HOST_NAME',"localhost");
}
else
{
    define('HOST_NAME',"localhost");

}

// DB Connection here
function db_connection(){
    // $servername = "localhost";
    $servername = "localhost";
    $password = "asdasd";
    // $password = "";
    $username = "devtest";
    $dbname = "intro";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    return $conn;
    // end
}

// Push Notofication goes here
function iOS($data, $devicetoken, $id,$type,$group_type = ""){
    try{
            $tokens = $devicetoken;
            $development    = false; #make it false if it is not in development mode
            $passphrase     = ''; #your passphrase

            $body['aps'] = array(
                'alert' => array(
                    'title' => $data['title'],
                    'body' => $data['desc'],
                ),
                'sound' => 'default',
                'id' => $id,
                'type' => $type,
                'group_type'=>$group_type,
            );
            $payload        = json_encode($body);

           
          
            // echo $apns_cert; die;
            $apns_port      = 2195;

            if($development)
            {
                $apns_url   = 'gateway.sandbox.push.apple.com';
                $apns_cert      = "apns_Certificates.pem";
            }
            else
            {
                $apns_url   = 'gateway.push.apple.com';
                $apns_cert      = "apns_Certificates.pem";
                // $apns_cert       = "INTROJUICER-APNS-PROD.pem";
            }

            $stream_context = stream_context_create();
            stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);
            stream_context_set_option($stream_context, 'ssl', 'passphrase', $passphrase);

            $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT,$stream_context);
                
            $device_tokens = str_replace("<","",$tokens);
            $device_tokens1= str_replace(">","",$device_tokens);
            $device_tokens2= str_replace(' ', '', $device_tokens1);
            $device_tokens3= str_replace('-', '', $device_tokens2);

            $apns_message  = chr(0) . pack('n', 32) . pack('H*', $device_tokens3) . chr(0) . chr(strlen($payload)) . $payload;
            $msg = fwrite($apns, $apns_message);
                                
            if(!$msg){
                 //file_put_contents('message.txt', $string.'Message not delivered');
                echo 'Message not delivered' . PHP_EOL;
                exit;
            }
            @socket_close($apns);
            @fclose($apns);        
    }catch(Exception $e){}
}

// End here
$null = NULL;
error_reporting(E_ALL);
/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

if (($socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);

if (socket_bind($socketResource, '0.0.0.0', PORT) === false) {
    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($socketResource)) . "\n";
}


if (socket_listen($socketResource,'5')=== false) {
    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($socketResource)) . "\n";
}
$clientSocketArray = array($socketResource);
while (true) {
    $newSocketArray = $clientSocketArray;
    socket_select($newSocketArray, $null, $null, 0, 10);

    if (in_array($socketResource, $newSocketArray)) {
        $newSocket = socket_accept($socketResource);
        $clientSocketArray[] = $newSocket;

        $header = socket_read($newSocket, 1024);
        doHandshake($header, $newSocket, HOST_NAME, PORT);

        socket_getpeername($newSocket, $client_ip_address, $port);
        $connectionACK = newConnectionACK($client_ip_address);

        //send($connectionACK);

        $newSocketIndex = array_search($socketResource, $newSocketArray);
        unset($newSocketArray[$newSocketIndex]);
    }

    foreach ($newSocketArray as $newSocketArrayResource) {
        while(socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1){
            $socketMessage = unseal($socketData);
            $messageObj = json_decode($socketMessage);
        if(isset($messageObj->message_id)){
            $chat_message =  updateReadMessageStatus($messageObj->message_id,$messageObj->receiver_id);
            send($chat_message);
        }
        elseif(isset($messageObj->method) and $messageObj->method == "one_to_one"){
            $chat_box_message = createOneToOneChatBoxMessage($messageObj->sender_id,$messageObj->receiver_id,$messageObj->msg_type, $messageObj->message,$messageObj->timezone,$messageObj->method);
            send($chat_box_message);
        }
        elseif(isset($messageObj->method) and $messageObj->method == "group_chat"){
            $chat_box_message = createGroupChatBoxMessage($messageObj->sender_id,$messageObj->receiver_id,$messageObj->msg_type, $messageObj->message, $messageObj->group_id,$messageObj->timezone,$messageObj->method);
            send($chat_box_message);
        }
        elseif(isset($messageObj->method) and $messageObj->method == "money_section_chat"){
            $chat_box_message = createMoneySectionChatBoxMessage($messageObj->sender_id,$messageObj->receiver_id,$messageObj->msg_type, $messageObj->message, $messageObj->group_id,$messageObj->timezone,$messageObj->method);
            send($chat_box_message);
        }
        
            break 2;
        }

        $socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
        if ($socketData === false) {
            socket_getpeername($newSocketArrayResource, $client_ip_address);
            $connectionACK = connectionDisconnectACK($client_ip_address);
            //send($connectionACK);
            $newSocketIndex = array_search($newSocketArrayResource, $clientSocketArray);
            unset($clientSocketArray[$newSocketIndex]);
        }
    }
}
socket_close($socketResource);


function send($message) {
    global $clientSocketArray;
    $messageLength = strlen($message);
    foreach($clientSocketArray as $clientSocket)
    {
        @socket_write($clientSocket,$message,$messageLength);
    }
    return true;
}

function unseal($socketData) {
    $length = ord($socketData[1]) & 127;
    if($length == 126) {
        $masks = substr($socketData, 4, 4);
        $data = substr($socketData, 8);
    }
    elseif($length == 127) {
        $masks = substr($socketData, 10, 4);
        $data = substr($socketData, 14);
    }
    else {
        $masks = substr($socketData, 2, 4);
        $data = substr($socketData, 6);
    }
    $socketData = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $socketData .= $data[$i] ^ $masks[$i%4];
    }
    return $socketData;
}

function seal($socketData) {
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($socketData);

    if($length <= 125)
        $header = pack('CC', $b1, $length);
    elseif($length > 125 && $length < 65536)
        $header = pack('CCn', $b1, 126, $length);
    elseif($length >= 65536)
        $header = pack('CCNN', $b1, 127, $length);
    return $header.$socketData;
}

function doHandshake($received_header,$client_socket_resource, $host_name, $port) {
    $headers = array();
    $lines = preg_split("/\r\n/", $received_header);
    foreach($lines as $line)
    {
        $line = chop($line);
        if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
        {
            $headers[$matches[1]] = $matches[2];
        }
    }

    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    if (SECURE == true)
    {
        $buffer  = "HTTPS/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host_name\r\n" .
            "WebSocket-Location: wss://$host_name:$port/php-socket.php\r\n".
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    }
    else{
        $buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host_name\r\n" .
            "WebSocket-Location: ws://$host_name:$port/php-socket.php\r\n".
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    }
    socket_write($client_socket_resource,$buffer,strlen($buffer));
}

function newConnectionACK($client_ip_address) {

    $message = 'New client :' . $client_ip_address.' joined';
    $messageArray = array('message'=>$message,'name' =>'Connected','type'=>'on','id'=>0);
    $ACK = seal(json_encode($messageArray));
    return $ACK;
}

function connectionDisconnectACK($client_ip_address) {
    $message = 'Client :' . $client_ip_address.' disconnected';
    $messageArray = array('message'=>$message,'name' =>'Disconnected','type'=>'off','id'=>0);
    $ACK = seal(json_encode($messageArray));
    return $ACK;
}



function updateReadMessageStatus($message_id,$receiver_id){
  if($message_id && $receiver_id){

    // Check connection
    if (db_connection()->connect_error) {
        $status="Connection failed: " . db_connection()->connect_error;
        $messageArray = array('message'=>$status,'name' => 'Sql Connection Error','type'=>'off','id' => 0);
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
    }
    else
    {
       $updatesql = "Update chat SET read_status = '1' WHERE receiver=".$receiver_id." AND  id=".$message_id;
        if (db_connection()->query($updatesql) === TRUE) {
         $messageArray = array('message'=>"updated read status","message_id"=>$message_id,"receiver_id"=>$receiver_id,"success"=>1);
        $chatMessage = seal(json_encode($messageArray));
        //return $chatMessage;
           }else {        
            $message= "Error: " . $sql . "<br>" . db_connection()->error;
            $messageArray = array('message'=>"read status not update","message_id"=>$message_id,"receiver_id"=>$receiver_id,"success"=>0);
            $chatMessage = seal(json_encode($messageArray));
           // return $chatMessage;
        }
    }
}else{

            $messageArray = array('message'=>"message id or receiver_id null","success"=>0);
            $chatMessage = seal(json_encode($messageArray));
            return $chatMessage; 
}
db_connection()->close();
      
}
function createOneToOneChatBoxMessage($sender_id,$receiver_id, $message_type,$message, $timezone,$method) {

    // Check connection
    if (db_connection()->connect_error) {
        $status="Connection failed: " . db_connection()->connect_error;
        $messageArray = array('message'=>$status,'name' => 'Sql Connection Error','type'=>'off','id' => 0);
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
    }
    else
    {
        //date_default_timezone_set('Asia/Kolkata');
        $mytime = Carbon\Carbon::now('Asia/Kolkata')->toDateTimeString();
        
        $type =0;
        
        if(empty($message)){
        $message="";
        }else{
        // $message_text = $conn->real_escape_string($message);
        }
         if($sender_id){
            //$message = htmlspecialchars($message);
        $message_encode = json_encode($message);
        $message_text = db_connection()->real_escape_string($message_encode);
        if($message_type == "rose"){
            $message_encode = json_encode("Default rose message.");
            $message_text = db_connection()->real_escape_string($message_encode);
            $sql = "INSERT INTO chat (sender_id,receiver_id,message_type,message,group_id,read_status, created_at, updated_at) VALUES ($sender_id,'$receiver_id','$message_type','$message_text',0,0,'$mytime','$mytime')";
            // Send push notification
            $sql1 ="SELECT device_token FROM device_token WHERE user_id = $receiver_id ORDER BY id DESC";
            $result1 =  db_connection()->query($sql1);
            if($result1->num_rows > 0){
                $rows1 = $result1->fetch_row();
                $deviceToken = $rows1[0];
                if(!empty($deviceToken)){
                    $data = [];
                    $data['title'] = "You have received a Rose";
                    $data['desc'] = "Default message sent with rose.";
                    iOS($data,$deviceToken,$receiver_id,'roseReceived');
                }
            }
            
        }
        else {
            $sql = "INSERT INTO chat (sender_id,receiver_id,message_type,message,group_id,read_status, created_at, updated_at) VALUES ($sender_id,'$receiver_id','$message_type','$message_text',0,0,'$mytime','$mytime')";
            
            // Send push notification 
            $sql1 ="SELECT device_token FROM device_token  WHERE user_id = $receiver_id ORDER BY id DESC";
            $result1 =  db_connection()->query($sql1);
            if($result1->num_rows > 0){
                $rows1 = $result1->fetch_row();
                $deviceToken = $rows1[0];
                if(!empty($deviceToken)){
                    $data = [];
                    $data['title'] = "You have received a message";
                    $data['desc'] = "Message";
                    iOS($data,$deviceToken,$receiver_id,'simpleMessage');
                }
            }
            // end here
           
        }
        if (db_connection()->query($sql) === TRUE) {
        $last_id = db_connection()->insert_id;
        $messageArray = array('message'=>$message,'type'=>'chat','id' => $last_id,'user_id'=> $sender_id, "success"=>1);
        
        $sql="SELECT * FROM chat WHERE (sender_id=".$sender_id." AND  receiver_id=".$receiver_id.") OR (sender_id=".$receiver_id." AND  receiver_id=".$sender_id.") AND id='$last_id'";
        $result=  db_connection()->query($sql); 
        
        $rows = $result->fetch_array();
        if(!empty( $rows)){
            if($timezone){
            $default = date_default_timezone_get();
            $date = new DateTime();
            $date->format('Y-m-d H:i:s');
            $date->setTimezone(new DateTimeZone($timezone));
           
            // $messageArray['message_datetime']=  $date->format('Y-m-d H:i:s');
            $messageArray['message_datetime']=  $mytime;
          }else{
            $default = date_default_timezone_get();
            $date = new DateTime();
            // $messageArray['message_datetime']=  $date->format('Y-m-d H:i:s');
            $messageArray['message_datetime']=  $mytime;
          }
            $messageArray['sender_id']= (int)$rows['sender_id'];
            // Add user image in response 
            // $user_images_display_path = "http://127.0.0.1:8000\uploads\user_profile_images".'/'.$rows['sender_id'];
            $user_images_display_path = "https://introjuicer.iapplabz.co.in/uploads/user_profile_images".'/'.$rows['sender_id'];
            
            // $path = dirname(__FILE__,2)."/uploads/user_profile_images".'/'.$rows['sender_id'];
            $path = "/var/www/html/projects/introjuicer/public/uploads/user_profile_images".'/'.$rows['sender_id'];
            if (is_dir($path)) $imagePathArray = scandir($path);
            else $imagePathArray = [];
            if (!empty($imagePathArray)) {
                $messageArray['user_image'] = $user_images_display_path.'/'.$imagePathArray[2];
            }
            else{
                $messageArray['user_image']= " ";
            }
            // end here
            $messageArray['receiver_id']= (int)$rows['receiver_id'];
            $messageArray['read_status']= (int)$rows['read_status'];
           
            $userresult = db_connection()->query("SELECT * FROM users WHERE id=".$sender_id);  
            $userresults = $userresult->fetch_array();
            $messageArray['name']= $userresults['name'];
            $messageArray['email']= $userresults['email'];
            $messageArray['method']= $method;
            // $messageArray['profile_pic']= $userresults['profile_pic'];
            
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
        }
      }else {
            $message = "Error: " . $sql . "<br>" . db_connection()->error;
            $messageArray = array('message'=>$message,'name' => $name,'type'=>'chat','id' => $sender_id,"success"=>0);
            $chatMessage = seal(json_encode($messageArray));
            return $chatMessage;
        }
      }  
    }
    db_connection()->close();
}

function createGroupChatBoxMessage($sender_id,$receiver_id, $message_type,$message, $group_id,$timezone,$method) {

  
    if (db_connection()->connect_error) {
        $status="Connection failed: " . db_connection()->connect_error;
        $messageArray = array('message'=>$status,'name' => 'Sql Connection Error','type'=>'off','id' => 0);
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
    }
    else
    {
        //date_default_timezone_set('Asia/Kolkata');
        $mytime = Carbon\Carbon::now('Asia/Kolkata')->toDateTimeString();
        
        if(empty($message)){
        $message="";
        }else{
        $message_text = db_connection()->real_escape_string($message);
        }
         if($sender_id){
             // Send push notification 
            $sql1 ="SELECT user_id FROM group_users WHERE group_id = $group_id AND user_id != $sender_id";
            $result1=  db_connection()->query($sql1); 
            if($result1->num_rows > 0){
                while($row = mysqli_fetch_assoc($result1)){
                    $user_id = $row['user_id'];
                    $sql2 ="SELECT device_token FROM device_token WHERE user_id = $user_id ORDER BY id DESC";
                    $result2 = db_connection()->query($sql2); 
                    if($result2->num_rows > 0){
                        $rows2 = $result2->fetch_row();
                        $deviceToken = $rows2[0];
                        if(!empty($deviceToken)){
                            $data = [];
                            $data['title'] = "You have received a message in Group";
                            $data['desc'] = "Message";
                            iOS($data,$deviceToken,$group_id,'groupMessage');
                        }
                    }
                }
            }
            $message_encode = json_encode($message);
            $message_text = db_connection()->real_escape_string($message_encode);
        $sql = "INSERT INTO chat (sender_id,receiver_id,message_type,message,group_id,read_status, created_at, updated_at) VALUES ($sender_id,'$receiver_id','$message_type','$message_text','$group_id',0,'$mytime','$mytime')";
        if (db_connection()->query($sql) === TRUE) {
        $last_id = db_connection()->insert_id;
        $messageArray = array('message'=>htmlspecialchars($message),'type'=>'chat','id' => $last_id,'user_id'=> $sender_id, "success"=>1);
        
        $sql="SELECT * FROM chat WHERE (sender_id=".$sender_id." AND  receiver_id=".$receiver_id.") OR (sender_id=".$receiver_id." AND  receiver_id=".$sender_id.") AND id='$last_id'";
        $result=  db_connection()->query($sql); 
        
        $rows = $result->fetch_array();
        if(!empty( $rows)){
            if($timezone){
            $default = date_default_timezone_get();
            $date = new DateTime();
            $date->format('Y-m-d H:i:s');
            $date->setTimezone(new DateTimeZone($timezone));
           
            $messageArray['message_datetime']=  $mytime;
          }else{
            $default = date_default_timezone_get();
            $date = new DateTime();
            $messageArray['message_datetime']=  $mytime;
          }
            $messageArray['sender_id']= (int)$rows['sender_id'];
            // Send user_image in message
            $user_images_display_path = "https://introjuicer.iapplabz.co.in/uploads/user_profile_images".'/'.$rows['sender_id'];
            
            $path = "/var/www/html/projects/introjuicer/public/uploads/user_profile_images".'/'.$rows['sender_id'];
            if (is_dir($path)) $imagePathArray = scandir($path);
            else $imagePathArray = [];
            if (!empty($imagePathArray)) {
                $messageArray['user_image'] = $user_images_display_path.'/'.$imagePathArray[2];
            }
            else{
                $messageArray['user_image']= " ";
            }

            // end here
            //$messageArray['group_id']= (int)$rows['group_id'];
            $messageArray['group_id']= $group_id;
            $messageArray['read_status']= (int)$rows['read_status'];
           
            $userresult = db_connection()->query("SELECT * FROM users WHERE id=".$sender_id);  
            $userresults = $userresult->fetch_array();
            $messageArray['name']= $userresults['name'];
            $messageArray['email']= $userresults['email'];
            $messageArray['method']= $method;
            $sqlquery = "SELECT user_id from group_users where group_id = $group_id and  user_status = 1";
            $result1 =  db_connection()->query($sqlquery); 
            $group_members_ids = $result1->fetch_all(MYSQLI_ASSOC);
            foreach ($group_members_ids as $key => $value) {
                $ids[] = $value['user_id'];
            }
            $messageArray['group_members_ids']= $ids;
            // $messageArray['profile_pic']= $userresults['profile_pic'];

            
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
        }
      }else {      
            $message = "Error: " . $sql . "<br>" . db_connection()->error;
            $messageArray = array('message'=>$message,'name' => $name,'type'=>'chat','id' => $sender_id,"success"=>0);
            $chatMessage = seal(json_encode($messageArray));
            return $chatMessage;
        }
      }  
    }
    db_connection()->close();
}

function createMoneySectionChatBoxMessage($sender_id,$receiver_id, $message_type,$message, $group_id,$timezone,$method) {

    // Check connection
    if (db_connection()->connect_error) {
        $status="Connection failed: " . db_connection()->connect_error;
        $messageArray = array('message'=>$status,'name' => 'Sql Connection Error','type'=>'off','id' => 0);
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
    }
    else
    {
        //date_default_timezone_set('Asia/Kolkata');
        $mytime = Carbon\Carbon::now('Asia/Kolkata')->toDateTimeString();
        
        $type =0;
        // if(!empty($image_name) || $image_name!='' || $image_name != null)
        // {


          
        // $type = 1;
        
        // }else{
       
        // $image_name="";
        // }
        if(empty($message)){
        $message="";
        }else{
        $message_text = db_connection()->real_escape_string($message);
        }
         if($sender_id){
            
            // Send push notification  
                $sql1 ="SELECT device_token FROM device_token  WHERE user_id = $receiver_id ORDER BY id DESC";
                $result1 =  db_connection()->query($sql1);
                if($result1->num_rows > 0){
                    $rows1 = $result1->fetch_row();
                    $deviceToken = $rows1[0];
                    if(!empty($deviceToken)){
                        $data = [];
                        $data['title'] = "You have received a message";
                        $data['desc'] = "Message";
                        iOS($data,$deviceToken,$receiver_id,'moneyMessage');
                    }
                }
            // end here
            
            $message_encode = json_encode($message);
            $message_text = db_connection()->real_escape_string($message_encode);
        $sql = "INSERT INTO chat (sender_id,receiver_id,message_type,message,group_id,read_status,source, created_at, updated_at) VALUES ($sender_id,'$receiver_id','$message_type','$message_text',0,0,1,'$mytime','$mytime')";
        if (db_connection()->query($sql) === TRUE) {
        $last_id = db_connection()->insert_id;
        $messageArray = array('message'=>htmlspecialchars($message),'type'=>'chat','id' => $last_id,'user_id'=> $sender_id, "success"=>1);
        
        $sql="SELECT * FROM chat WHERE (sender_id=".$sender_id." AND  receiver_id=".$receiver_id.") OR (sender_id=".$receiver_id." AND  receiver_id=".$sender_id.") AND id='$last_id'";
        $result=  db_connection()->query($sql); 
        
        $rows = $result->fetch_array();
        if(!empty( $rows)){
            if($timezone){
            $default = date_default_timezone_get();
            $date = new DateTime();
            $date->format('Y-m-d H:i:s');
            $date->setTimezone(new DateTimeZone($timezone));
           
            $messageArray['message_datetime']=  $mytime;
          }else{
            $default = date_default_timezone_get();
            $date = new DateTime();
            $messageArray['message_datetime']=  $mytime;
          }
            $messageArray['sender_id']= (int)$rows['sender_id'];
            // Add user image here
            $user_images_display_path = "https://introjuicer.iapplabz.co.in/uploads/user_profile_images".'/'.$rows['sender_id'];
            
            $path = "/var/www/html/projects/introjuicer/public/uploads/user_profile_images".'/'.$rows['sender_id'];
            if (is_dir($path)) $imagePathArray = scandir($path);
            else $imagePathArray = [];
            if (!empty($imagePathArray)) {
                $messageArray['user_image'] = $user_images_display_path.'/'.$imagePathArray[2];
            }
            else{
                $messageArray['user_image']= " ";
            }
            // end here
            $messageArray['receiver_id']= (int)$rows['receiver_id'];
            //$messageArray['group_id']= (int)$rows['group_id'];
            $messageArray['group_id']= $group_id;
            $messageArray['read_status']= (int)$rows['read_status'];
           
            $userresult = db_connection()->query("SELECT * FROM users WHERE id=".$sender_id);  
            $userresults = $userresult->fetch_array();
            $messageArray['name']= $userresults['name'];
            $messageArray['email']= $userresults['email'];
            $messageArray['method']= $method;
            // $messageArray['profile_pic']= $userresults['profile_pic'];

            
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
        }
      }else {      
            $message = "Error: " . $sql . "<br>" . db_connection()->error;
            $messageArray = array('message'=>$message,'name' => $name,'type'=>'chat','id' => $sender_id,"success"=>0);
            $chatMessage = seal(json_encode($messageArray));
            return $chatMessage;
        }
      }  
    }
    db_connection()->close();
}