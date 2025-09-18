<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["ruolo"], ["Team Principal", "Owner"])) {
    header("Location: login.php");
    exit();
}

$messaggi = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["nuova_squadra"])) {
        $nome = trim($_POST["nome"]);
        $categoria = $_POST["categoria"];

        if ($nome !== '' && in_array($categoria, ['HY', 'GTE', 'GT3'])) {
            $stmt = $conn->prepare("INSERT INTO squadre (nome, categoria) VALUES (?, ?)");
            $stmt->bind_param("ss", $nome, $categoria);
            if ($stmt->execute()) {
                $messaggi[] = "✅ Squadra \"$nome\" creata";
            } else {
                $messaggi[] = "❌ Errore durante la creazione: " . $conn->error;
            }
        }
    }

    if (isset($_POST["assegna"])) {
        $user_id = (int) $_POST["user_id"];
        $squadra_id = (int) $_POST["squadra_id"];
        $stmt = $conn->prepare("UPDATE utenti SET squadra_id=? WHERE id=?");
        $stmt->bind_param("ii", $squadra_id, $user_id);
        if ($stmt->execute()) {
            $messaggi[] = "✅ Assegnazione aggiornata";
        }
    }

    if (isset($_POST['rimuovi'])) {
        $user_id = (int) $_POST['user_id'];
        $stmt = $conn->prepare("UPDATE utenti SET squadra_id=NULL WHERE id=?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $messaggi[] = "ℹ️ Pilota rimosso dalla squadra";
        }
    }
}

$squadre = $conn->query("SELECT * FROM squadre ORDER BY FIELD(categoria, 'HY','GTE','GT3'), nome");
$utenti = $conn->query("SELECT id, username, ruolo, squadra_id FROM utenti WHERE ruolo IN ('Racer','Pro Racer') ORDER BY username");

$elenco = $conn->query("SELECT s.id, s.nome, s.categoria, u.username
                         FROM squadre s
                         LEFT JOIN utenti u ON u.squadra_id = s.id
                         ORDER BY FIELD(s.categoria, 'HY','GTE','GT3'), s.nome, u.username");
$teams = [];
while ($row = $elenco->fetch_assoc()) {
    if (!isset($teams[$row['id']])) {
        $teams[$row['id']] = [
            'nome' => $row['nome'],
            'categoria' => $row['categoria'],
            'membri' => []
        ];
    }
    if (!empty($row['username'])) {
        $teams[$row['id']]['membri'][] = $row['username'];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Gestione Squadre - Alienity Racing</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="stars"></div>
  <?php include 'navbar.php'; ?>

  <main class="dashboard-content">
    <header class="flex-between">
      <div>
        <span class="tag">Controllo line-up</span>
        <h1>Gestione Squadre</h1>
        <p class="muted">Amministra le formazioni delle classi HY, GTE e GT3.</p>
      </div>
    </header>

    <?php if ($messaggi): ?>
      <div class="card highlight">
        <ul style="margin:0; padding-left:20px;">
          <?php foreach ($messaggi as $msg): ?>
            <li><?php echo htmlspecialchars($msg); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <section class="grid">
      <article class="card">
        <h3>Crea nuova squadra</h3>
        <form method="POST">
          <input type="hidden" name="nuova_squadra" value="1">
          <label for="nome">Nome squadra</label>
          <input id="nome" type="text" name="nome" placeholder="Es. Alienity Hypercar" required>

          <label for="categoria">Categoria</label>
          <select id="categoria" name="categoria">
            <option value="HY">HY</option>
            <option value="GTE">GTE</option>
            <option value="GT3">GT3</option>
          </select>

          <button type="submit">Crea squadra</button>
        </form>
      </article>

      <article class="card">
        <h3>Assegna pilota</h3>
        <form method="POST">
          <input type="hidden" name="assegna" value="1">

          <label for="pilota">Pilota</label>
          <select id="pilota" name="user_id" required>
            <option value="" disabled selected>Seleziona un pilota</option>
            <?php while ($u = $utenti->fetch_assoc()): ?>
              <option value="<?php echo $u['id']; ?>">
                <?php echo htmlspecialchars($u['username']); ?>
                <?php if ($u['ruolo'] === 'Pro Racer') echo ' · Pro'; ?>
              </option>
            <?php endwhile; ?>
          </select>

          <label for="squadra">Squadra</label>
          <select id="squadra" name="squadra_id" required>
            <option value="" disabled selected>Seleziona squadra</option>
            <?php $squadre->data_seek(0); while ($s = $squadre->fetch_assoc()): ?>
              <option value="<?php echo $s['id']; ?>">
                <?php echo htmlspecialchars($s['nome']); ?> · <?php echo htmlspecialchars($s['categoria']); ?>
              </option>
            <?php endwhile; ?>
          </select>

          <button type="submit">Assegna</button>
        </form>
      </article>
    </section>

    <section>
      <h3>Situazione squadre</h3>
      <div class="grid">
        <?php if ($teams): ?>
          <?php foreach ($teams as $team): ?>
            <article class="card">
              <div class="flex-between">
                <div>
                  <h4 style="margin:0;"><?php echo htmlspecialchars($team['nome']); ?></h4>
                  <span class="muted">Categoria: <?php echo htmlspecialchars($team['categoria']); ?></span>
                </div>
                <span class="badge"><?php echo htmlspecialchars($team['categoria']); ?></span>
              </div>
              <ul style="margin:18px 0 0; padding-left:18px;">
                <?php if ($team['membri']): ?>
                  <?php foreach ($team['membri'] as $member): ?>
                    <li><?php echo htmlspecialchars($member); ?></li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <li class="muted">Nessun pilota assegnato</li>
                <?php endif; ?>
              </ul>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="muted">Non sono ancora state create squadre.</p>
        <?php endif; ?>
      </div>
    </section>

    <section>
      <h3>Rimuovi assegnazione</h3>
      <article class="card">
        <form method="POST" class="flex-between" style="flex-wrap:wrap; gap:16px;">
          <input type="hidden" name="rimuovi" value="1">
          <div style="flex:1 1 220px;">
            <label for="pilota-rimuovi">Pilota</label>
            <select id="pilota-rimuovi" name="user_id" required>
              <option value="" disabled selected>Seleziona un pilota assegnato</option>
              <?php
              $assegnati = $conn->query("SELECT u.id, u.username, s.nome AS squadra FROM utenti u JOIN squadre s ON u.squadra_id=s.id ORDER BY u.username");
              while ($row = $assegnati->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>">
                  <?php echo htmlspecialchars($row['username']); ?> · <?php echo htmlspecialchars($row['squadra']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <button type="submit" class="btn secondary">Rimuovi pilota</button>
        </form>
      </article>
    </section>
  </main>
</body>
</html>
