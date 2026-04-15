<?php
$title = 'Главная - Кулинарный портал';
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Кулинарные рецепты</h1>
        <p class="text-muted">Откройте для себя вкусные и полезные блюда</p>
    </div>
    <div class="col-md-4">
        <form method="GET" class="d-flex">
            <select name="category" class="form-select me-2" onchange="this.form.submit()">
                <option value="">Все категории</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $selectedCategory == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($selectedCategory): ?>
                <a href="/" class="btn btn-secondary">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<form method="GET" class="row g-3 mb-4">
    <div class="col-md-8">
        <input type="text" name="search" class="form-control" 
               placeholder="Поиск по названию рецепта..." 
               value="<?= htmlspecialchars($searchQuery ?? '') ?>">
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100">Найти</button>
    </div>
    <?php if (!empty($searchQuery)): ?>
        <div class="col-12 text-end">
            <a href="/" class="text-muted">Сбросить поиск</a>
        </div>
    <?php endif; ?>
</form>

<?php if (empty($recipes)): ?>
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-book-open fa-3x mb-3"></i>
        <h4>Рецептов пока нет</h4>
        <p>Станьте первым, кто добавит рецепт!</p>
        
        <!-- Кнопка добавления рецепта -->
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="/admin/recipe/create" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i> Добавить рецепт (админка)
            </a>
        <?php elseif (isset($_SESSION['user_id'])): ?>
            <a href="/recipe/create" class="btn btn-success mt-3">
                <i class="fas fa-plus"></i> Добавить рецепт
            </a>
        <?php else: ?>
            <a href="/login" class="btn btn-outline-primary mt-3">
                <i class="fas fa-sign-in-alt"></i> Войдите, чтобы добавить рецепт
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($recipes as $recipe): ?>
            <div class="col-md-4">
                <div class="card recipe-card h-100">
                    <?php if ($recipe['image_url']): ?>
                        <img src="<?= htmlspecialchars($recipe['image_url']) ?>" class="card-img-top recipe-img" alt="<?= htmlspecialchars($recipe['title']) ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top recipe-img" alt="Нет изображения">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                        <p class="card-text text-muted small">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($recipe['category_name']) ?>
                        </p>
                        <p class="card-text"><?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...</p>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($recipe['author_name'] ?? 'Админ') ?>
                                <i class="fas fa-heart text-danger ms-2"></i> <?= $recipe['favorites_count'] ?>
                            </small>
                        </p>
                        <a href="/recipe?id=<?= $recipe['id'] ?>" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $selectedCategory ? '&category=' . $selectedCategory : '' ?><?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>">
                            &laquo; Назад
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $selectedCategory ? '&category=' . $selectedCategory : '' ?><?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $selectedCategory ? '&category=' . $selectedCategory : '' ?><?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>">
                            Вперед &raquo;
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="text-center text-muted">
            <small>Показано <?= count($recipes) ?> из <?= $totalRecipes ?> рецептов</small>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';