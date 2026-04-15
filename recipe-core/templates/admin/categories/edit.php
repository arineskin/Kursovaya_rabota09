<?php
$title = 'Редактировать категорию - Админ-панель';
ob_start();
?>

<h1><i class="fas fa-edit"></i> Редактировать категорию</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="card p-4">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <div class="mb-3">
        <label class="form-label">Название категории *</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning">Обновить</button>
        <a href="/admin/categories" class="btn btn-secondary">Отмена</a>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>