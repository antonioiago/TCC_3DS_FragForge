<?php
include "config.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION["usuario"];
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="navbar">
  <div class="logo">Portal de Vagas</div>
  <div class="nav-actions">
    <a href="logout.php">Sair</a>
  </div>
</div>

<div class="layout">

<div class="sidebar">
<ul>
<li>Dashboard</li>
</ul>
</div>

<div class="content">

<h2>Bem-vindo, <?php echo $user["nome"]; ?></h2>

<?php if ($user["tipo"] == "empresa") { ?>
<form method="POST">
<input name="titulo" placeholder="Título da vaga" required>
<textarea name="descricao" placeholder="Descrição" required></textarea>
<button type="submit">Publicar</button>
</form>
<?php } ?>

<div class="vagas-grid">
<?php
$result = $conn->query("
SELECT vagas.*, usuarios.nome AS empresa
FROM vagas
JOIN usuarios ON vagas.empresa_id = usuarios.id
");

while ($vaga = $result->fetch_assoc()) {
echo "
<div class='vaga-card'>
<h4>{$vaga['titulo']}</h4>
<p><strong>{$vaga['empresa']}</strong></p>
<p>{$vaga['descricao']}</p>
</div>
";
}
?>
</div>

</div>
</div>

</body>
</html>