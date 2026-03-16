<?php
INCLUDE __DIR__.'/includes/conn.php';

if ($_POST["senha_jogador"] != $_POST["chkpassword"]) {
    session_start();
    $_SESSION["MnsErro"] = "As senhas não são iguais!";
    header('Location: form-cadastro.php');
    die();
}

try{
    //Criar a query para o insert
    $stmt=$conn->prepare("insert into jogador(nickname_jogador, email_jogador, senha_jogador) values(?,?,?);");
    //Passar o parâmetro dos valores
    $stmt->bindParam(1,$_POST['nickname_jogador']);
    $stmt->bindParam(2,$_POST['email_jogador']);
    $stmt->bindParam(3,$_POST['senha_jogador']);
    
    //Executando o insert
    $stmt->execute();
    header("Location: form-login.php");
    die();
}catch(PDOexception $e){
    echo "ERROR: ".$e->getMessage();
    echo $_POST['nickname_jogador'];
    echo $_POST['email_jogador'];
    echo $_POST['senha_jogador'];
}
?>

