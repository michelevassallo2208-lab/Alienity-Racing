<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["ruolo"], ["Team Principal", "Owner"])) {
    header("Location: login.php");
    exit();
}

$candidature = $conn->query("SELECT * FROM candidature ORDER BY data_invio DESC");
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Pannello Admin - Alienity Racing</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="stars"></div>
  <?php include "navbar.php"; ?>

  <main class="dashboard-content admin-dashboard">
    <h1>Gestione Candidature</h1>

    <section class="card wide-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Messaggio</th>
            <th>Data</th>
            <th>Stato</th>
            <th>Azione</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($candidature && $candidature->num_rows > 0): ?>
            <?php while ($row = $candidature->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td class="message-cell"><?php echo nl2br(htmlspecialchars($row['messaggio'])); ?></td>
                <td><?php echo htmlspecialchars($row['data_invio']); ?></td>
                <td><span class="status-chip status-<?php echo strtolower(str_replace(' ', '-', $row['stato'])); ?>"><?php echo htmlspecialchars($row['stato']); ?></span></td>
                <td class="table-actions">
                  <?php if ($row['stato'] === 'In attesa'): ?>
                    <form class="inline-form" action="process_candidatura_admin.php" method="POST">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <div class="button-group">
                        <button name="action" value="accetta" type="submit">Accetta</button>
                        <button name="action" value="rifiuta" type="submit" class="secondary">Rifiuta</button>
                      </div>
                    </form>
                  <?php elseif ($row['stato'] === 'Accettata'): ?>
                    <form class="inline-form stacked" action="crea_utente.php" method="POST">
                      <input type="hidden" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
                      <input type="hidden" name="nome" value="<?php echo htmlspecialchars($row['nome']); ?>">
                      <select name="ruolo" required>
                        <option value="Racer">Racer</option>
                        <option value="Pro Racer">Pro Racer</option>
                        <option value="Team Principal">Team Principal</option>
                      </select>
                      <input type="password" name="password" placeholder="Password temporanea" required>
                      <button type="submit">Crea Utente</button>
                    </form>
                  <?php else: ?>
                    <span class="status-note">Nessuna azione necessaria</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="empty-state">Non ci sono candidature al momento.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>

  <script src="js/script.js"></script>
</body>
</html>
