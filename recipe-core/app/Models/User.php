<?php
// app/Models/User.php
namespace Models;

use Core\Database;
use PDO;

class User
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function create($email, $password, $username = null)
    {
        $sql = "INSERT INTO users (email, password_hash, username, role, is_blocked) VALUES (:email, :hash, :username, 'client', 0)";
        $stmt = $this->db->prepare($sql);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        return $stmt->execute([
            ':email' => $email,
            ':hash' => $hash,
            ':username' => $username
        ]);
    }
    
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    // НОВЫЙ МЕТОД: Получить всех пользователей (для админ-панели)
    public function getAll(int $limit = 50, int $offset = 0, string $search = ''): array
    {
        $sql = "SELECT u.*, 
                (SELECT COUNT(*) FROM recipes WHERE user_id = u.id) as recipes_count,
                (SELECT COUNT(*) FROM favorites WHERE user_id = u.id) as favorites_count
                FROM users u";
        $params = [];
        
        if ($search !== '') {
            $sql .= " WHERE u.email LIKE :search OR u.username LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $sql .= " ORDER BY u.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // НОВЫЙ МЕТОД: Получить общее количество пользователей
    public function getTotalCount(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as count FROM users";
        $params = [];
        
        if ($search !== '') {
            $sql .= " WHERE email LIKE :search OR username LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }
    
    public function updateUsername($id, $username)
    {
        $sql = "UPDATE users SET username = :username WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':username' => $username, ':id' => $id]);
    }
    
    /**
     * Обновление имени и email пользователя
     */
    public function update(int $id, string $username, string $email): bool
    {
        $sql = "UPDATE users SET username = :username, email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':id' => $id
        ]);
    }
    
    /**
     * Обновление пароля пользователя
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = :hash WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':hash' => $hash,
            ':id' => $id
        ]);
    }
    
    // НОВЫЙ МЕТОД: Обновление роли пользователя
    public function updateRole(int $id, string $role): bool
    {
        $sql = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':role' => $role,
            ':id' => $id
        ]);
    }
    
    // НОВЫЙ МЕТОД: Блокировка/разблокировка пользователя
    public function toggleBlock(int $id, bool $isBlocked): bool
    {
        $sql = "UPDATE users SET is_blocked = :is_blocked WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':is_blocked' => $isBlocked ? 1 : 0,
            ':id' => $id
        ]);
    }
    
    // НОВЫЙ МЕТОД: Проверка заблокирован ли пользователь
    public function isBlocked(int $id): bool
    {
        $user = $this->findById($id);
        return $user && isset($user['is_blocked']) && $user['is_blocked'] == 1;
    }
    
    /**
     * Удаление пользователя и связанных данных
     */
    public function delete(int $id): bool
    {
        try {
            // Начинаем транзакцию
            $this->db->beginTransaction();
            
            // Получаем рецепты пользователя, чтобы удалить их изображения
            $stmt = $this->db->prepare("SELECT image_url FROM recipes WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $id]);
            $recipes = $stmt->fetchAll();
            
            foreach ($recipes as $recipe) {
                if (!empty($recipe['image_url'])) {
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $recipe['image_url'];
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
            
            // Удаляем избранное пользователя
            $stmt = $this->db->prepare("DELETE FROM favorites WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $id]);
            
            // Удаляем связи рецепт-ингредиенты для рецептов пользователя
            $stmt = $this->db->prepare("DELETE ri FROM recipe_ingredients ri 
                                        JOIN recipes r ON ri.recipe_id = r.id 
                                        WHERE r.user_id = :user_id");
            $stmt->execute([':user_id' => $id]);
            
            // Удаляем рецепты пользователя
            $stmt = $this->db->prepare("DELETE FROM recipes WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $id]);
            
            // Удаляем самого пользователя
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);
            
            // Подтверждаем транзакцию
            $this->db->commit();
            return $result;
            
        } catch (\PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $this->db->rollBack();
            return false;
        }
    }
}