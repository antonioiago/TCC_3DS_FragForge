<?php
INCLUDE __DIR__.'/includes/head.php';
include __DIR__.'/includes/header.php';
?>

<main>
    <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>FragForge</title>
                <link rel="stylesheet" href="style/style.css">
                <!---- esse link é so pra usar uma fonte que tem um icone bonito pro botão de login no header---->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
                
            </head>

            <body>
                <div class="boasv">
                    <title>
                        FragForge
                    </title><!-- asdawsdwasd -->
                        <h2>
                            <p>
                                Bem vindo(a) ao FragForge! Sua última destinação para noticias de Overwatch!
                            </p>
                        </h2>
                </div>

                <container class="not">
                    <div class="card">
                        <img src="includes/imagens/overwatch.jpg" alt="overwatch">
                         <title>
                                Overwatch 2 
                            </title>
                                <p>
                                    <h2>
                                    Ex-diretor de Overwatch revela que Blizzard o ameaçou ao exigir lucros astronômicos do jogo
                                    </h2>
                                </p>
                            </a>
                        </div>
<!-- DIV DAS POSTAGENS -->
                    <div class="timeline">
                        <button onclick="window.open('http://localhost/TCC_3DS_FragForge/Site_E-Sports/post.php', '_blank', 'width=600,height=400')">Criar postagem</button>
                        <?php
        $instancia = new PDO('mysql:host=localhost;dbname=fragforge; charset=utf8','root', 'root');
        $stm = $instancia->query("SELECT 
                                       -- j.id_jogador,
                                        p.mensagem,
                                        j.nickname_jogador,
                                        p.print_estatistica     
                                    FROM post p
                                    JOIN jogador j ON p.id_jogador = j.id_jogador;");
        echo "<table>";
        echo "<tr><th>Nome</th><th>Mensagem</th><th>Imagem</tr>";
        foreach ($stm as $row) {
            echo "<tr>
                    <td>".$row["nickname_jogador"]."</td>
                    <td>".$row["mensagem"]."</td>
                    <td><img src='data:print_estatistica/png;base64," . base64_encode($row["print_estatistica"]) . "' width='100'/></td>
                </tr>";
        }
        echo "</table>";
    ?>
                    </div>
<!-- DIV DAS POSTAGENS -->
                    <div class="card">
                        <img src="includes/imagens/overwatch2.jpg" alt="overwatch2">
                            <title>
                                Overwatch 2 
                            </title>
                                <p>
                                    <h2>
                                    Time Overwatch sai vencedor do evento Conquest Meta com skin azul Echo agora reivindicável
                                    </h2>
                                </p>
                            </a>
                    </div>
            </container>

      

            </body>
            </html>


</main>
    



<?php
include __DIR__.'/includes/footer.php';
?>