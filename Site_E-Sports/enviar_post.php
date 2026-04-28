<?php
include __DIR__.'/includes/conn.php';

try {

    // Verifica se veio arquivo
    if (isset($_FILES['print_estatistica']) && $_FILES['print_estatistica']['error'] == 0) {

        $mensagem = $_POST['mensagem'];
        $id_jogador = $_POST['id_jogador'];

        // 🔥 pega o conteúdo da imagem
        $imagem = file_get_contents($_FILES['print_estatistica']['tmp_name']);

        // (opcional mas recomendado)
        $tipo = $_FILES['print_estatistica']['type'];

        // prepara query
        $stmt = $conn->prepare("
            INSERT INTO post (mensagem, id_jogador, print_estatistica)
            VALUES (?, ?, ?)
        ");

        // bind correto (LOB!)
        $stmt->bindParam(1, $mensagem);
        $stmt->bindParam(2, $id_jogador);
        $stmt->bindParam(3, $imagem, PDO::PARAM_LOB);

        $stmt->execute();

    } else {
        echo "Erro no upload da imagem!";
        exit;
    }

    header("Location: post.php");
    exit;

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>