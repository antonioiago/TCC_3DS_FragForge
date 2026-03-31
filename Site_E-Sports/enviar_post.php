<?php
session_start();
INCLUDE __DIR__.'/includes/conn.php'; //

// Verificação extra de segurança
if (!isset($_POST['id_jogador']) || $_POST['id_jogador'] != $_SESSION['jogador']['id']) {
    die("Ação não autorizada.");
}

try {
    $stmt = $conn->prepare("insert into post(mensagem, id_jogador) values(?,?);"); //
    $stmt->bindParam(1, $_POST['mensagem']); //
    $stmt->bindParam(2, $_POST['id_jogador']); //
  
    $stmt->execute(); //
    
    // Redireciona de volta para a index ou para a lista de posts
    header("Location: index.php"); 
    die(); //
} catch(PDOexception $e) {
    // Log de erro discreto para o usuário
    error_log($e->getMessage()); // Grava o erro real no log do servidor
    echo "Não foi possível publicar seu post no momento."; 
}
?>