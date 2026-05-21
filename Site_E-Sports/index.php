<?php
// Inicia a sessão no topo absoluto para evitar problemas com redirecionamento e AJAX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_logado = isset($_SESSION['jogador']['id']);
$id_jogador = $usuario_logado ? $_SESSION['jogador']['id'] : null;

// Conexão principal com o banco de dados
try {
    $instancia = new PDO('mysql:host=localhost;dbname=fragforge;charset=utf8', 'root', 'root');
    $instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "<div style='text-align:center; padding:20px; color:red;'>⚠️ Erro ao conectar ao banco de dados.</div>";
    exit;
}

// --- LÓGICA DE CURTIDAS INTERATIVAS (AJAX) ---
if (isset($_GET['acao']) && $_GET['acao'] === 'curtir' && isset($_GET['id_post'])) {
    header('Content-Type: application/json');
    
    if (!$usuario_logado) {
        echo json_encode(['sucesso' => false, 'erro' => 'login_obrigatorio']);
        exit;
    }

    $id_post_curtir = (int)$_GET['id_post'];

    if (!isset($_SESSION['curtidas_usuario'])) {
        $_SESSION['curtidas_usuario'] = [];
    }

    if (isset($_SESSION['curtidas_usuario'][$id_post_curtir])) {
        $stmt = $instancia->prepare("UPDATE post SET curtidas = GREATEST(0, curtidas - 1) WHERE id_post = ?");
        $stmt->execute([$id_post_curtir]);
        unset($_SESSION['curtidas_usuario'][$id_post_curtir]);
        $status = 'descurtido';
    } else {
        $stmt = $instancia->prepare("UPDATE post SET curtidas = COALESCE(curtidas, 0) + 1 WHERE id_post = ?");
        $stmt->execute([$id_post_curtir]);
        $_SESSION['curtidas_usuario'][$id_post_curtir] = true;
        $status = 'curtido';
    }

    $stmt = $instancia->prepare("SELECT curtidas FROM post WHERE id_post = ?");
    $stmt->execute([$id_post_curtir]);
    $nova_quantidade = $stmt->fetchColumn();

    echo json_encode([
        'sucesso' => true,
        'status' => $status,
        'curtidas' => (int)$nova_quantidade
    ]);
    exit;
}

