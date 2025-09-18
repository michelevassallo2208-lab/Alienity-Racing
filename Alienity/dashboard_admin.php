<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["ruolo"], ["Team Principal", "Owner"])) {
    header("Location: login.php");
    exit();
}

$isOwner = $_SESSION["ruolo"] === "Owner";

$alerts = $_SESSION['dashboard_alerts'] ?? [];
unset($_SESSION['dashboard_alerts']);

$activeTab = $_GET['tab'] ?? 'candidature';
$allowedTabs = ['candidature', 'gestione-squadre'];
if ($isOwner) {
    $allowedTabs[] = 'gestione-utenze';
}
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'candidature';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $formType = $_POST['form_type'] ?? '';
    $redirectTab = $_POST['active_tab'] ?? 'candidature';

    if (!in_array($redirectTab, $allowedTabs, true)) {
        $redirectTab = 'candidature';
    }

    $addAlert = function (string $type, string $message) {
        if (!isset($_SESSION['dashboard_alerts']) || !is_array($_SESSION['dashboard_alerts'])) {
            $_SESSION['dashboard_alerts'] = [];
        }
        $_SESSION['dashboard_alerts'][] = ['type' => $type, 'message' => $message];
    };

    if ($formType === 'candidatura') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $action = $_POST['action'] ?? '';

        if ($id > 0 && in_array($action, ['accetta', 'rifiuta'], true)) {
            $status = $action === 'accetta' ? 'Accettata' : 'Rifiutata';
            $stmt = $conn->prepare("UPDATE candidature SET stato=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param("si", $status, $id);
                if ($stmt->execute()) {
                    $addAlert('success', "Candidatura aggiornata a {$status}.");
                } else {
                    $addAlert('error', "Errore durante l'aggiornamento della candidatura.");
                }
            } else {
                $addAlert('error', "Impossibile preparare la modifica della candidatura.");
            }
        } else {
            $addAlert('error', "Dati candidatura non validi.");
        }
    } elseif ($formType === 'create_user') {
        if (!$isOwner) {
            $addAlert('error', "Non hai i permessi per creare un nuovo utente.");
        } else {
            $candidaturaId = isset($_POST['candidatura_id']) ? (int) $_POST['candidatura_id'] : 0;
            $username = trim($_POST['username'] ?? '');
            $role = $_POST['ruolo'] ?? '';
            $password = $_POST['password'] ?? '';
            $validRoles = ['Racer', 'Pro Racer', 'Team Principal'];

            if ($candidaturaId <= 0 || $username === '' || $password === '' || !in_array($role, $validRoles, true)) {
                $addAlert('error', "Dati per la creazione dell'utente non validi.");
            } else {
                $candidateStmt = $conn->prepare("SELECT stato FROM candidature WHERE id=?");
                if ($candidateStmt) {
                    $candidateStmt->bind_param("i", $candidaturaId);
                    $candidateStmt->execute();
                    $candidateResult = $candidateStmt->get_result();
                    $candidate = $candidateResult ? $candidateResult->fetch_assoc() : null;

                    if (!$candidate) {
                        $addAlert('error', "Candidatura non trovata.");
                    } elseif ($candidate['stato'] !== 'Accettata') {
                        $addAlert('error', "La candidatura deve essere nello stato 'Accettata' prima di registrare l'utente.");
                    } else {
                        $checkStmt = $conn->prepare("SELECT id FROM utenti WHERE username=?");
                        if ($checkStmt) {
                            $checkStmt->bind_param("s", $username);
                            $checkStmt->execute();
                            $checkResult = $checkStmt->get_result();
                            if ($checkResult && $checkResult->num_rows > 0) {
                                $addAlert('error', "Esiste già un utente con questo username.");
                            } else {
                                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                                $insertStmt = $conn->prepare("INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, ?)");
                                if ($insertStmt) {
                                    $insertStmt->bind_param("sss", $username, $hashedPassword, $role);
                                    if ($insertStmt->execute()) {
                                        $statusStmt = $conn->prepare("UPDATE candidature SET stato='Registrata' WHERE id=?");
                                        if ($statusStmt) {
                                            $statusStmt->bind_param("i", $candidaturaId);
                                            $statusStmt->execute();
                                        }
                                        $addAlert('success', "Utente creato e candidatura contrassegnata come registrata.");
                                        $redirectTab = 'candidature';
                                    } else {
                                        $addAlert('error', "Errore durante la creazione dell'utente.");
                                    }
                                } else {
                                    $addAlert('error', "Impossibile preparare l'inserimento dell'utente.");
                                }
                            }
                        } else {
                            $addAlert('error', "Impossibile verificare l'esistenza dell'utente.");
                        }
                    }
                } else {
                    $addAlert('error', "Impossibile recuperare la candidatura.");
                }
            }
        }
    } elseif ($formType === 'nuova_squadra') {
        $nome = trim($_POST['nome'] ?? '');
        $categoria = $_POST['categoria'] ?? '';
        if ($nome === '' || $categoria === '') {
            $addAlert('error', "Inserisci nome e categoria per creare una squadra.");
        } else {
            $stmt = $conn->prepare("INSERT INTO squadre (nome, categoria) VALUES (?, ?)");
            if ($stmt) {
                $stmt->bind_param("ss", $nome, $categoria);
                if ($stmt->execute()) {
                    $addAlert('success', "Squadra '{$nome}' creata con successo.");
                    $redirectTab = 'gestione-squadre';
                } else {
                    $addAlert('error', "Errore durante la creazione della squadra.");
                }
            } else {
                $addAlert('error', "Impossibile preparare l'inserimento della squadra.");
            }
        }
    } elseif ($formType === 'assegna_pilota') {
        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $squadraId = isset($_POST['squadra_id']) ? (int) $_POST['squadra_id'] : 0;
        if ($userId <= 0 || $squadraId <= 0) {
            $addAlert('error', "Seleziona sia il pilota che la squadra.");
        } else {
            $stmt = $conn->prepare("UPDATE utenti SET squadra_id=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param("ii", $squadraId, $userId);
                if ($stmt->execute()) {
                    $addAlert('success', "Pilota assegnato correttamente alla squadra.");
                    $redirectTab = 'gestione-squadre';
                } else {
                    $addAlert('error', "Errore durante l'assegnazione del pilota.");
                }
            } else {
                $addAlert('error', "Impossibile preparare l'aggiornamento del pilota.");
            }
        }
    } elseif ($formType === 'update_user') {
        if (!$isOwner) {
            $addAlert('error', "Non hai i permessi per modificare le utenze.");
        } else {
            $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
            $username = trim($_POST['username'] ?? '');
            $role = $_POST['ruolo'] ?? '';
            $password = $_POST['password'] ?? '';
            $squadraInput = $_POST['squadra_id'] ?? '';
            $validRoles = ['Racer', 'Pro Racer', 'Team Principal', 'Owner'];

            if ($userId <= 0 || $username === '' || !in_array($role, $validRoles, true)) {
                $addAlert('error', "Dati per l'aggiornamento dell'utente non validi.");
            } else {
                $squadraId = ($squadraInput === '' || (int) $squadraInput === 0) ? null : (int) $squadraInput;

                if ($squadraId !== null) {
                    $updateStmt = $conn->prepare("UPDATE utenti SET username=?, ruolo=?, squadra_id=? WHERE id=?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("ssii", $username, $role, $squadraId, $userId);
                    }
                } else {
                    $updateStmt = $conn->prepare("UPDATE utenti SET username=?, ruolo=?, squadra_id=NULL WHERE id=?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("ssi", $username, $role, $userId);
                    }
                }

                if (!isset($updateStmt) || !$updateStmt) {
                    $addAlert('error', "Impossibile preparare l'aggiornamento dell'utente.");
                } elseif (!$updateStmt->execute()) {
                    $addAlert('error', "Errore durante l'aggiornamento dell'utente.");
                } else {
                    if ($password !== '') {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $pwdStmt = $conn->prepare("UPDATE utenti SET password=? WHERE id=?");
                        if ($pwdStmt) {
                            $pwdStmt->bind_param("si", $hashedPassword, $userId);
                            if (!$pwdStmt->execute()) {
                                $addAlert('error', "Utente aggiornato ma impossibile aggiornare la password.");
                                header("Location: dashboard_admin.php?tab=gestione-utenze");
                                exit();
                            }
                        } else {
                            $addAlert('error', "Utente aggiornato ma impossibile preparare l'aggiornamento della password.");
                            header("Location: dashboard_admin.php?tab=gestione-utenze");
                            exit();
                        }
                    }

                    $addAlert('success', "Utenza aggiornata con successo.");
                }
                $redirectTab = 'gestione-utenze';
            }
        }
    }

    header("Location: dashboard_admin.php?tab=" . urlencode($redirectTab));
    exit();
}

