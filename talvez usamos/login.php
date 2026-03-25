<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuarios WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user["senha"])) {
            $_SESSION["usuario"] = $user;
            header("Location: dashboard.php");
            exit();
        }
    }

    echo "Login inválido";
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
<h2>Login</h2>

<form method="POST">
<input name="email" placeholder="Email" required>
<input name="senha" type="password" placeholder="Senha" required>
<button type="submit">Entrar</button>
</form>

</div>
</div>
</body>
</html>