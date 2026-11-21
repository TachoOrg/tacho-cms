<?php
  $dbHost = trim($_POST["db_host"] ?? "localhost");
  $dbName = trim($_POST["db_name"] ?? "");
  $dbUser = trim($_POST["db_user"] ?? "admin");
  $dbPassword = $_POST["db_password"] ?? "";
  $error = "";
  $warning = "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($dbHost === "" || $dbName === "" || $dbUser === "") {
      $error = "Preencha os campos!";
    } else {
      if ($dbPassword === "") {
        $warning = "Deixar o banco de dados sem senha é um perigo sem freio! coloque uma senha pra proteger seu site!";
      } else {
        try {
          $dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8mb4";
          $pdo = new PDO($dsn, $dbUser, $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          ]);
          header("Location: ../tacho-site-configure/index.php");
        } catch (PDOException $e) {
          $error = "Falha. Motivo: " . $e->getMessage();
        }
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Configurar Banco — Tacho</title>
</head>
<body>
  <h1>Configurar Banco de Dados — Tacho</h1>

  <?php if ($error !== ""): ?>
    <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
  <?php endif; ?>

  <?php if ($warning !== ""): ?>
    <p><?= htmlspecialchars($warning, ENT_QUOTES, "UTF-8") ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <label>
      Local do banco (host):<br>
      <input type="text" name="db_host" value="<?= htmlspecialchars($dbHost, ENT_QUOTES, "UTF-8") ?>">
    </label><br><br>

    <label>
      Nome do banco de dados:<br>
      <input type="text" name="db_name" value="<?= htmlspecialchars($dbName, ENT_QUOTES, "UTF-8") ?>">
    </label><br><br>

    <label>
      Usuário do banco:<br>
      <input type="text" name="db_user" value="<?= htmlspecialchars($dbUser, ENT_QUOTES, "UTF-8") ?>">
    </label><br><br>

    <label>
      Senha do banco:<br>
      <input type="password" name="db_password">
    </label><br><br>

    <button type="submit">Salvar configuração</button>
  </form>
</body>
</html>
