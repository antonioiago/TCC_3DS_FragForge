<form action="enviar_post.php" method="post">
    <input type="text" name="mensagem" id="mensagem"  placeholder="Escreve um post"><br>
    <input type="text" name="id_jogador" id="id_jogador" placeholder="Id jogador"><br>
    <input type="submit" value="Postar">
</form>
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