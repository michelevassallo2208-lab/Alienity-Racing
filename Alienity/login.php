<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM utenti WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["ruolo"] = $user["ruolo"];
        header("Location: dashboard.php");
        exit();
    } else {
        $errore = "Credenziali non valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Login - Alienity Racing</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="stars"></div>
  <div class="form-container">
    <img src="logo.png" alt="Alienity Racing Logo" class="form-logo">
    <h1>Accedi</h1>
    <?php if (!empty($errore)) echo "<p class='error'>$errore</p>"; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Entra</button>
    </form>
    <p>Non hai un account? <a href="register.php">Registrati</a></p>
  </div>
</body>
</html>
