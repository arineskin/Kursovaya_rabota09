<?php
$title = 'Управление категориями - Админ-панель';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-tags"></i> Управление категориями</h1>
    <a href="/admin/category/create" class="btn btn-success">
        <i class="fas fa-plus"></i> Добавить категорию
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="alert alert-info text-center">
                <p>Категорий пока нет. Добавьте первую категорию!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название категории</th>
                            <th>Количество рецептов</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td>
                                    <?php
                                    // Считаем количество рецептов в категории
                                    $recipeCount = 0;
                                    if (isset($category['recipe_count'])) {
                                        $recipeCount = $category['recipe_count'];
                                    }
                                    ?>
                                    <span class="badge bg-info"><?= $recipeCount ?> рецептов</span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($category['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/category/edit?id=<?= $category['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/category/delete?id=<?= $category['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Удалить категорию &quot;<?= htmlspecialchars($category['name']) ?>&quot;?')">
                                        <i class="fas fa-trash"></i>
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