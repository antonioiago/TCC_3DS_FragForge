<?php
include __DIR__.'/includes/conn.php';

try {
    $mensagem = $_POST['mensagem'] ?? '';
    $id_jogador = $_POST['id_jogador'] ?? null;

    // Inicializa as variáveis como null
    $imagem = null;
    $video = null;

    // Tratamento da Imagem
    if (isset($_FILES['print_estatistica']) && $_FILES['print_estatistica']['error'] == 0) {
        $imagem = file_get_contents($_FILES['print_estatistica']['tmp_name']);
    }

    // Tratamento do Vídeo
    if (isset($_FILES['jogada']) && $_FILES['jogada']['error'] == 0) {
        $video = file_get_contents($_FILES['jogada']['tmp_name']);
    }

    // Prepara a query - Note que agora aceitamos nulos para os blobs
    $stmt = $conn->prepare("
        INSERT INTO post (mensagem, id_jogador, print_estatistica, jogada)
        VALUES (?, ?, ?, ?)
    ");

    // Bind dos parâmetros
    $stmt->bindParam(1, $mensagem);
    $stmt->bindParam(2, $id_jogador);
    
    // Para campos BLOB que podem ser nulos, usamos essa lógica:
    $stmt->bindParam(3, $imagem, $imagem ? PDO::PARAM_LOB : PDO::PARAM_NULL);
    $stmt->bindParam(4, $video, $video ? PDO::PARAM_LOB : PDO::PARAM_NULL);

    $stmt->execute();

    header("Location: post.php");
    exit;

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>