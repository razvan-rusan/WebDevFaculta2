<?php

use Razvan\WebDevFaculta2\Database;

require_once '../vendor/autoload.php';

$db = Database::getConnection();
$folderPath = '../images/*.{png,jpg,jpeg,gif,bmp,svg,webp}';
$files = glob($folderPath, GLOB_BRACE);
if (empty($files)) {
    die("No photos found.");
}

echo "Found " . count($files) . " photos. Starting db population\n";

$stmt = $db->prepare("INSERT INTO gallery_site_db.photos (file_path) VALUES (:file_path)");
$insertedCount = 0;

foreach ($files as $filePath) {
    try {
        $normalizedPath = str_replace('\\', '/',$filePath);

        $stmt->execute(['file_path' => $normalizedPath]);

        echo "Inserted: {$normalizedPath}} ✅ \n ";
        $insertedCount++;
    } catch (PDOException $e) {
        echo "Failed to insert {$filePath}: " . $e->getMessage() . " ❌\n";
    }
}

echo "\nSynchronization complete! Successfully added {$insertedCount} photos to 'gallery_site_db.photos'. 🎉\n";