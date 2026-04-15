<?php
// app/Controllers/FavoriteController.php
namespace Controllers;

use Models\Favorite;

class FavoriteController extends BaseController
{
    private Favorite $favoriteModel;
    
    public function __construct()
    {
        $this->favoriteModel = new Favorite();
        $this->generateCsrfToken();
    }
    
    public function index(): void
    {
        $this->requireLogin();
        
        $favorites = $this->favoriteModel->getUserFavorites($_SESSION['user_id']);
        
        $this->render('favorites/index', [
            'favorites' => $favorites
        ]);
    }
    
    public function add(): void
    {
        $this->requireLogin();
        
        $recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($recipeId > 0) {
            $this->favoriteModel->add($_SESSION['user_id'], $recipeId);
            $_SESSION['success'] = 'Рецепт добавлен в избранное!';
        }
        
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
    
    public function remove(): void
    {
        $this->requireLogin();
        
        $recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($recipeId > 0) {
            $this->favoriteModel->remove($_SESSION['user_id'], $recipeId);
            $_SESSION['success'] = 'Рецепт удален из избранного!';
        }
        
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}