<?php
$title = 'Управление ингредиентами - Админ-панель';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-carrot"></i> Управление ингредиентами</h1>
    <a href="/admin/ingredient/create" class="btn btn-success">
        <i class="fas fa-plus"></i> Добавить ингредиент
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($ingredients)): ?>
            <div class="alert alert-info text-center">
                <p>Ингредиентов пока нет. Добавьте первый ингредиент!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название ингредиента</th>
                            <th>Калорийность (ккал/100г)</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <tr>
                                <td><?= $ingredient['id'] ?></td>
                                <td><?= htmlspecialchars($ingredient['name']) ?></td>
                                <td>
                                    <span class="badge bg-warning"><?= number_format($ingredient['calories_per_100g'], 2) ?> ккал</span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($ingredient['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/ingredient/edit?id=<?= $ingredient['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/ingredient/delete?id=<?= $ingredient['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Удалить ингредиент &quot;<?= htmlspecialchars($ingredient['name']) ?>&quot;?')">
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