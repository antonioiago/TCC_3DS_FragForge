<link rel="stylesheet" href="styles/post.css">
<?php
    session_start();
    // Verificação simples se o usuário está logado
    $id_jogador = $_SESSION['jogador']['id'] ?? null;
?>

<form class="timeline" action="enviar_post.php" method="post" enctype="multipart/form-data">
    <h1>Postagem</h1>
    
    <input class="comentario" type="text" name="mensagem" id="mensagem" placeholder="Escreva um post" required><br>
    
    <input type="hidden" name="id_jogador" value="<?php echo $id_jogador; ?>">

    <label for="print_estatistica">Imagem (Opcional):</label>
    <input type="file" name="print_estatistica" id="print_estatistica" accept="image/*"><br>

    <label for="jogada">Vídeo da Jogada (Opcional):</label>
    <input type="file" name="jogada" id="jogada" accept="video/*"><br>

    <input type="submit" value="Postar">

    <button type="button" onclick="window.close()">Fechar Esta Janela</button>
</form>