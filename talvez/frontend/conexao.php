<?php
$conn = new mysqli("localhost", "root", "", "portal_vagas");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

session_start();
?>