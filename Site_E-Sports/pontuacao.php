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
    <title>FragForge - Calculadora Estatísticas</title>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; color: #1e293b; padding: 15px; margin: 0; }
        .container { background: #ffffff; padding: 20px; border-radius: 16px; width: 100%; max-width: 660px; box-sizing: border-box; margin: 0 auto; }
        h2 { font-size: 20px; color: #0f172a; margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        h2::before { content: ""; width: 4px; height: 20px; background: #2563eb; border-radius: 4px; }
        
        .box { background: #f1f5f9; padding: 16px; border-radius: 14px; margin-bottom: 16px; border: 1px solid #e2e8f0; }
        label { display: block; color: #1e293b; margin-bottom: 6px; font-size: 14px; font-weight: 700; }
        
        input, select, textarea { width: 100%; padding: 10px 12px; background: #ffffff; border: 1px solid #cbd5e1; color: #1e293b; border-radius: 10px; box-sizing: border-box; font-family: inherit; font-size: 14px; transition: border-color 0.2s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #2563eb; }
        
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .selection-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        
        .btn-enviar { width: 100%; background: #2563eb; border: none; padding: 14px; color: white; font-weight: bold; font-size: 15px; cursor: pointer; border-radius: 12px; margin-top: 15px; transition: background 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15); }
        .btn-enviar:hover { background: #1d4ed8; }
        
        #log-ocr { font-size: 0.85em; color: #2563eb; margin-top: 8px; text-align: center; font-weight: 600; }
        
        .resultado-final { display: none; margin-top: 20px; padding: 20px; background: #f0fdf4; text-align: center; border: 2px solid #bbf7d0; border-radius: 14px; }
        
        .painel-linhas { background: #ffffff; border: 1px dashed #cbd5e1; padding: 12px; margin-top: 12px; border-radius: 10px; display: none; }
        .btn-linha-detectada { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; padding: 6px 12px; margin: 4px; cursor: pointer; border-radius: 8px; font-size: 0.85em; font-weight: 500; display: inline-block; transition: all 0.15s; }
        .btn-linha-detectada:hover { background: #dbeafe; color: #2563eb; border-color: #bfdbfe; }

        .preview-container { text-align: center; display: none; background: #ffffff; padding: 8px; border-radius: 10px; border: 1px solid #e2e8f0; margin-top: 12px; }
        .preview-container img { max-width: 100%; max-height: 260px; border-radius: 8px; border: 1px solid #cbd5e1; object-fit: contain; }
    </style>
</head>
<body>

<div class="container">
    <h2>Analisador de Placar</h2>

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
            <select id="vitoria" style="grid-column: span 2;">
                <option value="vitoria">Vitória</option>
                <option value="empate">Empate</option>
                <option value="derrota">Derrota</option>
            </select>
        </div>
    </div>

    <div class="box">
        <label>2. Sua Posição no Placar</label>
        <div class="selection-row">
            <div>
                <label style="font-size: 0.8em; color: #64748b;">Time no Placar</label>
                <select id="escolha_time">
                    <option value="azul">Time Azul (Superior)</option>
                    <option value="vermelho">Time Vermelho (Inferior)</option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.8em; color: #64748b;">Sua Linha</label>
                <select id="escolha_linha">
                    <option value="1">Linha 1</option>
                    <option value="2">Linha 2</option>
                    <option value="3">Linha 3</option>
                    <option value="4">Linha 4</option>
                    <option value="5">Linha 5</option>
                    <option value="6">Linha 6</option>
                </select>
            </div>
        </div>
    </div>

    <div class="box">
        <label>3. Print das Estatísticas</label>
        <input type="file" id="arquivo" accept="image/*">
        <div id="log-ocr">Selecione o print para carregar...</div>
        
        <div id="box-preview" class="preview-container">
            <label style="font-size: 0.8em; color: #64748b; margin-bottom: 6px;">Visualização do Print:</label>
            <img id="img-renderizada" src="" alt="Preview do Placar">
        </div>

        <div id="painel-linhas" class="painel-linhas">
            <span style="font-size: 0.8em; color: #64748b; display:block; margin-bottom: 6px; font-weight: 600;">Linhas detectadas (Clique para escolher):</span>
            <div id="lista-linhas-botoes"></div>
        </div>
    </div>

    <div class="box">
        <label>4. Confirmar Dados (Ajuste se necessário)</label>
        <div class="stats-row">
            <div><label style="font-size: 12px; color:#64748b;">Kills</label><input type="number" id="k" value="0"></div>
            <div><label style="font-size: 12px; color:#64748b;">Assists</label><input type="number" id="a" value="0"></div>
            <div><label style="font-size: 12px; color:#64748b;">Deaths</label><input type="number" id="d" value="0"></div>
            <div><label style="font-size: 12px; color:#64748b;">Cura/Dano</label><input type="number" id="main_stat" value="0"></div>
        </div>
    </div>

    <textarea id="mensagem" placeholder="Deixe um comentário sobre a partida..." rows="2"></textarea>
    
    <button class="btn-enviar" onclick="processarEnvio()">CALCULAR E SALVAR NO PERFIL</button>

    <div id="res_box" class="resultado-final">
        <div style="font-size: 1.1em; color: #166534; font-weight: 600;">Total de Pontos Ganhos:</div>
        <div id="valor_total" style="font-size: 2.8em; color: #15803d; font-weight: 800; margin: 5px 0;">0</div>
        <div id="server_msg" style="font-size: 13px; color: #166534;"></div>
    </div>
</div>

<script>
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
    },
    "Tanque": {
        "Platina":{k:4, a:4, c:3, d:-5, vit:2, emp:1.5, der:0.5}
    }
};

document.getElementById('arquivo').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if(!file) return;

    const urlImagem = URL.createObjectURL(file);
    document.getElementById('img-renderizada').src = urlImagem;
    document.getElementById('box-preview').style.display = "block";

    document.getElementById('log-ocr').innerText = "Analisando imagem de fundo...";
    document.getElementById('painel-linhas').style.display = "none";
    
    try {
        const img = new Image();
        img.src = urlImagem;
        await new Promise(res => img.onload = res);

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);

        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imgData.data;
        for (let i = 0; i < data.length; i += 4) {
            let brilho = data[i] * 0.299 + data[i+1] * 0.587 + data[i+2] * 0.114;
            let corFinal = (brilho > 125) ? 0 : 255;
            data[i] = corFinal; data[i+1] = corFinal; data[i+2] = corFinal;
        }
        ctx.putImageData(imgData, 0, 0);

        const worker = await Tesseract.createWorker('eng');
        const { data: { text } } = await worker.recognize(canvas);
        await worker.terminate();

        const linhas = text.split('\n');
        const linhasValidas = [];

        linhas.forEach(linha => {
            let textoLimpo = linha.replace(/(\d+)\s*[\.,]\s*(\d+)/g, '$1$2');
            textoLimpo = textoLimpo.replace(/[\.,]/g, '');
            const numeros = textoLimpo.match(/\d+/g);
            if (numeros && numeros.length >= 3) {
                linhasValidas.push(numeros.map(Number));
            }
        });

        if (linhasValidas.length === 0) {
            document.getElementById('log-ocr').innerText = "Imagem carregada. Insira os números manualmente olhando o preview.";
            return;
        }

        const painelBotoes = document.getElementById('lista-linhas-botoes');
        painelBotoes.innerHTML = "";
        linhasValidas.forEach((numArray, idx) => {
            const btn = document.createElement('button');
            btn.type = "button";
            btn.className = "btn-linha-detectada";
            btn.innerText = `Opção ${idx + 1} ➔ [ ${numArray.join(' | ')} ]`;
            btn.onclick = () => preencherCamposDoFormulario(numArray);
            painelBotoes.appendChild(btn);
        });
        document.getElementById('painel-linhas').style.display = "block";

        const timeSelecionado = document.getElementById('escolha_time').value;
        const linhaSelecionada = parseInt(document.getElementById('escolha_linha').value) - 1;

        let dadosJogador = null;
        if (timeSelecionado === 'azul') {
            dadosJogador = linhasValidas[linhaSelecionada];
        } else {
            let metade = linhasValidas.length >= 10 ? Math.floor(linhasValidas.length / 2) : 5;
            dadosJogador = linhasValidas[metade + Math.min(linhaSelecionada, 4)];
        }

        if (dadosJogador) {
            preencherCamposDoFormulario(dadosJogador);
            document.getElementById('log-ocr').innerText = "✓ Preenchido! Ajuste qualquer detalhe usando a imagem.";
        } else {
            document.getElementById('log-ocr').innerText = "Imagem carregada! Digite os dados olhando o preview.";
        }

    } catch (erro) {
        console.error(erro);
        document.getElementById('log-ocr').innerText = "Pronto para checagem manual.";
    }
});

function preencherCamposDoFormulario(arrayNumeros) {
    if (!arrayNumeros || arrayNumeros.length < 3) return;
    document.getElementById('k').value = arrayNumeros[0] || 0;
    document.getElementById('a').value = arrayNumeros[1] || 0;
    document.getElementById('d').value = arrayNumeros[2] || 0;

    const copiaOrdenada = [...arrayNumeros].sort((x, y) => y - x);
    const maiorValorDoPlacar = copiaOrdenada[0] > 45 ? copiaOrdenada[0] : 0;
    document.getElementById('main_stat').value = maiorValorDoPlacar;
}

function processarEnvio() {
    const func = document.getElementById('funcao').value;
    const rnk = document.getElementById('rank').value;
    const res = document.getElementById('vitoria').value;

    const k = parseInt(document.getElementById('k').value);
    const a = parseInt(document.getElementById('a').value);
    const d = parseInt(document.getElementById('d').value);
    const m = parseInt(document.getElementById('main_stat').value);

    const p = (TABELA_PESOS[func] && TABELA_PESOS[func][rnk]) ? TABELA_PESOS[func][rnk] : TABELA_PESOS["Suporte"]["Platina"];

    let total = (k * p.k) + (a * p.a) + (d * p.d) + (Math.floor(m / 1000) * p.c);

    if(res === 'vitoria') total *= p.vit;
    else if(res === 'derrota') total *= p.der;
    else total *= p.emp;

    total = Math.round(total);

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

        if (msg.indexOf("Sucesso!") !== -1) {
            setTimeout(() => {
                // MODIFICAÇÃO AQUI: 
                // 1. Aciona o fechamento do modal na página de perfil (pai)
                if (window.parent && typeof window.parent.fecharModalEstatisticas === 'function') {
                    window.parent.fecharModalEstatisticas();
                }
                // 2. Atualiza os dados do perfil em segundo plano sem precisar mudar de URL
                window.parent.location.reload();
            }, 2000);
        }
    });
}
</script>
</body>
</html>