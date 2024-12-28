<?php

$servername = "localhost";
$username = "u848595465_motuG";
$password = "u848595465_MotuG";
$dbname = "u848595465_motuG";

// Create connection  
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

