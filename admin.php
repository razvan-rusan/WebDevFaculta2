<?php
session_start();

use Razvan\WebDevFaculta2\Database;
use Razvan\WebDevFaculta2\Auth\AuthService;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FileSystemLoader;

require_once "vendor/autoload.php";

if (!AuthService::isAdmin()) {
    header("Location: index.php");
    exit;
}



$db = Database::getConnection();

// Handle user deletion
if (isset($_GET['delete_user_id'])) {
    $stmt = $db->prepare("DELETE FROM gallery_site_db.users WHERE id = :id AND role != 'admin'");
    $stmt->execute(['id' => $_GET['delete_user_id']]);
    header("Location: admin.php?status=user_deleted");
    exit;
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $userIdToReset = $_POST['user_id'];
    $newPassword = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    $stmt = $db->prepare("UPDATE gallery_site_db.users SET password_hash = :password WHERE id = :id");
    $stmt->execute(['password' => $newPassword, 'id' => $userIdToReset]);
    header("Location: admin.php?status=password_reset");
    exit;
}

// setup la twig
$loader = new FileSystemLoader('templates');
$twig = new Environment($loader);
$twig->addGlobal('session', $_SESSION);

// Fetch all users for management
$stmt = $db->prepare("SELECT id, username, role FROM gallery_site_db.users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    echo $twig->render('admin.twig', [
        'page_title' => 'Admin Panel',
        'users' => $users,
        'status' => $_GET['status'] ?? null
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}
