<?php

include __DIR__.'/includes/conn.php';

session_start();

$email = $_POST["email"];
$senha = $_POST["senha"];

$sql = "SELECT * FROM usuarios WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch();

if(!$user){

header("Location: forms/form-login.php");
exit;

}

if(!password_verify($senha,$user["senha"])){

header("Location: forms/form-login.php");
exit;

}

$_SESSION["usuario"] = $user;

header("Location: index.php");