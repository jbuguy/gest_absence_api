<?php
$host = "localhost";
$db_name = "gest_absence";
$username = "pmauser";
$password = "yourpassword";

$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
