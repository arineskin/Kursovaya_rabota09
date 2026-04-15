<?php
// app/Core/Router.php
namespace Core;

class Router
{
    private $routes = [];

    public function __construct()
    {
        $this->defineRoutes();
    }

    private function defineRoutes()
    {
        $this->routes['/'] = ['controller' => 'RecipeController', 'action' => 'index'];
        $this->routes[''] = ['controller' => 'RecipeController', 'action' => 'index'];
        $this->routes['/recipes'] = ['controller' => 'RecipeController', 'action' => 'index'];
        $this->routes['/recipe'] = ['controller' => 'RecipeController', 'action' => 'show'];
        $this->routes['/calculator'] = ['controller' => 'CalculatorController', 'action' => 'index'];
        $this->routes['/calculator/calculate'] = ['controller' => 'CalculatorController', 'action' => 'calculate'];
        $this->routes['/favorites'] = ['controller' => 'FavoriteController', 'action' => 'index'];
        $this->routes['/favorites/add'] = ['controller' => 'FavoriteController', 'action' => 'add'];
        $this->routes['/favorites/remove'] = ['controller' => 'FavoriteController', 'action' => 'remove'];
        $this->routes['/login'] = ['controller' => 'AuthController', 'action' => 'login'];
        $this->routes['/register'] = ['controller' => 'AuthController', 'action' => 'register'];
        $this->routes['/logout'] = ['controller' => 'AuthController', 'action' => 'logout'];
        $this->routes['/admin'] = ['controller' => 'AdminController', 'action' => 'index'];
        $this->routes['/admin/recipes'] = ['controller' => 'AdminController', 'action' => 'recipes'];
        $this->routes['/admin/recipe/create'] = ['controller' => 'AdminController', 'action' => 'createRecipe'];
        $this->routes['/admin/recipe/edit'] = ['controller' => 'AdminController', 'action' => 'editRecipe'];
        $this->routes['/admin/recipe/delete'] = ['controller' => 'AdminController', 'action' => 'deleteRecipe'];
        $this->routes['/admin/ingredients'] = ['controller' => 'AdminController', 'action' => 'ingredients'];
        $this->routes['/admin/ingredient/create'] = ['controller' => 'AdminController', 'action' => 'createIngredient'];
        $this->routes['/admin/ingredient/edit'] = ['controller' => 'AdminController', 'action' => 'editIngredient'];
        $this->routes['/admin/ingredient/delete'] = ['controller' => 'AdminController', 'action' => 'deleteIngredient'];
        $this->routes['/admin/categories'] = ['controller' => 'AdminController', 'action' => 'categories'];
        $this->routes['/admin/category/create'] = ['controller' => 'AdminController', 'action' => 'createCategory'];
        $this->routes['/admin/category/edit'] = ['controller' => 'AdminController', 'action' => 'editCategory'];
        $this->routes['/admin/category/delete'] = ['controller' => 'AdminController', 'action' => 'deleteCategory'];
        // НОВЫЕ МАРШРУТЫ ДЛЯ УПРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЯМИ
        $this->routes['/admin/users'] = ['controller' => 'AdminController', 'action' => 'users'];
        $this->routes['/admin/user/edit'] = ['controller' => 'AdminController', 'action' => 'editUser'];
        $this->routes['/admin/user/delete'] = ['controller' => 'AdminController', 'action' => 'deleteUser'];
        $this->routes['/admin/user/toggle-block'] = ['controller' => 'AdminController', 'action' => 'toggleBlockUser'];
        // КОНЕЦ НОВЫХ МАРШРУТОВ
        $this->routes['/profile'] = ['controller' => 'ProfileController', 'action' => 'index'];
        $this->routes['/profile/edit'] = ['controller' => 'ProfileController', 'action' => 'edit'];
        $this->routes['/recipe/create'] = ['controller' => 'RecipeController', 'action' => 'create'];
        $this->routes['/recipe/edit'] = ['controller' => 'RecipeController', 'action' => 'edit'];
        $this->routes['/recipe/delete'] = ['controller' => 'RecipeController', 'action' => 'delete'];
    }

    public function dispatch($uri)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        if (isset($this->routes[$uri])) {
            $route = $this->routes[$uri];
            $this->callController($route['controller'], $route['action']);
            return;
        }
        
        // Ищем маршруты с параметрами
        foreach ($this->routes as $routeUri => $route) {
            if (strpos($uri, $routeUri) === 0 && $routeUri !== '/') {
                $this->callController($route['controller'], $route['action']);
                return;
            }
        }
        
        http_response_code(404);
        echo "<h1>404 - Страница не найдена</h1>";
        echo "<a href='/'>Вернуться на главную</a>";
    }

    private function callController($controllerName, $action)
    {
        $controllerClass = "\\Controllers\\" . $controllerName;
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $action)) {
                $controller->$action();
                return;
            }
        }
        
        die("Ошибка: Контроллер или метод не найден: " . $controllerClass . "::" . $action);
    }
}