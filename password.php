<?php

function passwd($password, $salt)
{
  $password .= $salt;
  for($i = 0; $i < 100; $i++) {
    $password = sha1($password);
  }
  return $password;
}

?>
