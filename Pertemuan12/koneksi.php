<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "artikel_db";

$connection = mysqli_connect(
    $dbhost,
    $dbuser,
    $dbpass,
    $dbname
);

if (!$connection) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>