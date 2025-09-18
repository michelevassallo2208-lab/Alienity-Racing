<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];
$sql = "SELECT u.username, u.ruolo, t.nome as team 
        FROM utenti u 
        LEFT JOIN teams t ON u.team_id = t.id 
        WHERE u.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Alienity Racing</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="stars"></div>
  <?php include "navbar.php"; ?>

  <main class="dashboard-content">
    <h1>Benvenuto, <?php echo $user['username']; ?> 👽</h1>
    <p>Ruolo: <b><?php echo $user['ruolo']; ?></b></p>

    <?php if ($user['team']): ?>
      <p>Appartieni alla squadra: <b><?php echo $user['team']; ?></b></p>
    <?php else: ?>
      <p>Non sei ancora assegnato a nessuna squadra.</p>
    <?php endif; ?>

    <section class="card">
      <?php if ($user['ruolo'] == "Racer" || $user['ruolo'] == "Pro Racer"): ?>
        <h3>Area Racer</h3>
        <p>Calendario gare, statistiche e briefing.</p>
      <?php elseif ($user['ruolo'] == "Team Principal"): ?>
        <h3>Gestione Team</h3>
        <p>Visualizza e gestisci i tuoi piloti.</p>
      <?php elseif ($user['ruolo'] == "Owner"): ?>
        <h3>Pannello Owner</h3>
        <p>Gestisci utenti, squadre e candidature.</p>
        <a href="dashboard_admin.php" class="btn">Vai al Pannello</a>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
