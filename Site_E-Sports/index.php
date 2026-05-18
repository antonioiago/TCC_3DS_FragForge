<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';

// Captura os filtros da URL (via GET) com valores padrão
$ordenar = $_GET['ordenar'] ?? 'recente';
$funcao  = $_GET['funcao'] ?? 'todas';
?>
<link rel="stylesheet" href="styles/index.css">

<style>
    .card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .card-link:hover {
        text-decoration: none;
        color: inherit;
    }
</style>

<main class="not">
    <aside class="news-sidebar">
        
        <a href="javascript:void(0);" onclick="abrirChat()" class="chat-btn" title="Abrir Chat">
        💬
        </a>

        <script>
        function abrirChat() {
            window.open('chat.php', 'ChatFragForge', 'width=450,height=600,scrollbars=no,resizable=no');
        }
        </script>

        <a href="https://overwatch.blizzard.com/en-us/news/patch-notes/" class="card-link">
            <div class="card">
                <img src="https://preview.redd.it/patch-notes-summary-for-s17-v0-hnarqy4jax8f1.jpeg?auto=webp&s=ae1b854f85a3659536becdf949e7044107a3e42d" alt="Overwatch">
                <div class="card-content">
                    <h3>Patch Notes</h3>
                    <p>Notas da atualização do Overwatch – 28 de abril de 2026</p>
                </div>
            </div>
        </a>

        <a href="https://overwatch.blizzard.com/pt-br/heroes/" class="card-link">
            <div class="card">
                <img src="https://bnetcmsus-a.akamaihd.net/cms/blog_header/2c/2C587UV06J9Q1778097666570.png" alt="Conquest">
                <div class="card-content">
                    <h3>Overwatch</h3>
                    <p>Conheça os heróis</p>
                </div>
            </div>
        </a>

        <a href="https://overwatch.blizzard.com/pt-br/" class="card-link">
            <div class="card">
                <img src="https://blz-contentstack-images.akamaized.net/v3/assets/blt2477dcaf4ebd440c/blt30aabe1c0e164812/6504ca8b0d91ee465b55006b/Action.jpg" alt="Overwatch">
                <div class="card-content">
                    <h3>Overwatch</h3>
                    <p>Baixe agora</p>
                </div>
            </div>
        </a>

        <a href="https://overwatch.blizzard.com/pt-br/news/24266793/" class="card-link">
            <div class="card">
                <img src="https://bnetcmsus-a.akamaihd.net/cms/blog_header/ao/AOA1HSHQ12FI1775525381310.png" alt="Conquest">
                <div class="card-content">
                    <h3>Nova temporada</h3>
                    <p>Alcance alturas heroicas no Reinado da Talon – 2ª Temporada: Apogeu</p>
                </div>
            </div>
        </a>
    </aside>

    <section class="timeline">
        
        <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 16px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
            <form method="GET" action="" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                
                <div style="flex: 1; min-width: 160px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px; text-transform:uppercase;">Focar/Ordenar por:</label>
                    <select name="ordenar" onchange="this.form.submit()" style="width:100%; padding:8px 12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; color:#1e293b; font-weight:600; font-size:14px; outline:none;">
                        <option value="recente" <?php echo $ordenar === 'recente' ? 'selected' : ''; ?>>🕒 Postagens Recentes</option>
                        <option value="maior_pontuacao" <?php echo $ordenar === 'maior_pontuacao' ? 'selected' : ''; ?>>🔥 Maior Pontuação</option>
                        <option value="maior_rank" <?php echo $ordenar === 'maior_rank' ? 'selected' : ''; ?>>👑 Maior Patente / Rank</option>
                    </select>
                </div>

                <div style="flex: 1; min-width: 160px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px; text-transform:uppercase;">Filtrar por Função:</label>
                    <select name="funcao" onchange="this.form.submit()" style="width:100%; padding:8px 12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; color:#1e293b; font-weight:600; font-size:14px; outline:none;">
                        <option value="todas" <?php echo $funcao === 'todas' ? 'selected' : ''; ?>>⚡ Todas as Funções</option>
                        <option value="tank" <?php echo $funcao === 'tank' ? 'selected' : ''; ?>>🦾 Tank</option>
                        <option value="dps" <?php echo $funcao === 'dps' ? 'selected' : ''; ?>>⚔️ DPS</option>
                        <option value="sup" <?php echo $funcao === 'sup' ? 'selected' : ''; ?>>🛡️ Sup</option>
                    </select>
                </div>
                
                <?php if($ordenar !== 'recente' || $funcao !== 'todas'): ?>
                    <div style="margin-top: 18px;">
                        <a href="index.php" style="color: #2563eb; text-decoration: none; font-size: 13px; font-weight: 700; border-bottom: 2px dashed #2563eb; padding-bottom: 2px;">Limpar Filtros</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <button class="btn-criar-postagem" onclick="window.open('post.php', '_blank', 'width=600,height=500')" style="background: #2563eb; color: #ffffff; width:100%; border-radius:12px; padding:12px; font-weight:bold; margin-bottom: 20px; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(37,99,235,0.2);">
            + CRIAR NOVA POSTAGEM
        </button>

        <div class="feed">
            <?php
try {
    $instancia = new PDO('mysql:host=localhost;dbname=fragforge;charset=utf8', 'root', 'root');
    $instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $whereClauses = [];
    $params = [];
    if ($funcao !== 'todas') {
        $whereClauses[] = "f.nome_funcao = :funcao";
        $params[':funcao'] = $funcao;
    }
    $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

    switch ($ordenar) {
        case 'maior_pontuacao':
            $orderBySql = "ORDER BY j.pontuacao_jogador DESC, p.id_post DESC";
            break;
        case 'maior_rank':
            $orderBySql = "ORDER BY j.id_patente DESC, j.pontuacao_jogador DESC, p.id_post DESC";
            break;
        case 'recente':
        default:
            $orderBySql = "ORDER BY p.id_post DESC";
            break;
    }

    $query_string = "
        SELECT p.id_post, p.mensagem, j.nickname_jogador, j.id_jogador, j.foto_jogador, 
               p.print_estatistica, p.jogada, f.icon_funcao, pa.icon_patente
        FROM post p
        JOIN jogador j ON p.id_jogador = j.id_jogador
        LEFT JOIN funcao f ON j.id_funcao = f.id_funcao
        LEFT JOIN patente pa ON j.id_patente = pa.id_patente
        $whereSql
        $orderBySql
    ";

    $stmt = $instancia->prepare($query_string);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultados) > 0) {
        foreach ($resultados as $row) {
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

                if($row['print_estatistica']){
                    $img = base64_encode($row['print_estatistica']);
                    echo "<img class='post-img' src='data:image/jpeg;base64,{$img}' style='max-width:100%; border-radius:8px; margin-top:10px;'>";
                }

                if($row['jogada']){
                    echo "<div style='margin-top:10px;'>";
                        echo "<video controls style='width:100%; border-radius:8px; background:black;'>";
                            echo "<source src='exibir_video.php?id=".$row['id_post']."' type='video/mp4'>";
                            echo "Seu navegador não suporta vídeos.";
                        echo "</video>";
                    echo "</div>";
                }
            echo "</div>";
        }
    } else {
        echo "<div style='text-align:center; padding: 40px; background:#1e293b; border-radius:12px; color:#64748b;'><p>Nenhuma postagem encontrada para esse filtro.</p></div>";
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