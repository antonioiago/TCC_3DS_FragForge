<?php
    const host = "localhost";
    const port = "3306";
    const user = "root";
    const senha = "root";
    const banco = "fragforge";

    try {
        // Cria a conexão PDO com o banco de dados
        $conn = new PDO("mysql:host=".host.";port=".port.";dbname=".banco, user, senha);
        // Configura para disparar exceções em caso de erro
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) { // Corrigido de PDOexpection para PDOException
        error_log("Erro de Conexão: " . $e->getMessage());
        die("Ops! Tivemos um problema técnico. Tente novamente mais tarde.");
    }
?>