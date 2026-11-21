<?php
  $configureDir = __DIR__ . "/tacho-configure";
  $siteConfigureDir = __DIR__ . "/tacho-site-configure";

  $siteConfigured = !is_dir($configureDir) && !is_dir($siteConfigureDir);

  if (!$siteConfigured) {
    header("Location: /tch-admin/tacho-configure/index.php");
    exit;
  }
?>
