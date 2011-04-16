<?php
session_start();
include_once('config.php');
include_once('includes/sip2.php');

$formaction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $formaction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$page='home';
//set page for inclusion below
if (!empty($_GET['page']) && file_exists('pages/'.$_GET['page'].'.php')){
	$page=$_GET['page'];
	$include='pages/'.$page.'.php';
//if there's no page listed go to the home page
} else {
	header('location:index.php?page='.$page);
}

//header
include_once('includes/header.php');

//sounds
echo '
<div style="display:none">
<a id="error" href="'.$error_sound.'"></a>
<a id="welcome" href="'.$welcome_sound.'"></a>
<a id="note" href="'.$note_sound.'"></a>
</div>';

//include page
include_once($include);

//footer
include_once('includes/footer.php');
?>