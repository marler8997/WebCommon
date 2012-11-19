<?php

require_once('util.php');
require_once('mysql.php');

//
// This can be used to keep track of client sessions and logins
// Requirements:
//   1. The tables defined in sessionTables must be present in the mysql database.
//   2. Constant MYSQL_USER_TABLE must be a mysql table with the column:
//      1. Uid          INT UNSIGNED
//

if(!defined('MYSQL_USER_TABLE'))
   throw new Exception('Constant MYSQL_USER_TABLE is undefined');

// Check that integers are 64 bit (to store IP address and Port)
if(PHP_INT_MAX < 9223372036854775807)
   throw new Exception('PHP_INT_MAX must be at least 9223372036854775807 but is '.PHP_INT_MAX);

// Tracks requests from the current ip/port
// call: MysqlIPAndPortSession();
//       throws RuntimeException, MysqlException (or MysqlQueryOneException) on error
function MysqlIPAndPortSession()
{
  list($ip,$port,$ipString) = ClientEndPoint();
  $ipAndPort = (0xFFFFFFFF0000 & ($ip << 16)) | (0xFFFF & $port);

  $result = MysqlValue("SELECT Count(IPAndPort) FROM IPAndPortSessions WHERE IPAndPort=$ipAndPort");

  if($result < 1) {
    MysqlExec("INSERT INTO IPAndPortSessions VALUES ($ipAndPort,0,NOW(),NOW(),1);");
  } else {
    // update last request time
    MysqlExec("UPDATE IPAndPortSessions SET LastRequest=NOW(),RequestCount=RequestCount+1 WHERE IPAndPort=$ipAndPort;");
  }
}

// call: list($logins) = MysqlIPAndPortLoginAttemp();
//       throws RuntimeException, MysqlException (or MysqlQueryOneException) on error
function MysqlIPAndPortLoginAttempt()
{
  list($ip,$port,$ipString) = ClientEndPoint();
  $ipAndPort = (0xFFFFFFFF0000 & ($ip << 16)) | (0xFFFF & $port);

  $result = MysqlRow("SELECT LoginCount FROM IPAndPortSessions WHERE IPAndPort=$ipAndPort");

  if($result === FALSE) {
    MysqlExec("INSERT INTO IPAndPortSessions VALUES ($ipAndPort,0,NOW(),NOW(),1);");
    return MysqlValue("SELECT LoginCount FROM IPAndPortSessions WHERE IPAndPort=$ipAndPort");
  }

  // update last request time and login attempt
  MysqlExec("UPDATE IPAndPortSessions SET LoginCount=LoginCount=1,LastRequest=NOW(),RequestCount=RequestCount+1 WHERE IPAndPort=$ipAndPort;");
  return $result[0] + 1;
}




function GenerateSid($ip,$port) {
  $randomString = '';
  for($i = 0; $i < 5; $i++) {
    $randomString .= rand();
  }
  return sha1($ip.$port.$randomString);
}


function EndCookieSession() {
  if(isset($_COOKIE['Sid'])) {
    setcookie('Sid','',1); // Unset the cookie
  }
}

// throws MysqlException on error
function MysqlNewCookieSession($uid) {
  // check if user already has a session
  $result = MysqlRow("SELECT Sid,GenTime FROM Sessions WHERE Uid=$uid;");
  if($result === FALSE) {

    list($ip,$port,$ipString) = ClientEndPoint();
    $sidHex = GenerateSid($ipString,$port);

    MysqlExec("INSERT INTO Sessions VALUES(x'$sid',$uid,$ip,$port,NOW(),NOW(),0);");

  } else {
    list($sid,$genTime) = $result;
    // TODO: if genTime is too far in the past, then create a new sid
    //       (to help prevent hackers from using sids that have been found)
    // TODO: i can also check if the current ip is the same as the genip
    MysqlExec("UPDATE Sessions SET LastRequest=NOW(),RequestCount=RequestCount+1 WHERE Uid=$uid;");
  }
  setcookie("Sid",$sid);
}

// call: $uid = MysqlCookieSession();
//       if($uid === FALSE) // no cookie session
function MysqlCookieSession() {
  if(!isset($_COOKIE["Sid"])) return FALSE;

  $sidBase64 = $_COOKIE["Sid"];
  $sid = base64_decode($sidBase64);
  if($sid === FALSE) {
    client_warning("Cookie 'Sid' was not valid base64: '%sidBase64'");
    setcookie('Sid','',1); // Unset the cookie
    return FALSE;
  }

  $sidHex = bin2hex($sid);
  $result = MysqlRow("SELECT Uid,GenIP,GenPort,GenTime FROM Sessions WHERE Sid=x'$sidHex';");
  if($result === FALSE) {
    // session must have expired
    setcookie('Sid','',1); // Unset the cookie
    return FALSE;
  }

  // update the session
  MysqlExec("UPDATE Sessions SET LastRequest=NOW(),RequestCount=RequestCount+1 WHERE Uid=$uid;");

  list($uid,$genIP,$genPort,$genTime) = $result;
  return $uid;
}

?>