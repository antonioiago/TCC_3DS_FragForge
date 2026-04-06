<?php
session_start();
include __DIR__.'/includes/head.php';
?>

<link rel="stylesheet" href="styles/form.css">

<header>
    <img src="logo.png">
    <h2>
        <a href="index.php" class="frag">FragForge</a>
        
    </h2>

</header>

<body>

<main>
    <h3>Cadastro</h3>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="erro">
            <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
        </div>
    <?php endif; ?>

    <?php
$conn = new mysqli("localhost", "root", "root", "fragforge");

$funcoes = $conn->query("SELECT * FROM funcao");
$patentes = $conn->query("SELECT * FROM patente");
?>

<form method="POST" action="cadastrar.php" onsubmit="return validarForm()">
        
    <input type="text" name="nickname_jogador" placeholder="Usuário" required>
    <input type="email" name="email_jogador" placeholder="Email" required>
    <input type="text" name="codigo_battlenet" placeholder="BattleTag" required>

    <input type="password" name="senha_jogador" id="senha" placeholder="Senha" required>
    <input type="password" name="chkpassword" id="chkpassword" placeholder="Repita a senha" required>

    <select name="id_funcao" required>
        <option value="">Função</option>
        <?php while($f = $funcoes->fetch_assoc()) echo "<option value='{$f['id_funcao']}'>{$f['nome_funcao']}</option>"; ?>
    </select>

    <select name="id_patente">
        <option value="">Patente</option>
        <?php while($p = $patentes->fetch_assoc()) echo "<option value='{$p['id_patente']}'>{$p['nome_patente']}</option>"; ?>
    </select>

    

    <input type="submit" value="Cadastrar">
</form>
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
