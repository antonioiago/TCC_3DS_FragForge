<link rel="stylesheet" href="styles/post.css">
<?php
    session_start();
    $_SESSION['jogador']['id'];
    $id_jogador = $_SESSION['jogador']['id'];
   
    ?>
<form class="timeline" action="enviar_post.php" method="post">
    <h1>teste</h1>
    <input class="comentario" type="text" name="mensagem" id="mensagem"  placeholder="Escreve um post"><br>
    <input type="hidden" name="id_jogador" value="<?php echo $id_jogador; ?>">
    <input type="file" name="print_estatistica" id="print_estatistica"><br>
    <input type="submit" value="Postar">

    <button onclick="window.close()">Fechar Esta Janela</button>
</form>