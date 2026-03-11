<?php
include __DIR__.'/../includes/head.php';
include __DIR__.'/../includes/header.php';
?>

<main class="auth-container">

<form method="POST" action="../cadastrar.php">

<h2>Cadastrar</h2>

<label>Email</label>
<input type="email" name="email" required>

<label>Senha</label>
<input type="password" name="password" required>

<label>Repita a senha</label>
<input type="password" name="chkpassword" required>

<label>Nome de usuário</label>
<input type="text" name="nameuser" required>

<input class="submit" type="submit" value="Cadastrar">

<p>
Já possui conta?
<a href="form-login.php">Entrar</a>
</p>

</form>

</main>

<?php
include __DIR__.'/../includes/footer.php';
?>