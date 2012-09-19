<?php

$_SESSION['user_id'] = '';
session_destroy();
include_once($templates . 'logout.tpl.php');
?>