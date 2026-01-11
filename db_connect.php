<?php
$host = "localhost";
$username = "root";
$password = "Bero@2005"; 
$database = "emart_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
