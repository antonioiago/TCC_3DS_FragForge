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
            /* Estilo unificado para a foto de perfil ou ícone padrão */
            .avatar-header {
                width: 40px;
                height: 40px;
                border-radius: 50%; /* Deixa perfeitamente redondo */
                object-fit: cover;  /* Evita distorção da imagem */
                 /* Borda azul combinando com o hover dos links */
                transition: transform 0.2s, border-color 0.2s;
                display: block;
            }
            .avatar-header:hover {
                transform: scale(1.05); /* Efeito sutil ao passar o mouse */
                border-color: #1d4ed8; /* Escurece a borda no hover */
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

            echo '<div style="display: flex; align-items: center; gap: 15px;">';
            echo '<a href="perfil.php?id='.$resultado->id_jogador.'" title="Ver Perfil de '.htmlspecialchars($resultado->nickname_jogador).'" style="text-decoration: none; display: block;">';

            // Verifica se existe foto armazenada no banco (campo BLOB)
            if (!empty($resultado->foto_jogador)) {
                // Converte os dados binários para Base64 para exibição direta na tag img
                $fotoBase64 = base64_encode($resultado->foto_jogador);
                echo '<img class="avatar-header" src="data:image/jpeg;base64,' . $fotoBase64 . '" alt="Foto de '.htmlspecialchars($resultado->nickname_jogador).'">';
            } else {
                // Caso não tenha foto, renderiza um ícone moderno de usuário em SVG (não precisa de arquivo local)
                echo '<svg class="avatar-header" style="background-color: #e2e8f0; padding: 6px; box-sizing: border-box;" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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