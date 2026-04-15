<?php
$title = 'Добавить рецепт - Кулинарный портал';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Добавить новый рецепт</h4>
            </div>
            <div class="card-body">
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Пожалуйста, исправьте следующие ошибки:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="recipe-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Название рецепта *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Категория *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"
                                    <?= (($old['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Краткое описание</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Изображение рецепта</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Поддерживаются: JPG, PNG, WEBP. Максимум 5 МБ</small>
                    </div>
                    
                    <!-- ИНГРЕДИЕНТЫ -->
                    <div class="mb-3">
                        <label class="form-label">Ингредиенты</label>
                        <div class="border p-3 rounded bg-light">
                            <div id="ingredients-container">
                                <div class="ingredient-row row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control ingredient-name" 
                                               name="ingredients_name[]" list="ingredients-list"
                                               placeholder="Начните вводить ингредиент...">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="ingredients_quantity[]" 
                                               class="form-control ingredient-quantity" 
                                               placeholder="Количество (г)" step="1" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-ingredient">
                                            <i class="fas fa-times"></i> Удалить
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm mt-2" id="add-ingredient">
                                <i class="fas fa-plus"></i> Добавить ингредиент
                            </button>
                        </div>
                        <small class="text-muted">Начните вводить название ингредиента и выберите из списка.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Пошаговый рецепт *</label>
                        <textarea name="instructions" class="form-control" rows="10" required><?= htmlspecialchars($old['instructions'] ?? '') ?></textarea>
                        <small class="text-muted">Опишите процесс приготовления подробно</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Опубликовать рецепт
                        </button>
                        <a href="/profile" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- DATALIST со всеми ингредиентами -->
<datalist id="ingredients-list">
    <?php foreach ($ingredients as $ingredient): ?>
        <option value="<?= htmlspecialchars($ingredient['name']) ?>" 
                data-id="<?= $ingredient['id'] ?>" 
                data-calories="<?= $ingredient['calories_per_100g'] ?>">
    <?php endforeach; ?>
</datalist>

<script>
// Добавление нового поля для ингредиента
document.getElementById('add-ingredient')?.addEventListener('click', function() {
    const container = document.getElementById('ingredients-container');
    const newRow = document.createElement('div');
    newRow.className = 'ingredient-row row mb-2';
    newRow.innerHTML = `
        <div class="col-md-6">
            <input type="text" class="form-control ingredient-name" 
                   name="ingredients_name[]" list="ingredients-list"
                   placeholder="Начните вводить ингредиент...">
        </div>
        <div class="col-md-4">
            <input type="number" name="ingredients_quantity[]" 
                   class="form-control ingredient-quantity" 
                   placeholder="Количество (г)" step="1" min="0">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-ingredient">
                <i class="fas fa-times"></i> Удалить
            </button>
        </div>
    `;
    container.appendChild(newRow);
    
    // Добавляем обработчик для новой кнопки удаления
    newRow.querySelector('.remove-ingredient').addEventListener('click', function() {
        newRow.remove();
    });
});

// Удаление существующих ингредиентов
document.querySelectorAll('.remove-ingredient').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.ingredient-row').remove();
    });
});

// При отправке формы – удаляем пустые строки
document.getElementById('recipe-form')?.addEventListener('submit', function(e) {
    document.querySelectorAll('.ingredient-row').forEach(row => {
        const nameInput = row.querySelector('.ingredient-name');
        const quantityInput = row.querySelector('.ingredient-quantity');
        if (!nameInput.value.trim() || !quantityInput.value || quantityInput.value <= 0) {
            row.remove();
        }
    });
});
</script>

<style>
.ingredient-row {
    background: white;
    padding: 8px;
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1px solid #dee2e6;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';