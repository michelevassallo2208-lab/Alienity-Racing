<nav>
  <div class="logo">
    <img src="logo.png" alt="Alienity Logo">
    <span>Alienity Racing</span>
  </div>
  <ul>
    <li><a href="index.html">Home</a></li>
    <li><a href="candidatura.php">Invia Candidatura</a></li>
    <li><a href="login.php">Accesso</a></li>
    <?php if (isset($_SESSION["user_id"])): ?>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="logout.php">Logout</a></li>
    <?php endif; ?>
  </ul>
</nav>
