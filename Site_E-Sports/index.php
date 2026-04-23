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
                // Conexão com senha vazia para XAMPP
                $instancia = new PDO('mysql:host=localhost;dbname=fragforge;charset=utf8', 'root', '');
                $instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stm = $instancia->query("
                    SELECT p.mensagem, j.nickname_jogador, p.print_estatistica     
                    FROM post p
                    JOIN jogador j ON p.id_jogador = j.id_jogador
                ");

                echo "<table>";
                echo "<thead><tr><th>Nome</th><th>Mensagem</th><th>Imagem</th></tr></thead>";
                echo "<tbody>";

                foreach ($stm as $row) {
                    $imagem = base64_encode($row["print_estatistica"]);
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["nickname_jogador"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["mensagem"]) . "</td>";
                    echo "<td><img src='data:image/jpg;base64,{$imagem}' width='100'></td>";
                    echo "</tr>";
                }

                echo "</tbody></table>";

            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erro ao conectar ao banco: " . $e->getMessage() . "</p>";
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
