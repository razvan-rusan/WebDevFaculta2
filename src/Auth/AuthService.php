<?php

namespace Razvan\WebDevFaculta2\Auth;

use Razvan\WebDevFaculta2\Database;

class AuthService
{

    public static function login($username, $password): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM gallery_site_db.users WHERE username = :username");
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

    public static function register($username, $password): bool
    {
        $db = Database::getConnection();

        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM gallery_site_db.users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO gallery_site_db.users (username, password_hash, role) VALUES (:username, :password_hash, 'normal_user')");
        return $stmt->execute([
            'username' => $username,
            'password_hash' => $hash
        ]);
    }

    public static function logout() {
        session_destroy();
        $_SESSION = [];
    }
}