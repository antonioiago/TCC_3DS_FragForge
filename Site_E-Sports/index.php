<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';
?>
<link rel="stylesheet" href="styles/index.css">
<main class="not">
    <aside class="news-sidebar">
        
        <a href="https://overwatch.blizzard.com/en-us/news/patch-notes/"><div class="card">
            <img src="https://preview.redd.it/patch-notes-summary-for-s17-v0-hnarqy4jax8f1.jpeg?auto=webp&s=ae1b854f85a3659536becdf949e7044107a3e42d" alt="Overwatch">
            <div class="card-content">
                <h3>Patch Notes</h3>
                <p>Notas da atualização do Overwatch – 28 de abril de 2026</p>
            </div>
        </div></a>

        <a href="https://overwatch.blizzard.com/pt-br/heroes/"><div class="card">
            <img src="https://assets.boostroyal.com/uploads/blog/1754907546303-image1.png" alt="Conquest">
            <div class="card-content">
                <h3>Overwatch</h3>
                <p>Conheça os heróis</p>
            </div>
        </div></a>
            <a href="https://overwatch.blizzard.com/pt-br/"><div class="card">
            <img src="https://blz-contentstack-images.akamaized.net/v3/assets/blt2477dcaf4ebd440c/blt30aabe1c0e164812/6504ca8b0d91ee465b55006b/Action.jpg" alt="Overwatch">
            <div class="card-content">
                <h3>Overwatch</h3>
                <p>Baixe agora</p>
            </div>
        </div></a>

        <a href="https://overwatch.blizzard.com/pt-br/news/24266793/"><div class="card">
            <img src="https://bnetcmsus-a.akamaihd.net/cms/blog_header/ao/AOA1HSHQ12FI1775525381310.png" alt="Conquest">
            <div class="card-content">
                <h3>Nova temporada</h3>
                <p>Alcance alturas heroicas no Reinado da Talon – 2ª Temporada: Apogeu</p>
            </div>
        </div></a>
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