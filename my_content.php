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

if (!AuthService::isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// setup la twig
$loader = new FileSystemLoader('templates');
$twig = new Environment($loader);
$twig->addGlobal('session', $_SESSION);

$db = Database::getConnection();
$userId = $_SESSION['user_id'];

// Fetch user's galleries
$stmt = $db->prepare("SELECT id, name, description FROM gallery_site_db.galleries WHERE author_user_id = :userId");
$stmt->execute(['userId' => $userId]);
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's articles
$stmt = $db->prepare("SELECT articles.id, articles.title, articles.content 
                      FROM gallery_site_db.articles 
                      INNER JOIN gallery_site_db.blogs ON articles.blog_id = blogs.id 
                      WHERE blogs.author_user_id = :userId");
$stmt->execute(['userId' => $userId]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    echo $twig->render('my_content.twig', [
        'page_title' => 'Conținutul Meu',
        'galleries' => $galleries,
        'articles' => $articles
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}
