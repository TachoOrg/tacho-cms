<?php
declare(strict_types=1);

session_start();

$jsonPath = __DIR__ . '/../../websitedata.json';
$message = '';
$error = '';

/**
 * Lê ../../websitedata.json como array (ou [] se ainda não existir).
 *
 * @return array<string, mixed>
 */
function loadWebsiteData(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Faz merge e salva em ../../websitedata.json com lock exclusivo e UTF-8 limpo.
 *
 * @param array<string, mixed> $patch
 */
function saveWebsiteData(string $path, array $patch): bool
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    $fp = fopen($path, 'c+');
    if ($fp === false) {
        return false;
    }

    try {
        if (!flock($fp, LOCK_EX)) {
            return false;
        }

        rewind($fp);
        $currentRaw = stream_get_contents($fp);
        $currentData = [];

        if (is_string($currentRaw) && trim($currentRaw) !== '') {
            $decoded = json_decode($currentRaw, true);
            if (is_array($decoded)) {
                $currentData = $decoded;
            }
        }

        $merged = array_replace_recursive($currentData, $patch);
        $json = json_encode(
            $merged,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            return false;
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $json . PHP_EOL);
        fflush($fp);
        flock($fp, LOCK_UN);

        return true;
    } finally {
        fclose($fp);
    }
}

$existingData = loadWebsiteData($jsonPath);
$existingAdminUser = (string)($existingData['admin']['username'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = trim((string)($_POST['admin_username'] ?? ''));
    $adminPass = (string)($_POST['admin_password'] ?? '');

    if ($adminUser === '' || $adminPass === '') {
        $error = 'Preencha o usuário e a senha do administrador.';
    } else {
        $payload = [
            'admin' => [
                'username' => $adminUser,
                'password_hash' => password_hash($adminPass, PASSWORD_DEFAULT),
                'updated_at' => date('c'),
            ],
        ];

        if (saveWebsiteData($jsonPath, $payload)) {
            $message = 'Dados do administrador salvos em ../../websitedata.json! ✅';
            $existingData = loadWebsiteData($jsonPath);
            $existingAdminUser = (string)($existingData['admin']['username'] ?? $adminUser);
        } else {
            $error = 'Não foi possível gravar ../../websitedata.json. Verifique as permissões da pasta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Admin — Tacho</title>
</head>
<body>
    <h1>⚙️ Configuração do Administrador</h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="admin_username">Usuário admin</label><br>
        <input type="text" id="admin_username" name="admin_username" value="<?= htmlspecialchars($existingAdminUser, ENT_QUOTES, 'UTF-8') ?>" required><br><br>

        <label for="admin_password">Senha admin</label><br>
        <input type="password" id="admin_password" name="admin_password" required><br><br>

        <button type="submit">Salvar em websitedata.json</button>
    </form>

    <p><a href="../tacho-site-configure/index.php">Ir para configuração do site →</a></p>
</body>
</html>
