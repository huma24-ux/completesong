<?php
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "sound_db");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
