<?php
require_once 'vendor/autoload.php';
session_start();

use Razvan\WebDevFaculta2\Auth\AuthService;
use Twig\Environment;
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
} catch (\Twig\Error\LoaderError $e) {
    error_log("Twig LoaderError: " . $e->getMessage());
} catch (\Twig\Error\RuntimeError $e) {
    error_log("Twig RuntimeError: " . $e->getMessage());
} catch (\Twig\Error\SyntaxError $e) {
    error_log("Twig SyntaxError: " . $e->getMessage());
}