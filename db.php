<?php
$conn = new mysqli("localhost", "root", "", "kdrama_library");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>