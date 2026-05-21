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

// Handle gallery deletion
if (isset($_GET['delete_gallery_id'])) {
    $stmt = $db->prepare("DELETE FROM gallery_site_db.galleries WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete_gallery_id']]);
    header("Location: admin.php?status=gallery_deleted");
    exit;
}

// Handle article deletion
if (isset($_GET['delete_article_id'])) {
    $stmt = $db->prepare("DELETE FROM gallery_site_db.articles WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete_article_id']]);
    header("Location: admin.php?status=article_deleted");
    exit;
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    
    $stmt = $db->prepare("INSERT INTO gallery_site_db.users (username, password_hash, role) VALUES (:username, :password, :role)");
    $stmt->execute(['username' => $username, 'password' => $password, 'role' => $role]);
    header("Location: admin.php?status=user_added");
    exit;
}

// setup la twig
$loader = new FileSystemLoader('templates');
$twig = new Environment($loader);
$twig->addGlobal('session', $_SESSION);

$userId = $_SESSION['user_id'];

// Fetch admin's galleries
$stmt = $db->prepare("SELECT id, name, description FROM gallery_site_db.galleries WHERE author_user_id = :userId");
$stmt->execute(['userId' => $userId]);
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin's articles
$stmt = $db->prepare("SELECT articles.id, articles.title, articles.content 
                      FROM gallery_site_db.articles 
                      INNER JOIN gallery_site_db.blogs ON articles.blog_id = blogs.id 
                      WHERE blogs.author_user_id = :userId");
$stmt->execute(['userId' => $userId]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users for management
$stmt = $db->prepare("SELECT id, username, role FROM gallery_site_db.users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    echo $twig->render('admin.twig', [
        'page_title' => 'Admin Panel',
        'galleries' => $galleries,
        'articles' => $articles,
        'users' => $users,
        'status' => $_GET['status'] ?? null
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}
