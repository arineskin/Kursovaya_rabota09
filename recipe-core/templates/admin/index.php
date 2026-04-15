<?php
$title = 'Админ-панель - Кулинарный портал';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-crown"></i> Админ-панель</h1>
    <a href="/" class="btn btn-secondary">Вернуться на сайт</a>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-utensils"></i> Рецепты</h5>
                <h2 class="display-4"><?= $recipesCount ?></h2>
                <a href="/admin/recipes" class="btn btn-light mt-2">Управлять →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-tags"></i> Категории</h5>
                <h2 class="display-4"><?= $categoriesCount ?></h2>
                <a href="/admin/categories" class="btn btn-light mt-2">Управлять →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-carrot"></i> Ингредиенты</h5>
                <h2 class="display-4"><?= $ingredientsCount ?></h2>
                <a href="/admin/ingredients" class="btn btn-light mt-2">Управлять →</a>
            </div>
        </div>
    </div>
    
    <!-- НОВЫЙ БЛОК: Пользователи -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users"></i> Пользователи</h5>
                <h2 class="display-4"><?= $usersCount ?? 0 ?></h2>
                <a href="/admin/users" class="btn btn-light mt-2">Управлять →</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Быстрые действия</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="/admin/recipe/create" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-plus"></i> Добавить рецепт
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/ingredient/create" class="btn btn-outline-success w-100 mb-2">
                    <i class="fas fa-plus"></i> Добавить ингредиент
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/category/create" class="btn btn-outline-info w-100 mb-2">
                    <i class="fas fa-plus"></i> Добавить категорию
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/users" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-users"></i> Управление пользователями
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>