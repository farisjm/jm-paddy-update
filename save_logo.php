<?php
declare(strict_types=1);
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: settings.php");
    exit;
}
if (!isset($_FILES["logo"]) || $_FILES["logo"]["error"] !== UPLOAD_ERR_OK) {
    header("Location: settings.php?logo_error=upload");
    exit;
}
$file = $_FILES["logo"];
if ((int)$file["size"] < 1 || (int)$file["size"] > 2 * 1024 * 1024) {
    header("Location: settings.php?logo_error=size");
    exit;
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file["tmp_name"]);
$allowed = ["image/png" => "png", "image/jpeg" => "jpg", "image/webp" => "webp"];
if (!isset($allowed[$mime]) || @getimagesize($file["tmp_name"]) === false) {
    header("Location: settings.php?logo_error=type");
    exit;
}
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    exit("Could not create the logo upload folder.");
}
$extension = $allowed[$mime];
$filename = "business-logo." . $extension;
$destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
foreach (["png", "jpg", "webp"] as $oldExtension) {
    $old = $uploadDir . DIRECTORY_SEPARATOR . "business-logo." . $oldExtension;
    if ($old !== $destination && is_file($old)) {
        @unlink($old);
    }
}
if (!move_uploaded_file($file["tmp_name"], $destination)) {
    http_response_code(500);
    exit("Could not save the uploaded logo.");
}
file_put_contents($uploadDir . DIRECTORY_SEPARATOR . "logo-path.txt", "uploads/" . $filename, LOCK_EX);
header("Location: settings.php?logo=updated");
exit;
