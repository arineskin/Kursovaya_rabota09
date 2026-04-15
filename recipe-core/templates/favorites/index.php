<?php
$title = 'Избранные рецепты - Кулинарный портал';
ob_start();
?>

<h1><i class="fas fa-heart text-danger"></i> Мои избранные рецепты</h1>
<p class="text-muted">Рецепты, которые вы сохранили для себя</p>

<?php if (empty($favorites)): ?>
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-heart-broken fa-3x mb-3"></i>
        <h4>У вас пока нет избранных рецептов</h4>
        <p>Добавляйте рецепты, чтобы сохранить их здесь</p>
        <a href="/" class="btn btn-primary">Перейти к рецептам</a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($favorites as $recipe): ?>
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
                        <div class="d-flex justify-content-between">
                            <a href="/recipe?id=<?= $recipe['id'] ?>" class="btn btn-primary btn-sm">Подробнее</a>
                            <a href="/favorites/remove?id=<?= $recipe['id'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Удалить из избранного?')">
                                <i class="fas fa-trash"></i> Удалить
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>