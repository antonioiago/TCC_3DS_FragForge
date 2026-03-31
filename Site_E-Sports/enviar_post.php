<?php
INCLUDE __DIR__.'/includes/conn.php';

try{
    //Criar a query para o insert
    $stmt=$conn->prepare("insert into post(mensagem, id_jogador) values(?,?);");
    //Passar o parâmetro dos valores
    $stmt->bindParam(1,$_POST['mensagem']);
    $stmt->bindParam(2,$_POST['id_jogador']);
  
    //Executando o insert
    $stmt->execute();
    header("Location: timeline.php");
    die();
}catch(PDOexception $e){
    echo "ERROR: ".$e->getMessage();
    echo $_POST['mensagem'];
    echo $_POST['id_jogador'];
}
?>

