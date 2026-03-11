<?php

include __DIR__.'/includes/conn.php';

session_start();

if($_POST["password"] != $_POST["chkpassword"]){

$_SESSION["MsgErro"] = "As senhas não são iguais";

header("Location: forms/form-cadastrar.php");

exit;

}

$email = $_POST["email"];
$senha = password_hash($_POST["password"], PASSWORD_DEFAULT);
$user = $_POST["nameuser"];

$sql = "INSERT INTO usuarios (email, senha, usuario)
VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->execute([$email,$senha,$user]);

header("Location: forms/form-login.php");