<?php
session_start();

use Razvan\WebDevFaculta2\Database;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FileSystemLoader;

require_once "vendor/autoload.php";

// setup la twig
$loader = new FileSystemLoader('templates');
$twig = new Environment($loader);
$twig->addGlobal('session', $_SESSION);

$db = Database::getConnection();

//phpinfo();

// Fetch all galleries
$stmt = $db->prepare("SELECT galleries.id, users.username AS author_name, galleries.name, galleries.description 
                      FROM galleries 
                      INNER JOIN users ON galleries.author_user_id = users.id");
$stmt->execute();
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all articles
$stmt = $db->prepare("SELECT articles.id, articles.title, articles.content, users.username AS author_name 
                      FROM articles 
                      INNER JOIN blogs ON articles.blog_id = blogs.id 
                      INNER JOIN users ON blogs.author_user_id = users.id");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    echo $twig->render('home.twig', [
        'page_title' => 'SiteGenial - Home',
        'galleries' => $galleries,
        'articles' => $articles
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}