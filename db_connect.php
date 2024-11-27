<?php 

$conn = new mysqli('localhost', 'root', '', 'evaluation_db');

// Check for connection errors
if ($conn->connect_error) {
    die("Could not connect to MySQL: " . $conn->connect_error);
}
