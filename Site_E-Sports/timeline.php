<link rel="stylesheet" href="style/timeline.css">

<button onclick="window.open('http://localhost/TCC_3DS_FragForge/Site_E-Sports/post.php', '_blank', 'width=600,height=400')">
  Criar postagem
</button>

<?php


        $instancia = new PDO('mysql:host=localhost;dbname=fragforge; charset=utf8','root', 'root');
        $stm = $instancia->query("SELECT 
                                        p.id_post,
                                        p.mensagem,
                                        j.nickname_jogador     
                                    FROM post p
                                    JOIN jogador j ON p.id_jogador = j.id_jogador;");
        echo "<table>";
        echo "<tr><th>Id</th><th>Nome</th><th>Mensagem</tr>";
        foreach ($stm as $row) {
            echo "<tr>
                    <td>".$row["id_post"]."</td>
                    <td>".$row["nickname_jogador"]."</td>
                    <td>".$row["mensagem"]."</td>
                </tr>";
        }
        echo "</table>";


        include __DIR__.'/includes/footer.php';
    ?>