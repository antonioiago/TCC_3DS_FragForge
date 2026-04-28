<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';
?>

<main>
    <div class="boasv">
        <h2>
            <p>
                Com dificuldade de montar uma equipe para jogar? Sem problemas!
                Bem vindo ao FragForge, o site onde você pode criar postagens para encontrar jogadores para jogar seus jogos favoritos! 
            </p>
        </h2>
    </div>

    <div class="not">
        <div class="card">
            <img src="includes/imagens/overwatch.jpg" alt="overwatch">
            <h3>Overwatch 2</h3>
            <p>Ex-diretor de Overwatch revela que Blizzard o ameaçou ao exigir lucros astronômicos do jogo</p>
        </div>

        <div class="timeline">
            <button class="btn-criar-postagem" onclick="window.open('http://localhost/TCC_3DS_FragForge/Site_E-Sports/post.php', '_blank', 'width=600,height=400')">
                Criar postagem
            </button>
            
<?php
try {
    $instancia = new PDO('mysql:host=localhost;dbname=fragforge;charset=utf8', 'root', 'root');
    $instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stm = $instancia->query("
        SELECT 
            p.mensagem, 
            j.nickname_jogador, 
            j.id_jogador, -- 👈 IMPORTANTE
            p.print_estatistica,
            f.icon_funcao,
            pa.icon_patente
        FROM post p
        JOIN jogador j ON p.id_jogador = j.id_jogador
        LEFT JOIN funcao f ON j.id_funcao = f.id_funcao
        LEFT JOIN patente pa ON j.id_patente = pa.id_patente
        ORDER BY p.id_post DESC
    ");

    echo "<div class='feed'>";

    foreach ($stm as $row) {

        echo "<div class='post'>";

        // HEADER
        echo "<div class='post-header'>";
        
        // 🔗 NOME COM LINK PARA O PERFIL
        echo "<strong>
                <a href='perfil.php?id=" . $row["id_jogador"] . "' style='color:white; text-decoration:none;'>
                    " . htmlspecialchars($row["nickname_jogador"]) . "
                </a>
              </strong>";

        if (!empty($row["icon_funcao"])) {
            echo "<img class='icon' src='" . $row["icon_funcao"] . "'>";
        }

        if (!empty($row["icon_patente"])) {
            echo "<img class='icon' src='" . $row["icon_patente"] . "'>";
        }

        echo "</div>";

        // MENSAGEM
        echo "<p class='mensagem'>" . htmlspecialchars($row["mensagem"]) . "</p>";

        // IMAGEM
        if (!empty($row["print_estatistica"])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipo = $finfo->buffer($row["print_estatistica"]);
            $img = base64_encode($row["print_estatistica"]);
            echo "<img class='post-img' src='data:{$tipo};base64,{$img}'>";
        }

        echo "</div>";
    }

    echo "</div>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
        </div>

        <div class="card">
            <img src="includes/imagens/overwatch2.jpg" alt="overwatch2">
            <h3>Overwatch 2</h3>
            <p>Time Overwatch sai vencedor do evento Conquest Meta com skin azul Echo agora reivindicável</p>
        </div>
    </div>
</main>

<?php
include __DIR__.'/includes/footer.php';
?>