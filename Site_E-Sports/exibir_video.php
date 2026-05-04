<?php
// exibir_video.php
include __DIR__.'/includes/conn.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT jogada FROM post WHERE id_post = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post && $post['jogada']) {
        // Importante: limpa qualquer espaço em branco antes do header
        ob_clean(); 
        header("Content-Type: video/mp4");
        // Se o seu vídeo for muito grande, talvez precise de:
        // header("Content-Length: " . strlen($post['jogada']));
        echo $post['jogada'];
        exit;
    }
}