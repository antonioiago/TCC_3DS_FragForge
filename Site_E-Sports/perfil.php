<?php
session_start();

// =========================
// 1. CONEXÃO SUPABASE (PDO)
// =========================
try {
    $conn = new PDO(
        "pgsql:host=aws-1-sa-east-1.pooler.supabase.com;port=5432;dbname=postgres;sslmode=require",
        "postgres.oxflxsewydmzxfieejdl",
        "3dsfr@gF0rg3"
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

// =========================
// 2. ID JOGADOR
// =========================
if (!isset($_GET['id'])) {
    die("Jogador não encontrado");
}

$id = (int) $_GET['id'];

// =========================
// 3. AUTO LOGIN
// =========================
if (!isset($_SESSION['id_jogador'])) {
    $_SESSION['id_jogador'] = $id;
}

$id_logado = $_SESSION['id_jogador'];
$ehDono = ($id === $id_logado);

// =========================
// 4. UPDATE DADOS (RANK/FUNÇÃO/BATTLE.NET)
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_atualizar_dados']) && $ehDono) {

    $id_patente = !empty($_POST['id_patente']) ? (int)$_POST['id_patente'] : null;
    $id_funcao = !empty($_POST['id_funcao']) ? (int)$_POST['id_funcao'] : null;
    $battlenet = !empty($_POST['codigo_battlenet']) ? trim($_POST['codigo_battlenet']) : null;

    $stmt = $conn->prepare("
        UPDATE jogador 
        SET id_patente = ?, id_funcao = ?, codigo_battlenet = ?
        WHERE id_jogador = ?
    ");

    $stmt->execute([$id_patente, $id_funcao, $battlenet, $id_logado]);

    header("Location: perfil.php?id=" . $id);
    exit;
}

// =========================
// 5. UPLOAD FOTO (BYTEA CORRIGIDO)
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto']) && $ehDono) {

    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        $conteudo = file_get_contents($_FILES['foto']['tmp_name']);

        $stmt = $conn->prepare("
            UPDATE jogador 
            SET foto_jogador = :foto 
            WHERE id_jogador = :id
        ");

        $stmt->bindValue(':foto', $conteudo, PDO::PARAM_LOB);
        $stmt->bindValue(':id', $id_logado, PDO::PARAM_INT);

        $stmt->execute();

        header("Location: perfil.php?id=" . $id);
        exit;
    }
}

// =========================
// 6. BUSCA JOGADOR
// =========================
$sql = "
SELECT 
    j.*, 
    f.nome_funcao, f.icon_funcao, 
    p.nome_patente, p.icon_patente, 
    e.nome_equipe
FROM jogador j
LEFT JOIN funcao f ON j.id_funcao = f.id_funcao
LEFT JOIN patente p ON j.id_patente = p.id_patente
LEFT JOIN equipe e ON j.id_equipe = e.id_equipe
WHERE j.id_jogador = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$jogador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jogador) {
    die("Jogador não encontrado.");
}

// =========================
// 7. SELECTS
// =========================
$todas_patentes = [];
$todas_funcoes = [];

if ($ehDono) {
    $todas_patentes = $conn->query("SELECT id_patente, nome_patente FROM patente ORDER BY id_patente ASC")
        ->fetchAll(PDO::FETCH_ASSOC);

    $todas_funcoes = $conn->query("SELECT id_funcao, nome_funcao FROM funcao ORDER BY nome_funcao ASC")
        ->fetchAll(PDO::FETCH_ASSOC);
}

