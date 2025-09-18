<nav>
  <div class="logo">
    <img src="logo.png" alt="Alienity Racing">
    <span>Alienity Racing</span>
  </div>
  <ul>
    <li><a href="index.html">Home</a></li>
    <li><a href="candidatura.php">Candidati</a></li>
    <?php if (isset($_SESSION["user_id"])): ?>
      <li><a href="dashboard.php">Dashboard</a></li>
      <?php if (in_array($_SESSION["ruolo"], ["Team Principal", "Owner"])): ?>
        <li><a href="squadre.php">Gestione squadre</a></li>
      <?php endif; ?>
      <?php if ($_SESSION["ruolo"] === "Owner"): ?>
        <li><a href="dashboard_admin.php">Candidature</a></li>
      <?php endif; ?>
      <li><a href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php">Accedi</a></li>
    <?php endif; ?>
  </ul>
</nav>
