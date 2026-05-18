<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';

// Captura dos filtros e ordenação via URL (GET) com valores padrão
$ordenar = $_GET['ordenar'] ?? 'mais_pontos';
$funcao  = $_GET['funcao'] ?? 'todas';
$patente = $_GET['patente'] ?? 'todas';

try {
    $instancia = new PDO('mysql:host=localhost;dbname=fragforge;charset=utf8', 'root', 'root');
    $instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Carregar opções de patentes e funções dinamicamente para os selects do filtro
    $todasFuncoes = $instancia->query("SELECT * FROM funcao")->fetchAll(PDO::FETCH_ASSOC);
    $todasPatentes = $instancia->query("SELECT * FROM patente ORDER BY id_patente ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Construção dinâmica das cláusulas WHERE
    $whereClauses = [];
    $params = [];

    if ($funcao !== 'todas') {
        $whereClauses[] = "j.id_funcao = :id_funcao";
        $params[':id_funcao'] = $funcao;
    }

    if ($patente !== 'todas') {
        $whereClauses[] = "j.id_patente = :id_patente";
        $params[':id_patente'] = $patente;
    }

    $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

    // Definição da Ordenação (Mais pontuação ou Menos pontuação)
    switch ($ordenar) {
        case 'menos_pontos':
            $orderBySql = "ORDER BY IFNULL(j.pontuacao_jogador, 0) ASC, j.nickname_jogador ASC";
            break;
        case 'mais_pontos':
        default:
            $orderBySql = "ORDER BY IFNULL(j.pontuacao_jogador, 0) DESC, j.id_patente DESC";
            break;
    }

    // Query para buscar o ranking dos jogadores
    $query_string = "
        SELECT j.id_jogador, j.nickname_jogador, j.codigo_battlenet, j.foto_jogador, 
               IFNULL(j.pontuacao_jogador, 0) AS pontuacao, 
               f.nome_funcao, f.icon_funcao, 
               pa.nome_patente, pa.icon_patente,
               e.nome_equipe
        FROM jogador j
        LEFT JOIN funcao f ON j.id_funcao = f.id_funcao
        LEFT JOIN patente pa ON j.id_patente = pa.id_patente
        LEFT JOIN equipe e ON j.id_equipe = e.id_equipe
        $whereSql
        $orderBySql
    ";

    $stmt = $instancia->prepare($query_string);
    $stmt->execute($params);
    $jogadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<p style='color:red; text-align:center; padding:20px;'>Erro de conexão com o banco: " . $e->getMessage() . "</p>";
    exit;
}
?>

<style>
    /* Força os textos, links e parágrafos do header a ficarem brancos */
    header, header a, header p, header h2, header .link-header, header .link-perfil {
        color: #ffffff !important;
    }

    body { background: #f8fafc; color: #1e293b; font-family: 'Segoe UI', system-ui, sans-serif; }
    .leaderboard-container { max-width: 1000px; margin: 30px auto; padding: 0 20px; box-sizing: border-box; }
    
    .leaderboard-header { margin-bottom: 25px; }
    .leaderboard-header h1 { font-size: 26px; color: #0f172a; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px; }
    .leaderboard-header h1::before { content: ""; width: 5px; height: 26px; background: #2563eb; border-radius: 4px; }
    .leaderboard-header p { color: #64748b; margin: 0; font-size: 15px; }

    .filter-box { background: #ffffff; border: 1px solid #e2e8f0; padding: 18px 24px; border-radius: 16px; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .filter-form { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
    .filter-group { flex: 1; min-width: 180px; }
    .filter-group label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-select { width: 100%; padding: 10px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; color: #1e293b; font-weight: 600; font-size: 14px; outline: none; transition: border-color 0.2s; -webkit-appearance: none; -moz-appearance: none; appearance: none; }
    .filter-select:focus { border-color: #2563eb; background: #ffffff; }
    
    .btn-clear { color: #2563eb; text-decoration: none; font-size: 13px; font-weight: 700; border-bottom: 2px dashed #2563eb; padding-bottom: 2px; margin-top: 18px; display: inline-block; }

    .table-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .ranking-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 15px; }
    .ranking-table th { background: #f1f5f9; color: #475569; font-weight: 700; padding: 16px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
    .ranking-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
    .ranking-table tr:last-child td { border-bottom: none; }
    .ranking-table tr:hover td { background: #f8fafc; }

    .podium-position { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-weight: 800; font-size: 14px; }
    .pos-1 { background: #fef08a; color: #854d0e; }
    .pos-2 { background: #e2e8f0; color: #475569; }
    .pos-3 { background: #ffedd5; color: #9a3412; }
    .pos-normal { color: #64748b; font-weight: 600; padding-left: 8px; }

    .player-profile { display: flex; align-items: center; gap: 12px; }
    .player-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; background: #e2e8f0; }
    .player-name { font-weight: 700; color: #0f172a !important; text-decoration: none; transition: color 0.15s; }
    .player-name:hover { color: #2563eb !important; }
    .player-tag { font-size: 12px; color: #94a3b8; font-weight: 400; }
    .player-team { font-size: 12px; background: #eff6ff; color: #1e40af; padding: 2px 8px; border-radius: 20px; font-weight: 600; display: inline-block; }

    .badge-container { display: flex; align-items: center; gap: 8px; font-weight: 600; text-transform: capitalize; font-size: 14px; }
    .icon-small { width: 22px; height: 22px; object-fit: contain; }
    .score-value { font-weight: 800; color: #2563eb; font-size: 16px; }

    .no-data { text-align: center; padding: 50px 20px; color: #94a3b8; font-weight: 500; }
</style>

<main class="leaderboard-container">
    
    <div class="leaderboard-header">
        <h1>Hall da Fama - Jogadores</h1>
        <p>Acompanhe e filtre os melhores atletas da FragForge com base em performance, patentes e funções.</p>
    </div>

    <div class="filter-box">
        <form method="GET" action="" class="filter-form">
            
            <div class="filter-group">
                <label>Ordenar Pontuação</label>
                <select name="ordenar" onchange="this.form.submit()" class="filter-select">
                    <option value="mais_pontos" <?php echo $ordenar === 'mais_pontos' ? 'selected' : ''; ?>>🔥 Maior Pontuação</option>
                    <option value="menos_pontos" <?php echo $ordenar === 'menos_pontos' ? 'selected' : ''; ?>>📉 Menor Pontuação</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Filtrar por Função</label>
                <select name="funcao" onchange="this.form.submit()" class="filter-select">
                    <option value="todas">⚡ Todas as Funções</option>
                    <?php foreach ($todasFuncoes as $f): ?>
                        <option value="<?php echo $f['id_funcao']; ?>" <?php echo (string)$funcao === (string)$f['id_funcao'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($f['nome_funcao'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Filtrar por Rank</label>
                <select name="patente" onchange="this.form.submit()" class="filter-select">
                    <option value="todas">👑 Todos os Ranks</option>
                    <?php foreach ($todasPatentes as $p): ?>
                        <option value="<?php echo $p['id_patente']; ?>" <?php echo (string)$patente === (string)$p['id_patente'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($p['nome_patente'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if($ordenar !== 'mais_pontos' || $funcao !== 'todas' || $patente !== 'todas'): ?>
                <div>
                    <a href="lideranca.php" class="btn-clear">Limpar Filtros</a>
                </div>
            <?php endif; ?>

        </form>
    </div>

    <div class="table-container">
        <?php if (count($jogadores) > 0): ?>
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Pos</th>
                        <th>Jogador</th>
                        <th>Equipe</th>
                        <th>Função</th>
                        <th>Rank / Patente</th>
                        <th style="text-align: right; padding-right: 25px;">Pontuação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $posicaoCorrente = 1; 
                    foreach ($jogadores as $player): 
                    ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php if ($ordenar === 'mais_pontos' && $posicaoCorrente === 1): ?>
                                    <span class="podium-position pos-1">1</span>
                                <?php elseif ($ordenar === 'mais_pontos' && $posicaoCorrente === 2): ?>
                                    <span class="podium-position pos-2">2</span>
                                <?php elseif ($ordenar === 'mais_pontos' && $posicaoCorrente === 3): ?>
                                    <span class="podium-position pos-3">3</span>
                                <?php else: ?>
                                    <span class="pos-normal"><?php echo $posicaoCorrente; ?></span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="player-profile">
                                    <?php if ($player['foto_jogador']): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($player['foto_jogador']); ?>" class="player-avatar" alt="Avatar">
                                    <?php else: ?>
                                        <div class="player-avatar" style="display:flex; align-items:center; justify-content:center; background:#cbd5e1; font-weight:bold; color:#475569; font-size:12px;">N/A</div>
                                    <?php endif; ?>
                                    
                                    <div style="display: flex; flex-direction: column;">
                                        <a href="perfil.php?id=<?php echo $player['id_jogador']; ?>" class="player-name">
                                            <?php echo htmlspecialchars($player['nickname_jogador']); ?>
                                        </a>
                                        <?php if ($player['codigo_battlenet']): ?>
                                            <span class="player-tag"><?php echo htmlspecialchars($player['codigo_battlenet']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <?php if ($player['nome_equipe']): ?>
                                    <span class="player-team"><?php echo htmlspecialchars($player['nome_equipe']); ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 13px;">Sem Equipe</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="badge-container">
                                    <?php if ($player['icon_funcao']): ?>
                                        <img src="<?php echo htmlspecialchars($player['icon_funcao']); ?>" class="icon-small" alt="">
                                    <?php endif; ?>
                                    <span><?php echo $player['nome_funcao'] ? htmlspecialchars($player['nome_funcao']) : '<span style="color: #94a3b8; font-weight:400;">Não definida</span>'; ?></span>
                                </div>
                            </td>

                            <td>
                                <div class="badge-container">
                                    <?php if ($player['icon_patente']): ?>
                                        <img src="<?php echo htmlspecialchars($player['icon_patente']); ?>" class="icon-small" alt="">
                                    <?php endif; ?>
                                    <span><?php echo $player['nome_patente'] ? htmlspecialchars($player['nome_patente']) : '<span style="color: #94a3b8; font-weight:400;">Unranked</span>'; ?></span>
                                </div>
                            </td>

                            <td style="text-align: right; padding-right: 25px;">
                                <span class="score-value"><?php echo number_format($player['pontuacao'], 0, ',', '.'); ?></span>
                                <span style="font-size: 11px; color: #64748b; font-weight:700; display:block;">PTS</span>
                            </td>
                        </tr>
                    <?php 
                    $posicaoCorrente++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <div style="font-size: 32px; margin-bottom: 10px;">🔍</div>
                <p>Nenhum competidor corresponde aos filtros aplicados.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<footer>
    <p>&copy; 2026 FragForge - Todos os direitos reservados.</p>
</footer>