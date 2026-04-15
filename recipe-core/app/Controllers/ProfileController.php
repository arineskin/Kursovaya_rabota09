<?php
namespace Controllers;

use Models\User;
use Models\Recipe;
use Models\Favorite;

class ProfileController extends BaseController
{
    private User $userModel;
    private Recipe $recipeModel;
    private Favorite $favoriteModel;

    public function __construct()
    {
        $this->requireLogin();
        $this->userModel = new User();
        $this->recipeModel = new Recipe();
        $this->favoriteModel = new Favorite();
        $this->generateCsrfToken();
    }

    /**
     * Главная страница профиля (мои данные + мои рецепты + избранное)
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        
        $user = $this->userModel->findById($userId);
        $myRecipes = $this->recipeModel->getByUser($userId);
        $favorites = $this->favoriteModel->getUserFavorites($userId);
        
        $this->render('profile/index', [
            'user' => $user,
            'myRecipes' => $myRecipes,
            'favorites' => $favorites
        ]);
    }

    /**
     * Редактирование профиля (имя, email)
     */
    public function edit(): void
    {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrfToken();
            
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
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
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors[] = "Этот email уже используется другим пользователем";
            }
            
            if (empty($errors)) {
                $this->userModel->update($userId, $username, $email);
                $_SESSION['success'] = 'Профиль успешно обновлён!';
                $this->redirect('/profile');
                return;
            }
            
            $_SESSION['error'] = implode('<br>', $errors);
        }
        
        $user = $this->userModel->findById($userId);
        $this->render('profile/edit', ['user' => $user]);
    }
}