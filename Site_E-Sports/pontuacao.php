<?php
session_start();

// Simulação de login
if (!isset($_SESSION['id_jogador'])) {
    $_SESSION['id_jogador'] = 1; 
    $_SESSION['nickname'] = "JogadorTeste";
}

$host = "localhost";
$user = "root";
$pass = "root";
$db   = "fragforge";

$conn = new mysqli($host, $user, $pass, $db);

// LÓGICA DE SALVAMENTO AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_save'])) {
    $id_jogador = $_SESSION['id_jogador'];
    $pontos = intval($_POST['pontos']);
    $mensagem = $_POST['mensagem'];
    
    $imgConteudo = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['tmp_name'] != "") {
        $imgConteudo = file_get_contents($_FILES['foto']['tmp_name']);
    }

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("UPDATE jogador SET pontuacao_jogador = IFNULL(pontuacao_jogador, 0) + ? WHERE id_jogador = ?");
        $stmt1->bind_param("ii", $pontos, $id_jogador);
        $stmt1->execute();

        $stmt2 = $conn->prepare("INSERT INTO post (mensagem, print_estatistica, id_jogador) VALUES (?, ?, ?)");
        $null = NULL;
        $stmt2->bind_param("sbi", $mensagem, $null, $id_jogador);
        $stmt2->send_long_data(1, $imgConteudo);
        $stmt2->execute();

        $stmt3 = $conn->prepare("UPDATE equipe e JOIN jogador j ON e.id_equipe = j.id_equipe SET e.pontuacao_equipe = IFNULL(e.pontuacao_equipe, 0) + ? WHERE j.id_jogador = ?");
        $stmt3->bind_param("ii", $pontos, $id_jogador);
        $stmt3->execute();

        $conn->commit();
        echo "Sucesso! " . $pontos . " pontos adicionados.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro: " . $e->getMessage();
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>FragForge - Calculadora Corrigida</title>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: white; display: flex; flex-direction: column; align-items: center; padding: 20px; }
        .container { background: #1e1e1e; padding: 25px; border-radius: 8px; width: 100%; max-width: 700px; border-top: 4px solid #ff9c00; }
        .box { background: #252525; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        label { display: block; color: #ff9c00; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; background: #333; border: 1px solid #444; color: white; border-radius: 4px; box-sizing: border-box; }
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 10px; }
        button { width: 100%; background: #ff9c00; border: none; padding: 15px; color: black; font-weight: bold; cursor: pointer; border-radius: 4px; margin-top: 20px; }
        #log-ocr { font-size: 0.8em; color: #888; margin-top: 10px; text-align: center; }
        .resultado-final { display: none; margin-top: 20px; padding: 20px; background: #000; text-align: center; border: 2px solid #ff9c00; }
    </style>
</head>
<body>

<div class="container">
    <h2>Analisador FragForge</h2>

    <div class="box">
        <label>1. Detalhes da Partida</label>
        <div class="stats-row">
            <select id="funcao">
                <option value="Suporte">Suporte</option>
                <option value="Dano">Dano</option>
                <option value="Tanque">Tanque</option>
            </select>
            <select id="rank">
                <option value="Bronze">Bronze</option>
                <option value="Prata">Prata</option>
                <option value="Ouro">Ouro</option>
                <option value="Platina" selected>Platina</option>
                <option value="Diamante">Diamante</option>
                <option value="Mestre">Mestre</option>
                <option value="Grão-Mestre">Grão-Mestre</option>
                <option value="Champion">Champion</option>
            </select>
            <select id="vitoria">
                <option value="vitoria">Vitória</option>
                <option value="empate">Empate</option>
                <option value="derrota">Derrota</option>
            </select>
        </div>
    </div>

    <div class="box">
        <label>2. Print das Estatísticas</label>
        <input type="file" id="arquivo" accept="image/*">
        <div id="log-ocr">Selecione o print para leitura automática...</div>
    </div>

    <div class="box">
        <label>3. Confirmar Dados (Extraídos do Print)</label>
        <div class="stats-row">
            <div><label>Kills</label><input type="number" id="k" value="0"></div>
            <div><label>Assists</label><input type="number" id="a" value="0"></div>
            <div><label>Deaths</label><input type="number" id="d" value="0"></div>
            <div><label>Cura/Dano</label><input type="number" id="main_stat" value="0"></div>
        </div>
    </div>

    <textarea id="mensagem" placeholder="Sua mensagem sobre a partida..." rows="2"></textarea>
    
    <button onclick="processarEnvio()">CALCULAR E SALVAR NO BANCO</button>

    <div id="res_box" class="resultado-final">
        <div style="font-size: 1.2em;">Total de Pontos Ganhos:</div>
        <div id="valor_total" style="font-size: 3em; color: #4CAF50;">0</div>
        <div id="server_msg"></div>
    </div>
</div>

<script>
// TABELA DE PESOS OFICIAL DO SEU EXCEL
const TABELA_PESOS = {
    "Suporte": {
        "Bronze": {k:1, a:2, c:1, d:-1, vit:2, emp:1.5, der:0.5},
        "Prata":  {k:2, a:3, c:2, d:-2, vit:2, emp:1.5, der:0.5},
        "Ouro":   {k:3, a:4, c:3, d:-3, vit:2, emp:1.5, der:0.5},
        "Platina":{k:4, a:5, c:4, d:-4, vit:2, emp:1.5, der:0.5},
        "Diamante":{k:5, a:6, c:5, d:-5, vit:2, emp:1.5, der:0.5},
        "Mestre": {k:6, a:7, c:6, d:-6, vit:2, emp:1, der:0.4},
        "Grão-Mestre":{k:7, a:8, c:7, d:-7, vit:1.8, emp:1, der:0.3},
        "Champion":{k:8, a:9, c:8, d:-8, vit:1.5, emp:1, der:0.3}
    },
    "Dano": {
        "Platina":{k:5, a:4, c:3, d:-4, vit:2, emp:1.5, der:0.5} 
        // Adicione os outros ranks de Dano conforme necessário
    }
};

document.getElementById('arquivo').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if(!file) return;

    document.getElementById('log-ocr').innerText = "Lendo imagem... Aguarde...";
    
    const worker = await Tesseract.createWorker('por');
    const { data: { text } } = await worker.recognize(file);
    
    // Filtro para pegar apenas os números ignorando textos
    const nums = text.match(/\d+/g) || [];
    if(nums.length >= 4) {
        document.getElementById('k').value = nums[0];
        document.getElementById('a').value = nums[1];
        document.getElementById('d').value = nums[2];
        document.getElementById('main_stat').value = nums[3];
        document.getElementById('log-ocr').innerText = "Leitura concluída! Verifique os campos.";
    }
    await worker.terminate();
});

function processarEnvio() {
    const func = document.getElementById('funcao').value;
    const rnk = document.getElementById('rank').value;
    const res = document.getElementById('vitoria').value;

    const k = parseInt(document.getElementById('k').value);
    const a = parseInt(document.getElementById('a').value);
    const d = parseInt(document.getElementById('d').value);
    const m = parseInt(document.getElementById('main_stat').value);

    // Busca pesos ou usa Platina como padrão caso não encontre
    const p = (TABELA_PESOS[func] && TABELA_PESOS[func][rnk]) ? TABELA_PESOS[func][rnk] : TABELA_PESOS["Suporte"]["Platina"];

    // CÁLCULO: (Abates*Peso) + (Assist*Peso) + (Morte*Peso) + (Cura por 1000 * Peso)
    let total = (k * p.k) + (a * p.a) + (d * p.d) + (Math.floor(m / 1000) * p.c);

    // MULTIPLICADORES DE RESULTADO
    if(res === 'vitoria') total *= p.vit;
    else if(res === 'derrota') total *= p.der;
    else total *= p.emp;

    total = Math.round(total);

    // Enviar via AJAX para o PHP
    const formData = new FormData();
    formData.append('ajax_save', true);
    formData.append('pontos', total);
    formData.append('mensagem', document.getElementById('mensagem').value);
    formData.append('foto', document.getElementById('arquivo').files[0]);

    fetch(window.location.href, { method: 'POST', body: formData })
    .then(r => r.text())
    .then(msg => {
        document.getElementById('res_box').style.display = "block";
        document.getElementById('valor_total').innerText = total;
        document.getElementById('server_msg').innerText = msg;
    });
}
</script>
</body>
</html>