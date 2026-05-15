<?php
session_start();
require_once 'vendor/autoload.php';

use Razvan\WebDevFaculta2\Auth\AuthService;

AuthService::logout();

header("Location: index.php");
exit();