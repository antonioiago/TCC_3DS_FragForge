<?php 
include 'includes/head.php'; 
include 'includes/header.php'; 
?>

<main style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
    <div class="card" style="width: 100%; max-width: 400px; padding: 30px;">
        <h2 style="color: #333; margin-bottom: 10px;">Recuperar Senha</h2>
        <p style="color: #666; margin-bottom: 20px;">Digite seu e-mail para receber um link de redefinição.</p>
        
        <form action="esqueciasenha.php" method="POST">
            <input type="email" name="email" placeholder="Seu e-mail cadastrado" required 
                   style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px;">
            
            <button type="submit" class="btn-criar-postagem" style="width: 100%; cursor: pointer;">
                Enviar Link de Recuperação
            </button>
        </form>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="form-login.php" style="text-decoration: none; color: #3432c7; font-weight: bold;">Voltar ao Login</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>