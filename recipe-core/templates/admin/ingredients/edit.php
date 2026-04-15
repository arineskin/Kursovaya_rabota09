<?php
$title = 'Редактировать ингредиент - Админ-панель';
ob_start();
?>

<h1><i class="fas fa-edit"></i> Редактировать ингредиент</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="card p-4">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <div class="mb-3">
        <label class="form-label">Название ингредиента *</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($ingredient['name']) ?>" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Калорийность на 100 г *</label>
        <input type="number" name="calories_per_100g" class="form-control" step="0.01" value="<?= $ingredient['calories_per_100g'] ?>" required>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning">Обновить</button>
        <a href="/admin/ingredients" class="btn btn-secondary">Отмена</a>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>