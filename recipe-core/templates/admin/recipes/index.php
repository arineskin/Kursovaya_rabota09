<?php
$title = 'Управление рецептами - Админ-панель';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-utensils"></i> Управление рецептами</h1>
    <a href="/admin/recipe/create" class="btn btn-success">
        <i class="fas fa-plus"></i> Добавить рецепт
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($recipes)): ?>
            <div class="alert alert-info text-center">
                <p>Рецептов пока нет. Добавьте первый рецепт!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Изображение</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Автор</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipes as $recipe): ?>
                            <tr>
                                <td><?= $recipe['id'] ?></td>
                                <td>
                                    <?php if ($recipe['image_url']): ?>
                                        <img src="<?= htmlspecialchars($recipe['image_url']) ?>" width="50" height="50" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white text-center" style="width: 50px; height: 50px; line-height: 50px;">Нет</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($recipe['title']) ?></td>
                                <td><?= htmlspecialchars($recipe['category_name']) ?></td>
                                <td><?= htmlspecialchars($recipe['author_name'] ?? 'Админ') ?></td>
                                <td><?= date('d.m.Y', strtotime($recipe['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/recipe/edit?id=<?= $recipe['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/recipe/delete?id=<?= $recipe['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Удалить рецепт &quot;<?= htmlspecialchars($recipe['title']) ?>&quot;?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="/recipe?id=<?= $recipe['id'] ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<a href="/admin" class="btn btn-secondary mt-3">← Назад в админ-панель</a>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>