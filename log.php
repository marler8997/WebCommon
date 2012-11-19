<?php
function client_warning($message) {
  $ip   = $_SERVER['REMOTE_ADDR'];
  $port = $_SERVER['REMOTE_PORT'];
  error_log("[warning] client '$ip:$port': $message");
}
function warning($message) {
  error_log("[warning] $message");
}
function error_log_with_ref($message) {
  $ref = rand();
  error_log("[log_reference_number=$ref] $message");
  return $ref;
}
// Error Log with code location
function code_error($file, $line, $message) {
  error_log("$file line $line: $message");
}
function code_error_with_ref($file, $line, $message) {
  $ref = rand();
  error_log("[log_reference_number=$ref] $file line $line: $message");
  return $ref;
}
?>