$candidature = $conn->query("SELECT * FROM candidature ORDER BY data_invio DESC");

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

$users = [];
if ($isOwner) {
    $usersQuery = $conn->query("SELECT u.id, u.username, u.ruolo, u.squadra_id, s.nome AS squadra_nome
        FROM utenti u
        LEFT JOIN squadre s ON u.squadra_id = s.id
        ORDER BY u.username");
    if ($usersQuery) {
        while ($row = $usersQuery->fetch_assoc()) {
            $users[] = $row;
        }
    }
}
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
    <h1>Pannello amministrazione</h1>

    <?php if (!empty($alerts)): ?>
      <div class="alert-stack">
        <?php foreach ($alerts as $alert): ?>
          <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?>">
            <?php echo htmlspecialchars($alert['message']); ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <section class="card tabbed-panel">
      <div class="tab-buttons">
        <button class="tab-button <?php echo $activeTab === 'candidature' ? 'is-active' : ''; ?>" type="button" data-panel-target="panel-candidature">Candidature</button>
        <button class="tab-button <?php echo $activeTab === 'gestione-squadre' ? 'is-active' : ''; ?>" type="button" data-panel-target="panel-gestione-squadre">Gestione Squadre</button>
        <?php if ($isOwner): ?>
          <button class="tab-button <?php echo $activeTab === 'gestione-utenze' ? 'is-active' : ''; ?>" type="button" data-panel-target="panel-gestione-utenze">Gestione Utenze</button>
        <?php endif; ?>
      </div>

      <div class="tab-panels">
        <section class="tab-panel <?php echo $activeTab === 'candidature' ? 'is-active' : ''; ?>" data-panel="panel-candidature">
          <h2>Gestione candidature</h2>
          <p class="panel-description">Approva, rifiuta e registra nuovi piloti direttamente dal pannello.</p>
          <div class="table-wrapper">
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
                          <form class="inline-form" method="POST">
                            <input type="hidden" name="form_type" value="candidatura">
                            <input type="hidden" name="active_tab" value="candidature">
                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                            <div class="button-group">
                              <button name="action" value="accetta" type="submit">Accetta</button>
                              <button name="action" value="rifiuta" type="submit" class="secondary">Rifiuta</button>
                            </div>
                          </form>
                        <?php elseif ($row['stato'] === 'Accettata' && $isOwner): ?>
                          <form class="inline-form stacked" method="POST">
                            <input type="hidden" name="form_type" value="create_user">
                            <input type="hidden" name="active_tab" value="candidature">
                            <input type="hidden" name="candidatura_id" value="<?php echo (int) $row['id']; ?>">
                            <label for="username-<?php echo (int) $row['id']; ?>">Username</label>
                            <input id="username-<?php echo (int) $row['id']; ?>" type="text" name="username" value="<?php echo htmlspecialchars($row['nome']); ?>" required>
                            <label for="ruolo-<?php echo (int) $row['id']; ?>">Ruolo</label>
                            <select id="ruolo-<?php echo (int) $row['id']; ?>" name="ruolo" required>
                              <option value="Racer">Racer</option>
                              <option value="Pro Racer">Pro Racer</option>
                              <option value="Team Principal">Team Principal</option>
                            </select>
                            <label for="password-<?php echo (int) $row['id']; ?>">Password temporanea</label>
                            <input id="password-<?php echo (int) $row['id']; ?>" type="password" name="password" placeholder="Password temporanea" required>
                            <button type="submit">Registra utente</button>
                          </form>
                        <?php elseif ($row['stato'] === 'Accettata'): ?>
                          <span class="status-note">In attesa di registrazione da parte dell'owner</span>
                        <?php elseif ($row['stato'] === 'Registrata'): ?>
                          <span class="status-note">Utente registrato</span>
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
          </div>
        </section>

        <section class="tab-panel <?php echo $activeTab === 'gestione-squadre' ? 'is-active' : ''; ?>" data-panel="panel-gestione-squadre">
          <h2>Gestione squadre</h2>
          <p class="panel-description">Crea nuove squadre, assegna i piloti e controlla la composizione dei team.</p>

          <form class="panel-form" method="POST">
            <input type="hidden" name="form_type" value="nuova_squadra">
            <input type="hidden" name="active_tab" value="gestione-squadre">
            <input type="text" name="nome" placeholder="Nome squadra" required>
            <select name="categoria" required>
              <option value="">Seleziona categoria</option>
              <option value="GT">GT</option>
              <option value="LMP2">LMP2</option>
              <option value="Hypercar">Hypercar</option>
            </select>
            <button type="submit">Crea Squadra</button>
          </form>

          <form class="panel-form" method="POST">
            <input type="hidden" name="form_type" value="assegna_pilota">
            <input type="hidden" name="active_tab" value="gestione-squadre">
            <label for="user_id">Seleziona pilota</label>
            <select name="user_id" id="user_id" required>
              <option value="">Scegli pilota</option>
              <?php foreach ($utentiList as $u): ?>
                <option value="<?php echo (int) $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?> (<?php echo htmlspecialchars($u['ruolo']); ?>)</option>
              <?php endforeach; ?>
            </select>

            <label for="squadra_id">Assegna a squadra</label>
            <select name="squadra_id" id="squadra_id" required>
              <option value="">Scegli squadra</option>
              <?php foreach ($squadreList as $s): ?>
                <option value="<?php echo (int) $s['id']; ?>"><?php echo htmlspecialchars($s['nome']); ?> (<?php echo htmlspecialchars($s['categoria']); ?>)</option>
              <?php endforeach; ?>
            </select>
            <button type="submit">Assegna pilota</button>
          </form>

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

        <?php if ($isOwner): ?>
          <section class="tab-panel <?php echo $activeTab === 'gestione-utenze' ? 'is-active' : ''; ?>" data-panel="panel-gestione-utenze">
            <h2>Gestione utenze</h2>
            <p class="panel-description">Aggiorna username, ruolo, password e squadra degli utenti già registrati.</p>

            <?php if (!empty($users)): ?>
              <div class="user-management-list">
                <?php $ruoliDisponibili = ['Racer', 'Pro Racer', 'Team Principal', 'Owner']; ?>
                <?php foreach ($users as $user): ?>
                  <article class="user-card">
                    <header class="user-card__header">
                      <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                      <span class="role-chip"><?php echo htmlspecialchars($user['ruolo']); ?></span>
                    </header>
                    <form class="user-card__form" method="POST">
                      <input type="hidden" name="form_type" value="update_user">
                      <input type="hidden" name="active_tab" value="gestione-utenze">
                      <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">

                      <div class="form-grid">
                        <label>
                          <span>Username</span>
                          <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </label>
                        <label>
                          <span>Ruolo</span>
                          <select name="ruolo" required>
                            <?php foreach ($ruoliDisponibili as $ruolo): ?>
                              <option value="<?php echo $ruolo; ?>" <?php echo $user['ruolo'] === $ruolo ? 'selected' : ''; ?>><?php echo $ruolo; ?></option>
                            <?php endforeach; ?>
                          </select>
                        </label>
                        <label>
                          <span>Password</span>
                          <input type="password" name="password" placeholder="Lascia vuoto per non cambiare">
                        </label>
                        <label>
                          <span>Squadra</span>
                          <select name="squadra_id">
                            <option value="">Nessuna squadra</option>
                            <?php foreach ($squadreList as $squadra): ?>
                              <option value="<?php echo (int) $squadra['id']; ?>" <?php echo ((int) $user['squadra_id'] === (int) $squadra['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($squadra['nome']); ?> (<?php echo htmlspecialchars($squadra['categoria']); ?>)</option>
                            <?php endforeach; ?>
                          </select>
                        </label>
                      </div>

                      <div class="button-group end">
                        <button type="submit">Salva modifiche</button>
                      </div>
                    </form>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="empty-state">Non ci sono utenti registrati.</p>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <script src="js/script.js"></script>
</body>
</html>
