<header style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
    <div class="logofrag">        
        <h2 class="frag" style="margin: 0;">
            <a href="index.php" style="text-decoration: none; color: inherit;">FragForge</a>
        </h2>
    </div>
    
    <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
        <style>
            p {
                display: inline;
            }
            .link-header {
                text-decoration: none;
                color: inherit;
                font-weight: bold;
                transition: color 0.2s;
            }
            .link-header:hover {
                color: #2563eb; /* Azul da paleta padrão */
                text-decoration: underline;
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

        <a class="link-header" href="lideranca.php">🏆 Liderança</a>
        <a class="link-header" href="equipes.php">🛡️ Equipes</a>

        <?php
        // Evita duplicar o session_start caso ele já tenha sido iniciado em páginas pai (ex: index.php)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['jogador']['id'])) {
            include __DIR__.'/conn.php';
            $cmd = $conn->prepare("SELECT * from jogador where id_jogador = ?");
            $cmd->bindParam(1, $_SESSION['jogador']['id']);
            $cmd->execute();
            $resultado = $cmd->fetch(PDO::FETCH_OBJ);

            // O nome agora é um link que aponta para perfil.php enviando o ID
            echo '<div style="display: flex; align-items: center; gap: 15px;">
                    <p>Bem vindo, 
                        <a class="link-perfil" href="perfil.php?id='.$resultado->id_jogador.'">'.
                            htmlspecialchars($resultado->nickname_jogador).
                        '</a>
                    </p>
                    <a class="btn-login" href="sair.php">Sair</a>
                </div>';
        } else {
            echo '<p><a class="btn-login" href="form-login.php">Entre agora!</a></p>';
        }
        ?>
    </div>
</header>