<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';
?>
<link rel="stylesheet" href="styles/index.css">
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
    // ADICIONADO: p.id_post e p.jogada na query
    $stm = $instancia->query("
        SELECT p.id_post, p.mensagem, j.nickname_jogador, j.id_jogador, j.foto_jogador, 
               p.print_estatistica, p.jogada, f.icon_funcao, pa.icon_patente
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
                    echo "<img src='data:image/jpeg;base64,{$pfp}' class='foto-jogador-feed' style='width:50px; height:50px; border-radius:50%; object-fit:cover;'>";
                }

                echo "<div style='display:flex; flex-direction:column;'>";
                    echo "<strong><a href='perfil.php?id=".$row['id_jogador']."' style='color:white; text-decoration:none;'>".htmlspecialchars($row['nickname_jogador'])."</a></strong>";
                echo "</div>";
            echo "</div>"; 

            echo "<p style='margin: 10px 0;'>".htmlspecialchars($row['mensagem'])."</p>";

            // --- EXIBIÇÃO DE IMAGEM ---
            if($row['print_estatistica']){
                $img = base64_encode($row['print_estatistica']);
                echo "<img class='post-img' src='data:image/jpeg;base64,{$img}' style='max-width:100%; border-radius:8px; margin-top:10px;'>";
            }

            // --- EXIBIÇÃO DE VÍDEO (NOVO) ---
            if($row['jogada']){
                echo "<div style='margin-top:10px;'>";
                    echo "<video controls style='width:100%; border-radius:8px; background:black;'>";
                        // Chamamos o arquivo externo passando o ID do post
                        echo "<source src='exibir_video.php?id=".$row['id_post']."' type='video/mp4'>";
                        echo "Seu navegador não suporta vídeos.";
                    echo "</video>";
                echo "</div>";
            }
        echo "</div>";
    }
} catch (Exception $e) { 
    echo "<p style='color:white;'>Erro ao carregar o feed.</p>"; 
}
?>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2026 FragForge - Todos os direitos reservados.</p>
</footer>