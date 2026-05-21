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

$articleId = $_GET['articleId'] ?? $_POST['article_id'] ?? null;
$userId = $_SESSION['user_id'];

$article = [
    'id' => null,
    'title' => '',
    'content' => ''
];

$isEdit = false;

if ($articleId) {
    // Fetch article details and check ownership
    $stmt = $db->prepare("SELECT articles.id, articles.title, articles.content, blogs.author_user_id 
                          FROM gallery_site_db.articles 
                          INNER JOIN gallery_site_db.blogs ON articles.blog_id = blogs.id 
                          WHERE articles.id = :articleId");
    $stmt->execute(['articleId' => $articleId]);
    $fetchedArticle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fetchedArticle || $fetchedArticle['author_user_id'] != $userId) {
        header('Location: my_content.php');
        exit;
    }
    $article = $fetchedArticle;
    $isEdit = true;
}

// Handle update or create
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if (!empty($title) && !empty($content)) {
        if ($isEdit) {
            $updateStmt = $db->prepare("UPDATE gallery_site_db.articles SET title = :title, content = :content WHERE id = :articleId");
            $updateStmt->execute([
                'title' => $title,
                'content' => $content,
                'articleId' => $articleId
            ]);
            header("Location: my_content.php");
            exit;
        } else {
            // For adding a new article, we need a blog_id. 
            // We'll pick the first blog of the user. If they have none, we might need to create one, 
            // but for now let's assume they have at least one or we'll find/create one.
            $blogStmt = $db->prepare("SELECT id FROM gallery_site_db.blogs WHERE author_user_id = :userId LIMIT 1");
            $blogStmt->execute(['userId' => $userId]);
            $blog = $blogStmt->fetch(PDO::FETCH_ASSOC);

            if (!$blog) {
                // Create a default blog if none exists
                $createBlogStmt = $db->prepare("INSERT INTO gallery_site_db.blogs (author_user_id, name) VALUES (:userId, :name)");
                $createBlogStmt->execute([
                    'userId' => $userId,
                    'name' => $_SESSION['username'] . "'s Blog"
                ]);
                $blogId = $db->lastInsertId();
            } else {
                $blogId = $blog['id'];
            }

            $insertStmt = $db->prepare("INSERT INTO gallery_site_db.articles (blog_id, title, content) VALUES (:blogId, :title, :content)");
            $insertStmt->execute([
                'blogId' => $blogId,
                'title' => $title,
                'content' => $content
            ]);
            $newArticleId = $db->lastInsertId();
            header("Location: my_content.php");
            exit;
        }
    }
}

echo $twig->render('edit_article.twig', [
    'page_title' => $isEdit ? 'Editează Articol: ' . $article['title'] : 'Scrie Articol Nou',
    'article' => $article,
    'is_edit' => $isEdit
]);
