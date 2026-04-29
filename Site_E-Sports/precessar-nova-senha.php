<?php
require_once 'includes/conn.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $email = $_POST['email'];
    $hash = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

    $conn->prepare("UPDATE jogador SET senha_jogador = ? WHERE email_jogador = ?")->execute([$hash, $email]);
    $conn->prepare("UPDATE adm SET senha_adm = ? WHERE email_adm = ?")->execute([$hash, $email]);
    $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE token = ?")->execute([$token]);

    echo "<script>alert('Senha alterada!'); window.location.href='form-login.php';</script>";
}