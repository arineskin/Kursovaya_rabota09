<?php
$title = htmlspecialchars($recipe['title']) . ' - Кулинарный портал';
ob_start();
?>

<div class="row">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                <li class="breadcrumb-item"><a href="/?category=<?= $recipe['category_id'] ?>"><?= htmlspecialchars($recipe['category_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($recipe['title']) ?></li>
            </ol>
        </nav>

        <h1><?= htmlspecialchars($recipe['title']) ?></h1>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="badge bg-secondary">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($recipe['author_name'] ?? 'Админ') ?>
                </span>
                <span class="badge bg-info ms-2">
                    <i class="fas fa-calendar"></i> <?= date('d.m.Y', strtotime($recipe['created_at'])) ?>
                </span>
                <span class="badge bg-danger ms-2">
                    <i class="fas fa-heart"></i> <?= $recipe['favorites_count'] ?>
                </span>
            </div>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($isFavorite): ?>
                    <a href="/favorites/remove?id=<?= $recipe['id'] ?>" class="btn btn-danger" onclick="return confirm('Удалить из избранного?')">
                        <i class="fas fa-heart-broken"></i> Удалить из избранного
                    </a>
                <?php else: ?>
                    <a href="/favorites/add?id=<?= $recipe['id'] ?>" class="btn btn-outline-danger">
                        <i class="far fa-heart"></i> Добавить в избранное
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($recipe['image_url']): ?>
            <img src="<?= htmlspecialchars($recipe['image_url']) ?>" class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($recipe['title']) ?>" style="max-height: 400px; width: 100%; object-fit: cover;">
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-info-circle"></i> Описание</h4>
            </div>
            <div class="card-body">
                <p><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
            </div>
        </div>

        <!-- ИНГРЕДИЕНТЫ С КАЛОРИЯМИ -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-list-ul"></i> Ингредиенты</h4>
            </div>
            <div class="card-body">
                <?php if (empty($ingredients)): ?>
                    <p class="text-muted">Ингредиенты не указаны.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($ingredients as $ingredient): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($ingredient['name']) ?>
                                <span class="badge bg-primary rounded-pill">
                                    <?= $ingredient['quantity_grams'] ?> г
                                    (<?= number_format(($ingredient['calories_per_100g'] / 100) * $ingredient['quantity_grams'], 2) ?> ккал)
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- КАЛОРИЙНОСТЬ БЛЮДА -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h4 class="mb-0"><i class="fas fa-fire"></i> Калорийность блюда</h4>
            </div>
            <div class="card-body text-center">
                <h2 class="display-4"><?= number_format($totalCalories, 2) ?> <small class="text-muted">ккал</small></h2>
                <p class="text-muted">на всё блюдо</p>
            </div>
        </div>

        <!-- ПРИГОТОВЛЕНИЕ -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="fas fa-book-open"></i> Приготовление</h4>
            </div>
            <div class="card-body">
                <?= nl2br(htmlspecialchars($recipe['instructions'])) ?>
            </div>
        </div>
    </div>

</div>

<script>
function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Ссылка скопирована в буфер обмена!');
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';