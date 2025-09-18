<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Candidatura - Alienity Racing</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="stars"></div>
  <?php include "navbar.php"; ?>

  <div class="form-container">
    <img src="logo.png" alt="Alienity Racing Logo" class="form-logo">
    <h1>Invia la tua candidatura</h1>
    <p class="form-subtitle">
      Compila il modulo con le tue informazioni, il nostro staff valuterà la tua candidatura.
    </p>
    <form action="process_candidatura.php" method="POST">
      <input type="text" name="nome" placeholder="Nome completo" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="discord" placeholder="ID Discord" required>
      <input type="text" name="steam" placeholder="Nome Steam" required>

      <label for="sr">SR (Safety Rating)</label>
      <select name="sr" id="sr" required>
        <option value="">-- Seleziona --</option>
        <option>B1</option><option>B2</option><option>B3</option>
        <option>S0</option><option>S1</option><option>S2</option><option>S3</option>
        <option>G0</option><option>G1</option><option>G2</option><option>G3</option>
        <option>P0</option><option>P1</option><option>P2</option><option>P3</option>
      </select>

      <label for="dr">DR (Driver Rating)</label>
      <select name="dr" id="dr" required>
        <option value="">-- Seleziona --</option>
        <option>B1</option><option>B2</option><option>B3</option>
        <option>S0</option><option>S1</option><option>S2</option><option>S3</option>
        <option>G0</option><option>G1</option><option>G2</option><option>G3</option>
        <option>P0</option><option>P1</option><option>P2</option><option>P3</option>
      </select>

      <textarea name="messaggio" placeholder="Parlaci di te e perché vuoi unirti al team..." required></textarea>
      <button type="submit">Invia candidatura</button>
    </form>
  </div>
</body>
</html>
