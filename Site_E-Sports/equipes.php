<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $conn = new PDO('mysql:host=localhost;dbname=fragforge;charset=utf8', 'root', 'root');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<div style='text-align:center; padding:20px; color:red; font-weight:bold;'>⚠️ Erro de Conexão: " . $e->getMessage() . "</div>";
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_logado = isset($_SESSION['jogador']['id']);
$id_jogador = $usuario_logado ? $_SESSION['jogador']['id'] : null;
$jogador_info = null;
$solicitacao_pendente = null;
$eh_lider = false;

if ($usuario_logado) {
    $stmt = $conn->prepare("SELECT id_equipe FROM jogador WHERE id_jogador = ?");
    $stmt->execute([$id_jogador]);
    $jogador_info = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT id_equipe FROM solicitacao_equipe WHERE id_jogador = ?");
    $stmt->execute([$id_jogador]);
    $solicitacao_pendente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($jogador_info['id_equipe'])) {
        $stmt = $conn->prepare("SELECT id_jogador FROM jogador WHERE id_equipe = ? ORDER BY id_jogador ASC LIMIT 1");
        $stmt->execute([$jogador_info['id_equipe']]);
        $primeiro_membro = $stmt->fetchColumn();
        if ($primeiro_membro == $id_jogador) {
            $eh_lider = true;
        }
    }
}

// --- FUNÇÃO PARA ATUALIZAR A PONTUAÇÃO DA EQUIPE ---
function atualizarPontuacaoEquipe($conn, $id_equipe) {
    if (empty($id_equipe)) return;

    // Soma a pontuação de todos os jogadores pertencentes a esta equipe
    $stmtSoma = $conn->prepare("SELECT SUM(COALESCE(pontuacao_jogador, 0)) FROM jogador WHERE id_equipe = ?");
    $stmtSoma->execute([$id_equipe]);
    $total_pontos = $stmtSoma->fetchColumn();

    $total_pontos = $total_pontos ? $total_pontos : 0;

    // Atualiza o registro da equipe correspondente
    $stmtUpdate = $conn->prepare("UPDATE equipe SET pontuacao_equipe = ? WHERE id_equipe = ?");
    $stmtUpdate->execute([$total_pontos, $id_equipe]);
}

// --- CORREÇÃO/SINCRONIZAÇÃO: Atualiza a pontuação de todas as equipes existentes ao carregar a página ---
try {
    $stmtTodasEquipes = $conn->query("SELECT id_equipe FROM equipe");
    $equipes_ids = $stmtTodasEquipes->fetchAll(PDO::FETCH_COLUMN);
    foreach ($equipes_ids as $id_eq) {
        atualizarPontuacaoEquipe($conn, $id_eq);
    }
} catch (Exception $e) {
    // Falha silenciosa para não quebrar o layout se o banco falhar temporariamente
}


