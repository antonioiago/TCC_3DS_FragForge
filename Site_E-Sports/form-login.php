<?php
session_start();

include __DIR__.'/includes/formhead.php';
?>

<main class="container-cadastro">

    <h3>Login</h3>

    <?php

    if (isset($_SESSION['MnsErro'])) {

        echo '<p style="font-size: 15px; text-align: center; margin-bottom: 5px; color: rgb(245, 24, 30); font-weight: normal;">'
            . $_SESSION['MnsErro'] .
        '</p>';

        unset($_SESSION['MnsErro']);
    }
    ?>

    <form method="POST" action="logar.php">

        <div class="input-box">
            <input type="text" name="nome-email" placeholder="Nome/Email" required>
        </div>

        <div class="input-box">
            <input type="password" name="senha" placeholder="Senha" required>
        </div>

        <button type="submit" class="btn-cadastro">
            Entrar
        </button>

    </form>

    <div class="login-link">
        Não possui conta?
        <a href="form-cadastro.php">Fazer Cadastro</a>
    </div>

    <div class="login-link">
        Esqueceu a senha?
        <a href="form-esqueciasenha.php">Recuperar a Senha</a>
    </div>

</main>

</body>