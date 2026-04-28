<header>
    <h2>
        FragForge
    </h2>
    <style>
        p {
            display: inline;
        }
        .link-perfil {
            text-decoration: none;
            color: inherit; /* Mantém a cor do texto original */
            font-weight: bold;
        }
        .link-perfil:hover {
            text-decoration: underline;
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

        // O nome agora é um link que aponta para perfil.php enviando o ID
        echo '<div>
                <p>Bem vindo, 
                    <a class="link-perfil" href="perfil.php?id='.$resultado->id_jogador.'">'.
                        $resultado->nickname_jogador.
                    '</a>
                </p>
                <a class="btn-login" href="sair.php">Sair</a>
              </div>';
    } else {
        echo '<p>Você não está logado! <a class="btn-login" href="form-login.php">Entre agora!</a></p>';
    }
    ?>
</header>