// --- PROCESSAMENTO DE AÇÕES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario_logado) {
    
    // Expulsar Jogador da Equipe (Ação do Líder)
    if (isset($_POST['acao_expulsar']) && !empty($_POST['id_membro_expulsar']) && $eh_lider) {
        $id_membro = $_POST['id_membro_expulsar'];
        if ($id_membro != $id_jogador) {
            $id_equipe_atual = $jogador_info['id_equipe'];
            $stmt = $conn->prepare("UPDATE jogador SET id_equipe = NULL WHERE id_jogador = ? AND id_equipe = ?");
            $stmt->execute([$id_membro, $id_equipe_atual]);
            
            atualizarPontuacaoEquipe($conn, $id_equipe_atual);
        }
        echo "<script>window.location.href='equipes.php';</script>";
        exit;
    }

    // Enviar mensagem no Mural
    if (isset($_POST['acao_mural']) && !empty($_POST['mensagem_mural'])) {
        if (!empty($jogador_info['id_equipe'])) {
            $msg = trim($_POST['mensagem_mural']);
            $stmt = $conn->prepare("INSERT INTO mural_equipe (id_equipe, id_jogador, mensagem) VALUES (?, ?, ?)");
            $stmt->execute([$jogador_info['id_equipe'], $id_jogador, $msg]);
            echo "<script>window.location.href='equipes.php';</script>";
            exit;
        }
    }
    
    // Criar Equipe
    if (isset($_POST['acao_criar']) && !empty($_POST['nome_equipe'])) {
        if (empty($jogador_info['id_equipe']) && !$solicitacao_pendente) {
            $nome_equipe = trim($_POST['nome_equipe']);
            $stmt = $conn->prepare("INSERT INTO equipe (nome_equipe, pontuacao_equipe) VALUES (?, 0)");
            $stmt->execute([$nome_equipe]);
            $id_nova_equipe = $conn->lastInsertId();
            
            $stmt = $conn->prepare("UPDATE jogador SET id_equipe = ? WHERE id_jogador = ?");
            $stmt->execute([$id_nova_equipe, $id_jogador]);

            atualizarPontuacaoEquipe($conn, $id_nova_equipe);
            echo "<script>window.location.href='equipes.php';</script>";
            exit;
        }
    }

    // Solicitar Entrada
    if (isset($_POST['acao_solicitar']) && !empty($_POST['id_equipe_solicitada'])) {
        if (empty($jogador_info['id_equipe']) && !$solicitacao_pendente) {
            $id_equipe_alvo = $_POST['id_equipe_solicitada'];
            $stmt = $conn->prepare("INSERT INTO solicitacao_equipe (id_jogador, id_equipe) VALUES (?, ?)");
            $stmt->execute([$id_jogador, $id_equipe_alvo]);
            echo "<script>window.location.href='equipes.php';</script>";
            exit;
        }
    }

    // Sair da Equipe
    if (isset($_POST['acao_sair'])) {
        $id_equipe_antiga = $jogador_info['id_equipe'] ?? null;

        $stmt = $conn->prepare("UPDATE jogador SET id_equipe = NULL WHERE id_jogador = ?");
        $stmt->execute([$id_jogador]);
        $stmt = $conn->prepare("DELETE FROM solicitacao_equipe WHERE id_jogador = ?");
        $stmt->execute([$id_jogador]);
        
        if ($id_equipe_antiga) {
            atualizarPontuacaoEquipe($conn, $id_equipe_antiga);
        }

        echo "<script>window.location.href='equipes.php';</script>";
        exit;
    }

    // Aceitar Jogador
    if (isset($_POST['acao_aceitar']) && !empty($_POST['id_candidato']) && $eh_lider) {
        $id_candidato = $_POST['id_candidato'];
        $id_equipe_atual = $jogador_info['id_equipe'];

        $stmt = $conn->prepare("UPDATE jogador SET id_equipe = ? WHERE id_jogador = ?");
        $stmt->execute([$id_equipe_atual, $id_candidato]);
        $stmt = $conn->prepare("DELETE FROM solicitacao_equipe WHERE id_jogador = ?");
        $stmt->execute([$id_candidato]);
        
        atualizarPontuacaoEquipe($conn, $id_equipe_atual);
        echo "<script>window.location.href='equipes.php';</script>";
        exit;
    }

    // Recusar Jogador
    if (isset($_POST['acao_recusar']) && !empty($_POST['id_candidato']) && $eh_lider) {
        $stmt = $conn->prepare("DELETE FROM solicitacao_equipe WHERE id_jogador = ? AND id_equipe = ?");
        $stmt->execute([$_POST['id_candidato'], $jogador_info['id_equipe']]);
        echo "<script>window.location.href='equipes.php';</script>";
        exit;
    }
}

