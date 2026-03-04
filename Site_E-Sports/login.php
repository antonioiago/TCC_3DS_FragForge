<?php
include __DIR__.'/includes/head.php';
?>
<link rel="stylesheet" href="styles/form.css">

<main>
    <div>
        <form method="POST">
            <?php
                if (false) {
                    echo '<p style="font-size: 15px;text-align: center; margin-bottom: 5px; color: rgb(245, 24, 30);font-weight:normal;">Login invalido, tente novamente.</p>';
                }
            ?>
            <label>Email/Nickname:</label><input type="text">
            <label>Senha:</label><input type="text">
            <input class="submit" type="submit" value="Entrar">
        </form>
        <div style="display: flex; justify-content: space-between">
            <a href="cadastro.php">Não possui conta?</a>
            <a href="cadastro.php">Esqueceu a senha?</a>
        </div>
    </div>
</main>
</body>