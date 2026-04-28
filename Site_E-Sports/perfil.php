<?php
session_start();

// 1. CONEXÃO
$conn = new mysqli("localhost", "root", "root", "fragforge");
if ($conn->connect_error) { die("Erro: " . $conn->connect_error); }

if (!isset($_GET['id'])) { die("Jogador não encontrado"); }
$id = intval($_GET['id']);

// 2. AUTO LOGIN
if (!isset($_SESSION['id_jogador'])) { $_SESSION['id_jogador'] = $id; }
$id_logado = $_SESSION['id_jogador'];
$ehDono = ($id === $id_logado);

// 3. UPLOAD DA FOTO (MEDIUMBLOB)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto']) && $ehDono) {
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $conteudo = file_get_contents($_FILES['foto']['tmp_name']);
        $update = $conn->prepare("UPDATE jogador SET foto_jogador = ? WHERE id_jogador = ?");
        $null = NULL; 
        $update->bind_param("bi", $null, $id_logado);
        $update->send_long_data(0, $conteudo);
        $update->execute();
        header("Location: perfil.php?id=" . $id);
        exit;
    }
}

// 4. BUSCA DADOS DO JOGADOR
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
$stmt->bind_param("i", $id);
$stmt->execute();
$jogador = $stmt->get_result()->fetch_assoc();

if (!$jogador) { die("Jogador não encontrado."); }

// 5. BUSCA POSTS
$query_posts = $conn->prepare("SELECT * FROM post WHERE id_jogador = ? ORDER BY id_post DESC");
$query_posts->bind_param("i", $id);
$query_posts->execute();
$posts = $query_posts->get_result();

// Função para renderizar arquivos BLOB
function renderizarArquivoBlob($binario, $isPost = false) {
    if (!$binario) return $isPost ? "" : "<span style='font-size:50px; color:#cbd5e1;'>👤</span>";
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($binario);
    $base64 = base64_encode($binario);
    $src = "data:$mime;base64,$base64";
    if (strpos($mime, 'image/') === 0) return "<img src='$src' style='width:100%; height:100%; object-fit:cover;'>";
    if (strpos($mime, 'video/') === 0) return "<video width='100%' height='100%' style='object-fit:cover' controls><source src='$src' type='$mime'></video>";
    return "<div style='text-align:center'><a href='$src' download='arquivo' style='font-size:12px; color:#2563eb; font-weight:bold;'>📄 BAIXAR ARQUIVO</a></div>";
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
        .perfil-foto { width: 140px; height: 140px; border-radius: 50%; border: 5px solid #2563eb; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        
        .nickname { font-size: 36px; font-weight: 800; margin: 0; color: #0f172a; display: flex; align-items: center; gap: 15px; }

        /* DESTAQUE NOS ÍCONES */
        .badge-wrapper {
            background-color: #dbeafe; /* Azul bem leve */
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

        /* Timeline */
        .posts-section { margin-top: 50px; }
        .section-title { font-size: 22px; color: #0f172a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ""; width: 6px; height: 24px; background: #2563eb; border-radius: 10px; }
        .post-card { background: #ffffff; padding: 25px; border-radius: 20px; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .post-media { max-width: 100%; border-radius: 15px; overflow: hidden; border: 1px solid #f1f5f9; }
        
        .btn-action { background: #2563eb; color: #ffffff; border: none; padding: 12px 25px; border-radius: 12px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-action:hover { background: #1d4ed8; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-voltar">← Voltar para o Início</a>

    <div class="card">
        <div class="header">
            <div class="perfil-foto">
                <?php echo renderizarArquivoBlob($jogador['foto_jogador']); ?>
            </div>
            
            <div class="info">
                <h1 class="nickname">
                    <?php echo htmlspecialchars($jogador['nickname_jogador']); ?>
                    
                    <?php if(!empty($jogador['icon_patente'])): ?>
                        <div class="badge-wrapper" title="Patente: <?php echo $jogador['nome_patente']; ?>">
                            <img src="<?php echo $jogador['icon_patente']; ?>" class="badge-icon">
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($jogador['icon_funcao'])): ?>
                        <div class="badge-wrapper" title="Função: <?php echo $jogador['nome_funcao']; ?>">
                            <img src="<?php echo $jogador['icon_funcao']; ?>" class="badge-icon">
                        </div>
                    <?php endif; ?>
                </h1>
                
                <div class="sub-info">
                    <span class="equipe-nome"><?php echo $jogador['nome_equipe'] ?? 'SEM EQUIPE'; ?></span> 
                    • <?php echo $jogador['nome_funcao'] ?? 'S/ Função'; ?> 
                    • <?php echo $jogador['nome_patente'] ?? 'S/ Patente'; ?>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <strong><?php echo number_format($jogador['pontuacao_jogador'], 0, '', '.'); ?></strong>
                <span>PONTUAÇÃO GLOBAL</span>
            </div>
            <div class="stat">
                <strong>#<?php echo htmlspecialchars($jogador['codigo_battlenet']); ?></strong>
                <span>BATTLE.NET ID</span>
            </div>
        </div>

        <?php if($ehDono): ?>
        <div style="margin-top: 30px; padding-top: 25px; border-top: 1px solid #f1f5f9;">
            <form method="POST" enctype="multipart/form-data">
                <p style="font-weight: bold; margin-bottom: 10px; font-size: 14px;">Atualizar Foto ou Mídia de Perfil</p>
                <input type="file" name="foto" style="margin-bottom: 15px; font-size: 14px;">
                <button type="submit" class="btn-action">ATUALIZAR AGORA</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="posts-section">
        <h2 class="section-title">Timeline de Atividade</h2>
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($p = $posts->fetch_assoc()): ?>
                <div class="post-card">
                    <p><?php echo htmlspecialchars($p['mensagem']); ?></p>
                    <?php if ($p['print_estatistica']): ?>
                        <div class="post-media"><?php echo renderizarArquivoBlob($p['print_estatistica'], true); ?></div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="post-card" style="text-align: center;"><p style="color: #94a3b8;">Sem posts recentes.</p></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>