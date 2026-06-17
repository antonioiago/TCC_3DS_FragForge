<?php
// ======================================================
// SESSÃO
// ======================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✔ PADRÃO ÚNICO DE LOGIN
$id_jogador = $_SESSION['jogador']['id'] ?? null;
$usuario_logado = $id_jogador !== null;

// ======================================================
// CONEXÃO SUPABASE (POSTGRES)
// ======================================================
try {
    $host = "aws-1-sa-east-1.pooler.supabase.com";
    $port = "5432";
    $dbname = "postgres";
    $user = "postgres.oxflxsewydmzxfieejdl";
    $password = "3dsfr@gF0rg3"; // (recomendado mover para env)

    $instancia = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password
    );

    $instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (Exception $e) {
    echo "<div style='text-align:center;color:red;padding:20px;'>Erro conexão banco</div>";
    exit;
}

// ======================================================
// CURTIDAS AJAX
// ======================================================
if (
    isset($_GET['acao']) &&
    $_GET['acao'] === 'curtir' &&
    isset($_GET['id_post'])
) {

    header('Content-Type: application/json');

    if (!$usuario_logado) {
        echo json_encode(['sucesso' => false, 'erro' => 'login_obrigatorio']);
        exit;
    }

    $id_post = (int) $_GET['id_post'];

    if (!isset($_SESSION['curtidas_usuario'])) {
        $_SESSION['curtidas_usuario'] = [];
    }

    if (isset($_SESSION['curtidas_usuario'][$id_post])) {

        $stmt = $instancia->prepare("
            UPDATE post
            SET curtidas = GREATEST(0, curtidas - 1)
            WHERE id_post = ?
        ");
        $stmt->execute([$id_post]);

        unset($_SESSION['curtidas_usuario'][$id_post]);

        $status = 'descurtido';

    } else {

        $stmt = $instancia->prepare("
            UPDATE post
            SET curtidas = COALESCE(curtidas, 0) + 1
            WHERE id_post = ?
        ");
        $stmt->execute([$id_post]);

        $_SESSION['curtidas_usuario'][$id_post] = true;

        $status = 'curtido';
    }

    $stmt = $instancia->prepare("SELECT curtidas FROM post WHERE id_post = ?");
    $stmt->execute([$id_post]);
    $curtidas = (int)$stmt->fetchColumn();

    echo json_encode([
        'sucesso' => true,
        'status' => $status,
        'curtidas' => $curtidas
    ]);

    exit;
}

// ======================================================
// COMENTÁRIOS AJAX
// ======================================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'comentar_ajax'
) {

    header('Content-Type: application/json');

    if (!$usuario_logado) {
        echo json_encode(['sucesso' => false, 'erro' => 'login_obrigatorio']);
        exit;
    }

    $id_post = (int) $_POST['id_post'];
    $texto = trim($_POST['texto']);

    if ($texto === '') {
        echo json_encode(['sucesso' => false]);
        exit;
    }

    $stmt = $instancia->prepare("
        INSERT INTO comentario (id_post, id_jogador, texto)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([$id_post, $id_jogador, $texto]);

    echo json_encode([
        'sucesso' => true,
        'nickname' => $_SESSION['jogador']['nickname'] ?? 'Usuario',
        'texto' => $texto
    ]);

    exit;
}

// Includes
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';

$ordenar = $_GET['ordenar'] ?? 'recente';
$funcao  = $_GET['funcao'] ?? 'todas';

// ======================================================
// POSTAGEM (CORRIGIDA)
// ======================================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['acao_postar']) &&
    $usuario_logado
) {

    $mensagem = trim($_POST['mensagem']);

    $print_estatistica = null;
    $jogada = null;

    // pasta local
    if (!is_dir("uploads")) {
        mkdir("uploads", 0755, true);
    }

    // ================= IMAGEM =================
if (!empty($_FILES['print_estatistica']['name'])) {

    $ext = pathinfo($_FILES['print_estatistica']['name'], PATHINFO_EXTENSION);
    $nome = uniqid("img_") . "." . $ext;

    move_uploaded_file($_FILES['print_estatistica']['tmp_name'], "uploads/" . $nome);

    $print_estatistica = "uploads/" . $nome;
}

// ================= VÍDEO =================
if (!empty($_FILES['jogada']['name'])) {

    $ext = pathinfo($_FILES['jogada']['name'], PATHINFO_EXTENSION);
    $nome = uniqid("vid_") . "." . $ext;

    move_uploaded_file($_FILES['jogada']['tmp_name'], "uploads/" . $nome);

    $jogada = "uploads/" . $nome;
}

    if (!empty($mensagem)) {

        $stmt = $instancia->prepare("
            INSERT INTO post (
                mensagem,
                print_estatistica,
                jogada,
                id_jogador,
                curtidas
            )
            VALUES (?, ?, ?, ?, 0)
        ");

        $stmt->execute([
            $mensagem,
            $print_estatistica,
            $jogada,
            $id_jogador
        ]);

        header("Location: index.php");
        exit;
    }
}
?>
<link rel="stylesheet" href="styles/index.css">

<style>
    /* Forçar cabeçalho branco */
    header, header a, header span, header li, header div { color: #ffffff !important; }

    .card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .card-link:hover {
        text-decoration: none;
        color: inherit;
    }

    /* --- ESTILIZAÇÃO DO NOVO MODAL DE POSTAGEM --- */
    .modal-post { 
        display: none; 
        position: fixed; 
        top: 0; left: 0; 
        width: 100%; height: 100%; 
        background: rgba(15, 23, 42, 0.6); 
        backdrop-filter: blur(4px); 
        justify-content: center; 
        align-items: center; 
        z-index: 9999; 
    }
    .modal-post-content { 
        background: #ffffff; 
        width: 90%; 
        max-width: 550px; 
        padding: 24px; 
        border-radius: 16px; 
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); 
        position: relative; 
        animation: surgimentoRapido 0.2s ease-out;
    }
    
    @keyframes surgimentoRapido {
        from { transform: scale(0.96); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .modal-post-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; }
    .modal-post-title { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
    .btn-close-modal { background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; line-height: 1; }
    .btn-close-modal:hover { color: #475569; }

    .textarea-post { width: 100%; height: 120px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; font-size: 15px; outline: none; resize: none; font-family: inherit; box-sizing: border-box; color: #1e293b; }
    .textarea-post:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    
    .file-input-group { margin-top: 14px; display: flex; flex-direction: column; gap: 6px; }
    .file-label { font-size: 13px; font-weight: 700; color: #475569; }
    .file-field { font-size: 13px; color: #64748b; }

    .btn-submit-post { background: #2563eb; color: #ffffff; border: none; width: 100%; padding: 12px; font-weight: 700; font-size: 14px; border-radius: 8px; cursor: pointer; text-transform: uppercase; margin-top: 20px; transition: background 0.2s; }
    .btn-submit-post:hover { background: #1d4ed8; }

    /* Estilos do Botão Interativo de Curtida e Comentário */
    .post-footer {
        display: flex;
        gap: 10px;
        margin-top: 12px;
    }
    .btn-like, .btn-comment-toggle {
        background: #334155;
        border: none;
        color: #f1f5f9;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 20px;
        transition: all 0.2s ease;
    }
    .btn-like:hover, .btn-comment-toggle:hover {
        background: #475569;
        transform: translateY(-1px);
    }
    .btn-like.active {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* --- ESTILOS DO PAINEL DE COMENTÁRIOS --- */
    .box-comentarios {
        background: #151f32;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid #334155;
    }
    .lista-comentarios {
        max-height: 250px;
        overflow-y: auto;
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .item-comentario {
        background: #1e293b;
        padding: 10px 14px;
        border-radius: 8px;
        border-left: 3px solid #2563eb;
        font-size: 14px;
    }
    .item-comentario strong {
        color: #60a5fa;
    }
    .form-comentario {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }
    .input-comentario {
        flex: 1;
        background: #1e293b;
        border: 1px solid #475569;
        border-radius: 6px;
        padding: 8px 12px;
        color: white;
        font-size: 14px;
        outline: none;
    }
    .input-comentario:focus {
        border-color: #2563eb;
    }
    .btn-enviar-comentario {
        background: #2563eb;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        font-size: 14px;
    }
    .btn-enviar-comentario:hover {
        background: #1d4ed8;
    }
</style>

<main class="not">
    <aside class="news-sidebar">
        
        <a href="https://overwatch.blizzard.com/pt-br/news/24271881/lute-com-garrinhas-fofas-contra-o-reinado-da-talon-3a-temporada-na-toca-do-tigre/" class="card-link">
            <div class="card">
                <img src="https://bnetcmsus-a.akamaihd.net/cms/blog_header/8z/8Z9HILBLP3J81781145671355.jpg" alt="Conquest">
                <div class="card-content">
                    <h3>Nova temporada</h3>
                    <p>Lute com Garrinhas Fofas contra o Reinado da Talon – 3ª Temporada: Na Toca do Tigre</p>
                </div>
            </div>
        </a>
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

        
    </aside>

    <section class="timeline">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 16px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
            <form method="GET" action="" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 160px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:4px; text-transform:uppercase;">Focar/Ordenar por:</label>
                    <select name="ordenar" onchange="this.form.submit()" style="width:100%; padding:8px 12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; color:#1e293b; font-weight:600; font-size:14px; outline:none;">
                        <option value="recente" <?php echo $ordenar === 'recente' ? 'selected' : ''; ?>>🕒 Postagens Recentes</option>
                        <option value="mais_curtidas" <?php echo $ordenar === 'mais_curtidas' ? 'selected' : ''; ?>>❤️ Mais Curtidas</option>
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
                
                <?php if(
                    ($ordenar ?? 'recente') !== 'recente' ||
                    ($funcao ?? 'todas') !== 'todas'
                ): ?>
    <div style="margin-top: 18px;">
        <a href="index.php"
           style="color:#2563eb;text-decoration:none;font-size:13px;font-weight:700;border-bottom:2px dashed #2563eb;padding-bottom:2px;">
            Limpar Filtros
        </a>
    </div>
<?php endif; ?>
            </form>
        </div>

        <button class="btn-criar-postagem" onclick="abrirModalPost()" style="background: #2563eb; color: #ffffff; width:100%; border-radius:12px; padding:12px; font-weight:bold; margin-bottom: 20px; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(37,99,235,0.2);">
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

    // ORDENAÇÃO
    switch ($ordenar) {
        case 'mais_curtidas':
            $orderBySql = "ORDER BY p.curtidas DESC, p.id_post DESC";
            break;

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
        SELECT 
            p.id_post,
            p.mensagem,
            p.curtidas,
            j.nickname_jogador,
            j.id_jogador,
            j.foto_jogador,
            p.print_estatistica,
            p.jogada,
            f.icon_funcao,
            pa.icon_patente
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

    if ($resultados && count($resultados) > 0) {

        foreach ($resultados as $row) {

            $id_post_atual = $row['id_post'];
            $qtd_curtidas = (int)($row['curtidas'] ?? 0);

            $usuario_ja_curtiu = isset($_SESSION['curtidas_usuario'][$id_post_atual]);
            $classe_ativa = $usuario_ja_curtiu ? 'active' : '';
            $emoji_coracao = $usuario_ja_curtiu ? '❤️' : '🤍';

            // quantidade de comentários
            $stmt_qtd_c = $instancia->prepare("SELECT COUNT(*) FROM comentario WHERE id_post = ?");
            $stmt_qtd_c->execute([$id_post_atual]);
            $qtd_comentarios = (int)$stmt_qtd_c->fetchColumn();

            echo "<div class='post' style='background:#1e293b;border-radius:12px;padding:20px;color:white;margin-bottom:20px;'>";

            // HEADER
            echo "<div class='post-header' style='display:flex;align-items:center;gap:12px;margin-bottom:12px;'>";

            // FOTO DO USUÁRIO (continua base64 porque ainda está no banco)
           if (!empty($row['foto_jogador'])) {

    echo "<img 
            src='" . htmlspecialchars($row['foto_jogador'], ENT_QUOTES, 'UTF-8') . "' 
            style='width:50px;height:50px;border-radius:50%;object-fit:cover;' 
            alt='Foto de perfil'>";

} else {

    echo "<div style='width:50px;height:50px;border-radius:50%;
                    background:#e2e8f0;display:flex;
                    align-items:center;justify-content:center;
                    color:#64748b;font-weight:bold;'>
            👤
          </div>";
}

            echo "<div style='display:flex;flex-direction:column;'>";
            echo "<strong>
                    <a href='perfil.php?id=".$row['id_jogador']."' 
                       style='color:white;text-decoration:none;'>
                        ".htmlspecialchars($row['nickname_jogador'], ENT_QUOTES, 'UTF-8')."
                    </a>
                  </strong>";
            echo "</div>";

            echo "</div>";

            // MENSAGEM
            echo "<p style='margin:10px 0;'>".htmlspecialchars($row['mensagem'] ?? '', ENT_QUOTES, 'UTF-8')."</p>";

            /* =========================================================
               🖼 IMAGEM (AGORA URL SUPABASE STORAGE)
            ========================================================= */
            if (!empty($row['print_estatistica'])) {
                echo "<img class='post-img'
                        src='".htmlspecialchars($row['print_estatistica'], ENT_QUOTES, 'UTF-8')."'
                        style='max-width:100%;border-radius:8px;margin-top:10px;'>";
            }

            /* =========================================================
               🎥 VÍDEO (AGORA URL SUPABASE STORAGE)
            ========================================================= */
            if (!empty($row['jogada'])) {
                echo "<div style='margin-top:10px;'>";
                echo "<video controls style='width:100%;border-radius:8px;background:black;'>";
                echo "<source src='".htmlspecialchars($row['jogada'], ENT_QUOTES, 'UTF-8')."' type='video/mp4'>";
                echo "Seu navegador não suporta vídeos.";
                echo "</video>";
                echo "</div>";
            }

            // BOTÕES
            echo "<div class='post-footer'>";

            echo "<button
        id='btn-like-{$id_post_atual}'
        class='btn-like {$classe_ativa}'
        onclick='alternarCurtida({$id_post_atual})'>";

        echo "<span id='emoji-like-{$id_post_atual}'>{$emoji_coracao}</span>";

        echo "&nbsp;";

        echo "<span id='contagem-like-{$id_post_atual}'>{$qtd_curtidas}</span>";

        echo " Curtidas";

        echo "</button>";

            echo "<button class='btn-comment-toggle' onclick='toggleComentarios({$id_post_atual})'>";
            echo "💬 Comentários";
            echo "</button>";

            echo "</div>";

            // COMENTÁRIOS
            echo "<div id='box-comentarios-{$id_post_atual}' class='box-comentarios' style='display:none;'>";

            $stmt_c = $instancia->prepare("
                SELECT c.texto, j.nickname_jogador
                FROM comentario c
                JOIN jogador j ON c.id_jogador = j.id_jogador
                WHERE c.id_post = ?
                ORDER BY c.id_comentario ASC
            ");
            $stmt_c->execute([$id_post_atual]);
            $comentarios = $stmt_c->fetchAll(PDO::FETCH_ASSOC);

            if ($comentarios) {
                foreach ($comentarios as $com) {
                    echo "<div class='item-comentario'>";
                    echo "<strong>@".htmlspecialchars($com['nickname_jogador'], ENT_QUOTES, 'UTF-8').":</strong> ";
                    echo htmlspecialchars($com['texto'], ENT_QUOTES, 'UTF-8');
                    echo "</div>";
                }
            } else {
                echo "<p style='color:#64748b;font-size:13px;'>Nenhum comentário ainda.</p>";
            }

            if ($usuario_logado) {

    echo "
    <form
        class='form-comentario'
        onsubmit='enviarComentario(event, {$id_post_atual})'>

        <input
            type='text'
            id='input-comentario-{$id_post_atual}'
            class='input-comentario'
            placeholder='Escreva um comentário...'
            required>

        <button
            type='submit'
            class='btn-enviar-comentario'>
            Enviar
        </button>

    </form>";
}

echo "</div>";
echo "</div>";
        }

    } else {
        echo "<div style='text-align:center;padding:40px;background:#1e293b;color:#64748b;border-radius:12px;'>
                Nenhuma postagem encontrada.
              </div>";
    }

} catch (Exception $e) {
    echo "<div style='text-align:center;padding:20px;color:white;'>
            Erro ao carregar feed.
          </div>";
}
?>
        </div>
    </section>
</main>

<div id="modalNovaPostagem" class="modal-post" onclick="fecharModalPost()">
    <div class="modal-content modal-post-content" onclick="event.stopPropagation();">
        <div class="modal-post-header">
            <h3 class="modal-post-title">Nova Postagem</h3>
            <button class="btn-close-modal" onclick="fecharModalPost()">&times;</button>
        </div>

        <?php if ($usuario_logado): ?>
            <form method="POST" action="index.php" enctype="multipart/form-data">
                <textarea name="mensagem" class="textarea-post" placeholder="No que você está pensando hoje? Compartilhe conquistas ou jogadas..." required></textarea>
                
                <div class="file-input-group">
                    <label class="file-label">📸 Print de Estatística (Imagem):</label>
                    <input type="file" name="print_estatistica" accept="image/*" class="file-field">
                </div>

                <div class="file-input-group">
                    <label class="file-label">🎬 Highlight da Jogada (Vídeo MP4):</label>
                    <input type="file" name="jogada" accept="video/mp4" class="file-field">
                </div>

                <button type="submit" name="acao_postar" class="btn-submit-post">Publicar no Feed</button>
            </form>
        <?php else: ?>
            <div style="text-align:center; padding: 20px 0; color:#475569;">
                🔒 <br><p style="margin-top:10px;">Você precisa fazer <a href="form-login.php" style="color:#2563eb; font-weight:bold; text-decoration:underline;">Login</a> para poder criar uma postagem.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Exibe ou oculta a caixa de comentários ao clicar no botão
function toggleComentarios(idPost) {
    const box = document.getElementById(`box-comentarios-${idPost}`);
    if (box.style.display === "none" || box.style.display === "") {
        box.style.display = "block";
    } else {
        box.style.display = "none";
    }
}

// Controle assíncrono das curtidas via AJAX Fetch API
function alternarCurtida(idPost) {
    fetch(`index.php?acao=curtir&id_post=${idPost}`)
        .then(response => response.json())
        .then(dados => {
            if (dados.sucesso) {
                const botao = document.getElementById(`btn-like-${idPost}`);
                const emoji = document.getElementById(`emoji-like-${idPost}`);
                const textoContagem = document.getElementById(`contagem-like-${idPost}`);

                // Atualiza o contador na tela com o novo valor retornado pelo PHP
                textoContagem.innerText = dados.curtidas;

                // Modifica o estilo visual conforme o estado retornado
                if (dados.status === 'curtido') {
                    botao.classList.add('active');
                    emoji.innerText = '❤️';
                } else {
                    botao.classList.remove('active');
                    emoji.innerText = '🤍';
                }
            } else if (dados.erro === 'login_obrigatorio') {
                alert('🔒 Você precisa fazer login para poder curtir as publicações!');
                window.location.href = 'form-login.php';
            }
        })
        .catch(erro => console.error('Erro na requisição da curtida:', erro));
}

// Funções JavaScript de controle da janela modal
function abrirModalPost() {
    document.getElementById('modalNovaPostagem').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fecharModalPost() {
    document.getElementById('modalNovaPostagem').style.display = 'none';
    document.body.style.overflow = 'auto';
}
function enviarComentario(event, idPost) {

    event.preventDefault();

    const input = document.getElementById(
        `input-comentario-${idPost}`
    );

    const texto = input.value.trim();

    if (!texto) return;

    const formData = new FormData();

    formData.append('acao', 'comentar_ajax');
    formData.append('id_post', idPost);
    formData.append('texto', texto);

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(dados => {

        if (!dados.sucesso) return;

        const lista = document.querySelector(
            `#box-comentarios-${idPost}`
        );

        const novoComentario =
            document.createElement('div');

        novoComentario.className =
            'item-comentario';

        novoComentario.innerHTML =
            `<strong>@${dados.nickname}:</strong> ${dados.texto}`;

        lista.insertBefore(
            novoComentario,
            lista.querySelector('.form-comentario')
        );

        input.value = '';
    })
    .catch(erro => {
        console.error(erro);
    });
}
</script>

<footer>
    <p>&copy; 2026 FragForge - Todos os direitos reservados.</p>
</footer>