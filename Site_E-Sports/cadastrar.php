<?php
session_start(); //
INCLUDE __DIR__.'/includes/conn.php'; //

if ($_POST["senha_jogador"] != $_POST["chkpassword"]) { //
    $_SESSION["MnsErro"] = "As senhas não são iguais!"; //
    header('Location: form-cadastro.php'); //
    die(); //
}

try {
    $stmt = $conn->prepare("insert into jogador(nickname_jogador, email_jogador, senha_jogador, codigo_battlenet) values(?,?,?, ?);"); //
    
    $stmt->bindParam(1, $_POST['nickname_jogador']); //
    $stmt->bindParam(2, $_POST['email_jogador']); //
    $stmt->bindParam(3, $_POST['senha_jogador']); //
    $stmt->bindParam(4, $_POST['codigo_battlenet']); //
    
    $stmt->execute(); //
    header("Location: form-login.php"); //
    die(); //

} catch(PDOexception $e) {
    // Em vez de echo, usamos a sessão para feedback seguro
    $_SESSION["MnsErro"] = "Erro ao cadastrar. O Nickname ou Email já podem estar em uso.";
    header('Location: form-cadastro.php');
    die();
}
?>