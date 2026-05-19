<?php
require_once 'vendor/autoload.php';
session_start();

use Razvan\WebDevFaculta2\Auth\AuthService;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (AuthService::login($user, $pass)) {
        header('Location: index.php');
    } else {
        $error = "Invalid username or password!";
    }
}

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, []);

try {
    echo $twig->render('login.twig', ['error' => $error ?? null]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}