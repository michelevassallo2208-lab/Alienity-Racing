<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["ruolo"] != "Owner") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $ruolo = $_POST["ruolo"];

    $check = $conn->prepare("SELECT id FROM utenti WHERE username = ? LIMIT 1");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<p style='color:orange'>⚠️ Username già utilizzato, scegline un altro.</p>";
        return;
    }

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
