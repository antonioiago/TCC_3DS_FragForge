<?php
include __DIR__.'/includes/conn.php';

session_start();

// validar senha
if ($_POST["senha_jogador"] != $_POST["chkpassword"]) {
    $_SESSION["MnsErro"] = "As senhas não são iguais!";
    header('Location: form-cadastro.php');
    die();
}

try {
    $stmt = $conn->prepare("
        INSERT INTO jogador 
        (nickname_jogador, email_jogador, senha_jogador, codigo_battlenet, id_funcao, id_patente, id_equipe) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bindParam(1, $_POST['nickname_jogador']);
    $stmt->bindParam(2, $_POST['email_jogador']);
    $stmt->bindParam(3, $_POST['senha_jogador']);
    $stmt->bindParam(4, $_POST['codigo_battlenet']);
    $stmt->bindParam(5, $_POST['id_funcao']);
    $stmt->bindParam(6, $_POST['id_patente']);
    $stmt->bindParam(7, $_POST['id_equipe']);

    $stmt->execute();

    header("Location: form-login.php");
    die();

} catch (PDOException $e) {
    echo "ERRO: " . $e->getMessage();
}
?>