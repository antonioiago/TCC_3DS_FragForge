<?php 
require_once 'includes/conn.php';
include 'includes/head.php'; 
include 'includes/header.php'; 

$token = $_GET['token'] ?? '';
$token_valido = false;
$email_alvo = "";

if ($token) {
    // Valida o token no banco
    $stmt = $conn->prepare("SELECT email FROM recuperacao_senha WHERE token = ? AND usado = 0 AND data_expiracao > NOW()");
    $stmt->execute([$token]);
    $dados = $stmt->fetch(PDO::FETCH_OBJ);

    if ($dados) {
        $email_alvo = $dados->email;
        $token_valido = true;
    }
}
?>

<main style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
    <div class="card" style="width: 100%; max-width: 400px; padding: 30px;">
        <?php if ($token_valido): ?>
            <h2 style="color: #333;">Nova Senha</h2>
            <p style="color: #666; font-size: 0.9em;">Conta: <b><?php echo $email_alvo; ?></b></p>
            
            <form action="processar-nova-senha.php" method="POST">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="email" value="<?php echo $email_alvo; ?>">
                
                <input type="password" name="nova_senha" placeholder="Nova senha (mín. 6 caracteres)" required minlength="6"
                       style="width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ccc; border-radius: 8px;">
                
                <button type="submit" class="btn-criar-postagem" style="width: 100%;">Atualizar Senha</button>
            </form>
        <?php else: ?>
            <h2 style="color: #e74c3c;">Link Inválido</h2>
            <p>Este link expirou ou já foi usado.</p>
            <a href="form-esqueciasenha.php" class="btn-login">Tentar Novamente</a>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>