<?php
$conn = new mysqli("localhost", "root", "root", "fragforge");

// Pega as últimas 50 mensagens
$res = $conn->query("SELECT * FROM mensagens ORDER BY data_envio ASC LIMIT 50");

while ($row = $res->fetch_assoc()) {
    echo "<div class='msg'>";
    echo "<b>{$row['nickname']}:</b> {$row['mensagem']}";
    echo "<br><small style='color:gray; font-size: 10px;'>" . date('H:i', strtotime($row['data_envio'])) . "</small>";
    echo "</div>";
}
?>