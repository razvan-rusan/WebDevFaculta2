<?php

namespace Razvan\WebDevFaculta2\Auth;

use Razvan\WebDevFaculta2\Database;

class AuthService
{

    public static function login($username, $password): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        var_dump($password);
        var_dump($user['password_hash']);
        echo password_hash('password123', PASSWORD_BCRYPT);
        var_dump(password_verify($password, $user['password_hash']));

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(); // previne atacuri de "session fixation"

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }

        return false;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
    }

    public static function logout() {
        session_destroy();
        $_SESSION = [];
    }
}