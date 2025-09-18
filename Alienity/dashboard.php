<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];
$sql = "SELECT u.username, u.ruolo, u.squadra_id, s.nome AS team_nome
        FROM utenti u
        LEFT JOIN squadre s ON u.squadra_id = s.id
        WHERE u.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$teamMembers = [];
if (!empty($user['squadra_id'])) {
    $memberSql = "SELECT username, ruolo FROM utenti WHERE squadra_id = ? ORDER BY FIELD(ruolo, 'Team Principal', 'Pro Racer', 'Racer'), username";
    $memberStmt = $conn->prepare($memberSql);
    $memberStmt->bind_param("i", $user['squadra_id']);
    $memberStmt->execute();
    $membersResult = $memberStmt->get_result();
    while ($row = $membersResult->fetch_assoc()) {
        $teamMembers[] = $row;
    }
}
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
    <h1>Benvenuto, <?php echo htmlspecialchars($user['username']); ?> 👽</h1>
    <p class="role-chip">Ruolo: <b><?php echo htmlspecialchars($user['ruolo']); ?></b></p>

    <?php if (!empty($user['team_nome']) && in_array($user['ruolo'], ['Racer', 'Pro Racer', 'Team Principal'])): ?>
      <section class="card team-card">
        <h3>La tua squadra</h3>
        <button class="link-button" type="button" data-toggle="team-members">
          <?php echo htmlspecialchars($user['team_nome']); ?>
        </button>
        <div id="team-members" class="collapsible">
          <?php if (!empty($teamMembers)): ?>
            <ul class="team-list">
              <?php foreach ($teamMembers as $member): ?>
                <li>
                  <span class="team-member-name"><?php echo htmlspecialchars($member['username']); ?></span>
                  <span class="team-member-role"><?php echo htmlspecialchars($member['ruolo']); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="empty-state">Non ci sono ancora piloti assegnati alla squadra.</p>
          <?php endif; ?>
        </div>
      </section>
    <?php elseif (!empty($user['team_nome'])): ?>
      <p>Appartieni alla squadra: <b><?php echo htmlspecialchars($user['team_nome']); ?></b></p>
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
        <a href="squadre.php" class="btn">Gestisci Squadre</a>
      <?php elseif ($user['ruolo'] == "Owner"): ?>
        <h3>Pannello Owner</h3>
        <p>Gestisci utenti, squadre e candidature.</p>
        <a href="dashboard_admin.php" class="btn">Vai al Pannello</a>
      <?php endif; ?>
    </section>
  </main>

  <script src="js/script.js"></script>
</body>
</html>
