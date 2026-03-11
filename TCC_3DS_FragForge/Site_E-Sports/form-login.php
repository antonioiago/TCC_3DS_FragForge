<?php
include __DIR__.'/../includes/head.php';
include __DIR__.'/../includes/header.php';
?>

<main class="auth-container">

<form method="POST" action="../login.php">

<h2>Entrar</h2>

<label>Email</label>
<input type="email" name="email" required>

<label>Senha</label>
<input type="password" name="senha" required>

<input class="submit" type="submit" value="Entrar">

<p>
Não possui conta?
<a href="form-cadastrar.php">Cadastre-se</a>
</p>

</form>

</main>

<?php
include __DIR__.'/../includes/footer.php';
?>