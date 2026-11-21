<?php
  $siteName = trim($_POST["site_name"] ?? "");
  $siteUser = trim($_POST["site_user"] ?? "admin");
  $sitePassword = $_POST["site_password"] ?? "";
  $error = "";
  $warning = "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($siteName === "" || $siteUser === "") {
      $error = "Preencha o nome do site e o nome de usuário!";
    } else {
      if ($sitePassword === "") {
        $warning = "Colocar uma senha no usuário do site deixa tudo bem mais seguro!";
      } else {
        header("Location: ../../dcf.php");
        exit;
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Configurar Site — Tacho</title>
</head>
<body>
  <h1>Configurar Site — Tacho</h1>

  <?php if ($error !== ""): ?>
    <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
  <?php endif; ?>

  <?php if ($warning !== ""): ?>
    <p><?= htmlspecialchars($warning, ENT_QUOTES, "UTF-8") ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <label>
      Nome do site:
      <input type="text" name="site_name" value="<?= htmlspecialchars($siteName, ENT_QUOTES, "UTF-8") ?>">
    </label><br>

    <label>
      Nome de usuário:
      <input type="text" name="site_user" value="<?= htmlspecialchars($siteUser, ENT_QUOTES, "UTF-8") ?>">
    </label><br>

    <label>
      Senha:
      <input type="password" name="site_password">
    </label><br>

    <button type="submit">Salvar site</button>
  </form>
</body>
</html>
