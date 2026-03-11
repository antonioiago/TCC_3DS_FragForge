<?php
include __DIR__.'/includes/head.php';
?>
<link rel="stylesheet" href="styles/form.css">

<main>
    <form method="POST" action="cadastrar.php">
        <?php
            session_start();
            if (false) {
            }
        ?>
        <label>Nome de Usuário ou Email</label><input type="text" name="nickname_jogador || email_jogador" required>
        <label>Senha:</label><input type="password" name="senha_jogador" required>
        <input class="submit" type="submit" value="Logar">
    <p style="font-size: 15px">Ainda não possuí uma conta?<a href="login.php">Clique aqui :)!</a></p>
</main>


<?php
include __DIR__.'/includes/head.php';
?>