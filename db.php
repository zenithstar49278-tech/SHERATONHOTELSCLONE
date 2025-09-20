<?php
// File: db.php
$servername = "localhost";
$username = "ubpkik01jujna";
$password = "f0ahnf2qsque";
$dbname = "dbgvo0mimzlwt8";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
