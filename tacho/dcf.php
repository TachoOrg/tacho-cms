<?php
declare(strict_types=1);

$adminDirReal = realpath(__DIR__);
$websiteDataPath = __DIR__ . '/../../websitedata.json';

$targetsToDelete = [
    'tacho-configure',
    'tacho-site-configure',
];

function deletePathRecursively(string $targetPath, string $allowedBaseDir): void
{
    $realTarget = realpath($targetPath);
    $realBase = realpath($allowedBaseDir);

    if ($realTarget === false || $realBase === false) {
        return;
    }

    $basePrefix = rtrim($realBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if ($realTarget === $realBase || !str_starts_with($realTarget, $basePrefix)) {
        return;
    }

    if (is_file($realTarget) || is_link($realTarget)) {
        @unlink($realTarget);
        return;
    }

    if (!is_dir($realTarget)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($realTarget, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        $itemPath = $item->getPathname();
        if ($item->isLink() || $item->isFile()) {
            @unlink($itemPath);
        } elseif ($item->isDir()) {
            @rmdir($itemPath);
        }
    }

    @rmdir($realTarget);
}

if ($adminDirReal !== false && is_file($websiteDataPath)) {
    foreach ($targetsToDelete as $folderName) {
        deletePathRecursively($adminDirReal . DIRECTORY_SEPARATOR . $folderName, $adminDirReal);
    }
}

header('Location: index.php');
exit;
