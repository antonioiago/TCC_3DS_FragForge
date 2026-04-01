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
        include __DIR__.'/conn.php';
        $cmd = $conn->prepare("SELECT * from jogador where id_jogador = ?");
        $cmd->bindParam(1, $_SESSION['jogador']['id']);
        $cmd->execute();
        $resultado = $cmd->fetch(PDO::FETCH_OBJ);

        echo '<div><p>Bem vindo, '.$resultado->nickname_jogador.'</p><a class="btn-login" href="sair.php">Sair</a></div>';
    } else {
        echo '<p>Você não está logado! <a class="btn-login" href="form-login.php">Entre agora!</a>';
    }
    ?>
</header>