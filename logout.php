<?php
require_once 'config.php';

unset($_SESSION['admin']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

session_destroy();

header('Location: login.php?logged_out=1');
exit;
?>