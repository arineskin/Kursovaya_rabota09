<?php
// app/Controllers/RecipeController.php
namespace Controllers;

use Models\Recipe;
use Models\Category;
use Models\Favorite;
use Models\Ingredient;

class RecipeController extends BaseController
{
    private Recipe $recipeModel;
    private Category $categoryModel;
    private Favorite $favoriteModel;
    private Ingredient $ingredientModel;
    
    public function __construct()
    {
        $this->recipeModel = new Recipe();
        $this->categoryModel = new Category();
        $this->favoriteModel = new Favorite();
        $this->ingredientModel = new Ingredient();
        $this->generateCsrfToken();
    }
    
    /**
     * Главная страница со списком рецептов
     */
    public function index(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $search = trim($_GET['search'] ?? '');
        $limit = 9;
        $offset = ($page - 1) * $limit;
        
        $recipes = $this->recipeModel->getAll($limit, $offset, $categoryId, $search);
        $totalRecipes = $this->recipeModel->getTotalCount($categoryId, $search);
        $totalPages = ceil($totalRecipes / $limit);
        $categories = $this->categoryModel->getAll();
        
        $this->render('recipes/index', [
            'recipes' => $recipes,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecipes' => $totalRecipes,
            'selectedCategory' => $categoryId,
            'searchQuery' => $search
        ]);
    }
    
    /**
     * Просмотр одного рецепта
     */
    public function show(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            $this->redirect('/');
            return;
        }
        
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            $this->redirect('/');
            return;
        }
        
        $ingredients = $this->recipeModel->getIngredients($id);
        $totalCalories = $this->recipeModel->calculateCalories($id);
        
        // Проверяем, добавлен ли рецепт в избранное (если пользователь авторизован)
        $isFavorite = false;
        if ($this->isLoggedIn()) {
            $isFavorite = $this->favoriteModel->isFavorite($_SESSION['user_id'], $id);
        }
        
        $this->render('recipes/show', [
            'recipe' => $recipe,
            'ingredients' => $ingredients,
            'totalCalories' => $totalCalories,
            'isFavorite' => $isFavorite
        ]);
    }
    
    /**
     * ФОРМА СОЗДАНИЯ РЕЦЕПТА (для обычных пользователей)
     * URL: /recipe/create
     */
    public function create(): void
    {
        // Только авторизованные пользователи
        $this->requireLogin();
        
        $categories = $this->categoryModel->getAll();
        $allIngredients = $this->ingredientModel->getAll();
        
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
            
            // Валидация
            $errors = [];
            if (empty($data['title'])) {
                $errors[] = "Название рецепта обязательно!";
            }
            if (empty($data['instructions'])) {
                $errors[] = "Инструкция по приготовлению обязательна!";
            }
            if ($data['category_id'] <= 0) {
                $errors[] = "Выберите категорию!";
            }
            
            if (!empty($errors)) {
                $this->render('recipes/user_create', [
                    'categories' => $categories,
                    'ingredients' => $allIngredients,
                    'errors' => $errors,
                    'old' => $_POST
                ]);
                return;
            }
            
            // Обработка загруженного изображения
            $imagePath = $this->uploadImage($_FILES['image'] ?? []);
            if ($imagePath !== null) {
                $data['image_url'] = $imagePath;
            }
            
            // Сохраняем рецепт
            $recipeId = $this->recipeModel->create($data);
            
            // ========== ОБРАБОТКА ИНГРЕДИЕНТОВ ==========
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
            // ===========================================
            
            $this->recipeModel->addIngredients($recipeId, $selectedIngredients);
            
            $_SESSION['success'] = 'Рецепт успешно добавлен!';
            $this->redirect('/profile');
            return;
        }
        
        $this->render('recipes/user_create', [
            'categories' => $categories,
            'ingredients' => $allIngredients,
            'errors' => [],
            'old' => []
        ]);
    }
    
    /**
     * ФОРМА РЕДАКТИРОВАНИЯ РЕЦЕПТА (для автора)
     * URL: /recipe/edit?id=123
     */
    public function edit(): void
    {
        // Только авторизованные пользователи
        $this->requireLogin();
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $recipe = $this->recipeModel->findById($id);
        
        // Проверяем, существует ли рецепт
        if (!$recipe) {
            $_SESSION['error'] = 'Рецепт не найден';
            $this->redirect('/profile');
            return;
        }
        
        // Проверяем, что пользователь - автор рецепта ИЛИ администратор
        if ($recipe['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'У вас нет прав на редактирование этого рецепта';
            $this->redirect('/profile');
            return;
        }
        
        $categories = $this->categoryModel->getAll();
        $allIngredients = $this->ingredientModel->getAll();
        $recipeIngredients = $this->recipeModel->getIngredients($id);
        
        // Создаем массив для быстрого доступа к количеству ингредиентов
        $ingredientQuantities = [];
        foreach ($recipeIngredients as $ri) {
            $ingredientQuantities[$ri['id']] = $ri['quantity_grams'];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrfToken();
            
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'instructions' => trim($_POST['instructions'] ?? ''),
                'image_url' => $recipe['image_url'],
                'category_id' => (int)($_POST['category_id'] ?? 0)
            ];
            
            // Валидация
            $errors = [];
            if (empty($data['title'])) {
                $errors[] = "Название рецепта обязательно!";
            }
            if (empty($data['instructions'])) {
                $errors[] = "Инструкция по приготовлению обязательна!";
            }
            
            if (!empty($errors)) {
                $this->render('recipes/user_edit', [
                    'recipe' => $recipe,
                    'categories' => $categories,
                    'allIngredients' => $allIngredients,
                    'ingredientQuantities' => $ingredientQuantities,
                    'errors' => $errors,
                    'old' => $_POST
                ]);
                return;
            }
            
            // Обработка нового изображения
            $imagePath = $this->uploadImage($_FILES['image'] ?? [], $recipe['image_url']);
            if ($imagePath !== null) {
                $data['image_url'] = $imagePath;
            }
            
            $this->recipeModel->update($id, $data);
            
            // ========== ОБРАБОТКА ИНГРЕДИЕНТОВ ==========
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
            // ===========================================
            
            $this->recipeModel->addIngredients($id, $selectedIngredients);
            
            $_SESSION['success'] = 'Рецепт успешно обновлён!';
            $this->redirect('/profile');
            return;
        }
        
        $this->render('recipes/user_edit', [
            'recipe' => $recipe,
            'categories' => $categories,
            'allIngredients' => $allIngredients,
            'ingredientQuantities' => $ingredientQuantities,
            'errors' => [],
            'old' => []
        ]);
    }
    
    /**
     * УДАЛЕНИЕ РЕЦЕПТА (для автора)
     * URL: /recipe/delete?id=123
     */
    public function delete(): void
    {
        // Только авторизованные пользователи
        $this->requireLogin();
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            $_SESSION['error'] = 'Рецепт не найден';
            $this->redirect('/profile');
            return;
        }
        
        // Проверяем права: автор или админ
        if ($recipe['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'У вас нет прав на удаление этого рецепта';
            $this->redirect('/profile');
            return;
        }
        
        // Удаляем изображение, если есть
        if (!empty($recipe['image_url'])) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $recipe['image_url'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        $this->recipeModel->delete($id);
        $_SESSION['success'] = 'Рецепт успешно удалён!';
        $this->redirect('/profile');
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
        
        return null;
    }
}