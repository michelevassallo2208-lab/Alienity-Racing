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

  <main class="dashboard-content">
    <h1>Gestione Candidature</h1>

    <table>
      <tr>
        <th>Nome</th>
        <th>Email</th>
        <th>Messaggio</th>
        <th>Data</th>
        <th>Stato</th>
        <th>Azione</th>
      </tr>
      <?php while ($row = $candidature->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['nome']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['messaggio']); ?></td>
        <td><?php echo $row['data_invio']; ?></td>
        <td><?php echo $row['stato']; ?></td>
        <td>
          <?php if ($row['stato'] == 'In attesa'): ?>
            <form action="process_candidatura_admin.php" method="POST" style="display:inline;">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <td>
  <?php if ($row['stato'] == 'In attesa'): ?>
    <form action="process_candidatura_admin.php" method="POST">
      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
      <div class="action-buttons">
        <button name="action" value="accetta">Accetta</button>
        <button name="action" value="rifiuta">Rifiuta</button>
      </div>
    </form>
  <?php elseif ($row['stato'] == 'Accettata'): ?>
    <form action="crea_utente.php" method="POST">
      <input type="hidden" name="email" value="<?php echo $row['email']; ?>">
      <input type="hidden" name="nome" value="<?php echo $row['nome']; ?>">
      <select name="ruolo" required>
        <option value="Racer">Racer</option>
        <option value="Pro Racer">Pro Racer</option>
        <option value="Team Principal">Team Principal</option>
      </select>
      <input type="password" name="password" placeholder="Password temporanea" required>
      <button type="submit">Crea Utente</button>
    </form>
  <?php endif; ?>
</td>

            </form>
          <?php elseif ($row['stato'] == 'Accettata'): ?>
            <form action="crea_utente.php" method="POST" style="display:inline;">
              <input type="hidden" name="email" value="<?php echo $row['email']; ?>">
              <input type="hidden" name="nome" value="<?php echo $row['nome']; ?>">
              <select name="ruolo" required>
                <option value="Racer">Racer</option>
                <option value="Pro Racer">Pro Racer</option>
                <option value="Team Principal">Team Principal</option>
              </select>
              <input type="password" name="password" placeholder="Password temporanea" required>
              <button type="submit">Crea Utente</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </main>
</body>
</html>
