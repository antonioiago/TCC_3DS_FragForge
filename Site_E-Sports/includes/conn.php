<?php

$host = "aws-1-sa-east-1.pooler.supabase.com";
$port = "5432";
$dbname = "postgres";
$user = "postgres.oxflxsewydmzxfieejdl";
$password = "3dsfr@gF0rg3";

try {

    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password
    );
    

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //echo "Conectado ao Supabase!"; 

} catch(PDOException $e) {

    die("ERRO: " . $e->getMessage());
}
?>