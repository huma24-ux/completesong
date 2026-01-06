<?php
session_start();

// Sab session variables hatao
session_unset();

// Session destroy karo
session_destroy();

// Homepage par redirect karo
header("Location: index.php");
exit();
?>
