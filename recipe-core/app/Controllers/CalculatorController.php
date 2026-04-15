<?php
// app/Controllers/CalculatorController.php
namespace Controllers;

use Models\Ingredient;

class CalculatorController extends BaseController
{
    private Ingredient $ingredientModel;

    public function __construct()
    {
        $this->ingredientModel = new Ingredient();
        $this->generateCsrfToken();
    }

    public function index(): void
    {
        $ingredients = $this->ingredientModel->getAll();
        $this->render('calculator/index', [
            'ingredients' => $ingredients,
            'totalCalories' => null,
            'selectedIngredients' => []
        ]);
    }
    
    public function calculate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/calculator');
            return;
        }
        
        // Проверяем CSRF-токен
        $this->requireCsrfToken();
        
        $ingredients = $this->ingredientModel->getAll();
        $selectedIngredients = [];
        $totalCalories = 0;
        
        // Получаем данные из формы
        $names = $_POST['ingredients_name'] ?? [];
        $quantities = $_POST['ingredients_quantity'] ?? [];
        
        // Для отладки (удалите после проверки)
        // error_log('Names: ' . print_r($names, true));
        // error_log('Quantities: ' . print_r($quantities, true));
        
        // Создаём массив для быстрого поиска ингредиентов по названию (без учёта регистра)
        $ingredientsByName = [];
        foreach ($ingredients as $ingredient) {
            $ingredientsByName[mb_strtolower($ingredient['name'])] = $ingredient;
        }
        
        foreach ($names as $index => $name) {
            $name = trim($name);
            $quantity = isset($quantities[$index]) ? (int)$quantities[$index] : 0;
            
            // Пропускаем пустые строки и нулевое количество
            if (empty($name) || $quantity <= 0) {
                continue;
            }
            
            // Ищем ингредиент по названию (без учёта регистра)
            $nameLower = mb_strtolower($name);
            if (isset($ingredientsByName[$nameLower])) {
                $ingredient = $ingredientsByName[$nameLower];
                $calories = ($ingredient['calories_per_100g'] / 100) * $quantity;
                $totalCalories += $calories;
                
                $selectedIngredients[$ingredient['id']] = [
                    'id' => $ingredient['id'],
                    'name' => $ingredient['name'],
                    'quantity' => $quantity,
                    'calories' => $calories,
                    'calories_per_100g' => $ingredient['calories_per_100g']
                ];
            }
        }
        
        $this->render('calculator/index', [
            'ingredients' => $ingredients,
            'totalCalories' => round($totalCalories, 2),
            'selectedIngredients' => $selectedIngredients
        ]);
    }
}