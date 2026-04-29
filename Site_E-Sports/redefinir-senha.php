<?php 
require_once 'includes/conn.php';
include 'includes/head.php'; 
include 'includes/header.php'; 

$token = $_GET['token'] ?? '';
$stmt = $conn->prepare("SELECT email FROM recuperacao_senha WHERE token = ? AND usado = 0 AND data_expiracao > NOW()");
$stmt->execute([$token]);
$res = $stmt->fetch(PDO::FETCH_OBJ);
?>

<main style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
    <div class="card" style="width: 100%; max-width: 400px; padding: 30px;">
        <?php if ($res): ?>
            <h2>Nova Senha</h2>
            <form action="processar-nova-senha.php" method="POST">
                <input type="hidden" name="token" value="<?= $token ?>">
                <input type="hidden" name="email" value="<?= $res->email ?>">
                <input type="password" name="nova_senha" placeholder="Digite a nova senha" required style="width: 100%; padding: 10px; margin: 10px 0;">
                <button type="submit" class="btn-criar-postagem">Salvar Nova Senha</button>
            </form>
        <?php else: ?>
            <p>Link inválido ou expirado.</p>
        <?php endif; ?>
    </div>
</main>