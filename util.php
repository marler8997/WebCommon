<?php
function ValidEmail($email)
{
  return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$", $email);
}
function SplitEmails($emails) {
  return preg_split('/[\\s,;:]+/', $emails, -1, PREG_SPLIT_NO_EMPTY);
}
function ValidUserName($userName)
{
  return eregi("^[a-zA-Z0-9]+$", $userName);
}
function ValidPersonName($personName)
{
  return eregi("^[-a-zA-Z]+$", $personName);
}
function ValidSqlColumn($column)
{
  return eregi("^[a-zA-Z][a-zA-Z0-9_]*$", $column);
}
function ValidFloat($str)
{
  return eregi("^-?[0-9]*(\.[0-9]*)?$", $str);
}
function ValidScriptName($scriptName)
{
  return eregi('^[a-zA-Z][-a-zA-Z0-9_ ]*$', $scriptName);
}



$clientIPString;
$clientIP = FALSE;

$clientPortString;
$clientPort = FALSE;

// call: list($ip,$port,$ipString) = ClientEndPoint();
function ClientEndPoint() {
  global $clientIPString,$clientIP,$clientPortString,$clientPort;
  if($clientIP === FALSE) {
    if(!isset($_SERVER['REMOTE_ADDR']))
       throw new Exception("Missing \$_SERVER['REMOTE_ADDR']");

    $clientIPString = $_SERVER['REMOTE_ADDR'];
    $clientIP = ip2long($clientIPString);

    if($clientIP === FALSE)
      throw new Exception("The \$_SERVER['REMOTE_ADDR'] variable should be an ip address but it is '$clientIPString'");
  }
  if($clientPort === FALSE) {
    if(!isset($_SERVER['REMOTE_PORT']))
       throw new Exception("Missing \$_SERVER['REMOTE_PORT']");

    $clientPortString = $_SERVER['REMOTE_PORT'];
    $clientPort = intval($clientPortString);

    if($clientPort === FALSE)
      throw new Exception("The \$_SERVER['REMOTE_PORT'] variable should be a port but it is '$clientPortString'");
  }  
  return array($clientIP,$clientPort,$clientIPString);
}

function uploadErrorString($err) {
  $upload_errors = array(
    "No errors",
    "File size larger than upload_max_filesize",
    "File size larger than MAX_FILE_SIZE directive",
    "Partial upload",
    "No file",
    "No temporary directory",
    "Can't write to disk",
    "File uploaded stopped by extension",
    "File is empty"  
  );

  if(!isset($err)) {
    return "No file";
  }
  error_log('err='.$err);
  if($err >= count($upload_errors)) {
    return "Unknown File error";
  }
  return $upload_errors[$err];
}
?>