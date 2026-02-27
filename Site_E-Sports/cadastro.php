<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <title>FragForce</title>
</head>
<body>
<style>
    header, main{
        background-color: #135D04;
    }
    main{
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    main > *{
        align-self: center;
    }
    form{
        height: 320px;
        width: 400px;
        border-radius: 25px;
        background-color: #888888;
        justify-content: end;
        display: flex;
        flex-direction: column;
        padding: 20px;
        font-weight: bold;
    }
    label{
        font-size: 12px;
    }
    p, label{
        color: #eeeeee;
    }
    a{
        color: #59aefd;
        text-decoration-line: none;
    }
</style>

<main>
    <form method="POST" action="cadastrar.php">
        <?php
            session_start();
            if (isset($_SESSION["MnsErro"])) {
                echo '<p style="font-size: 15px;text-align: center; margin-bottom: 5px; color: rgb(245, 24, 30);font-weight:normal;">'.$_SESSION["MnsErro"].'</p>';
            }
        ?>
        <label>Email:</label><input type="text" name="email" placeholder="nome@local.com" required>
        <label>Senha:</label><input type="password" name="password" required>
        <label>Repita a senha:</label><input type="password" name="chkpassword" required>
        <label>Data de nascimento:</label><input type="date" name= "birthdate" required>
        <label>Nome de Usuário</label><input type="text" name="nameuser" required>
        <input style="margin-top:10px" type="submit" value="Cadastrar">
    </form>
    <p style="font-size: 15px">Já possui uma conta? <a href="login.php">Entre aqui!</a></p>
</main>
</body>