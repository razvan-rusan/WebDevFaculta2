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
$stmt = $db->prepare("select galleries.id, username as author_name, name, description from
users inner join galleries on
users.id = galleries.author_user_id");
$stmt->execute();
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    echo $twig->render('home.twig',
        ['page_title' => 'Artist Portofolio',
            'galleries' => $galleries
        ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}