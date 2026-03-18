<?php
include __DIR__.'/includes/head.php';
?>
<link rel="stylesheet" href="styles/form.css">

<header>
    <img src="logo.png">
    <h2>
        FragForge
    </h2>
</header>

<main>
    <div>
        <form method="POST" action="logar.php">
            <?php
                session_start();
                if (isset($_SESSION['MnsErro'])) {
                    echo '<p style="font-size: 15px;text-align: center; margin-bottom: 5px; color: rgb(245, 24, 30);font-weight:normal;">'.$_SESSION['MnsErro'].'</p>';
                    session_abort();
                }
            ?>
            <label>Email/Nickname:</label><input type="text" name="nome-email">
            <label>Senha:</label><input type="password" name="senha">
            <input class="submit" type="submit" value="Entrar">
        </form>
        <div style="display: flex; justify-content: space-between">
            <a href="form-cadastro.php">Não possui conta?</a>
            <a href="form-redfsenha.php">Esqueceu a senha?</a>
        </div>
    </div>
</main>
</body>
