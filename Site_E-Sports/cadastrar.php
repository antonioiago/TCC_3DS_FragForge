<?php
INCLUDE __DIR__.'/includes/conn.php';

if ($_POST["password"] != $_POST["chkpassword"]) {
    session_start();
    $_SESSION["MnsErro"] = "As senhas não são iguais!";
    header('Location: cadastro.php');
    die();
}

?>

