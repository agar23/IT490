<?php
require_once('Ini/path.inc');
require_once('Ini/get_host_info.inc');
require_once('Ini/rabbitMQLib.inc');

//create the function to check if message is critical
function ifCrit($msg)
{
  $msg = strtolower($msg);
  if (preg_match('/error/',$msg) | preg_match('/critical/',$msg)
      | preg_match('/failed/',$msg) | preg_match('/successful/',$msg))
  {
    return True;
  }
  return false;
}

function Logger($status,$message,$path,$user){
  if($status == 1){
    LogMsg($message." successful",$path,$user);
  }
  else{
    LogMsg($message." failed",$path,$user);
  }
}

// Create the error logging function
function LogMsg($e,$extFile,$user)
{
  $file = basename($_SERVER['PHP_SELF']);
  $string = trim(preg_replace('/\s+/', ' ', $extFile));

  $logmsg = array();
  $logmsg['date'] = date("Y-m-d");
  $logmsg['day'] = date("l");
  $logmsg['time'] = date("h:i:sa");
  $logmsg['user'] = $user;
  $logmsg['text'] = $e;
  $logmsg['file'] = $string;

  //log the message
  $msg = implode(" - ",$logmsg);
  //check if msg is critical
  if (ifCrit($msg))
  {
    //send to log server
    $client = new rabbitMQClient("logRabbitMQ.ini","testServer");
    $client->publish($msg);
  }
  //log the message
  error_log($msg.PHP_EOL,3,"../logs/logfile.log");
}

function LogServerMsg($e)
{
  error_log($e.PHP_EOL,3,"../logs/logfile.log");
}
?>
