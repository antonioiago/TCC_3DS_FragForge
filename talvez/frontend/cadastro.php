<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $tipo = $_POST["tipo"];

    $sql = "INSERT INTO usuarios (nome, email, senha, tipo)
            VALUES ('$nome', '$email', '$senha', '$tipo')";

    if ($conn->query($sql)) {
        header("Location: login.php");
    } else {
        echo "Erro: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
<div class="auth-card">
<h2>Criar Conta</h2>

<form method="POST">
<input name="nome" placeholder="Nome" required>
<input name="email" placeholder="Email" required>
<input name="senha" type="password" placeholder="Senha" required>

<select name="tipo">
<option value="tecnico">Técnico</option>
<option value="empresa">Empresa</option>
</select>

<button type="submit">Cadastrar</button>
</form>

</div>
</div>
</body>
</html>