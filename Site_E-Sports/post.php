<?php
session_start();
// Proteção: Se não estiver logado, manda para o login
if (!isset($_SESSION['jogador']['id'])) {
    header("Location: form-login.php");
    die();
}
?>

<form class="timeline" action="enviar_post.php" method="post">
    <h3>Novo Post</h3>
    
    <input type="text" name="mensagem" id="mensagem" placeholder="O que está acontecendo no mapa?" required>
    
    <input type="hidden" name="id_jogador" value="<?= $_SESSION['jogador']['id'] ?>">
    
    <input type="submit" value="Postar">
    <button type="button" onclick="window.close()">Cancelar</button>
</form>