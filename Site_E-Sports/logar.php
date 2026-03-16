<?php
INCLUDE __DIR__.'/includes/conn.php';

try{
    if (str_contains($_POST['nome-email'], "@")) {
        $search = "email";
    }
    else {
        $search = "nickname";
    }
    $cmd = $conn->prepare("SELECT id_jogador, senha_jogador FROM jogador WHERE ".$search."_jogador = ?;");
    $cmd->bindParam(1, $_POST['nome-email']);
    $cmd->execute();

    session_start();
    if ($cmd->rowCount() != 1) {
        $_SESSION['MnsErro'] = "Não foi possivel localizar a sua conta.";
        header('Location:form-login.php');
        die();
    }
    
    $data = $cmd->fetch(PDO::FETCH_OBJ);

    if (!($data->senha_jogador == $_POST['senha'])) {
        $_SESSION['MnsErro'] = "Senha incorreta. Por favor, tente novamente.";
        header('Location:form-login.php');
        die();
    }

    $_SESSION['jogador']['id'] = $data->id_jogador;
    header('Location:index.php');
    die();
} 
catch (PDOException $erro) {
    echo 'Erro encontrado:'.$erro->getMessage();
}


?>