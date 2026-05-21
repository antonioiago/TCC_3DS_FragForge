<?php
include __DIR__.'/includes/formhead.php'
?>


<main class="container-cadastro">
    <h3>Recuperar senha</h3>
    <form action="esqueciasenha.php" method="POST">
        <div class="input-box">
            <input type="text" name="email" placeholder="E-mail cadastrado na conta" required>
        </div>

        <button type="submit" class="btn-cadastro">
            Enviar Link de Recuperação
        </button>
    </form>
    <div class="login-link">
        <a href="form-login.php">Voltar ao Login</a>
    </div>
</main>
