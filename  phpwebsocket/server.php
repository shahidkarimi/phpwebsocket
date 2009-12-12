#!/php -q
<?php
/* 

Run from command line:
> php -q server.php

*/

session_start();
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

echo "Initiating...\n";
$address = 'localhost';
$port    = 12345;
$maxconn = 999;
$uselog  = true;
   
if(($master=socket_create(AF_INET,SOCK_STREAM,SOL_TCP))<0){
  die("socket_create() failed, reason: ".socket_strerror($master));
}
socket_set_option($master,SOL_SOCKET,SO_REUSEADDR,1);
if(($ret=socket_bind($master,$address,$port))<0){
  die("socket_bind() failed, reason: ".socket_strerror($ret));
}
if(($ret=socket_listen($master,5))<0){
  die("socket_listen() failed, reason: ".socket_strerror($ret));
}

echo "Server Started : ".date('Y-m-d H:i:s')."\n";
echo "Max connections: ".$maxconn."\n";
echo "Master socket  : ".$master."\n";
echo "Listening on   : ".$address." port ".$port."\n";

$users = array();
$allsockets = array($master);
$handshake  = false;

while(true){
  $changed_sockets = $allsockets;
  $num_sockets = socket_select($changed_sockets,$write=NULL,$except=NULL,NULL);
  foreach($changed_sockets as $socket){
    console();
    if ($socket==$master) {
      if(($client=socket_accept($master))<0) {
        console("socket_accept() failed: reason: ".socket_strerror(socket_last_error($client)));
        continue;
      }
      else{
        array_push($allsockets,$client);
        console($client." CONNECTED!");
      }
    }
    else{
      $bytes = @socket_recv($socket,$buffer,2048,0);
      if($bytes==0){ disconnected($socket); }
      else{
        
         /* TODO: store handshake per socket */
        if(!$handshake){
          console("\nRequesting handshake...");
          console($buffer);
          /*        
            GET {resource} HTTP/1.1
            Upgrade: WebSocket
            Connection: Upgrade
            Host: {host}
            Origin: {origin}
            \r\n
          */
          list($resource,$host,$origin) = getheaders($buffer);
          //$resource = "/websocket/server.php";
          //$host     = "localhost:12345";
          //$origin   = "http://localhost";
          console("Handshaking...");
          $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                      "Upgrade: WebSocket\r\n" .
                      "Connection: Upgrade\r\n" .
                      "WebSocket-Origin: " . $origin . "\r\n" .
                      "WebSocket-Location: ws://" . $host . $resource . "\r\n" .
                      "\r\n";
          $handshake = true;
          socket_write($client,$upgrade.chr(0),strlen($upgrade.chr(0)));
          console($upgrade);
          console("Done handshaking...");
        }
        else{
          console("<".$buffer);
          $action = substr($buffer,1,$bytes-2); // remove chr(0) and chr(255)
          switch($action){
            case "hello" : send($socket,"hello human"); break;
            case "hi"    : send($socket,"zup human"); break;
            case "name"  : send($socket,"my name is Multivac, silly I know"); break;
            case "age"   : send($socket,"I am older than time itself"); break;
            case "date"  : send($socket,"Today is ".date("Y.m.d")); break;
            case "time"  : send($socket,"Server time is ".date("H:i:s")); break;
            case "thanks": send($socket,"you're welcome"); break;
            case "bye"   : send($socket,"bye"); break;
            default      : send($socket,$action." not understood"); break;
          }
        }
      }
    }
  }
}

//---------------------------------------------------------------
function wrap($msg){ return chr(0).$msg.chr(255); }

function send($client,$msg){ 
  console("> ".$msg);
  $msg = wrap($msg);
  socket_write($client,$msg,strlen($msg));
} 

function disconnected($socket){
  global $allsockets;
  $index = array_search($socket, $allsockets);
  if($index>=0){ unset($allsockets[$index]); }
  socket_close($socket);
  console($socket." disconnected!");
}

function console($msg=""){
  global $uselog;
  if($uselog){ echo $msg."\n"; }
}

function getheaders($req){
  $req  = substr($req,4); /* RegEx kill babies */
  $res  = substr($req,0,strpos($req," HTTP"));
  $req  = substr($req,strpos($req,"Host:")+6);
  $host = substr($req,0,strpos($req,"\r\n"));
  $req  = substr($req,strpos($req,"Origin:")+8);
  $ori  = substr($req,0,strpos($req,"\r\n"));
  return array($res,$host,$ori);
}
?>