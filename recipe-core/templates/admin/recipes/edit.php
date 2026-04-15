<?php
$title = 'Редактировать рецепт - Админ-панель';
ob_start();
?>

<h1><i class="fas fa-edit"></i> Редактировать рецепт</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card p-4" id="recipe-form">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <div class="mb-3">
        <label class="form-label">Название рецепта *</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($recipe['title']) ?>" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Категория *</label>
        <select name="category_id" class="form-select" required>
            <option value="">Выберите категорию</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $recipe['category_id'] == $category['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Краткое описание</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($recipe['description']) ?></textarea>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Изображение рецепта</label>
        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
        <small class="text-muted">Поддерживаются форматы: JPG, PNG, WEBP. Максимальный размер: 5 МБ</small>
        
        <?php if (!empty($recipe['image_url'])): ?>
            <div class="mt-2">
                <img src="<?= htmlspecialchars($recipe['image_url']) ?>" style="max-height: 100px; border-radius: 5px;">
                <p class="text-muted small mt-1">Текущее изображение. Если загрузите новое - оно заменит старое.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- НОВЫЙ БЛОК ИНГРЕДИЕНТОВ (как в калькуляторе) -->
    <div class="mb-3">
        <label class="form-label">Ингредиенты</label>
        <div class="border p-3 rounded">
            <div id="ingredients-container">
                <!-- Существующие ингредиенты будут загружены через JavaScript -->
            </div>
            <button type="button" id="add-ingredient" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-plus"></i> Добавить ингредиент
            </button>
        </div>
        <small class="text-muted">Начните вводить название ингредиента и выберите из списка. Укажите количество в граммах.</small>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Пошаговый рецепт *</label>
        <textarea name="instructions" class="form-control" rows="10" required><?= htmlspecialchars($recipe['instructions']) ?></textarea>
        <small class="text-muted">Опишите процесс приготовления подробно, желательно по шагам</small>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning">Обновить рецепт</button>
        <a href="/admin/recipes" class="btn btn-secondary">Отмена</a>
    </div>
</form>

<!-- DATALIST со всеми ингредиентами -->
<datalist id="ingredients-list">
    <?php foreach ($allIngredients as $ingredient): ?>
        <option value="<?= htmlspecialchars($ingredient['name']) ?>" 
                data-id="<?= $ingredient['id'] ?>" 
                data-calories="<?= $ingredient['calories_per_100g'] ?>">
    <?php endforeach; ?>
</datalist>

<script>
// Данные о существующих ингредиентах рецепта из PHP
const existingIngredients = <?= json_encode($recipeIngredients) ?>;

// Функция создания строки ингредиента
function createIngredientRow(ingredientId = '', ingredientName = '', quantity = '', caloriesPer100g = '') {
    const row = document.createElement('div');
    row.className = 'ingredient-row row mb-2';
    row.setAttribute('data-id', ingredientId);
    row.setAttribute('data-calories', caloriesPer100g);
    row.innerHTML = `
        <div class="col-md-6">
            <input type="text" 
                   name="ingredients_name[]"
                   class="form-control ingredient-name" 
                   list="ingredients-list"
                   placeholder="Начните вводить ингредиент..."
                   value="${ingredientName.replace(/"/g, '&quot;')}"
                   autocomplete="off">
        </div>
        <div class="col-md-4">
            <input type="number" 
                   name="ingredients_quantity[]"
                   class="form-control ingredient-quantity" 
                   placeholder="Количество (г)"
                   value="${quantity}"
                   min="0"
                   step="1">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-ingredient">
                <i class="fas fa-times"></i> Удалить
            </button>
        </div>
    `;
    
    // Обработчик изменения названия
    const nameInput = row.querySelector('.ingredient-name');
    nameInput.addEventListener('change', function() {
        const selectedOption = document.querySelector(`#ingredients-list option[value="${this.value}"]`);
        if (selectedOption) {
            row.setAttribute('data-id', selectedOption.dataset.id);
            row.setAttribute('data-calories', selectedOption.dataset.calories);
        } else {
            row.setAttribute('data-id', '');
            row.setAttribute('data-calories', '0');
        }
    });
    
    // Кнопка удаления
    row.querySelector('.remove-ingredient').addEventListener('click', function() {
        row.remove();
    });
    
    return row;
}

// Загрузка существующих ингредиентов
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('ingredients-container');
    
    // Добавляем существующие ингредиенты
    if (existingIngredients && existingIngredients.length > 0) {
        existingIngredients.forEach(ing => {
            const row = createIngredientRow(ing.id, ing.name, ing.quantity_grams, ing.calories_per_100g);
            container.appendChild(row);
        });
    } else {
        // Добавляем одну пустую строку, если нет ингредиентов
        const row = createIngredientRow();
        container.appendChild(row);
    }
    
    // Кнопка добавления нового ингредиента
    document.getElementById('add-ingredient').addEventListener('click', function() {
        const row = createIngredientRow();
        container.appendChild(row);
    });
});
</script>

<style>
.ingredient-row {
    background: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    border: 1px solid #dee2e6;
}
.ingredient-row:hover {
    background: #f8f9fa;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';