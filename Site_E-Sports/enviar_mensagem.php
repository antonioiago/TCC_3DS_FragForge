<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "fragforge");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_SESSION['nickname_jogador'] ?? 'Anonimo';
    $mensagem = $conn->real_escape_string($_POST['mensagem']);

    if (!empty($mensagem)) {
        $conn->query("INSERT INTO mensagens (nickname, mensagem) VALUES ('$nickname', '$mensagem')");
    }
}
?>