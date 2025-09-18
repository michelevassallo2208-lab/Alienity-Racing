<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["ruolo"], ["Team Principal", "Owner"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["nuova_squadra"])) {
        $nome = trim($_POST["nome"] ?? '');
        $categoria = $_POST["categoria"] ?? '';
        if ($nome !== '' && $categoria !== '') {
            $stmt = $conn->prepare("INSERT INTO squadre (nome, categoria) VALUES (?, ?)");
            $stmt->bind_param("ss", $nome, $categoria);
            $stmt->execute();
        }
    }

    if (isset($_POST["assegna"])) {
        $user_id = (int) ($_POST["user_id"] ?? 0);
        $squadra_id = (int) ($_POST["squadra_id"] ?? 0);
        if ($user_id > 0 && $squadra_id > 0) {
            $stmt = $conn->prepare("UPDATE utenti SET squadra_id=? WHERE id=?");
            $stmt->bind_param("ii", $squadra_id, $user_id);
            $stmt->execute();
        }
    }
}

$squadreList = [];
$squadreQuery = $conn->query("SELECT id, nome, categoria FROM squadre ORDER BY nome");
if ($squadreQuery) {
    while ($row = $squadreQuery->fetch_assoc()) {
        $squadreList[] = $row;
    }
}

$utentiList = [];
$utentiQuery = $conn->query("SELECT id, username, ruolo FROM utenti WHERE ruolo IN ('Racer','Pro Racer') ORDER BY username");
if ($utentiQuery) {
    while ($row = $utentiQuery->fetch_assoc()) {
        $utentiList[] = $row;
    }
}

$teamOverview = [];
$overviewQuery = $conn->query("SELECT s.id, s.nome, s.categoria, GROUP_CONCAT(u.username ORDER BY u.username SEPARATOR ', ') AS membri
    FROM squadre s
    LEFT JOIN utenti u ON s.id = u.squadra_id
    GROUP BY s.id, s.nome, s.categoria
    ORDER BY s.nome");
if ($overviewQuery) {
    while ($row = $overviewQuery->fetch_assoc()) {
        $teamOverview[] = $row;
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
  <?php include "navbar.php"; ?>

  <main class="content">
    <h1>Gestione Squadre</h1>

    <section class="card tabbed-panel">
      <div class="tab-buttons">
        <button class="tab-button is-active" type="button" data-panel-target="panel-create">Crea squadra</button>
        <button class="tab-button" type="button" data-panel-target="panel-assign">Assegna piloti</button>
        <button class="tab-button" type="button" data-panel-target="panel-overview">Elenco squadre</button>
      </div>

      <div class="tab-panels">
        <section class="tab-panel is-active" data-panel="panel-create">
          <h2>Crea nuova squadra</h2>
          <p class="panel-description">Definisci nome e categoria per aggiungere una nuova squadra al campionato.</p>
          <form class="panel-form" method="POST">
            <input type="hidden" name="nuova_squadra" value="1">
            <input type="text" name="nome" placeholder="Nome squadra" required>
            <select name="categoria" required>
              <option value="">Seleziona categoria</option>
              <option value="GT">GT</option>
              <option value="LMP2">LMP2</option>
              <option value="Hypercar">Hypercar</option>
            </select>
            <button type="submit">Crea Squadra</button>
          </form>
        </section>

        <section class="tab-panel" data-panel="panel-assign">
          <h2>Assegna piloti</h2>
          <p class="panel-description">Scegli un pilota e seleziona la squadra di appartenenza.</p>
          <form class="panel-form" method="POST">
            <input type="hidden" name="assegna" value="1">
            <label for="user_id">Seleziona pilota</label>
            <select name="user_id" id="user_id" required>
              <option value="">Scegli pilota</option>
              <?php foreach ($utentiList as $u): ?>
                <option value="<?php echo $u['id']; ?>">
                  <?php echo htmlspecialchars($u['username']); ?> (<?php echo htmlspecialchars($u['ruolo']); ?>)
                </option>
              <?php endforeach; ?>
            </select>

            <label for="squadra_id">Assegna a squadra</label>
            <select name="squadra_id" id="squadra_id" required>
              <option value="">Scegli squadra</option>
              <?php foreach ($squadreList as $s): ?>
                <option value="<?php echo $s['id']; ?>">
                  <?php echo htmlspecialchars($s['nome']); ?> (<?php echo htmlspecialchars($s['categoria']); ?>)
                </option>
              <?php endforeach; ?>
            </select>

            <button type="submit">Assegna pilota</button>
          </form>
        </section>

        <section class="tab-panel" data-panel="panel-overview">
          <h2>Elenco squadre</h2>
          <p class="panel-description">Visualizza le squadre attive e i piloti assegnati.</p>
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Squadra</th>
                  <th>Categoria</th>
                  <th>Piloti</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($teamOverview)): ?>
                  <?php foreach ($teamOverview as $team): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($team['nome']); ?></td>
                      <td><?php echo htmlspecialchars($team['categoria']); ?></td>
                      <td>
                        <?php if (!empty($team['membri'])): ?>
                          <?php echo htmlspecialchars($team['membri']); ?>
                        <?php else: ?>
                          <span class="empty-state-text">Nessun pilota assegnato</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" class="empty-state">Non ci sono squadre registrate.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </section>
  </main>

  <script src="js/script.js"></script>
</body>
</html>
