<?php
session_start();
include_once 'includes/functions.php'; 
session_unset();
session_destroy();
redirectToLogin();
?>