<?php
// app/Controllers/AdminController.php
namespace Controllers;

use Models\Recipe;
use Models\Category;
use Models\Ingredient;
use Models\User;

class AdminController extends BaseController
{
    private Recipe $recipeModel;
    private Category $categoryModel;
    private Ingredient $ingredientModel;
    private User $userModel;

    public function __construct()
    {
        // Проверяем, что пользователь - администратор
        $this->requireAdmin();
        
        // Инициализируем модели
        $this->recipeModel = new Recipe();
        $this->categoryModel = new Category();
        $this->ingredientModel = new Ingredient();
        $this->userModel = new User();
        
        // Генерируем CSRF-токен для форм
        $this->generateCsrfToken();
    }
    
    /**
     * Главная страница админ-панели
     */
    public function index(): void
    {
        $recipesCount = count($this->recipeModel->getAll(1000, 0));
        $categoriesCount = count($this->categoryModel->getAll());
        $ingredientsCount = count($this->ingredientModel->getAll());
        $usersCount = $this->userModel->getTotalCount();
        
        $this->render('admin/index', [
            'recipesCount' => $recipesCount,
            'categoriesCount' => $categoriesCount,
            'ingredientsCount' => $ingredientsCount,
            'usersCount' => $usersCount
        ]);
    }
    
    /**
     * Управление рецептами
     */
    public function recipes(): void
    {
        $recipes = $this->recipeModel->getAll(100, 0);
        $this->render('admin/recipes/index', ['recipes' => $recipes]);
    }
    
    /**
     * Создание рецепта
     */
    public function createRecipe(): void
    {
        $categories = $this->categoryModel->getAll();
        $ingredients = $this->ingredientModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'instructions' => trim($_POST['instructions'] ?? ''),
                'image_url' => null,
                'category_id' => (int)($_POST['category_id'] ?? 0),
                'user_id' => $_SESSION['user_id']
            ];
            
            if (empty($data['title'])) {
                $error = "Название рецепта обязательно!";
                $this->render('admin/recipes/create', [
                    'categories' => $categories,
                    'ingredients' => $ingredients,
                    'error' => $error
                ]);
                return;
            }
            
            // Обработка загруженного изображения
            $imagePath = $this->uploadImage($_FILES['image'] ?? []);
            if ($imagePath !== null) {
                $data['image_url'] = $imagePath;
            }
            
            $recipeId = $this->recipeModel->create($data);
            
            // Обработка ингредиентов из динамических полей
            $selectedIngredients = [];
            $names = $_POST['ingredients_name'] ?? [];
            $quantities = $_POST['ingredients_quantity'] ?? [];
            
            foreach ($names as $index => $name) {
                $name = trim($name);
                $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 0;
                
                if (empty($name) || $quantity <= 0) {
                    continue;
                }
                
                // Ищем ID ингредиента по названию
                $stmt = $this->ingredientModel->db->prepare("SELECT id FROM ingredients WHERE name = :name");
                $stmt->execute([':name' => $name]);
                $ingredient = $stmt->fetch();
                
                if ($ingredient) {
                    $selectedIngredients[] = [
                        'id' => $ingredient['id'],
                        'quantity' => $quantity
                    ];
                }
            }
            
            $this->recipeModel->addIngredients($recipeId, $selectedIngredients);
            
