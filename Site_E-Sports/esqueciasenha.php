<?php
require_once 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Busca o e-mail nas duas tabelas
    $stmt = $conn->prepare("SELECT email_jogador as email FROM jogador WHERE email_jogador = :email 
                            UNION 
                            SELECT email_adm as email FROM adm WHERE email_adm = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $expiracao = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Salva na tabela recuperacao_senha que criamos no MySQL
        $sql = "INSERT INTO recuperacao_senha (email, token, data_expiracao) VALUES (?, ?, ?)";
        $query = $conn->prepare($sql);
        
        if ($query->execute([$email, $token, $expiracao])) {
            // O link aponta para o nome correto do arquivo redefinir-senha.php
            $link = "http://localhost/TCC_3DS_FragForge/Site_E-Sports/redefinir-senha.php?token=" . $token;
            
            echo "<body style='font-family: Arial; text-align: center; padding: 50px; background: #f4f4f4;'>";
            echo "<div style='background: white; padding: 30px; display: inline-block; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
            echo "<h2 style='color: #3432c7;'>Solicitação Confirmada!</h2>";
            echo "<p>Clique no botão abaixo para simular o e-mail de recuperação:</p>";
            echo "<a href='$link' style='background: #4bf89c; color: black; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; margin-top: 20px;'>REDEFINIR MINHA SENHA</a>";
            echo "</div></body>";
        }
    } else {
        echo "<script>alert('E-mail não cadastrado!'); window.location.href='form-esqueciasenha.php';</script>";
    }
}
?>