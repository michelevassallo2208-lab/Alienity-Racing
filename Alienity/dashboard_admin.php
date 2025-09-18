<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION['ruolo'] !== 'Owner') {
    header("Location: login.php");
    exit();
}

$candidature = $conn->query("SELECT * FROM candidature ORDER BY data_invio DESC");
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Pannello Candidature - Alienity Racing</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="stars"></div>
  <?php include 'navbar.php'; ?>

  <main class="dashboard-content">
    <header>
      <span class="tag">Recruiting</span>
      <h1>Candidature</h1>
      <p class="muted">Valuta le richieste ricevute e crea gli account per i profili approvati.</p>
    </header>

    <section>
      <table class="data-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Messaggio</th>
            <th>Data</th>
            <th>Stato</th>
            <th class="actions-heading">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $candidature->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['nome']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo nl2br(htmlspecialchars($row['messaggio'])); ?></td>
              <td><?php echo htmlspecialchars($row['data_invio']); ?></td>
              <td><?php echo htmlspecialchars($row['stato']); ?></td>
              <td class="actions-cell">
                <?php if ($row['stato'] === 'In attesa'): ?>
                  <div class="table-actions">
                    <form action="process_candidatura_admin.php" method="POST" class="table-action">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button name="action" value="accetta">Accetta</button>
                    </form>
                    <form action="process_candidatura_admin.php" method="POST" class="table-action">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button name="action" value="rifiuta">Rifiuta</button>
                    </form>
                  </div>
                <?php elseif ($row['stato'] === 'Accettata'): ?>
                  <form action="crea_utente.php" method="POST" class="owner-account-form">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
                    <input type="hidden" name="nome" value="<?php echo htmlspecialchars($row['nome']); ?>">
                    <input type="text" name="username" placeholder="Username" required>
                    <select name="ruolo" required>
                      <option value="Racer">Racer</option>
                      <option value="Pro Racer">Pro Racer</option>
                      <option value="Team Principal">Team Principal</option>
                    </select>
                    <input type="password" name="password" placeholder="Password temporanea" required>
                    <button type="submit" class="full-span">Crea utente</button>
                  </form>
                <?php else: ?>
                  <span class="muted">Nessuna azione disponibile</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
