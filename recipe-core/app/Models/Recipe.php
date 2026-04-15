<?php
// app/Models/Recipe.php
namespace Models;

use Core\Database;
use PDO;

class Recipe
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function getByUser(int $userId): array
    {
        $sql = "SELECT * FROM recipes WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function getAll(int $limit = 12, int $offset = 0, ?int $categoryId = null, string $search = ''): array
    {
        $sql = "SELECT r.*, c.name as category_name, u.username as author_name,
                (SELECT COUNT(*) FROM favorites WHERE recipe_id = r.id) as favorites_count
                FROM recipes r
                JOIN categories c ON r.category_id = c.id
                JOIN users u ON r.user_id = u.id";
        $params = [];
        $conditions = [];

        if ($categoryId) {
            $conditions[] = "r.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        if ($search !== '') {
            $conditions[] = "r.title LIKE :search";
            $params[':search'] = "%$search%";
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalCount(?int $categoryId = null, string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as count FROM recipes r";
        $params = [];
        $conditions = [];
        if ($categoryId) {
            $conditions[] = "r.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        if ($search !== '') {
            $conditions[] = "r.title LIKE :search";
            $params[':search'] = "%$search%";
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }
    
    public function findById(int $id): ?array
    {
        $sql = "SELECT r.*, c.name as category_name, u.username as author_name,
                (SELECT COUNT(*) FROM favorites WHERE recipe_id = r.id) as favorites_count
                FROM recipes r
                JOIN categories c ON r.category_id = c.id
                JOIN users u ON r.user_id = u.id
                WHERE r.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $recipe = $stmt->fetch();
        return $recipe ?: null;
    }
    
    public function getIngredients(int $recipeId): array
    {
        $sql = "SELECT i.*, ri.quantity_grams 
                FROM recipe_ingredients ri
                JOIN ingredients i ON ri.ingredient_id = i.id
                WHERE ri.recipe_id = :recipe_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':recipe_id' => $recipeId]);
        return $stmt->fetchAll();
    }
    
    public function create(array $data): int
    {
        $sql = "INSERT INTO recipes (title, description, instructions, image_url, category_id, user_id) 
                VALUES (:title, :description, :instructions, :image_url, :category_id, :user_id)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':instructions' => $data['instructions'],
            ':image_url' => $data['image_url'] ?? null,
            ':category_id' => $data['category_id'],
            ':user_id' => $data['user_id']
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    public function addIngredients(int $recipeId, array $ingredients): void
    {
        // Сначала удаляем старые связи
        $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':recipe_id' => $recipeId]);
        
        // Добавляем новые
        $sql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity_grams) 
                VALUES (:recipe_id, :ingredient_id, :quantity)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($ingredients as $ingredient) {
            $stmt->execute([
                ':recipe_id' => $recipeId,
                ':ingredient_id' => $ingredient['id'],
                ':quantity' => $ingredient['quantity']
            ]);
        }
    }
    
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE recipes SET title = :title, description = :description, 
                instructions = :instructions, image_url = :image_url, category_id = :category_id 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':instructions' => $data['instructions'],
            ':image_url' => $data['image_url'] ?? null,
            ':category_id' => $data['category_id'],
            ':id' => $id
        ]);
    }
    
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM recipes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function calculateCalories(int $recipeId): float
    {
        $ingredients = $this->getIngredients($recipeId);
        $totalCalories = 0;
        
        foreach ($ingredients as $ingredient) {
            $caloriesPerGram = $ingredient['calories_per_100g'] / 100;
            $totalCalories += $caloriesPerGram * $ingredient['quantity_grams'];
        }
        
        return round($totalCalories, 2);
    }
}