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
        height: 200px;
        width: 400px;
        border-radius: 25px;
        background-color: #888888;
        justify-content: end;
        display: flex;
        flex-direction: column;
        padding: 20px;
        color: #eeeeee;
        font-weight: bold;
    }
    label{
        font-size: 12px;
    }
</style>

<main>
    <form method="POST">
        <?php
            if (true) {
                echo '<p style="font-size: 15px;text-align: center; margin-bottom: 5px; color: rgb(245, 24, 30);font-weight:normal;">Login invalido, tente novamente.</p>';
            }
        ?>
        <label>Email/Nickname:</label><input type="text">
        <label>Senha:</label><input type="text">
        <input style="margin-top:10px" type="submit" value="Entrar">
    </form>
    <p style="font-size: 15px">Não possui conta? <a href="cadastro.php">Cadastre-se!</a></p>
</main>
</body>