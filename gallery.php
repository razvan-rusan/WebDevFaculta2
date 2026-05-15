<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';
session_start();

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, []);

try {
    echo $twig->render('login.twig',
        ['error' => $error ?? null
        ]);
} catch (\Twig\Error\LoaderError $e) {
    error_log($e->getMessage());
} catch (\Twig\Error\RuntimeError $e) {
    error_log($e->getMessage());
} catch (\Twig\Error\SyntaxError $e) {
    error_log($e->getMessage());
}