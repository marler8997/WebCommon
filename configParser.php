<?php


// returns string (error message) on error,
// otherwise returns an array
function parseConfigFile($filename)
{
  $fp = fopen($filename, 'r');
  if($fp === FALSE) return 'Failed to open file';

  $config = array();

  while(TRUE) {
    $line = fgets($fp);
    if($line === FALSE) {
      fclose($fp);
      return $config;
    }

    $line = trim($line);
    if(empty($line)) continue;

    if($line[0] == '#' || $line[0] == ';') continue;

    if(preg_match('/^(\S*)\s*(.*)$/', $line, $matches)) {
      $config[$matches[1]] = $matches[2];
    } else {
      fclose($fp);
      return "Line '$line' was invalid";
    }
  }
}




//$config = parseConfigFile('test/testConfigFile1');
//print_r($config);



?>