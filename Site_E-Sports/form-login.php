<?php 
include __DIR__.'/includes/head.php'; 
session_start();
?>
<link rel="stylesheet" href="styles/form.css">

<body>
<header>
    <img src="logo.png" alt="FragForge">
    <a href="index.php" class="frag">FragForge</a>
</header>

<main>
    <form method="POST" action="logar.php">
        <h3>Acesse sua Conta</h3>
        
        <?php if (isset($_SESSION['MnsErro'])): ?>
            <div class="erro"><?= $_SESSION['MnsErro']; unset($_SESSION['MnsErro']); ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label>Email ou Nickname</label>
            <input type="text" name="nome-email" required placeholder="Digite seu acesso">
        </div>

        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" required placeholder="••••••••">
        </div>

        <input class="submit" type="submit" value="Entrar no Jogo">

        <div class="footer-links">
            <a href="form-cadastro.php">Criar conta</a>
            <a href="#">Esqueceu a senha?</a>
        </div>
    </form>
</main>
</body>