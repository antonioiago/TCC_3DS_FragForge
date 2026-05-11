<?php
session_start();
// Se o jogador não estiver logado, você pode redirecionar, 
// mas aqui vamos simular usando o nickname da sessão.
$nickname = $_SESSION['nickname_jogador'] ?? 'Visitante'; 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Chat FragForge</title>
    <style>
        #chat-box { width: 400px; height: 300px; border: 1px solid #ccc; overflow-y: scroll; padding: 10px; background: #f9f9f9; }
        .msg { margin-bottom: 10px; padding: 5px; border-bottom: 1px solid #eee; }
        .msg b { color: #555; }
        #form-chat { margin-top: 10px; }
        input[type="text"] { width: 300px; padding: 5px; }
    </style>
</head>
<body>

    <h2>Chat da Comunidade</h2>
    
    <div id="chat-box"></div>

    <div id="form-chat">
        <input type="text" id="mensagem" placeholder="Digite sua mensagem..." required>
        <button onclick="enviarMensagem()">Enviar</button>
    </div>

    <script>
        // Função para carregar as mensagens do banco
        function carregarMensagens() {
            fetch('buscar_mensagens.php')
                .then(response => response.text())
                .then(data => {
                    const chatBox = document.getElementById('chat-box');
                    chatBox.innerHTML = data;
                    // Faz o scroll descer automaticamente
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        // Função para enviar a mensagem via AJAX
        function enviarMensagem() {
            const msgInput = document.getElementById('mensagem');
            const msg = msgInput.value;

            if (msg.trim() === "") return;

            const dados = new FormData();
            dados.append('mensagem', msg);

            fetch('enviar_mensagem.php', {
                method: 'POST',
                body: dados
            }).then(() => {
                msgInput.value = ''; // Limpa o campo
                carregarMensagens(); // Atualiza na hora
            });
        }

        // Atualiza o chat a cada 2 segundos
        setInterval(carregarMensagens, 2000);
        // Carrega ao abrir a página
        carregarMensagens();
    </script>
</body>
</html>