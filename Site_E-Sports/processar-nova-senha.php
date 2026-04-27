<?php
require_once 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $email = $_POST['email'];
    $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

    try {
        $conn->beginTransaction();

        // Atualiza Jogador
        $u1 = $conn->prepare("UPDATE jogador SET senha_jogador = ? WHERE email_jogador = ?");
        $u1->execute([$nova_senha, $email]);

        // Atualiza ADM
        $u2 = $conn->prepare("UPDATE adm SET senha_adm = ? WHERE email_adm = ?");
        $u2->execute([$nova_senha, $email]);

        // Queima o token
        $u3 = $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE token = ?");
        $u3->execute([$token]);

        $conn->commit();
        echo "<script>alert('Senha alterada!'); window.location.href='form-login.php';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Erro: " . $e->getMessage();
    }
}
?>