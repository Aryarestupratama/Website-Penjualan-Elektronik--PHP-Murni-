<?php
session_start();
$_SESSION['logout_message'] = 'Anda telah logout, silahkan login kembali!';

session_destroy();
header("Location: login.php");
exit;
?>