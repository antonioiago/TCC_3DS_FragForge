<?php
session_start();
include __DIR__.'/includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $login = $_POST['nome-email'];
        $search = str_contains($login, "@") ? "email" : "nickname";

        $cmd = $conn->prepare("SELECT id_jogador, senha_jogador FROM jogador WHERE {$search}_jogador = ?");
        $cmd->execute([$login]);
        $data = $cmd->fetch(PDO::FETCH_OBJ);

        if ($data && $data->senha_jogador === $_POST['senha']) {
            $_SESSION['jogador']['id'] = $data->id_jogador;
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['MnsErro'] = "Usuário ou senha inválidos.";
            header('Location: form-login.php');
            exit();
        }
    } catch (PDOException $erro) {
        die("Erro no banco: " . $erro->getMessage());
    }
}