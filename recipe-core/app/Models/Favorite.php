<?php
// app/Models/Favorite.php
namespace Models;

use Core\Database;
use PDO;

class Favorite
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function add(int $userId, int $recipeId): bool
    {
        $sql = "INSERT IGNORE INTO favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
    }
    
    public function remove(int $userId, int $recipeId): bool
    {
        $sql = "DELETE FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
    }
    
    public function isFavorite(int $userId, int $recipeId): bool
    {
        $sql = "SELECT id FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
        return $stmt->fetch() !== false;
    }
    
    public function getUserFavorites(int $userId): array
    {
        $sql = "SELECT r.*, c.name as category_name, u.username as author_name,
                (SELECT COUNT(*) FROM favorites WHERE recipe_id = r.id) as favorites_count
                FROM favorites f
                JOIN recipes r ON f.recipe_id = r.id
                JOIN categories c ON r.category_id = c.id
                JOIN users u ON r.user_id = u.id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function getFavoritesCount(int $recipeId): int
    {
        $sql = "SELECT COUNT(*) as count FROM favorites WHERE recipe_id = :recipe_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':recipe_id' => $recipeId]);
        return $stmt->fetch()['count'];
    }
}