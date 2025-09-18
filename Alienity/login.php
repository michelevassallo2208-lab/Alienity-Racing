<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
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
        $errore = "Credenziali non valide";
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
  <?php include 'navbar.php'; ?>

  <div class="form-container">
    <img src="logo.png" alt="Alienity Racing Logo" class="form-logo" style="width:120px;">
    <h1>Area riservata</h1>
    <p class="muted">Accedi con le credenziali fornite dal Team Principal.</p>
    <?php if (!empty($errore)): ?>
      <p class="error"><?php echo htmlspecialchars($errore); ?></p>
    <?php endif; ?>
    <form method="POST">
      <label for="username">Username</label>
      <input id="username" type="text" name="username" placeholder="Il tuo username" required>
      <label for="password">Password</label>
      <input id="password" type="password" name="password" placeholder="Password" required>
      <button type="submit">Accedi</button>
    </form>
  </div>
</body>
</html>
