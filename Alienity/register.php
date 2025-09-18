<?php
require 'db.php';

$check = $conn->query("SELECT registration_locked FROM settings WHERE id=1");
$locked = $check->fetch_assoc()["registration_locked"];

if ($locked) {
    die("<h2>⚠️ Registrazione disattivata</h2><p>L'account iniziale è già stato creato. Contatta l'Owner.</p>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Creazione account Owner
    $sql = "INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, 'Owner')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        // Blocca registrazione
        $conn->query("UPDATE settings SET registration_locked=1 WHERE id=1");
        echo "<p style='color:lime'>✅ Account Owner creato! Ora puoi <a href='login.php'>accedere</a>.</p>";
        exit();
    } else {
        echo "<p style='color:red'>❌ Errore: " . $conn->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Registrazione - Alienity Racing</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="stars"></div>
  <?php include "navbar.php"; ?>
  <div class="form-container">
    <img src="logo.png" alt="Alienity Racing Logo" class="form-logo">
    <h1>Crea account Owner</h1>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Crea Owner</button>
    </form>
  </div>
</body>
</html>
