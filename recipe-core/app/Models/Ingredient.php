<?php
// app/Models/Ingredient.php
namespace Models;

use Core\Database;
use PDO;

class Ingredient
{
    public PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function getAll(): array
    {
        $sql = "SELECT * FROM ingredients ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM ingredients WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $ingredient = $stmt->fetch();
        return $ingredient ?: null;
    }
    
    public function create(string $name, float $calories): bool
    {
        $sql = "INSERT INTO ingredients (name, calories_per_100g) VALUES (:name, :calories)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $name, ':calories' => $calories]);
    }
    
    public function update(int $id, string $name, float $calories): bool
    {
        $sql = "UPDATE ingredients SET name = :name, calories_per_100g = :calories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $name, ':calories' => $calories, ':id' => $id]);
    }
    
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM ingredients WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}