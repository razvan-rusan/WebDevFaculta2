<?php
session_start();

use Razvan\WebDevFaculta2\Database;
use Razvan\WebDevFaculta2\Auth\AuthService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';

if (!AuthService::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, []);
$twig->addGlobal('session', $_SESSION);

$db = Database::getConnection();

$galleryId = $_GET['galleryId'] ?? $_POST['gallery_id'] ?? null;
$userId = $_SESSION['user_id'];

$gallery = [
    'id' => null,
    'name' => '',
    'description' => ''
];

$isEdit = false;
$photos = [];
$error = $_GET['error'] ?? null;

if ($galleryId) {
    // Fetch gallery details and check ownership
    $stmt = $db->prepare("SELECT id, name, description, author_user_id FROM gallery_site_db.galleries WHERE id = :galleryId");
    $stmt->execute(['galleryId' => $galleryId]);
    $fetchedGallery = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fetchedGallery || (!AuthService::isAdmin() && $fetchedGallery['author_user_id'] != $userId)) {
        header('Location: my_content.php');
        exit;
    }
    $gallery = $fetchedGallery;
    $isEdit = true;

    // Fetch photos for this gallery
    $stmt2 = $db->prepare("SELECT photos.file_path FROM gallery_site_db.photos 
                           INNER JOIN gallery_site_db.galleries_photos ON photos.id = galleries_photos.photo_id 
                           WHERE galleries_photos.gallery_id = :galleryId");
    $stmt2->execute(['galleryId' => $galleryId]);
    $photos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

// Handle empty POST due to post_max_size
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    header("Location: edit_gallery.php?galleryId=$galleryId&error=post_too_large");
    exit;
}

// Handle gallery update or create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gallery'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!empty($name)) {
        if ($isEdit) {
            $updateStmt = $db->prepare("UPDATE gallery_site_db.galleries SET name = :name, description = :description WHERE id = :galleryId");
            $updateStmt->execute([
                'name' => $name,
                'description' => $description,
                'galleryId' => $galleryId
            ]);
            // Handle optional photo upload
            if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
                $db->exec("INSERT INTO gallery_site_db.photos (file_path) VALUES ('temp')");
                $photoId = $db->lastInsertId();
                $fileName = "img_" . $photoId . ".png";
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], "images/" . $fileName)) {
                    $db->prepare("UPDATE gallery_site_db.photos SET file_path = :filePath WHERE id = :id")->execute(['filePath' => 'images/' . $fileName, 'id' => $photoId]);
                    $db->prepare("INSERT INTO gallery_site_db.galleries_photos (gallery_id, photo_id) VALUES (:galleryId, :photoId)")->execute(['galleryId' => $galleryId, 'photoId' => $photoId]);
                }
            }
            header("Location: my_content.php");
            exit;
        } else {
            $insertStmt = $db->prepare("INSERT INTO gallery_site_db.galleries (author_user_id, name, description) VALUES (:userId, :name, :description)");
            $insertStmt->execute([
                'userId' => $userId,
                'name' => $name,
                'description' => $description
            ]);
            $newGalleryId = $db->lastInsertId();
            // Handle optional photo upload
            if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
                $db->exec("INSERT INTO gallery_site_db.photos (file_path) VALUES ('temp')");
                $photoId = $db->lastInsertId();
                $fileName = "img_" . $photoId . ".png";
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], "images/" . $fileName)) {
                    $db->prepare("UPDATE gallery_site_db.photos SET file_path = :filePath WHERE id = :id")->execute(['filePath' => 'images/' . $fileName, 'id' => $photoId]);
                    $db->prepare("INSERT INTO gallery_site_db.galleries_photos (gallery_id, photo_id) VALUES (:galleryId, :photoId)")->execute(['galleryId' => $newGalleryId, 'photoId' => $photoId]);
                }
            }
            header("Location: my_content.php");
            exit;
        }
    }
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo']) && $isEdit) {
    if (isset($_FILES['photo'])) {
        if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['photo']['tmp_name'];
            $db->exec("INSERT INTO gallery_site_db.photos (file_path) VALUES ('temp')");
            $photoId = $db->lastInsertId();
            $fileName = "img_" . $photoId . ".png";
            if (move_uploaded_file($tmpName, "images/" . $fileName)) {
                $db->prepare("UPDATE gallery_site_db.photos SET file_path = :filePath WHERE id = :id")->execute(['filePath' => 'images/' . $fileName, 'id' => $photoId]);
                $db->prepare("INSERT INTO gallery_site_db.galleries_photos (gallery_id, photo_id) VALUES (:galleryId, :photoId)")->execute(['galleryId' => $galleryId, 'photoId' => $photoId]);
                header("Location: my_content.php");
                exit;
            } else {
                $db->prepare("DELETE FROM gallery_site_db.photos WHERE id = :id")->execute(['id' => $photoId]);
                header("Location: edit_gallery.php?galleryId=$galleryId&error=upload_failed");
                exit;
            }
        } else {
            $errCode = $_FILES['photo']['error'];
            $errorMap = [UPLOAD_ERR_INI_SIZE => 'file_too_large_ini', UPLOAD_ERR_FORM_SIZE => 'file_too_large_form', UPLOAD_ERR_PARTIAL => 'partial_upload', UPLOAD_ERR_NO_FILE => 'no_file'];
            $errorKey = $errorMap[$errCode] ?? 'unknown_error';
            header("Location: edit_gallery.php?galleryId=$galleryId&error=$errorKey");
            exit;
        }
    }
}

echo $twig->render('edit_gallery.twig', [
    'page_title' => $isEdit ? 'Editează Galerie: ' . $gallery['name'] : 'Adaugă Galerie Nouă',
    'gallery' => $gallery,
    'is_edit' => $isEdit,
    'photos' => $photos,
    'error' => $error
]);
