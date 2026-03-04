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
        <label>Email:</label><input type="text" name="email" placeholder="nome@local.com" required>
        <label>Senha:</label><input type="password" name="password" required>
        <label>Repita a senha:</label><input type="password" name="chkpassword" required>
        <label>Data de nascimento:</label><input type="date" name= "birthdate" required>
        <label>Nome de Usuário</label><input type="text" name="nameuser" required>
        <input class="submit" type="submit" value="Cadastrar">
    </form>
    <p style="font-size: 15px">Já possui uma conta? <a href="login.php">Entre aqui!</a></p>
</main>
</body>