// =========================
// 8. POSTS
// =========================
$stmt = $conn->prepare("SELECT * FROM post WHERE id_jogador = ? ORDER BY id_post DESC");
$stmt->execute([$id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// 9. FUNÇÃO BLOB (BYTEA)
// =========================
function renderizarArquivoBlob($binario, $isPost = false) {

    if (!$binario) {
        return $isPost
            ? ""
            : "<span style='font-size:50px; color:#cbd5e1;'>👤</span>";
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($binario);
    $base64 = base64_encode($binario);
    $src = "data:$mime;base64,$base64";

    if (strpos($mime, 'image/') === 0) {
        return "<img src='$src' style='width:100%;height:100%;object-fit:cover;'>";
    }

    if (strpos($mime, 'video/') === 0) {
        return "<video controls style='width:100%;height:auto;'><source src='$src' type='$mime'></video>";
    }

    return "<div style='text-align:center'>
                <a href='$src' download style='font-size:12px;color:#2563eb;font-weight:bold;'>
                    📄 BAIXAR ARQUIVO
                </a>
            </div>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil - <?php echo htmlspecialchars($jogador['nickname_jogador']); ?></title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .container { max-width: 850px; margin: 40px auto; padding: 0 20px; position: relative; }

        /* Botão Voltar */
        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #2563eb;
            font-weight: 600;
            background: #ffffff;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .btn-voltar:hover { background: #2563eb; color: #ffffff; transform: translateX(-5px); }

        /* Card Principal */
        .card { background: #ffffff; border-radius: 24px; padding: 40px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .header { display: flex; align-items: center; gap: 30px; }
        
        /* Estilização da Foto de Perfil Interativa */
        .perfil-foto { width: 140px; height: 140px; border-radius: 50%; border: 5px solid #2563eb; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); position: relative; }
        
        <?php if($ehDono): ?>
        .perfil-foto-dono { cursor: pointer; transition: all 0.3s ease; }
        .perfil-foto-dono:hover { filter: brightness(0.7); border-color: #1d4ed8; }
        .perfil-foto-dono::after { content: "📷"; position: absolute; font-size: 24px; opacity: 0; transition: opacity 0.3s ease; }
        .perfil-foto-dono:hover::after { opacity: 1; }
        <?php endif; ?>
        
        .nickname { font-size: 36px; font-weight: 800; margin: 0; color: #0f172a; display: flex; align-items: center; gap: 15px; }

        /* Ícones de Patente e Função */
        .badge-wrapper {
            background-color: #dbeafe;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #bfdbfe;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
            flex-shrink: 0;
        }
        .badge-icon { height: 38px; width: auto; object-fit: contain; }

        .sub-info { color: #64748b; margin-top: 10px; font-size: 16px; font-weight: 500; }
        .equipe-nome { color: #2563eb; background: #dbeafe; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
        
        .stats { display: flex; gap: 20px; margin-top: 35px; }
        .stat { background: #f8fafc; padding: 20px; border-radius: 18px; flex: 1; text-align: center; border: 1px solid #e2e8f0; }
        .stat strong { display: block; font-size: 26px; color: #2563eb; margin-bottom: 4px; }
        .stat span { font-size: 12px; color: #94a3b8; font-weight: bold; text-transform: uppercase; }

        /* Painel de Edição */
        .edit-panel { background: #f8fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 18px; margin-top: 30px; }
        .edit-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; }
        .form-control { padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

        .btn-action { background: #2563eb; color: #ffffff; border: none; padding: 12px 25px; border-radius: 12px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-action:hover { background: #1d4ed8; }

        /* Botão Enviar Estatísticas */
        .btn-criar-postagem {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn-criar-postagem:hover { background: #1d4ed8; }
        .btn-criar-postagem:active { transform: scale(0.98); }

        /* Timeline de Posts */
        .posts-section { margin-top: 40px; }
        .section-title { font-size: 22px; color: #0f172a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ""; width: 6px; height: 24px; background: #2563eb; border-radius: 10px; }
        .post-card { background: #ffffff; padding: 25px; border-radius: 20px; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .post-media { max-width: 100%; border-radius: 15px; overflow: hidden; border: 1px solid #f1f5f9; margin-top: 15px; }
        .post-footer { margin-top: 18px; padding-top: 12px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 6px; color: #64748b; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-voltar">← Voltar para o Início</a>

    <div class="card">
        <div class="header">
            <?php if($ehDono): ?>
            <form id="formFotoPerfil" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" name="foto" id="inputFotoPerfil" accept="image/*" onchange="enviarFormFoto()">
            </form>
            <?php endif; ?>

            <div class="perfil-foto <?php echo $ehDono ? 'perfil-foto-dono' : ''; ?>" <?php echo $ehDono ? 'onclick="dispararUpload()"' : ''; ?> title="<?php echo $ehDono ? 'Clique para mudar a foto de perfil' : ''; ?>">
                <?php echo renderizarArquivoBlob($jogador['foto_jogador']); ?>
            </div>
            
            <div class="info">
                <h1 class="nickname">
                    <?php echo htmlspecialchars($jogador['nickname_jogador']); ?>
                    
                    <?php if(!empty($jogador['icon_patente'])): ?>
                        <div class="badge-wrapper" title="Patente: <?php echo htmlspecialchars($jogador['nome_patente']); ?>">
                            <img src="<?php echo htmlspecialchars($jogador['icon_patente']); ?>" class="badge-icon">
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($jogador['icon_funcao'])): ?>
                        <div class="badge-wrapper" title="Função: <?php echo htmlspecialchars($jogador['nome_funcao']); ?>">
                            <img src="<?php echo htmlspecialchars($jogador['icon_funcao']); ?>" class="badge-icon">
                        </div>
                    <?php endif; ?>
                </h1>
                
                <div class="sub-info">
                    <span class="equipe-nome"><?php echo htmlspecialchars($jogador['nome_equipe'] ?? 'SEM EQUIPE'); ?></span> 
                    • <?php echo htmlspecialchars($jogador['nome_funcao'] ?? 'S/ Função'); ?> 
                    • <?php echo htmlspecialchars($jogador['nome_patente'] ?? 'S/ Patente'); ?>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <strong><?php echo number_format($jogador['pontuacao_jogador'], 0, '', '.'); ?></strong>
                <span>PONTUAÇÃO GLOBAL</span>
            </div>
            <div class="stat">
                <strong><?php echo htmlspecialchars($jogador['codigo_battlenet'] ?? 'Não informado'); ?></strong>
                <span>BATTLE.NET ID</span>
            </div>
        </div>

        <?php if($ehDono): ?>
        <div class="edit-panel">
            <p style="font-weight: bold; margin-top: 0; margin-bottom: 15px; font-size: 15px; color: #0f172a; display:flex; align-items:center; gap:6px;">⚙️ Gerenciar Dados do Perfil</p>
            <form method="POST" action="perfil.php?id=<?php echo $id; ?>">
                <div class="edit-grid">
                    <div class="form-group">
                        <label for="id_patente">Patente / Rank</label>
                        <select name="id_patente" id="id_patente" class="form-control">
                            <option value="">Selecione sua Patente</option>
                            <?php while($patente = $todas_patentes->fetch_assoc()){ ?>
                                <option value="<?php echo $patente['id_patente']; ?>" <?php echo ($jogador['id_patente'] == $patente['id_patente']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($patente['nome_patente']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_funcao">Função In-Game</label>
                        <select name="id_funcao" id="id_funcao" class="form-control">
                            <option value="">Selecione sua Função</option>
                            <?php while($funcao = $todas_funcoes->fetch_assoc()){ ?>
                                <option value="<?php echo $funcao['id_funcao']; ?>" <?php echo ($jogador['id_funcao'] == $funcao['id_funcao']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($funcao['nome_funcao']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="codigo_battlenet">Battle.net ID</label>
                        <input type="text" name="codigo_battlenet" id="codigo_battlenet" class="form-control" placeholder="Ex: Player#1234" value="<?php echo htmlspecialchars($jogador['codigo_battlenet'] ?? ''); ?>">
                    </div>
                </div>
                <div style="display: flex; gap: 15px; align-items: center; justify-content: space-between; margin-top: 20px;">
                    <button type="submit" name="acao_atualizar_dados" class="btn-action" style="flex: 1; padding: 12px;">Salvar Alterações do Perfil</button>
                    <button type="button" class="btn-criar-postagem" onclick="abrirModalEstatisticas()" style="padding: 12px 25px;">Enviar Estatísticas</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="posts-section">
        <h2 class="section-title">Timeline de Atividade</h2>
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($p = $posts->fetch_assoc()): ?>
                <div class="post-card">
                    <p style="margin-top: 0; font-size: 16px; line-height: 1.5;"><?php echo htmlspecialchars($p['mensagem']); ?></p>
                    
                    <?php if (!empty($p['print_estatistica'])): ?>
                        <div class="post-media">
                            <?php echo renderizarArquivoBlob($p['print_estatistica'], true); ?>
                        </div>
                    <?php endif; ?>

                    <div class="post-footer">
                        <span>❤️ <?php echo number_format($p['curtidas'] ?? 0, 0, '', '.'); ?> curtidas</span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="post-card" style="text-align: center; padding: 40px 20px;">
                <p style="color: #94a3b8; margin: 0;">Sem posts ou atividades recentes neste perfil.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="modalEstatisticas" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.75); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; width: 100%; max-width: 700px; height: 85vh; border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0;">
        
        <div style="padding: 18px 24px; background: #2563eb; display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #ffffff; font-weight: 700; font-family: 'Segoe UI', system-ui, sans-serif; font-size: 16px;">Calculadora de Estatísticas - FragForge</span>
            <button onclick="fecharModalEstatisticas()" style="background: none; border: none; color: #dbeafe; font-size: 30px; cursor: pointer; line-height: 1; padding: 0; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#dbeafe'">&times;</button>
        </div>

        <iframe src="pontuacao.php" style="width: 100%; flex: 1; border: none; background: #121212;"></iframe>
    </div>
</div>

<script>
// Funções para controle do clique na foto de perfil
function dispararUpload() {
    document.getElementById('inputFotoPerfil').click();
}

function enviarFormFoto() {
    const input = document.getElementById('inputFotoPerfil');
    if(input.files && input.files.length > 0) {
        document.getElementById('formFotoPerfil').submit();
    }
}

// Funções da calculadora
function abrirModalEstatisticas() {
    document.getElementById('modalEstatisticas').style.display = 'flex';
}

function fecharModalEstatisticas() {
    document.getElementById('modalEstatisticas').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('modalEstatisticas');
    if (event.target == modal) {
        fecharModalEstatisticas();
    }
}
</script>

</body>
</html>