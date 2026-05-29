<?php

const host = "db.oxflxsewydmzxfieejdl.supabase.co";
const port = "5432";
const user = "postgres";
const senha = "SUA_SENHA";
const banco = "postgres";

try {

    $conn = new PDO(
        "pgsql:host=".host.";
        port=".port.";
        dbname=".banco.";
        sslmode=require",
        user,
        senha
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexão realizada com sucesso!";

} catch(PDOException $e) {

    echo "Erro: " . $e->getMessage();
}
?>