// --- LÓGICA DE BUSCAR COMENTÁRIOS VIA AJAX ---
if (isset($_GET['acao']) && $_GET['acao'] === 'buscar_comentarios' && isset($_GET['id_post'])) {
    header('Content-Type: application/json');
    $id_post = (int)$_GET['id_post'];
    
    $stmt = $instancia->prepare("
        SELECT c.comentario, c.data_envio, j.nickname_jogador, j.foto_jogador 
        FROM comentarios c 
        JOIN jogador j ON c.id_jogador = j.id_jogador 
        WHERE c.id_post = ? 
        ORDER BY c.id_comentario ASC
    ");
    $stmt->execute([$id_post]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tratamento para converter a foto em base64 para o JSON
    foreach ($comentarios as &$com) {
        if ($com['foto_jogador']) {
            $com['foto_jogador'] = 'data:image/jpeg;base64,' . base64_encode($com['foto_jogador']);
        } else {
            $com['foto_jogador'] = null;
        }
        $com['comentario'] = htmlspecialchars($com['comentario']);
        $com['nickname_jogador'] = htmlspecialchars($com['nickname_jogador']);
        $com['data_envio'] = date('d/m H:i', strtotime($com['data_envio']));
    }
    
    echo json_encode($comentarios);
    exit;
}

// --- LÓGICA DE ADICIONAR COMENTÁRIO VIA AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['acao']) && $_GET['acao'] === 'comentar') {
    header('Content-Type: application/json');
    
    if (!$usuario_logado) {
        echo json_encode(['sucesso' => false, 'erro' => 'login_obrigatorio']);
        exit;
    }
    
    $id_post = (int)($_POST['id_post'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');
    
    if (empty($comentario)) {
        echo json_encode(['sucesso' => false, 'erro' => 'vazio']);
        exit;
    }
    
    $stmt = $instancia->prepare("INSERT INTO comentarios (id_post, id_jogador, comentario) VALUES (?, ?, ?)");
    $stmt->execute([$id_post, $id_jogador, $comentario]);
    
    // Pega a nova contagem de comentários do post
    $stmt_count = $instancia->prepare("SELECT COUNT(*) FROM comentarios WHERE id_post = ?");
    $stmt_count->execute([$id_post]);
    $total_comentarios = $stmt_count->fetchColumn();
    
    echo json_encode(['sucesso' => true, 'total' => $total_comentarios]);
    exit;
}

// Carrega os layouts estruturais da página após verificar requisições AJAX
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';

// Captura os filtros da URL (via GET) com valores padrão
$ordenar = $_GET['ordenar'] ?? 'recente';
$funcao  = $_GET['funcao'] ?? 'todas';

// --- LOGICA DE SUBMISSÃO DA NOVA POSTAGEM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_postar']) && $usuario_logado) {
    $mensagem = trim($_POST['mensagem']);
    $print_estatistica = null;
    $jogada = null;

    if (isset($_FILES['print_estatistica']) && $_FILES['print_estatistica']['error'] == 0) {
        $print_estatistica = file_get_contents($_FILES['print_estatistica']['tmp_name']);
    }

    if (isset($_FILES['jogada']) && $_FILES['jogada']['error'] == 0) {
        $jogada = file_get_contents($_FILES['jogada']['tmp_name']);
    }

    if (!empty($mensagem)) {
        $stmt = $instancia->prepare("INSERT INTO post (mensagem, print_estatistica, jogada, id_jogador, curtidas) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$mensagem, $print_estatistica, $jogada, $id_jogador]);
        
        echo "<script>window.location.href='index.php';</script>";
        exit;
    }
}
?>
<link rel="stylesheet" href="styles/index.css">

<style>
    header, header a, header span, header li, header div { color: #ffffff !important; }

    /* --- CENTRALIZAÇÃO FORÇADA DOS CARDS DE NOTÍCIAS --- */
    main.not .news-sidebar .card-link { 
        text-decoration: none !important; 
        color: inherit !important; 
        display: block !important; 
        width: 100% !important;
    }
    main.not .news-sidebar .card-link:hover { text-decoration: none !important; color: inherit !important; }

    main.not .news-sidebar .card {
        display: block !important;
        width: 100% !important;
        background: #ffffff !important;
        border-radius: 16px !important;
        overflow: hidden !important; /* Corta as rebarbas da imagem nas bordas arredondadas */
        margin-bottom: 20px !important;
        padding: 0 !important;      /* Remove paddings antigos que esmagavam a foto */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }

    main.not .news-sidebar .card img {
        display: block !important;
        width: 100% !important;       /* Obriga a foto azul a tocar as duas paredes laterais */
        max-width: 100% !important;
        height: 190px !important;     /* Altura fixa proporcional para virar banner */
        margin: 0 !important;         /* Limpa margens herdadas */
        padding: 0 !important;
        object-fit: cover !important; /* Corta inteligentemente o excesso sem achatar os textos internos da foto */
        object-position: center !important;
    }

    main.not .news-sidebar .card-content {
        padding: 16px !important;
        text-align: center !important; /* Garante que o Título e subtítulo fiquem centralizados */
        box-sizing: border-box !important;
    }

    /* --- MODAL DE POSTAGEM --- */
    .modal-post { 
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); 
        justify-content: center; align-items: center; z-index: 9999; 
    }
    .modal-post-content { 
        background: #ffffff; width: 90%; max-width: 550px; padding: 24px; border-radius: 16px; 
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); position: relative; animation: surgimentoRapido 0.2s ease-out;
    }
    @keyframes surgimentoRapido { from { transform: scale(0.96); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    .modal-post-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; }
    .modal-post-title { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
    .btn-close-modal { background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; line-height: 1; }

    .textarea-post { width: 100%; height: 120px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; font-size: 15px; outline: none; resize: none; box-sizing: border-box; }
    .file-input-group { margin-top: 14px; display: flex; flex-direction: column; gap: 6px; }
    .file-label { font-size: 13px; font-weight: 700; color: #475569; }
    .file-field { font-size: 13px; color: #64748b; }
    .btn-submit-post { background: #2563eb; color: #ffffff; border: none; width: 100%; padding: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; margin-top: 20px; }

    /* Botões Interativos no Feed */
    .btn-like, .btn-comment-trigger {
        background: #334155; border: none; color: #f1f5f9; cursor: pointer; 
        display: inline-flex; align-items: center; gap: 8px; font-size: 14px; 
        font-weight: 600; padding: 6px 14px; border-radius: 20px; transition: all 0.2s ease; margin-top: 12px;
    }
    .btn-like:hover, .btn-comment-trigger:hover { background: #475569; transform: translateY(-1px); }
    .btn-like.active { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

    /* --- GAVETA LATERAL DO CHAT DE COMENTÁRIOS --- */
    .comment-drawer {
        position: fixed; top: 0; right: -450px; width: 420px; max-width: 100%; height: 100%;
        background: #1e293b; box-shadow: -5px 0 25px rgba(0,0,0,0.3); z-index: 10000;
        display: flex; flex-direction: column; transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1); color: #ffffff;
    }
    .comment-drawer.open { right: 0; }
    .drawer-header { padding: 16px; background: #0f172a; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
    .drawer-header h3 { margin: 0; font-size: 16px; color: #f8fafc; }
    .btn-close-drawer { background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; }
    
    /* Corpo do Chat */
    .drawer-body { flex: 1; padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #111827; }
    .chat-bubble { display: flex; gap: 10px; align-items: flex-start; background: #1e293b; padding: 10px; border-radius: 12px; border: 1px solid #334155; }
    .chat-bubble img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: #475569; }
    .chat-content-text { display: flex; flex-direction: column; flex: 1; }
    .chat-meta { display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; margin-bottom: 4px; }
    .chat-meta strong { color: #38bdf8; }
    .chat-message { font-size: 13px; color: #e2e8f0; word-break: break-word; }

    /* Rodapé do Chat (Input) */
    .drawer-footer { padding: 12px; background: #0f172a; border-top: 1px solid #334155; }
    .chat-form { display: flex; gap: 8px; }
    .chat-input { flex: 1; background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 8px 12px; color: white; outline: none; font-size: 13px; }
    .chat-input:focus { border-color: #2563eb; }
    .btn-send-chat { background: #2563eb; color: white; border: none; border-radius: 8px; padding: 0 14px; cursor: pointer; font-weight: bold; }
    .btn-send-chat:hover { background: #1d4ed8; }
    
    .comment-overlay { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; }
    .comment-overlay.open { display: block; }
</style>

<main class="not">
    <aside class="news-sidebar">
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

        <button class="btn-criar-postagem" onclick="abrirModalPost()" style="background: #2563eb; color: #ffffff; width:100%; border-radius:12px; padding:12px; font-weight:bold; margin-bottom: 20px; border:none; cursor:pointer;">
            + CRIAR NOVA POSTAGEM
        </button>

        <div class="feed">
            <?php
            try {
                $whereClauses = [];
                $params = [];
                if ($funcao !== 'todas') {
                    $whereClauses[] = "f.nome_funcao = :funcao";
                    $params[':funcao'] = $funcao;
                }
                $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

                switch ($ordenar) {
                    case 'maior_pontuacao': $orderBySql = "ORDER BY j.pontuacao_jogador DESC, p.id_post DESC"; break;
                    case 'maior_rank': $orderBySql = "ORDER BY j.id_patente DESC, j.pontuacao_jogador DESC, p.id_post DESC"; break;
                    case 'recente': default: $orderBySql = "ORDER BY p.id_post DESC"; break;
                }

                $query_string = "
                    SELECT p.id_post, p.mensagem, p.curtidas, j.nickname_jogador, j.id_jogador, j.foto_jogador, 
                           p.print_estatistica, p.jogada, f.icon_funcao, pa.icon_patente,
                           (SELECT COUNT(*) FROM comentarios WHERE id_post = p.id_post) as total_comentarios
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
                        $id_post_atual = $row['id_post'];
                        $qtd_curtidas = (int)($row['curtidas'] ?? 0);
                        $qtd_comentarios = (int)($row['total_comentarios'] ?? 0);
                        
                        $usuario_ja_curtiu = isset($_SESSION['curtidas_usuario'][$id_post_atual]);
                        $classe_ativa = $usuario_ja_curtiu ? 'active' : '';
                        $emoji_coracao = $usuario_ja_curtiu ? '❤️' : '🤍';

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
                                    echo "</video>";
                                echo "</div style='margin-top:10px;'>";
                            }

                            echo "<div class='post-footer' style='display:flex; gap:10px;'>";
                                echo "<button class='btn-like {$classe_ativa}' onclick='alternarCurtida({$id_post_atual})' id='btn-like-{$id_post_atual}'>";
                                    echo "<span id='emoji-like-{$id_post_atual}'>{$emoji_coracao}</span>";
                                    echo "<span id='contagem-like-{$id_post_atual}'>{$qtd_curtidas}</span> Curtidas";
                                echo "</button>";

                                echo "<button class='btn-comment-trigger' onclick='abrirGavetaComentarios({$id_post_atual})'>";
                                    echo "💬 <span id='contagem-comment-{$id_post_atual}'>{$qtd_comentarios}</span> Comentários";
                                echo "</button>";
                            echo "</div>";

                        echo "</div>";
                    }
                } else {
                    echo "<div style='text-align:center; padding: 40px; background:#1e293b; border-radius:12px; color:#64748b;'><p>Nenhuma postagem encontrada para esse filtro.</p></div>";
                }
            } catch (Exception $e) { 
                echo "<div style='text-align:center; padding:20px; color:white;'>Erro ao carregar o feed.</div>"; 
            }
            ?>
        </div>
    </section>
</main>

<div id="commentOverlay" class="comment-overlay" onclick="fecharGavetaComentarios()"></div>
<div id="commentDrawer" class="comment-drawer">
    <div class="drawer-header">
        <h3>💬 Chat da Publicação</h3>
        <button class="btn-close-drawer" onclick="fecharGavetaComentarios()">&times;</button>
    </div>
    <div class="drawer-body" id="drawerChatBody">
        </div>
    <div class="drawer-footer">
        <?php if ($usuario_logado): ?>
            <form id="chatForm" class="chat-form" onsubmit="enviarComentario(event)">
                <input type="hidden" id="chatPostId" value="">
                <input type="text" id="chatInput" class="chat-input" placeholder="Escreva seu comentário..." autocomplete="off" required>
                <button type="submit" class="btn-send-chat">Enviar</button>
            </form>
        <?php else: ?>
            <p style="margin:0; font-size:12px; text-align:center; color:#94a3b8;">
                🔒 Faça <a href="form-login.php" style="color:#2563eb; font-weight:bold;">Login</a> para comentar.
            </p>
        <?php endif; ?>
    </div>
</div>

<div id="modalNovaPostagem" class="modal-post" onclick="fecharModalPost()">
    <div class="modal-content modal-post-content" onclick="event.stopPropagation();">
        <div class="modal-post-header">
            <h3 class="modal-post-title">Nova Postagem</h3>
            <button class="btn-close-modal" onclick="fecharModalPost()">&times;</button>
        </div>
        <?php if ($usuario_logado): ?>
            <form method="POST" action="index.php" enctype="multipart/form-data">
                <textarea name="mensagem" class="textarea-post" placeholder="No que você está pensando hoje?" required></textarea>
                <div class="file-input-group"><label class="file-label">📸 Print:</label><input type="file" name="print_estatistica" accept="image/*" class="file-field"></div>
                <div class="file-input-group"><label class="file-label">🎬 Highlight:</label><input type="file" name="jogada" accept="video/mp4" class="file-field"></div>
                <button type="submit" name="acao_postar" class="btn-submit-post">Publicar no Feed</button>
            </form>
        <?php else: ?>
            <div style="text-align:center; padding: 20px 0; color:#475569;">🔒 <br>Faça <a href="form-login.php">Login</a> para postar.</div>
        <?php endif; ?>
    </div>
</div>

<script>
let atualIdPostAberto = null;

// Lógica de Curtidas (AJAX)
function alternarCurtida(idPost) {
    fetch(`index.php?acao=curtir&id_post=${idPost}`)
        .then(response => response.json())
        .then(dados => {
            if (dados.sucesso) {
                const botao = document.getElementById(`btn-like-${idPost}`);
                const emoji = document.getElementById(`emoji-like-${idPost}`);
                const textoContagem = document.getElementById(`contagem-like-${idPost}`);
                textoContagem.innerText = dados.curtidas;
                if (dados.status === 'curtido') {
                    botao.classList.add('active'); emoji.innerText = '❤️';
                } else {
                    botao.classList.remove('active'); emoji.innerText = '🤍';
                }
            } else if (dados.erro === 'login_obrigatorio') {
                alert('🔒 Você precisa fazer login para poder curtir!');
                window.location.href = 'form-login.php';
            }
        }).catch(erro => console.error(erro));
}

// Abre o Chat do Post carregando dados via AJAX assíncrono
function abrirGavetaComentarios(idPost) {
    atualIdPostAberto = idPost;
    document.getElementById('chatPostId').value = idPost;
    
    document.getElementById('commentDrawer').classList.add('open');
    document.getElementById('commentOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    
    carregarComentariosDoPost(idPost);
}

function fecharGavetaComentarios() {
    document.getElementById('commentDrawer').classList.remove('open');
    document.getElementById('commentOverlay').classList.remove('open');
    document.body.style.overflow = 'auto';
    atualIdPostAberto = null;
}

// Busca a lista estruturada de comentários
function carregarComentariosDoPost(idPost) {
    const corpoChat = document.getElementById('drawerChatBody');
    corpoChat.innerHTML = "<p style='text-align:center; color:#64748b; font-size:13px;'>Carregando conversas...</p>";
    
    fetch(`index.php?acao=buscar_comentarios&id_post=${idPost}`)
        .then(res => res.json())
        .then(comentarios => {
            corpoChat.innerHTML = "";
            if(comentarios.length === 0) {
                corpoChat.innerHTML = "<p style='text-align:center; color:#64748b; font-size:13px; padding-top:20px;'>Nenhum comentário ainda. Seja o primeiro a puxar assunto! 🚀</p>";
                return;
            }
            comentarios.forEach(com => {
                const imgTag = com.foto_jogador ? `<img src="${com.foto_jogador}">` : `<div style="width:32px; height:32px; border-radius:50%; background:#475569; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold;">🎮</div>`;
                
                corpoChat.innerHTML += `
                    <div class="chat-bubble">
                        ${imgTag}
                        <div class="chat-content-text">
                            <div class="chat-meta">
                                <strong>${com.nickname_jogador}</strong>
                                <span>${com.data_envio}</span>
                            </div>
                            <div class="chat-message">${com.comentario}</div>
                        </div>
                    </div>
                `;
            });
            corpoChat.scrollTop = corpoChat.scrollHeight;
        });
}

// Envia a mensagem inserida no input sem recarregar a tela
function enviarComentario(event) {
    event.preventDefault();
    const input = document.getElementById('chatInput');
    const comentario = input.value.trim();
    const idPost = document.getElementById('chatPostId').value;
    
    if(!comentario) return;
    
    const formData = new FormData();
    formData.append('id_post', idPost);
    formData.append('comentario', comentario);
    
    fetch('index.php?acao=comentar', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(dados => {
        if(dados.sucesso) {
            input.value = "";
            document.getElementById(`contagem-comment-${idPost}`).innerText = dados.total;
            carregarComentariosDoPost(idPost);
        } else if(dados.erro === 'login_obrigatorio') {
            alert('🔒 Você precisa fazer login para enviar comentários!');
            window.location.href = 'form-login.php';
        }
    });
}

function abrirModalPost() { document.getElementById('modalNovaPostagem').style.display = 'flex'; document.body.style.overflow = 'hidden'; }
function fecharModalPost() { document.getElementById('modalNovaPostagem').style.display = 'none'; document.body.style.overflow = 'auto'; }
</script>

<footer>
    <p>&copy; 2026 FragForge - Todos os direitos reservados.</p>
</footer>