<?php
include __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';
?>

<style>
.feed {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.post {
    background: #1e293b;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    transition: 0.2s;
}

.post:hover {
    transform: scale(1.01);
}

.post-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.post-header strong {
    flex: 1;
    color: white;
}

.icon {
    width: 28px;
    height: 28px;
}

.mensagem {
    margin: 10px 0;
    color: #cbd5f5;
}

.post-img {
    width: 100%;
    border-radius: 10px;
    margin-top: 10px;
}
</style>

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
        
        echo "<strong>" . htmlspecialchars($row["nickname_jogador"]) . "</strong>";

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