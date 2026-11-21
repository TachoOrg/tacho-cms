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
$siteName = (string)($existingData['site']['site_name'] ?? '');
$siteTagline = (string)($existingData['site']['site_tagline'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newSiteName = trim((string)($_POST['site_name'] ?? ''));
    $newSiteTagline = trim((string)($_POST['site_tagline'] ?? ''));

    if ($newSiteName === '') {
        $error = 'Informe o nome do site.';
    } else {
        $payload = [
            'site' => [
                'site_name' => $newSiteName,
                'site_tagline' => $newSiteTagline,
                'updated_at' => date('c'),
            ],
        ];

        if (saveWebsiteData($jsonPath, $payload)) {
            $message = 'Dados do site salvos em ../../websitedata.json! ✅';
            $siteName = $newSiteName;
            $siteTagline = $newSiteTagline;
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
    <title>Configurar Site — Tacho</title>
</head>
<body>
    <h1>🌐 Configuração do Site</h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="site_name">Nome do site</label><br>
        <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>" required><br><br>

        <label for="site_tagline">Slogan / descrição curta</label><br>
        <input type="text" id="site_tagline" name="site_tagline" value="<?= htmlspecialchars($siteTagline, ENT_QUOTES, 'UTF-8') ?>"><br><br>

        <button type="submit">Salvar em websitedata.json</button>
    </form>

    <p><a href="../index.php">Ir para o painel admin →</a></p>
</body>
</html>
