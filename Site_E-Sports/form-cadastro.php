<?php
include __DIR__.'/includes/head.php';
?>
<link rel="stylesheet" href="styles/form.css">

<header>
    <img src="logo.png">
    <h2>
        FragForce
    </h2>
</header>

<main>
    <form method="POST" action="cadastrar.php">
        <?php
            session_start();
            if (false) {
            }
        ?>
        <label>Nome de Usuário</label><input type="text" name="nickname_jogador " required>
        <label>Email:</label><input type="text" name="email_jogador" placeholder="nome@local.com" required>
        <label>Senha:</label><input type="password" name="senha_jogador" required>
        <label>Repita a senha:</label><input type="password" name="chkpassword" required>
        
        <input class="submit" type="submit" value="Cadastrar">
    </form>
    <p style="font-size: 15px">Já possui uma conta? <a href="form-login;php">Entre aqui!</a></p>
</main>
</body>