<header>
    <h2>
        FragForge
    </h2>
    <style>
        p{
            display: inline;
        }
    </style>

    <?php
    session_start();

    if (isset($_SESSION['jogador']['id'])) {
        echo '<div><p>Bem vindo, '.$_SESSION['jogador']['id'].'</p><a class="btn-login" href="sair.php">Sair</a></div>';
    } else {
        echo '<p>Você não está logado! <a class="btn-login" href="form-login.php">Entre agora!</a>';
    }
    ?>
</header>