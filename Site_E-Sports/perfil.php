<?php
// 🔌 Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "root";
$db = "fragforge";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// 📥 Pega o ID do jogador
if (!isset($_GET['id'])) {
    echo "Jogador não encontrado!";
    exit;
}

$id = intval($_GET['id']);

// 🔎 Busca dados do jogador
$sql = "
SELECT j.*, f.nome_funcao, f.icon_funcao,
       p.nome_patente, p.icon_patente,
       e.nome_equipe
FROM jogador j
LEFT JOIN funcao f ON j.id_funcao = f.id_funcao
LEFT JOIN patente p ON j.id_patente = p.id_patente
LEFT JOIN equipe e ON j.id_equipe = e.id_equipe
WHERE j.id_jogador = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Jogador não encontrado.";
    exit;
}

$jogador = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Perfil do Jogador</title>

<style>
body {
    font-family: Arial;
    background: #0f172a;
    color: white;
}

.perfil {
    max-width: 800px;
    margin: 40px auto;
    background: #1e293b;
    padding: 20px;
    border-radius: 10px;
}

.foto img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
}

h2 {
    margin: 10px 0;
}

.info p {
    margin: 5px 0;
}

.posts {
    margin-top: 30px;
    border-top: 1px solid #334155;
    padding-top: 15px;
}

.post {
    margin-bottom: 20px;
    padding: 10px;
    background: #334155;
    border-radius: 8px;
}

.post img {
    margin-top: 10px;
    max-width: 200px;
    border-radius: 5px;
}
</style>
</head>

<body>

<div class="perfil">

    <!-- Foto -->
    <div class="foto">
        <?php
        if ($jogador['foto_jogador']) {
            echo '<img src="data:image/jpeg;base64,'.base64_encode($jogador['foto_jogador']).'">';
        } else {
            echo "<p>Sem foto</p>";
        }
        ?>
    </div>

    <!-- Nome -->
    <h2><?php echo $jogador['nickname_jogador']; ?></h2>

    <!-- Informações -->
    <div class="info">
        <p><strong>Email:</strong> <?php echo $jogador['email_jogador']; ?></p>
        <p><strong>BattleNet:</strong> <?php echo $jogador['codigo_battlenet']; ?></p>
        <p><strong>Função:</strong> <?php echo $jogador['nome_funcao']; ?></p>
        <p><strong>Patente:</strong> <?php echo $jogador['nome_patente']; ?></p>
        <p><strong>Equipe:</strong> <?php echo $jogador['nome_equipe']; ?></p>
        <p><strong>Pontuação:</strong> <?php echo $jogador['pontuacao_jogador']; ?></p>
    </div>

    <!-- Posts -->
    <div class="posts">
        <h3>Posts</h3>

        <?php
        $posts = $conn->query("SELECT * FROM post WHERE id_jogador = $id ORDER BY id_post DESC");

        if ($posts->num_rows > 0) {
            while ($p = $posts->fetch_assoc()) {
                echo "<div class='post'>";
                echo "<p>".$p['mensagem']."</p>";

                if ($p['print_estatistica']) {
                    echo '<img src="data:image/jpeg;base64,'.base64_encode($p['print_estatistica']).'">';
                }

                echo "</div>";
            }
        } else {
            echo "<p>Nenhum post ainda.</p>";
        }
        ?>
    </div>

</div>

</body>
</html>