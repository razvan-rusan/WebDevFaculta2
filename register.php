<?php
require_once 'vendor/autoload.php';
session_start();

use Razvan\WebDevFaculta2\Auth\AuthService;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

if (AuthService::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Toate câmpurile sunt obligatorii!";
    } elseif ($password !== $confirm_password) {
        $error = "Parolele nu se potrivesc!";
    } elseif (strlen($password) < 6) {
        $error = "Parola trebuie să aibă cel puțin 6 caractere!";
    } else {
        if (AuthService::register($username, $password)) {
            // Auto-login after registration
            AuthService::login($username, $password);
            header('Location: index.php');
            exit;
        } else {
            $error = "Username-ul este deja folosit!";
        }
    }
}

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, []);

try {
    echo $twig->render('register.twig', ['error' => $error]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    error_log($e->getMessage());
}
