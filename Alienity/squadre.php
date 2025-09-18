<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["ruolo"], ["Team Principal", "Owner"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["nuova_squadra"])) {
        $nome = $_POST["nome"];
        $categoria = $_POST["categoria"];
        $stmt = $conn->prepare("INSERT INTO squadre (nome, categoria) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $categoria);
        $stmt->execute();
    }

    if (isset($_POST["assegna"])) {
        $user_id = $_POST["user_id"];
        $squadra_id = $_POST["squadra_id"];
        $stmt = $conn->prepare("UPDATE utenti SET squadra_id=? WHERE id=?");
        $stmt->bind_param("ii", $squadra_id, $user_id);
        $stmt->execute();
    }
}

$squadre = $conn->query("SELECT * FROM squadre");
$utenti = $conn->query("SELECT id, username, ruolo, squadra_id FROM utenti WHERE ruolo IN ('Racer','Pro Racer')");
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

    <!-- Creazione nuova squadra -->
    <div class="form-container">
      <h2>Crea Nuova Squadra</h2>
      <form method="POST">
        <input type="hidden" name="nuova_squadra" value="1">
        <input type="text" name="nome" placeholder="Nome squadra" required>
        <select name="categoria">
          <option value="GT">GT</option>
          <option value="LMP2">LMP2</option>
          <option value="Hypercar">Hypercar</option>
        </select>
        <button type="submit">Crea Squadra</button>
      </form>
    </div>

    <!-- Assegna membri -->
    <div class="form-container">
      <h2>Assegna Membri</h2>
      <form method="POST">
        <input type="hidden" name="assegna" value="1">
        <label>Seleziona Utente</label>
        <select name="user_id" required>
          <?php while ($u = $utenti->fetch_assoc()): ?>
            <option value="<?php echo $u['id']; ?>">
              <?php echo $u['username']; ?> (<?php echo $u['ruolo']; ?>)
            </option>
          <?php endwhile; ?>
        </select>

        <label>Assegna a Squadra</label>
        <select name="squadra_id" required>
          <?php $squadre->data_seek(0); while ($s = $squadre->fetch_assoc()): ?>
            <option value="<?php echo $s['id']; ?>">
              <?php echo $s['nome']; ?> (<?php echo $s['categoria']; ?>)
            </option>
          <?php endwhile; ?>
        </select>

        <button type="submit">Assegna</button>
      </form>
    </div>

    <!-- Elenco squadre e membri -->
    <div class="card">
      <h2>Elenco Squadre</h2>
      <table>
        <tr>
          <th>Squadra</th>
          <th>Categoria</th>
          <th>Membri</th>
        </tr>
        <?php
        $result = $conn->query("
          SELECT s.id, s.nome, s.categoria, u.username
          FROM squadre s
          LEFT JOIN utenti u ON s.id = u.squadra_id
          ORDER BY s.id
        ");

        $current_team = null;
        $members = [];
        while ($row = $result->fetch_assoc()) {
            if ($current_team !== $row['id']) {
                if ($current_team !== null) {
                    echo "<tr><td>{$team_name}</td><td>{$team_cat}</td><td>".implode(", ", $members)."</td></tr>";
                }
                $current_team = $row['id'];
                $team_name = $row['nome'];
                $team_cat = $row['categoria'];
                $members = [];
            }
            if ($row['username']) $members[] = $row['username'];
        }
        if ($current_team !== null) {
            echo "<tr><td>{$team_name}</td><td>{$team_cat}</td><td>".implode(", ", $members)."</td></tr>";
        }
        ?>
      </table>
    </div>
  </main>
</body>
</html>