// --- BUSCA MENSAGENS DO MURAL ---
$mensagens_mural = [];
if ($usuario_logado && !empty($jogador_info['id_equipe'])) {
    $stmt = $conn->prepare("
        SELECT m.mensagem, DATE_FORMAT(m.data_envio, '%d/%m %H:%i') as data, j.nickname_jogador 
        FROM mural_equipe m
        JOIN jogador j ON m.id_jogador = j.id_jogador
        WHERE m.id_equipe = ?
        ORDER BY m.id_mural DESC LIMIT 15
    ");
    $stmt->execute([$jogador_info['id_equipe']]);
    $mensagens_mural = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- BUSCA SOLICITAÇÕES PARA O LÍDER ---
$solicitacoes = [];
if ($eh_lider) {
    $stmt = $conn->prepare("SELECT j.id_jogador, j.nickname_jogador FROM solicitacao_equipe s JOIN jogador j ON s.id_jogador = j.id_jogador WHERE s.id_equipe = ?");
    $stmt->execute([$jogador_info['id_equipe']]);
    $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- QUERY DE EQUIPES ---
$query_equipes = "
    SELECT e.id_equipe, e.nome_equipe, COALESCE(e.pontuacao_equipe, 0) AS pontuacao_equipe, COUNT(j.id_jogador) AS total_membros,
           GROUP_CONCAT(
               CONCAT(
                   j.id_jogador, ':::', j.nickname_jogador, ' [Patente: ', COALESCE(p.nome_patente, 'Sem Patente'), ' | Função: ', COALESCE(f.nome_funcao, 'Não informada'), ']'
               ) SEPARATOR '|||'
           ) AS lista_membros
    FROM equipe e
    LEFT JOIN jogador j ON e.id_equipe = j.id_equipe
    LEFT JOIN patente p ON j.id_patente = p.id_patente
    LEFT JOIN funcao f ON j.id_funcao = f.id_funcao
    GROUP BY e.id_equipe, e.nome_equipe, e.pontuacao_equipe
    ORDER BY pontuacao_equipe DESC, total_membros DESC, e.nome_equipe ASC
";
$todas_equipes = $conn->query($query_equipes)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    header, header a, header span, header li, header div {
        color: #ffffff !important;
    }

    body { background: #f8fafc; color: #1e293b; font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; padding: 0; }
    
    .teams-container { 
        max-width: 1000px; 
        margin: 60px auto 100px auto; 
        padding: 20px 20px 0 20px; 
        box-sizing: border-box; 
        display: grid; 
        grid-template-columns: 1fr; 
        gap: 24px; 
    }
    
    @media (min-width: 768px) { .teams-container { grid-template-columns: 2fr 1fr; } }
    
    .panel-action { background: #ffffff; border: 1px solid #e2e8f0; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .panel-title { font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 15px; color: #0f172a; }
    
    .input-group { display: flex; gap: 12px; }
    .input-text { flex: 1; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
    
    .btn { padding: 10px 20px; font-weight: 700; font-size: 13px; border: none; border-radius: 8px; cursor: pointer; text-transform: uppercase; }
    .btn-create { background: #2563eb; color: #ffffff; }
    .btn-join { background: #10b981; color: #ffffff; width: 100%; margin-top: 10px; }
    .btn-danger { background: #ef4444; color: #ffffff; }
    .btn-kick { background: #fee2e2; color: #ef4444; padding: 4px 8px; font-size: 11px; text-transform: none; font-weight: 600; border-radius: 4px; border: 1px solid #fca5a5; }
    .btn-kick:hover { background: #ef4444; color: #fff; }
    
    .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .team-card { background: #ffffff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; transition: transform 0.2s, border-color 0.2s; }
    .team-card:hover { transform: translateY(-2px); border-color: #2563eb; }
    .team-name { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0; }
    
    .mural-box { max-height: 300px; overflow-y: auto; background: #f1f5f9; padding: 14px; border-radius: 8px; margin-bottom: 12px; display: flex; flex-direction: column-reverse; gap: 8px; }
    .msg-chat { background: #fff; padding: 10px 12px; border-radius: 8px; border-left: 4px solid #2563eb; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 9999; }
    .modal-content { background: white; padding: 25px; border-radius: 12px; max-width: 450px; width: 90%; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    
    .member-item { background: #f8fafc; padding: 10px; border-radius: 6px; margin-bottom: 8px; border-left: 3px solid #2563eb; font-size: 14px; display: flex; justify-content: space-between; align-items: center; }
    .member-meta { font-size: 11px; color: #64748b; margin-top: 4px; display: block; }

    footer { text-align: center; padding: 30px 20px; color: #64748b; font-size: 14px; background: #ffffff; border-top: 1px solid #e2e8f0; }
</style>

<main class="teams-container">

    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <div class="panel-action">
            <?php if (!$usuario_logado): ?>
                <div style="text-align:center; color:#1e40af; font-weight: 500;">👋 <a href="form-login.php" style="text-decoration:underline; font-weight:bold; color: #2563eb !important;">Faça login</a> para interagir e gerenciar equipes.</div>
            <?php else: ?>
                <?php if (empty($jogador_info['id_equipe']) && !$solicitacao_pendente): ?>
                    <h3 class="panel-title">🛡️ Fundar Nova Equipe</h3>
                    <form method="POST" action="equipes.php" class="input-group">
                        <input type="text" name="nome_equipe" placeholder="Nome da equipe..." class="input-text" required>
                        <button type="submit" name="acao_criar" class="btn btn-create">+ Criar</button>
                    </form>
                <?php elseif ($solicitacao_pendente): ?>
                    <h3 class="panel-title">⏳ Inscrição Enviada</h3>
                    <p style="font-size:14px; color:#64748b; margin-bottom: 15px;">Seu pedido está aguardando a aprovação do líder.</p>
                    <form method="POST" action="equipes.php"><button type="submit" name="acao_sair" class="btn btn-danger">Cancelar Pedido</button></form>
                <?php else: ?>
                    <h3 class="panel-title">Status: Membro Ativo <?php echo $eh_lider ? '(Líder)' : ''; ?></h3>
                    <form method="POST" action="equipes.php"><button type="submit" name="acao_sair" class="btn btn-danger" onclick="return confirm('Deseja sair da equipe?')">❌ Abandonar Equipe</button></form>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($eh_lider && count($solicitacoes) > 0): ?>
            <div class="panel-action" style="border-color: #bbf7d0;">
                <h3 class="panel-title" style="color: #166534;">📩 Pedidos de Entrada</h3>
                <?php foreach ($solicitacoes as $req): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; background:#f0fdf4; padding:12px; border-radius:8px;">
                        <span style="font-weight: 600;">🎮 <?php echo htmlspecialchars($req['nickname_jogador']); ?></span>
                        <form method="POST" action="equipes.php" style="margin:0; display:flex; gap:6px;">
                            <input type="hidden" name="id_candidato" value="<?php echo $req['id_jogador']; ?>">
                            <button type="submit" name="acao_aceitar" class="btn" style="background:#10b981; color:#fff; padding:6px 12px;">Aceitar</button>
                            <button type="submit" name="acao_recusar" class="btn" style="background:#ef4444; color:#fff; padding:6px 12px;">X</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div>
            <h2 style="font-size: 22px; color: #0f172a; margin-top: 0; margin-bottom: 15px; font-weight: 800;">Guildas Registradas</h2>
            <p style="font-size: 14px; color: #64748b; margin-top: -10px; margin-bottom: 20px;">Clique no card de qualquer guilda para listar os integrantes, ranks e funções.</p>
            
            <div class="team-grid">
                <?php foreach ($todas_equipes as $equipe): ?>
                    <div class="team-card" onclick="abrirMembros('<?php echo htmlspecialchars($equipe['nome_equipe']); ?>', '<?php echo htmlspecialchars($equipe['lista_membros'] ?? ''); ?>', '<?php echo $equipe['id_equipe']; ?>')">
                        <div>
                            <h4 class="team-name"><?php echo htmlspecialchars($equipe['nome_equipe']); ?></h4>
                            <div style="font-size:13px; color:#64748b; margin-bottom: 5px;">👥 <?php echo $equipe['total_membros']; ?> membros</div>
                            <!-- CORREÇÃO: Mudado de Puntos para Pontos -->
                            <div style="font-size:13px; color:#2563eb; font-weight: bold;">⭐ Pontos: <?php echo $equipe['pontuacao_equipe']; ?></div>
                        </div>
                        <?php if ($usuario_logado && empty($jogador_info['id_equipe']) && !$solicitacao_pendente): ?>
                            <form method="POST" action="equipes.php" onclick="event.stopPropagation();" style="margin:0;">
                                <input type="hidden" name="id_equipe_solicitada" value="<?php echo $equipe['id_equipe']; ?>">
                                <button type="submit" name="acao_solicitar" class="btn btn-join">Pedir para Entrar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div>
        <?php if ($usuario_logado && !empty($jogador_info['id_equipe'])): ?>
            <div class="panel-action">
                <h3 class="panel-title">💬 Mural da sua Equipe</h3>
                <div class="mural-box">
                    <?php if(count($mensagens_mural) > 0): ?>
                        <?php foreach($mensagens_mural as $msg): ?>
                            <div class="msg-chat">
                                <strong><?php echo htmlspecialchars($msg['nickname_jogador']); ?></strong> 
                                <span style="font-size:10px; color:#94a3b8; float:right;"><?php echo $msg['data']; ?></span>
                                <p style="margin: 6px 0 0 0; display:block; line-height: 1.4; color: #334155;"><?php echo htmlspecialchars($msg['mensagem']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size:13px; color:#94a3b8; text-align:center; margin:auto 0;">Mural limpo. Deixe um recado para o time!</p>
                    <?php endif; ?>
                </div>
                <form method="POST" action="equipes.php">
                    <div style="display:flex; gap:6px;">
                        <input type="text" name="mensagem_mural" placeholder="Digite um aviso..." class="input-text" required maxlength="250">
                        <button type="submit" name="acao_mural" class="btn btn-create" style="padding:10px;">Enviar</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="panel-action" style="text-align:center; color:#64748b; font-size:14px; line-height: 1.5; padding: 30px 20px;">
                🔒 <br><strong style="color: #334155; display:block; margin-top:5px;">Mural Restrito</strong> Junte-se a uma guilda para liberar o bate-papo exclusivo dos membros.
            </div>
        <?php endif; ?>
    </div>
</main>

<div id="modalMembros" class="modal" onclick="fecharMembros()">
    <div class="modal-content" onclick="event.stopPropagation();">
        <h3 id="modalNomeEquipe" style="margin-top:0; color:#0f172a; font-weight: 800;"></h3>
        <p style="font-size:13px; color:#64748b; font-weight:bold; margin-bottom:10px;">Line-up / Membros Ativos:</p>
        <div id="modalLista" style="max-height:280px; overflow-y:auto; padding-right:5px;"></div>
        <button onclick="fecharMembros()" class="btn" style="background:#cbd5e1; color:#334155; width:100%; margin-top:15px;">Fechar</button>
    </div>
</div>

<script>
const jogadorLogadoId = <?php echo json_encode($id_jogador); ?>;
const ehLiderLogado = <?php echo json_encode($eh_lider); ?>;
const equipeLogadoId = <?php echo json_encode($jogador_info['id_equipe'] ?? null); ?>;

function abrirMembros(nome, membrosString, idEquipeCard) {
    document.getElementById('modalNomeEquipe').innerText = "🛡️ " + nome;
    const listaContainer = document.getElementById('modalLista');
    listaContainer.innerHTML = "";

    if (!membrosString || membrosString.trim() === "") {
        listaContainer.innerHTML = "<p style='color:#94a3b8; font-size:13px;'>Nenhum membro ativo nesta equipe.</p>";
    } else {
        const membros = membrosString.split('|||');
        membros.forEach(membro => {
            const partesIniciais = membro.split(':::');
            const idMembro = partesIniciais[0];
            
            const infoRestante = partesIniciais[1] ? partesIniciais[1].split(' [') : ['', ''];
            const nick = infoRestante[0];
            const meta = infoRestante[1] ? infoRestante[1].replace(']', '') : 'Patente: N/A | Função: N/A';

            const div = document.createElement('div');
            div.className = 'member-item';
            
            let itemHTML = `<div><strong>🎮 ${nick}</strong><span class='member-meta'>🏅 ${meta}</span></div>`;
            
            if (ehLiderLogado && equipeLogadoId == idEquipeCard && idMembro != jogadorLogadoId) {
                itemHTML += `
                    <form method="POST" action="equipes.php" style="margin:0;" onsubmit="return confirm('Tem certeza que deseja expulsar ${nick} da equipe?')">
                        <input type="hidden" name="id_membro_expulsar" value="${idMembro}">
                        <button type="submit" name="acao_expulsar" class="btn btn-kick">Expulsar</button>
                    </form>
                `;
            }
            
            div.innerHTML = itemHTML;
            listaContainer.appendChild(div);
        });
    }
    document.getElementById('modalMembros').style.display = 'flex';
}

function fecharMembros() {
    document.getElementById('modalMembros').style.display = 'none';
}
</script>

<footer>
    <p>&copy; 2026 FragForge - Todos os direitos reservados.</p>
</footer>