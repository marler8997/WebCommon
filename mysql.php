<?php
require_once "log.php";

// The following must be defined
// MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER
if(!defined('MYSQL_HOST'))     throw new Exception('Missing constant MYSQL_HOST');
if(!defined('MYSQL_DATABASE')) throw new Exception('Missing constant MYSQL_DATABASE');
if(!defined('MYSQL_USER'))     throw new Exception('Missing constant MYSQL_USER');

class MysqlException extends RuntimeException {
  public $logRefNum;
  public function __construct($logRefNum) {
    parent::__construct();
    $this->logRefNum = $logRefNum;
  }
}
class MysqlQueryOneException extends MysqlException {
  public $recordCount;
  public function __construct($logRefNum, $recordCount) {
    parent::__construct($logRefNum);
    $this->recordCount = $recordCount;
  }  
}

//
// call: MysqlInit();
//       throws MysqlException (error is logged in the function)
//       $mysqlException->logRegNum is the log reference number
//
function MysqlInit() {
  $mysql = mysql_connect(MYSQL_HOST, MYSQL_USER);
  if($mysql === FALSE) {
    $logRefNum = error_log_with_ref('mysql_connect failed: '. mysql_error());
    throw new MysqlException($logRefNum);
  }
  if(!mysql_select_db(MYSQL_DATABASE)) {
    $logRefNum = error_log_with_ref('mysql_select_db(\'sleep\') failed: '. mysql_error());
    mysql_close($mysql);
    throw new MysqlException($logRefNum);
  }
}


// call  : MysqlExec('query...');
// throws: MysqlException
function MysqlExec($query) {
  $result = mysql_query($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_query('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
}

// Use when inserting a row with an auto_increment column
// call  : $id = MysqlInsertID('query...');
// throws: MysqlException
function MysqlInsertID($query) {
  $result = mysql_query($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_query('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  $id = mysql_insert_id();
  if($id === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_insert_id('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  if($id === 0) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_insert_id('$query') returned 0 (nothing was inserted)");
    throw new MysqlException($logRefNum);
  }
  return $id;
}


// call  : list($result,$count) = MysqlRows('query...');
// throws: MysqlException
function MysqlRows($query) {
  $result = mysql_query($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_query('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  $count = mysql_num_rows($result);
  if($count === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_num_rows('$result') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  return array($result,$count);
}


// call  : $result = MysqlRow('query');
//         if($result === FALSE) {row was not found}
//         else {$result is the array of columns for the row}
// throws: MysqlException, MysqlQueryOneException
function MysqlRow($query) {
  $result = mysql_query($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_query('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  $count = mysql_num_rows($result);
  if($count === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_num_rows after query '$query' failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  if($count == 1) return mysql_fetch_row($result);
  if($count == 0) return FALSE;

  $logRefNum = code_error_with_ref(__FILE__,__LINE__,"expected 1 row from '$query' but got $count");
  throw new MysqlQueryOneException($logRefNum, $count);
}

// If query does not return exactly one entry then an exception is thrown.
// call  : $result = MysqlRow('query');
// throws: MysqlException, MysqlQueryOneException
function MysqlExactlyOneRow($query) {
  $result = MysqlRow($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"no rows found from '$query'");
    throw new MysqlQueryOneException($logRefNum, 0);
  }
  return $result;
}


// Use to query for one value of one row.
// call  : $value = MysqlValue($query);
// throws: MysqlException, MysqlQueryOneException
function MysqlValue($query) {
  $result = MysqlRow($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"no rows found from '$query'");
    throw new MysqlQueryOneException($logRefNum, 0);
  }
  return $result[0];
}

function MysqlArrayToWhere($conditions) {
  $count = count($conditions);
  if($count <= 0) {return '';}
  else {return ' WHERE '.implode(' AND ',$conditions);}
}

// call: $count = MysqlCount($table,$conditions);
function MysqlCount($table, $conditions = NULL)
{
  if($conditions != NULl) {
    $conditions = " WHERE $conditions";
  }

  // Check Email/Regcode combination
  $result = MysqlQueryOne("SELECT Count(*) FROM $table $conditions;");
  if($result === 0) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"MysqlRecordCount($table, $conditions) expected to have 1 row but have ".$count);
    throw new MysqlException($logRefNum);
  }
  return $result[0];
}

// returns error if length of $keys is invalid
function MysqlPrintResultAsJsonObjects($result, $keys)
{
  echo '[';
  $atFirst = TRUE;
  while(TRUE) {
    $row = mysql_fetch_row($result);
    if(!$row) break;
    if($atFirst) { $atFirst = FALSE; } else { echo ','; }
    echo json_encode(array_combine($keys,$row));
  }
  echo ']';
}
function MysqlPrintResultAsJsonArrays($result)
{
  echo '[';
  $row = mysql_fetch_row($result);
  if($row) {
    echo json_encode($row);
    while(TRUE) {
      $row = mysql_fetch_row($result);
      if(!$row) break;
      echo ',';
      echo json_encode($row);
    }
  }
  echo ']';
}
?>