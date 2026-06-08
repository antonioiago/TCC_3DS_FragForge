<?php
include __DIR__.'/includes/conn.php';

try {

    $mensagem = $_POST['mensagem'] ?? '';
    $id_jogador = $_POST['id_jogador'] ?? null;

    $supabaseUrl = 'https://oxflxsewydmzxfieejdl.supabase.co';
    $anonKey = 'sb_publishable_fTi-vAXwMYFFXI61eMfbAQ_V0-u6YG_';
    $bucket = 'posts';

    $imagem = null;
    $video = null;

    // ==========================
    // UPLOAD IMAGEM
    // ==========================
    if (
        isset($_FILES['print_estatistica']) &&
        $_FILES['print_estatistica']['error'] === 0
    ) {

        $tmp = $_FILES['print_estatistica']['tmp_name'];

        $nomeArquivo =
            'img_' .
            $id_jogador .
            '_' .
            time() .
            '_' .
            basename($_FILES['print_estatistica']['name']);

        $conteudo = file_get_contents($tmp);

        $urlUpload =
            $supabaseUrl .
            "/storage/v1/object/$bucket/$nomeArquivo";

        $ch = curl_init($urlUpload);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $conteudo);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $anonKey",
            "apikey: $anonKey",
            "Content-Type: " . mime_content_type($tmp)
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {

            $imagem =
                $supabaseUrl .
                "/storage/v1/object/public/$bucket/$nomeArquivo";
        }
    }

    // ==========================
    // UPLOAD VÍDEO
    // ==========================
    if (
        isset($_FILES['jogada']) &&
        $_FILES['jogada']['error'] === 0
    ) {

        $tmp = $_FILES['jogada']['tmp_name'];

        $nomeArquivo =
            'video_' .
            $id_jogador .
            '_' .
            time() .
            '_' .
            basename($_FILES['jogada']['name']);

        $conteudo = file_get_contents($tmp);

        $urlUpload =
            $supabaseUrl .
            "/storage/v1/object/$bucket/$nomeArquivo";

        $ch = curl_init($urlUpload);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $conteudo);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $anonKey",
            "apikey: $anonKey",
            "Content-Type: " . mime_content_type($tmp)
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {

            $video =
                $supabaseUrl .
                "/storage/v1/object/public/$bucket/$nomeArquivo";
        }
    }

    // ==========================
    // INSERT POST
    // ==========================
    $stmt = $conn->prepare("
        INSERT INTO post (
            mensagem,
            id_jogador,
            print_estatistica,
            jogada
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $mensagem,
        $id_jogador,
        $imagem,
        $video
    ]);

    header("Location: post.php");
    exit;

} catch (PDOException $e) {

    echo "ERRO: " . $e->getMessage();

}
?>