<?php
// app/Models/Category.php
namespace Models;

use Core\Database;
use PDO;

class Category
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function getAll(): array
    {
        $sql = "SELECT * FROM categories ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }
    
    public function create(string $name): bool
    {
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $name]);
    }
    
    public function update(int $id, string $name): bool
    {
        $sql = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $name, ':id' => $id]);
    }
    
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function getRecipeCount(int $id): int
    {
        $sql = "SELECT COUNT(*) as count FROM recipes WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch()['count'];
    }
}