<?php

use Razvan\WebDevFaculta2\Database;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';
session_start();

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, []);
$galleryId = $_GET['galleryId'];

$db = Database::getConnection();
$stmt = $db->prepare("select galleries.id, username as author_name, name, description from
    users inner join galleries on
        users.id = galleries.author_user_id
where galleries.id = :galleryId");
$stmt->execute(['galleryId' => $galleryId]);
$gallery = $stmt->fetch();

$stmt2 = $db->prepare("select file_path from gallery_site_db.galleries inner join
gallery_site_db.galleries_photos on galleries.id = galleries_photos.gallery_id inner join
gallery_site_db.photos on galleries_photos.photo_id = photos.id
WHERE galleries.id = :galleryId");
$stmt2->execute(['galleryId' => $galleryId]);
$photos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

try {
    echo $twig->render('gallery.twig',
        ['error' => $error ?? null,
         'gallery' => $gallery,
         'photos' => $photos
        ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}