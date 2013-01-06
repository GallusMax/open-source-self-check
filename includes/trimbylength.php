<?php
// Trim by length (by FELIXONE.it)
function TrimByLength($str, $len, $word) {
  $end = "";
  if (strlen($str) > $len) $end = "...";
  $str = substr($str, 0, $len);
  if ($word) $str = substr($str,0,strrpos($str," ")+0);
  return $str.$end;
}
?>