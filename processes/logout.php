<?php
//reset the php session
session_start();
session_regenerate_id();
session_destroy();

//redirect
header("location:../index.php?page=home");
exit;
?>