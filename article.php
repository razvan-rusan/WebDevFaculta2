<?php
session_start();

use Razvan\WebDevFaculta2\Database;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, []);
$twig->addGlobal('session', $_SESSION);

$db = Database::getConnection();

$articleId = $_GET['articleId'] ?? null;

if (!$articleId) {
    header('Location: index.php');
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $content = $_POST['content'] ?? '';
    if (!empty($content)) {
        $stmt = $db->prepare("INSERT INTO article_comments (article_id, author_user_id, content) VALUES (:articleId, :userId, :content)");
        $stmt->execute([
            'articleId' => $articleId,
            'userId' => $_SESSION['user_id'],
            'content' => $content
        ]);
        header("Location: article.php?articleId=$articleId");
        exit;
    }
}

// Fetch article details
$stmt = $db->prepare("SELECT articles.id, articles.title, articles.content, users.username AS author_name 
                      FROM articles 
                      INNER JOIN blogs ON articles.blog_id = blogs.id 
                      INNER JOIN users ON blogs.author_user_id = users.id
                      WHERE articles.id = :articleId");
$stmt->execute(['articleId' => $articleId]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: index.php');
    exit;
}

// Fetch comments
$stmt = $db->prepare("SELECT article_comments.content, users.username AS author_name 
                      FROM article_comments 
                      INNER JOIN users ON article_comments.author_user_id= users.id 
                      WHERE article_comments.article_id = :articleId");
$stmt->execute(['articleId' => $articleId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo $twig->render('article.twig', [
    'page_title' => $article['title'],
    'article' => $article,
    'comments' => $comments
]);