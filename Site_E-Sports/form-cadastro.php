<?php
session_start();
include __DIR__.'/includes/formhead.php'
?>


<?php
$conn = new mysqli("localhost", "root", "root", "fragforge");

$funcoes = $conn->query("SELECT * FROM funcao");
$patentes = $conn->query("SELECT * FROM patente");
?>

<main class="container-cadastro">

    <h3>Cadastro</h3>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="erro">
            <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="cadastrar.php" onsubmit="return validarForm()">

        <div class="input-box">
            <input type="text" name="nickname_jogador" placeholder="Usuário" required>
        </div>

        <div class="input-box">
            <input type="email" name="email_jogador" placeholder="Email" required>
        </div>

        <div class="input-box">
            <input type="text" name="codigo_battlenet" placeholder="BattleTag ex: jogador#1234" required>
        </div>

        <div class="input-box">
            <input type="password" name="senha_jogador" id="senha" placeholder="Senha" required>
        </div>

        <div class="input-box">
            <input type="password" name="chkpassword" id="chkpassword" placeholder="Repita a senha" required>
        </div>

        <div class="input-box">
            <select name="id_funcao" required>

                <option value="">Função</option>

                <?php
                while($f = $funcoes->fetch_assoc()) {

                    echo "
                    <option value='{$f['id_funcao']}'>
                        {$f['nome_funcao']}
                    </option>";
                }
                ?>

            </select>
        </div>

        <div class="input-box">
            <select name="id_patente">

                <option value="">Patente</option>

                <?php
                while($p = $patentes->fetch_assoc()) {

                    echo "
                    <option value='{$p['id_patente']}'>
                        {$p['nome_patente']}
                    </option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn-cadastro">
            Criar Conta
        </button>

        <div class="login-link">
            Já possui conta?
            <a href="form-login.php">Fazer Login</a>
        </div>

    </form>

</main>

<script>

function validarForm() {

    const senha = document.getElementById("senha").value;
    const chk = document.getElementById("chkpassword").value;

    if (senha !== chk) {

        alert("As senhas não coincidem!");
        return false;
    }

    if (senha.length < 6) {

        alert("A senha deve ter pelo menos 6 caracteres!");
        return false;
    }

    return true;
}

</script>

</body>