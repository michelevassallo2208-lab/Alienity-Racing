<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["ruolo"] != "Owner") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["nome"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $ruolo = $_POST["ruolo"];

    $sql = "INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $ruolo);

    if ($stmt->execute()) {
        echo "<p style='color:lime'>✅ Utente creato con successo! Ora può accedere.</p>";
    } else {
        echo "<p style='color:red'>❌ Errore: " . $conn->error . "</p>";
    }
}
?>
