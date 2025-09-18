<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $discord = $_POST["discord"];
    $steam = $_POST["steam"];
    $sr = $_POST["sr"];
    $dr = $_POST["dr"];
    $messaggio = $_POST["messaggio"];

    $sql = "INSERT INTO candidature (nome, email, discord_id, steam_name, sr, dr, messaggio) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $nome, $email, $discord, $steam, $sr, $dr, $messaggio);

    if ($stmt->execute()) {
        echo "<p style='color:lime'>✅ Candidatura inviata con successo!</p>";
    } else {
        echo "<p style='color:red'>❌ Errore: " . $conn->error . "</p>";
    }
}
?>
