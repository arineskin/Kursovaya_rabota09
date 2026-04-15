<?php
$title = 'Добавить рецепт - Админ-панель';
ob_start();
?>

<h1><i class="fas fa-plus"></i> Добавить новый рецепт</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- ВАЖНО: добавлен атрибут enctype="multipart/form-data" -->
<form method="POST" enctype="multipart/form-data" class="card p-4">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <div class="mb-3">
        <label class="form-label">Название рецепта *</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Категория *</label>
        <select name="category_id" class="form-select" required>
            <option value="">Выберите категорию</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Краткое описание</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    
    <!-- НОВОЕ ПОЛЕ: загрузка изображения вместо URL -->
    <div class="mb-3">
        <label class="form-label">Изображение рецепта</label>
        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
        <small class="text-muted">Поддерживаются форматы: JPG, PNG, WEBP. Максимальный размер: 5 МБ</small>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Ингредиенты</label>
        <div class="border p-3 rounded" id="ingredients-container">
            <div class="ingredient-row row mb-2">
                <div class="col-md-6">
                    <input type="text" class="form-control" list="ingredients-list" 
                           name="ingredients_name[]" placeholder="Начните вводить ингредиент...">
                    <datalist id="ingredients-list">
                        <?php foreach ($ingredients as $ingredient): ?>
                            <option value="<?= htmlspecialchars($ingredient['name']) ?>" 
                                    data-id="<?= $ingredient['id'] ?>" 
                                    data-calories="<?= $ingredient['calories_per_100g'] ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-3">
                    <input type="number" name="ingredients_quantity[]" class="form-control" 
                           placeholder="Количество (г)" step="1" min="0">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-ingredient">✖</button>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-ingredient">
            + Добавить ингредиент
        </button>
        <input type="hidden" name="ingredients_ids[]" id="ingredients-ids">
    </div>
    
    <div class="mb-3">
        <label class="form-label">Пошаговый рецепт *</label>
        <textarea name="instructions" class="form-control" rows="10" required></textarea>
        <small class="text-muted">Опишите процесс приготовления подробно, желательно по шагам</small>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">Сохранить рецепт</button>
        <a href="/admin/recipes" class="btn btn-secondary">Отмена</a>
    </div>
</form>
<script>
// Добавление нового поля для ингредиента
document.getElementById('add-ingredient')?.addEventListener('click', function() {
    const container = document.getElementById('ingredients-container');
    const newRow = document.createElement('div');
    newRow.className = 'ingredient-row row mb-2';
    newRow.innerHTML = `
        <div class="col-md-6">
            <input type="text" class="form-control" list="ingredients-list" 
                   name="ingredients_name[]" placeholder="Начните вводить ингредиент...">
        </div>
        <div class="col-md-3">
            <input type="number" name="ingredients_quantity[]" class="form-control" 
                   placeholder="Количество (г)" step="1" min="0">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-ingredient">✖</button>
        </div>
    `;
    container.appendChild(newRow);
    
    // Добавляем обработчик для кнопки удаления
    newRow.querySelector('.remove-ingredient').addEventListener('click', function() {
        newRow.remove();
    });
});

// Удаление ингредиента
document.querySelectorAll('.remove-ingredient').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.ingredient-row').remove();
    });
});

// При отправке формы – собираем ID ингредиентов
document.querySelector('form')?.addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.ingredient-row');
    const ids = [];
    rows.forEach(row => {
        const input = row.querySelector('input[list="ingredients-list"]');
        const selectedOption = document.querySelector(`#ingredients-list option[value="${input.value}"]`);
        if (selectedOption && selectedOption.dataset.id) {
            ids.push(selectedOption.dataset.id);
        }
    });
    document.getElementById('ingredients-ids').value = ids.join(',');
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>