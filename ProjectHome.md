# [PHP and WebSockets](http://mylittlehacks.appspot.com/phpwebsocket) #

Quick hack to implement [websockets](http://dev.w3.org/html5/websockets/) in php.
As of Feb/10 the only browsers that support websockets are Google Chrome and Webkit Nightlies. Get it from here http://www.google.com/chrome

[Browse the source code](http://code.google.com/p/phpwebsocket/source/browse/#svn/trunk/%20phpwebsocket)

### Changelog ###
  * 010.02.16 - Added basic demo and chatbot
  * 010.02.16 - Added users list to keep track of handshakes
  * 010.02.16 - Organized everything in a reusable websocket class
  * 010.02.16 - Minor cosmetic changes

### Client side ###
```
var host = "ws://localhost:12345/websocket/server.php";
try{
  socket = new WebSocket(host);
  log('WebSocket - status '+socket.readyState);
  socket.onopen    = function(msg){ log("Welcome - status "+this.readyState); };
  socket.onmessage = function(msg){ log("Received: "+msg.data); };
  socket.onclose   = function(msg){ log("Disconnected - status "+this.readyState); };
}
catch(ex){ log(ex); }
```
[View source code for the client](http://code.google.com/p/phpwebsocket/source/browse/trunk/%20phpwebsocket/client.html)

### Server side ###
```
log("Handshaking...");
list($resource,$host,$origin) = getheaders($buffer);
$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
           "Upgrade: WebSocket\r\n" .
           "Connection: Upgrade\r\n" .
           "WebSocket-Origin: " . $origin . "\r\n" .
           "WebSocket-Location: ws://" . $host . $resource . "\r\n" .
           "\r\n";
$handshake = true;
socket_write($socket,$upgrade.chr(0),strlen($upgrade.chr(0)));
```
[View source code for the server](http://code.google.com/p/phpwebsocket/source/browse/trunk/%20phpwebsocket/server.php)

### Steps to run the test: ###

  1. Save both files, client.php and server.php, in a folder in your local server running Apache and PHP.
  1. From the command line, run the server.php program to listen for socket connections.
  1. Open Google Chrome (dev build) and point to the client.php page
  1. Done, your browser now has a full-duplex channel with the server.
  1. Start sending commands to the server to get some responses.

2010 will be an interesting year.

## WebSockets for the masses! ##