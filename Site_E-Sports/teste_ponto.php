<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FragForge - Calculadora de Pontos OCR</title>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #1a1a1a; color: white; display: flex; flex-direction: column; align-items: center; padding: 20px; }
        .container { background: #2d2d2d; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); max-width: 600px; width: 100%; }
        input[type="file"] { margin: 20px 0; color: #ccc; }
        #status { color: #ff9c00; font-weight: bold; margin: 10px 0; }
        .resultado-box { margin-top: 20px; padding: 15px; border-top: 2px solid #444; display: none; }
        .ponto-final { font-size: 2em; color: #4CAF50; }
        .debug-text { font-size: 12px; color: #888; background: #000; padding: 10px; border-radius: 5px; height: 100px; overflow-y: scroll; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="container">
        <h2>FragForge: Sistema de Pontuação</h2>
        <p>Envie o print das suas estatísticas (ex: Suporte/Platina)</p>
        
        <input type="file" id="upload" accept="image/*">
        <div id="status">Aguardando arquivo...</div>

        <div id="resultado-container" class="resultado-box">
            <div>Pontuação Calculada:</div>
            <div id="ponto-valor" class="ponto-final">0</div>
            <div id="detalhes-stats"></div>
            <p>Texto lido pelo sistema (Debug):</p>
            <div id="debug" class="debug-text"></div>
        </div>
    </div>

    <script>
        const upload = document.getElementById('upload');
        const status = document.getElementById('status');
        const resultadoContainer = document.getElementById('resultado-container');
        const debugBox = document.getElementById('debug');

        upload.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            status.innerText = "Lendo imagem e filtrando dados... (Isso pode levar segundos)";
            resultadoContainer.style.display = "none";

            try {
                // Inicia o Reconhecimento Óptico
                const worker = await Tesseract.createWorker('por'); // 'por' para português
                const ret = await worker.recognize(file);
                const textoOriginal = ret.data.text;
                
                debugBox.innerText = textoOriginal;
                
                // Filtro de Dados (Baseado na estrutura da imagem status.jpeg)
                const dados = extrairDados(textoOriginal);
                
                // Cálculo de Pontos (Baseado no seu Excel: Suporte/Platina)
                const pontos = calcularPontuacao(dados);

                exibirResultado(pontos, dados);
                
                await worker.terminate();
            } catch (error) {
                console.error(error);
                status.innerText = "Erro ao processar imagem.";
            }
        });

        function extrairDados(texto) {
            // Limpeza básica e quebra por linhas
            const linhas = texto.split('\n');
            
            // Procuramos números em sequência comuns no placar (K A D Dano Cura)
            // Esta Regex busca números isolados ou em sequência
            const numeros = texto.match(/\d+/g) || [];

            // Nota: Em um sistema real, você mapearia a linha do seu nickname.
            // Para testes, pegaremos os maiores valores encontrados para simular:
            return {
                abates: parseInt(numeros[0]) || 0,
                assistencias: parseInt(numeros[1]) || 0,
                mortes: parseInt(numeros[2]) || 0,
                dano: parseInt(numeros[3]) || 0,
                cura: parseInt(numeros[4]) || 0,
                vitoria: texto.toLowerCase().includes('vitoria') || texto.toLowerCase().includes('vitória')
            };
        }

        function calcularPontuacao(d) {
            // CONFIGURAÇÃO CONFORME SEU EXCEL (Suporte - Platina)
            const pesos = {
                abate: 4,
                assistencia: 5,
                cura: 4, // a cada 1000
                morte: -4,
                vitoriaMult: 2
            };

            // Lógica: (Abates * 4) + (Assists * 5) + (Cura/1000 * 4) + (Mortes * -4)
            let total = (d.abates * pesos.abate) + 
                        (d.assistencias * pesos.assistencia) + 
                        (Math.floor(d.cura / 1000) * pesos.cura) + 
                        (d.mortes * pesos.morte);

            if (d.vitoria) {
                total *= pesos.vitoriaMult;
            }

            return total;
        }

        function exibirResultado(pontos, d) {
            status.innerText = "Processamento concluído!";
            resultadoContainer.style.display = "block";
            document.getElementById('ponto-valor').innerText = pontos;
            document.getElementById('detalhes-stats').innerHTML = `
                <p><strong>Dados detectados:</strong><br>
                Abates: ${d.abates} | Assist: ${d.assistencias} | Mortes: ${d.mortes}<br>
                Cura: ${d.cura} | Vitória: ${d.vitoria ? 'Sim' : 'Não'}</p>
            `;
        }
    </script>
</body>
</html>