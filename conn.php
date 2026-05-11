<?php
$conn = mysqli_connect("localhost", "root", "", "galeriseramikmbpg");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}