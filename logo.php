<?php
declare(strict_types=1);
$root = __DIR__;
$relative = "logo.svg";
$config = $root . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "logo-path.txt";
if (is_file($config)) {
    $saved = trim((string) file_get_contents($config));
    if (preg_match('/^uploads\/business-logo\.(png|jpg|webp)$/', $saved) && is_file($root . DIRECTORY_SEPARATOR . $saved)) {
        $relative = $saved;
    }
}
$fullPath = $root . DIRECTORY_SEPARATOR . $relative;
$extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
$mimeTypes = ["svg" => "image/svg+xml", "png" => "image/png", "jpg" => "image/jpeg", "webp" => "image/webp"];
header("Content-Type: " . ($mimeTypes[$extension] ?? "application/octet-stream"));
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("X-Content-Type-Options: nosniff");
readfile($fullPath);
