<?php
session_start();
include __DIR__.'/includes/head.php';
?>

<link rel="stylesheet" href="styles/form.css">

<body>

<header>
    <img src="logo_FragForge" alt="Logo FragForce">
    <a href="index.php" class="frag"><h3>FragForge</h3>
</header>

<main>
    <h3>Cadastro</h3>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="erro">
            <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="cadastrar.php" onsubmit="return validarForm()">
        
        <label>Nome de Usuário:</label>
        <input type="text" name="nickname_jogador" required minlength="3">

        <label>Email:</label>
        <input type="email" name="email_jogador" placeholder="nome@local.com" required>

        <label>Código Battle.net:</label>
        <input type="text" name="codigo_battlenet" placeholder="Ex: Player#1234" required>

        <label>Senha:</label>
        <input type="password" name="senha_jogador" id="senha" required minlength="6">

        <label>Repita a senha:</label>
        <input type="password" name="chkpassword" id="chkpassword" required>

        <input class="submit" type="submit" value="Cadastrar">
    </form>

    <p>Já possui uma conta? <a href="form-login.php">Entre aqui!</a></p>
</main>

<script>
function validarForm() {
    const senha = document.getElementById("senha").value;
    const chk = document.getElementById("chkpassword").value;

    if (senha !== chk) {
        alert("As senhas não coincidem!");
        return false;
    }

    if (senha.length < 6) {
        alert("A senha deve ter pelo menos 6 caracteres!");
        return false;
    }

    return true;
}
</script>

</body>
