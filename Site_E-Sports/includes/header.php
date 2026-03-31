<header>
    <a href="index.php" class="frag">FragForge</a>

    <div class="user-area">
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['jogador']['id'])) {
            // Exibe mensagem de boas-vindas para o jogador logado
            echo '<span class="welcome-text">🎮 Logado como: <strong>' . $_SESSION['jogador']['id'] . '</strong></span>';
            echo '<a class="btn-login" href="sair.php" style="margin-left: 15px; background: #ef4444;">Sair</a>';
        } else {
            // Link para quem ainda não entrou
            echo '<span class="welcome-text">Visitante</span>';
            echo '<a class="btn-login" href="form-login.php" style="margin-left: 15px;">Entrar</a>';
        }
        ?>
    </div>
</header>