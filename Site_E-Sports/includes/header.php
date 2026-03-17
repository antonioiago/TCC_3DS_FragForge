<header>
    <h2>
        FragForge
    </h2>
    
    <?php
    session_start();

    if (isset($_SESSION['jogador']['id'])) {
        echo "<p>Bem vindo, ".$_SESSION['jogador']['id']."</p>";
    } else {
        echo '<p>Você não está logado! <a class="btn-login" href="form-login.php">Entre agora!</a>';
    }
    ?>
</header>