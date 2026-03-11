<?php
INCLUDE __DIR__.'/includes/conn.php';

if ($_POST["senha_jogador"] != $_POST["chkpassword"]) {
    session_start();
    $_SESSION["MnsErro"] = "As senhas não são iguais!";
    header('Location: cadastrar.php');
    die();
}

 try{
        //Verificar se as variáveis $_POST existem
        if(!isset($_POST['nickname_jogador']) || !isset($_POST['email_jogador']) || !isset($_POST['senha_jogador'])){
            header("Location: form-cadastro.php");
            die();
        }
        //Passar os dados para variáveis
        $nome = $_POST['nickname_jogador'];
        $email = $_POST['email_jogador'];
        $senha = $_POST['senha_jogador'];
        //Criar a query para o insert
        $stmt=$conn->prepare("insert into jogador(nickname_jogador, email_jogador, senha_jogador)
        values(?,?,?);");
        //Passar o parâmetro dos valores
        $stmt->bindParam(1,$nome);
        $stmt->bindParam(2,$email);
        $stmt->bindParam(3,$senha);
       
        //Executando o insert
        $stmt->execute();
        header("Location: form-login.php");
        die();
    }catch(PDOexception $e){
        echo "ERROR: ".$e->getMessage();
    }
?>

