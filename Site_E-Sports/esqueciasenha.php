<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';
require_once 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email']; // Nome do campo no seu formulário

    $stmt = $conn->prepare("SELECT email_jogador as email FROM jogador WHERE email_jogador = :email 
                            UNION 
                            SELECT email_adm as email FROM adm WHERE email_adm = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $expiracao = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $sql = "INSERT INTO recuperacao_senha (email, token, data_expiracao) VALUES (?, ?, ?)";
        $query = $conn->prepare($sql);
        
        if ($query->execute([$email, $token, $expiracao])) {
            $link = "http://localhost/TCC_3DS_FragForge/Site_E-Sports/redefinir-senha.php?token=" . $token;
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'sandbox.smtp.mailtrap.io'; 
                $mail->SMTPAuth   = true;
                $mail->Port       = 2525; 
                $mail->Username   = '46f2c6ed1271d7'; 
                $mail->Password   = 'a0c00a4cd25997'; 
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('suporte@fragforge.com.br', 'FragForge');
                $mail->addAddress($email); 
                $mail->isHTML(true);
                $mail->Subject = 'Recuperação de Senha';
                $mail->Body    = "Clique aqui para mudar sua senha: <a href='$link'>$link</a>";

                $mail->send();
                echo "<script>alert('Sucesso! Olhe seu Mailtrap.'); window.location.href='form-login.php';</script>";
            } catch (Exception $e) { echo "Erro: {$mail->ErrorInfo}"; }
        }
    } else {
        echo "<script>alert('E-mail não cadastrado!'); window.location.href='form-esqueciasenha.php';</script>";
    }
}