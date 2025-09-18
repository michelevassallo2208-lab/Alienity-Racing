<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];
$sql = "SELECT u.username, u.ruolo, s.nome AS squadra_nome, s.categoria AS squadra_categoria
        FROM utenti u
        LEFT JOIN squadre s ON u.squadra_id = s.id
        WHERE u.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$teamOverview = [];
if (!empty($user['squadra_nome'])) {
    $teamOverview[] = [
        'nome' => $user['squadra_nome'],
        'categoria' => $user['squadra_categoria']
    ];
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
  <?php include 'navbar.php'; ?>

  <main class="dashboard-content">
    <header>
      <span class="tag">Area riservata</span>
      <h1>Benvenuto, <?php echo htmlspecialchars($user['username']); ?> 👽</h1>
      <p class="muted">Ruolo: <strong><?php echo htmlspecialchars($user['ruolo']); ?></strong></p>
    </header>

    <section class="dashboard-grid">
      <article class="card highlight">
        <h3>Le mie squadre</h3>
        <?php if ($teamOverview): ?>
          <ul style="list-style:none; padding:0; margin:16px 0 0; display:grid; gap:12px;">
            <?php foreach ($teamOverview as $team): ?>
              <li>
                <div class="flex-between">
                  <div>
                    <strong><?php echo htmlspecialchars($team['nome']); ?></strong><br>
                    <span class="muted">Categoria: <?php echo htmlspecialchars($team['categoria']); ?></span>
                  </div>
                  <span class="badge"><?php echo htmlspecialchars($team['categoria']); ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="muted">Non sei ancora stato assegnato a una squadra. Rimani pronto alla chiamata del Team Principal.</p>
        <?php endif; ?>
      </article>

      <article class="card">
        <h3>Briefing</h3>
        <p class="muted">
          Consulta periodicamente questa sezione per aggiornamenti su strategie di gara, sessioni di allenamento e
          materiali tecnici condivisi dallo staff.
        </p>
      </article>

      <?php if (in_array($user['ruolo'], ['Team Principal', 'Owner'])): ?>
      <article class="card">
        <h3>Gestione Squadre</h3>
        <p class="muted">
          Organizza le line-up delle classi HY, GTE e GT3. Assegna i racer alle categorie e monitora la composizione
          dei team.
        </p>
        <a class="btn" href="squadre.php">Apri pannello squadre</a>
      </article>
      <?php endif; ?>

      <?php if ($user['ruolo'] === 'Owner'): ?>
      <article class="card">
        <h3>Pannello candidature</h3>
        <p class="muted">
          Gestisci le richieste ricevute, approva i profili idonei e crea rapidamente gli account per inserirli nelle
          tue squadre.
        </p>
        <a class="btn secondary" href="dashboard_admin.php">Vai alle candidature</a>
      </article>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
