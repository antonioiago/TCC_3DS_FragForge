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
                color: #2563eb;
                text-decoration: underline;
            }

            .avatar-header {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
                display: block;
                transition: transform 0.2s, border-color 0.2s;
                border: 2px solid #e2e8f0;
            }

            .avatar-header:hover {
                transform: scale(1.05);
                border-color: #1d4ed8;
            }

            .btn-login {
                font-weight: bold;
                text-decoration: none;
                color: #2563eb;
            }

            .btn-login:hover {
                text-decoration: underline;
            }
        </style>

        <a class="link-header" href="lideranca.php">🏆 Liderança</a>
        <a class="link-header" href="equipes.php">🛡️ Equipes</a>

        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['jogador']['id'])) {

            include __DIR__ . '/conn.php';

            $cmd = $conn->prepare("
                SELECT id_jogador, nickname_jogador, foto_jogador
                FROM jogador
                WHERE id_jogador = ?
            ");

            $cmd->bindParam(1, $_SESSION['jogador']['id']);
            $cmd->execute();

            $resultado = $cmd->fetch(PDO::FETCH_OBJ);

            echo '<div style="display: flex; align-items: center; gap: 15px;">';

            echo '<a href="perfil.php?id=' . $resultado->id_jogador . '" 
                    title="Ver Perfil de ' . htmlspecialchars($resultado->nickname_jogador) . '" 
                    style="text-decoration: none; display: block;">';

            // =========================
            // FOTO SUPABASE (URL)
            // =========================
            if (!empty($resultado->foto_jogador)) {

                echo '<img class="avatar-header"
                        src="' . htmlspecialchars($resultado->foto_jogador) . '"
                        alt="Foto de ' . htmlspecialchars($resultado->nickname_jogador) . '">';

            } else {

                echo '<svg class="avatar-header"
                        style="background-color: #e2e8f0; padding: 6px; box-sizing: border-box;"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#64748b"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                      </svg>';
            }

            echo '</a>';

            echo '<a class="btn-login" href="sair.php">Sair</a>';
            echo '</div>';

        } else {
            echo '<p><a class="btn-login" href="form-login.php">Entre agora!</a></p>';
        }
        ?>

    </div>

</header>