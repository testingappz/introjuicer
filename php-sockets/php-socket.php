<?php
require_once '/var/www/html/introjuicer/vendor/autoload.php';
use DateTime;
use DateTimeZone;
define("SECURE",true);
define("PORT","8001");
if (SECURE== true){
    define('HOST_NAME',"localhost/introjuicer/public");
}
else
{
    define('HOST_NAME',"localhost/introjuicer/public");
//    define('HOST_NAME',"192.168.1.109");
}

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

        }else{
            $chat_box_message = createChatBoxMessage($messageObj->message, $messageObj->name,$messageObj->id, $messageObj->image, $messageObj->file_is,$messageObj->receiver_id,$messageObj->timezone);
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

    $servername = "localhost";
    $password = "welcome";
    $username = "root";
    $dbname = "introjuicer";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        $status="Connection failed: " . $conn->connect_error;
        $messageArray = array('message'=>$status,'name' => 'Sql Connection Error','type'=>'off','id' => 0);
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
    }
    else
    {
       $updatesql = "Update chat_messages SET read_status = '1' WHERE receiver=".$receiver_id." AND  id=".$message_id;
        if ($conn->query($updatesql) === TRUE) {
         $messageArray = array('message'=>"updated read status","message_id"=>$message_id,"receiver_id"=>$receiver_id,"success"=>1);
        $chatMessage = seal(json_encode($messageArray));
        //return $chatMessage;
           }else {        
            $message= "Error: " . $sql . "<br>" . $conn->error;
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
 $conn->close();
      
}
function createChatBoxMessage($message,$name, $id, $image_name, $file_is,$receiver_id,$timezone) {

    $servername = "localhost";
    $password = "welcome";
    $username = "root";
    $dbname = "introjuicer";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        $status="Connection failed: " . $conn->connect_error;
        $messageArray = array('message'=>$status,'name' => 'Sql Connection Error','type'=>'off','id' => 0);
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
    }
    else
    {
        //date_default_timezone_set('Asia/Kolkata');
        $mytime = Carbon\Carbon::now('Asia/Kolkata')->toDateTimeString();
        
        $type =0;
        if(!empty($image_name) || $image_name!='' || $image_name != null)
        {


          
        $type = 1;
        
        }else{
       
        $image_name="";
        }
        if(empty($message)){
        $message="";
        }else{
        $message_text = $conn->real_escape_string($message);
        }
         if($id){
        
        $sql = "INSERT INTO chat_messages (sender,message,type,additional_detail, created_at, updated_at,receiver) VALUES ($id,'$message_text',$type,'$image_name','$mytime','$mytime','$receiver_id')";
        if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
        $messageArray = array('message'=>$message,'name' => $name,'type'=>'chat','id' => $last_id,'user_id'=> $id, 'image'=> $image_name,'m_type'=> $type,'file_is'=> $file_is,"success"=>1);
        
        $sql="SELECT * FROM chat_messages WHERE (sender=".$id." AND  receiver=".$receiver_id.") OR (sender=".$receiver_id." AND  receiver=".$id.") AND id='$last_id'";
        $result=  $conn->query($sql); 
        
        $rows = $result->fetch_array();
        if(!empty( $rows)){
            if($timezone){
            $default = date_default_timezone_get();
            $date = new DateTime();
            $date->format('Y-m-d H:i:s');
            $date->setTimezone(new DateTimeZone($timezone));
           
            $messageArray['message_datetime']=  $date->format('Y-m-d H:i:s');
          }else{
            $default = date_default_timezone_get();
            $date = new DateTime();
            $messageArray['message_datetime']=  $date->format('Y-m-d H:i:s');
          }
            $messageArray['sender_id']= (int)$rows['sender'];
            $messageArray['receiver_id']= (int)$rows['receiver'];
            $messageArray['read_status']= (int)$rows['read_status'];
           
            $userresult = $conn->query("SELECT * FROM users WHERE id=".$id);  
            $userresults = $userresult->fetch_array();
            $messageArray['name']= $userresults['user_name'];
            $messageArray['email']= $userresults['email'];
            $messageArray['profile_pic']= $userresults['profile_pic'];

            
        $chatMessage = seal(json_encode($messageArray));
        return $chatMessage;
        }
      }else {        
            $message= "Error: " . $sql . "<br>" . $conn->error;
            $messageArray = array('message'=>$message,'name' => $name,'type'=>'chat','id' => $id,"success"=>0);
            $chatMessage = seal(json_encode($messageArray));
            return $chatMessage;
        }
      }  
    }
    $conn->close();
}