            $_SESSION['success'] = 'Рецепт успешно создан!';
            $this->redirect('/admin/recipes');
            return;
        }
        
        $this->render('admin/recipes/create', [
            'categories' => $categories,
            'ingredients' => $ingredients
        ]);
    }
    
    /**
     * Редактирование рецепта
     */
    public function editRecipe(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            $_SESSION['error'] = 'Рецепт не найден';
            $this->redirect('/admin/recipes');
            return;
        }
        
        $categories = $this->categoryModel->getAll();
        $allIngredients = $this->ingredientModel->getAll();
        $recipeIngredients = $this->recipeModel->getIngredients($id);
        
        // Создаем массив для быстрого доступа к количеству ингредиентов в рецепте
        $ingredientQuantities = [];
        foreach ($recipeIngredients as $ri) {
            $ingredientQuantities[$ri['id']] = $ri['quantity_grams'];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'instructions' => trim($_POST['instructions'] ?? ''),
                'image_url' => $recipe['image_url'],
                'category_id' => (int)($_POST['category_id'] ?? 0)
            ];
            
            // Обработка загруженного изображения
            $imagePath = $this->uploadImage($_FILES['image'] ?? [], $recipe['image_url']);
            if ($imagePath !== null) {
                $data['image_url'] = $imagePath;
            }
            
            $this->recipeModel->update($id, $data);
            
            // Обработка ингредиентов из динамических полей
            $selectedIngredients = [];
            $names = $_POST['ingredients_name'] ?? [];
            $quantities = $_POST['ingredients_quantity'] ?? [];
            
            foreach ($names as $index => $name) {
                $name = trim($name);
                $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 0;
                
                if (empty($name) || $quantity <= 0) {
                    continue;
                }
                
                // Ищем ID ингредиента по названию
                $stmt = $this->ingredientModel->db->prepare("SELECT id FROM ingredients WHERE name = :name");
                $stmt->execute([':name' => $name]);
                $ingredient = $stmt->fetch();
                
                if ($ingredient) {
                    $selectedIngredients[] = [
                        'id' => $ingredient['id'],
                        'quantity' => $quantity
                    ];
                }
            }
            
            $this->recipeModel->addIngredients($id, $selectedIngredients);
            
            $_SESSION['success'] = 'Рецепт успешно обновлен!';
            $this->redirect('/admin/recipes');
            return;
        }
        
        $this->render('admin/recipes/edit', [
            'recipe' => $recipe,
            'categories' => $categories,
            'allIngredients' => $allIngredients,
            'ingredientQuantities' => $ingredientQuantities,
            'recipeIngredients' => $recipeIngredients
        ]);
    }
    
    /**
     * Удаление рецепта
     */
    public function deleteRecipe(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id > 0) {
            // Перед удалением удаляем связанное изображение
            $recipe = $this->recipeModel->findById($id);
            if ($recipe && !empty($recipe['image_url'])) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $recipe['image_url'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $this->recipeModel->delete($id);
            $_SESSION['success'] = 'Рецепт успешно удален!';
        }
        
        $this->redirect('/admin/recipes');
    }
    
    /**
     * Управление ингредиентами
     */
    public function ingredients(): void
    {
        $ingredients = $this->ingredientModel->getAll();
        $this->render('admin/ingredients/index', ['ingredients' => $ingredients]);
    }
    
    /**
     * Создание ингредиента
     */
    public function createIngredient(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $name = trim($_POST['name'] ?? '');
            $calories = (float)($_POST['calories_per_100g'] ?? 0);
            
            if (empty($name)) {
                $error = "Название ингредиента обязательно!";
                $this->render('admin/ingredients/create', ['error' => $error]);
                return;
            }
            
            if ($calories <= 0) {
                $error = "Калорийность должна быть больше 0!";
                $this->render('admin/ingredients/create', ['error' => $error]);
                return;
            }
            
            $this->ingredientModel->create($name, $calories);
            $_SESSION['success'] = 'Ингредиент успешно добавлен!';
            $this->redirect('/admin/ingredients');
            return;
        }
        
        $this->render('admin/ingredients/create');
    }
    
    /**
     * Редактирование ингредиента
     */
    public function editIngredient(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $ingredient = $this->ingredientModel->findById($id);
        
        if (!$ingredient) {
            $_SESSION['error'] = 'Ингредиент не найден';
            $this->redirect('/admin/ingredients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $name = trim($_POST['name'] ?? '');
            $calories = (float)($_POST['calories_per_100g'] ?? 0);
            
            if (empty($name)) {
                $error = "Название ингредиента обязательно!";
                $this->render('admin/ingredients/edit', [
                    'ingredient' => $ingredient,
                    'error' => $error
                ]);
                return;
            }
            
            if ($calories <= 0) {
                $error = "Калорийность должна быть больше 0!";
                $this->render('admin/ingredients/edit', [
                    'ingredient' => $ingredient,
                    'error' => $error
                ]);
                return;
            }
            
            $this->ingredientModel->update($id, $name, $calories);
            $_SESSION['success'] = 'Ингредиент успешно обновлен!';
            $this->redirect('/admin/ingredients');
            return;
        }
        
        $this->render('admin/ingredients/edit', ['ingredient' => $ingredient]);
    }
    
    /**
     * Удаление ингредиента
     */
    public function deleteIngredient(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id > 0) {
            $this->ingredientModel->delete($id);
            $_SESSION['success'] = 'Ингредиент успешно удален!';
        }
        
        $this->redirect('/admin/ingredients');
    }
    
    /**
     * Управление категориями
     */
    public function categories(): void
    {
        $categories = $this->categoryModel->getAll();
        
        // Для каждой категории считаем количество рецептов
        foreach ($categories as &$category) {
            $category['recipe_count'] = $this->categoryModel->getRecipeCount($category['id']);
        }
        
        $this->render('admin/categories/index', ['categories' => $categories]);
    }
    
    /**
     * Создание категории
     */
    public function createCategory(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                $error = "Название категории обязательно!";
                $this->render('admin/categories/create', ['error' => $error]);
                return;
            }
            
            $this->categoryModel->create($name);
            $_SESSION['success'] = 'Категория успешно добавлена!';
            $this->redirect('/admin/categories');
            return;
        }
        
        $this->render('admin/categories/create');
    }
    
    /**
     * Редактирование категории
     */
    public function editCategory(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $category = $this->categoryModel->findById($id);
        
        if (!$category) {
            $_SESSION['error'] = 'Категория не найдена';
            $this->redirect('/admin/categories');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                $error = "Название категории обязательно!";
                $this->render('admin/categories/edit', [
                    'category' => $category,
                    'error' => $error
                ]);
                return;
            }
            
            $this->categoryModel->update($id, $name);
            $_SESSION['success'] = 'Категория успешно обновлена!';
            $this->redirect('/admin/categories');
            return;
        }
        
        $this->render('admin/categories/edit', ['category' => $category]);
    }
    
    /**
     * Удаление категории
     */
    public function deleteCategory(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id > 0) {
            $recipeCount = $this->categoryModel->getRecipeCount($id);
            if ($recipeCount > 0) {
                $_SESSION['error'] = "Невозможно удалить категорию, в которой есть рецепты ($recipeCount)";
            } else {
                $this->categoryModel->delete($id);
                $_SESSION['success'] = 'Категория успешно удалена!';
            }
        }
        
        $this->redirect('/admin/categories');
    }
    
    /**
     * Вспомогательный метод для загрузки изображений
     */
    private function uploadImage(array $file, ?string $oldImage = null): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $_SESSION['error'] = 'Можно загружать только изображения (JPEG, PNG, WEBP, GIF)';
            return null;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Размер файла не должен превышать 5 МБ';
            return null;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = 'recipe_' . uniqid() . '.' . $extension;
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $newName;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            if ($oldImage && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldImage)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $oldImage);
            }
            return '/uploads/' . $newName;
        }
        
        $_SESSION['error'] = 'Не удалось сохранить файл';
        return null;
    }
        /**
     * Управление пользователями (список всех пользователей)
     */
    public function users(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = trim($_GET['search'] ?? '');
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $users = $this->userModel->getAll($limit, $offset, $search);
        $totalUsers = $this->userModel->getTotalCount($search);
        $totalPages = ceil($totalUsers / $limit);
        
        $this->render('admin/users/index', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'searchQuery' => $search
        ]);
    }
    
    /**
     * Редактирование пользователя (администратором)
     */
    public function editUser(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'Пользователь не найден';
            $this->redirect('/admin/users');
            return;
        }
        
        // Нельзя редактировать самого себя через эту форму (можно, но осторожно)
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Вы не можете редактировать свой собственный профиль через админ-панель. Используйте раздел "Мой профиль".';
            $this->redirect('/admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrfToken();
            
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'client';
            $isBlocked = isset($_POST['is_blocked']) ? 1 : 0;
            $newPassword = trim($_POST['new_password'] ?? '');
            
            $errors = [];
            
            // Валидация
            if (empty($username)) {
                $errors[] = "Имя пользователя не может быть пустым";
            }
            
            if (empty($email)) {
                $errors[] = "Email не может быть пустым";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Некорректный формат email";
            }
            
            // Проверяем, не занят ли email другим пользователем
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $id) {
                $errors[] = "Этот email уже используется другим пользователем";
            }
            
            if (!in_array($role, ['admin', 'client'])) {
                $role = 'client';
            }
            
            if (empty($errors)) {
                // Обновляем основные данные
                $this->userModel->update($id, $username, $email);
                $this->userModel->updateRole($id, $role);
                $this->userModel->toggleBlock($id, (bool)$isBlocked);
                
                // Если указан новый пароль - обновляем его
                if (!empty($newPassword)) {
                    if (strlen($newPassword) < 6) {
                        $_SESSION['error'] = 'Пароль должен содержать минимум 6 символов';
                    } else {
                        $this->userModel->updatePassword($id, $newPassword);
                        $_SESSION['success'] = 'Данные пользователя обновлены. Пароль изменён.';
                        $this->redirect('/admin/users');
                        return;
                    }
                }
                
                $_SESSION['success'] = 'Данные пользователя успешно обновлены!';
                $this->redirect('/admin/users');
                return;
            }
            
            $_SESSION['error'] = implode('<br>', $errors);
        }
        
        // Получаем статистику пользователя
        $recipesCount = count($this->recipeModel->getByUser($id));
        
        $this->render('admin/users/edit', [
            'user' => $user,
            'recipesCount' => $recipesCount
        ]);
    }
    
    /**
     * Удаление пользователя (администратором)
     */
    public function deleteUser(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            $_SESSION['error'] = 'Неверный ID пользователя';
            $this->redirect('/admin/users');
            return;
        }
        
        // Нельзя удалить самого себя
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Вы не можете удалить свой собственный аккаунт';
            $this->redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = 'Пользователь не найден';
            $this->redirect('/admin/users');
            return;
        }
        
        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = 'Пользователь "' . htmlspecialchars($user['email']) . '" успешно удалён!';
        } else {
            $_SESSION['error'] = 'Ошибка при удалении пользователя';
        }
        
        $this->redirect('/admin/users');
    }
    
    /**
     * Блокировка/разблокировка пользователя (AJAX или GET)
     */
    public function toggleBlockUser(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            $_SESSION['error'] = 'Неверный ID пользователя';
            $this->redirect('/admin/users');
            return;
        }
        
        // Нельзя заблокировать самого себя
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Вы не можете заблокировать свой собственный аккаунт';
            $this->redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = 'Пользователь не найден';
            $this->redirect('/admin/users');
            return;
        }
        
        $newBlockStatus = $user['is_blocked'] ? 0 : 1;
        $this->userModel->toggleBlock($id, (bool)$newBlockStatus);
        
        $statusText = $newBlockStatus ? 'заблокирован' : 'разблокирован';
        $_SESSION['success'] = 'Пользователь "' . htmlspecialchars($user['email']) . '" успешно ' . $statusText . '!';
        
        $this->redirect('/admin/users');
    }
}