<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';
?>

<style>
    /* --- 1. HEADER (PADRÃO) --- */
    header {
        padding: 0 10% !important;
        height: 75px !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 9999 !important;
        width: 100% !important;
        background-color: #3432c7 !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        position: fixed !important;
        box-sizing: border-box !important;
    }

    header * { color: #ffffff !important; text-decoration: none !important; }

    /* --- 2. LAYOUT PRINCIPAL --- */
    main.not {
        display: flex !important;
        flex-direction: row !important;
        justify-content: center !important;
        align-items: flex-start !important;
        gap: 30px !important;
        padding: 0 10% !important;
        margin: 110px auto 40px auto !important;
        max-width: 1600px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* --- 3. SIDEBAR DE NOTÍCIAS --- */
    .news-sidebar {
        width: 350px !important;
        flex-shrink: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 20px !important;
    }

    .news-sidebar .card {
        background: #ffffff !important;
        border-radius: 15px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        overflow: hidden !important; /* Mantém o corte redondo da imagem no topo */
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    /* IMAGEM DA NOTÍCIA (PROTEGIDA) */
    .news-sidebar .card img {
        width: 100% !important;
        height: 180px !important;
        object-fit: cover !important; 
        display: block !important;
    }

    .card-content {
        padding: 15px !important;
        background: #ffffff !important;
    }

    .card-content h3 {
        color: #333 !important;
        margin: 0 0 8px 0 !important;
        font-size: 1.2rem !important;
        font-weight: bold !important;
    }

    .card-content p {
        color: #555 !important;
        font-size: 0.95rem !important;
        line-height: 1.4 !important;
        margin: 0 !important;
    }

    /* --- 4. TIMELINE --- */
    .timeline {
        flex: 1 !important;
        background: #ffffff !important;
        padding: 25px !important;
        border-radius: 20px !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }

    /* BOTÃO CRIAR POSTAGEM (FUNCIONAL) */
    .btn-criar-postagem {
        background-color: #3432c7 !important;
        color: white !important;
        width: 100% !important;
        padding: 15px !important;
        border-radius: 10px !important;
        border: none !important;
        font-weight: bold !important;
        cursor: pointer !important;
        margin-bottom: 20px !important;
        display: block !important;
        transition: opacity 0.2s !important;
        position: relative !important;
        z-index: 5 !important;
    }

    .btn-criar-postagem:hover { opacity: 0.9 !important; }

    /* FOTO JOGADOR (PROTEGIDA) */
    .foto-jogador-feed {
        width: 50px !important;
        height: 50px !important;
        min-width: 50px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 2px solid #3432c7 !important;
        flex-shrink: 0 !important;
    }

    .post-img {
        width: 100% !important;
        height: auto !important;
        border-radius: 10px !important;
        margin-top: 15px !important;
        display: block !important;
    }

    @media (max-width: 1000px) {
        main.not { flex-direction: column !important; align-items: center !important; }
        .news-sidebar { width: 100% !important; }
    }
</style>

<main class="not">
    <aside class="news-sidebar">
        <div class="card">
            <img src="includes/imagens/overwatch.jpg" alt="Overwatch">
            <div class="card-content">
                <h3>Overwatch 2 News</h3>
                <p>Ex-diretor revela pressões da Blizzard sobre lucros astronômicos.</p>
            </div>
        </div>

        <div class="card">
            <img src="includes/imagens/overwatch2.jpg" alt="Conquest">
            <div class="card-content">
                <h3>Conquest Meta</h3>
                <p>Time Overwatch vence evento e libera nova skin Echo.</p>
            </div>
        </div>
    </aside>

    <section class="timeline">
        <button class="btn-criar-postagem" onclick="window.open('post.php', '_blank', 'width=600,height=500')">
            + CRIAR NOVA POSTAGEM
        </button>

        <div class="feed">
            <?php
            try {
                $instancia = new PDO('mysql:host=localhost;dbname=fragforge;charset=utf8', 'root', 'root');
                $stm = $instancia->query("
                    SELECT p.mensagem, j.nickname_jogador, j.id_jogador, j.foto_jogador, p.print_estatistica, f.icon_funcao, pa.icon_patente
                    FROM post p
                    JOIN jogador j ON p.id_jogador = j.id_jogador
                    LEFT JOIN funcao f ON j.id_funcao = f.id_funcao
                    LEFT JOIN patente pa ON j.id_patente = pa.id_patente
                    ORDER BY p.id_post DESC
                ");

                foreach ($stm as $row) {
                    echo "<div class='post' style='background: #1e293b; border-radius: 12px; padding: 20px; color: white; margin-bottom: 20px;'>";
                    echo "<div class='post-header' style='display:flex; align-items:center; gap:12px; margin-bottom:12px;'>";
                    
                    if($row['foto_jogador']) {
                        $pfp = base64_encode($row['foto_jogador']);
                        echo "<img src='data:image/jpeg;base64,{$pfp}' class='foto-jogador-feed'>";
                    }

                    echo "<div style='display:flex; flex-direction:column;'>";
                        echo "<strong><a href='perfil.php?id=".$row['id_jogador']."' style='color:white; text-decoration:none;'>".htmlspecialchars($row['nickname_jogador'])."</a></strong>";
                    echo "</div>";
                    echo "</div>"; 

                    echo "<p style='margin: 10px 0;'>".htmlspecialchars($row['mensagem'])."</p>";

                    if($row['print_estatistica']){
                        $img = base64_encode($row['print_estatistica']);
                        echo "<img class='post-img' src='data:image/jpeg;base64,{$img}'>";
                    }
                    echo "</div>";
                }
            } catch (Exception $e) { echo "<p style='color:white;'>Erro ao carregar o feed.</p>"; }
            ?>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2026 FragForge - Todos os direitos reservados.</p>
</footer>