<?php

namespace Razvan\WebDevFaculta2\Models;

use Razvan\WebDevFaculta2\Database;

class Gallery
{
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT id, title, descriptio, cover_image FROM galleries");
        return $stmt->fetchAll();
    }
}