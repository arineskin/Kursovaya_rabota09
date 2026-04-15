<?php
$title = 'Калькулятор калорий - Кулинарный портал';
ob_start();
?>

<div class="row">
    <div class="col-md-6">
        <h1><i class="fas fa-calculator"></i> Калькулятор калорийности блюда</h1>
        <p class="lead">Выберите ингредиенты и укажите их количество, чтобы рассчитать калорийность вашего блюда.</p>
        
        <form method="POST" action="/calculator/calculate" id="calculator-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="calculator-form">
                <h4 class="mb-3">Ингредиенты</h4>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button type="button" id="add-ingredient" class="btn btn-primary btn-sm mb-3">
                            <i class="fas fa-plus"></i> Добавить ингредиент
                        </button>
                        <button type="button" id="clear-all" class="btn btn-secondary btn-sm mb-3">
                            <i class="fas fa-trash"></i> Очистить все
                        </button>
                    </div>
                </div>
                
                <div id="ingredients-container">
                    <!-- Будет динамически добавляться -->
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                    <i class="fas fa-calculator"></i> Рассчитать калорийность
                </button>
            </div>
        </form>
    </div>
    
    <div class="col-md-6">
        <?php if ($totalCalories !== null): ?>
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="card-title">Результат расчета</h3>
                    <div class="display-1 my-4">
                        <?= number_format($totalCalories, 2) ?> <small class="text-white-50">ккал</small>
                    </div>
                    <p class="card-text">Общая калорийность вашего блюда</p>
                </div>
            </div>
            
            <?php if (!empty($selectedIngredients)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Детальный расчет</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Ингредиент</th>
                                    <th>Количество (г)</th>
                                    <th>Калорийность</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($selectedIngredients as $ingredient): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ingredient['name']) ?></td>
                                        <td><?= $ingredient['quantity'] ?> г</td>
                                        <td><?= number_format($ingredient['calories'], 2) ?> ккал</td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary fw-bold">
                                    <td colspan="2" class="text-end">Итого:</td>
                                    <td><?= number_format($totalCalories, 2) ?> ккал</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center text-muted">
                    <i class="fas fa-arrow-left fa-3x mb-3"></i>
                    <p>Добавьте ингредиенты слева, чтобы рассчитать калорийность</p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card mt-4 bg-light">
            <div class="card-body">
                <h5><i class="fas fa-lightbulb"></i> Советы по снижению калорийности</h5>
                <ul class="mt-2">
                    <li>Используйте меньше масла при жарке</li>
                    <li>Замените жирные продукты на обезжиренные</li>
                    <li>Увеличьте количество овощей</li>
                    <li>Готовьте на пару вместо жарки</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Создаём datalist со всеми ингредиентами -->
<datalist id="ingredients-list">
    <?php foreach ($ingredients as $ingredient): ?>
        <option value="<?= htmlspecialchars($ingredient['name']) ?>" 
                data-id="<?= $ingredient['id'] ?>" 
                data-calories="<?= $ingredient['calories_per_100g'] ?>">
    <?php endforeach; ?>
</datalist>

<script>
// Массив для хранения выбранных ингредиентов (для восстановления после отправки)
let selectedIngredients = <?= json_encode($selectedIngredients ?? []) ?>;

// Функция для создания строки ингредиента
function createIngredientRow(ingredientId = '', ingredientName = '', quantity = '', caloriesPer100g = '') {
    const row = document.createElement('div');
    row.className = 'ingredient-row row mb-2';
    row.setAttribute('data-id', ingredientId);
    row.setAttribute('data-calories', caloriesPer100g);
    row.innerHTML = `
        <div class="col-md-5">
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
            <span class="ingredient-calories-badge badge bg-info">0 ккал</span>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm remove-ingredient">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Добавляем обработчики
    const nameInput = row.querySelector('.ingredient-name');
    const quantityInput = row.querySelector('.ingredient-quantity');
    const caloriesBadge = row.querySelector('.ingredient-calories-badge');
    
    // При изменении названия ингредиента
    nameInput.addEventListener('change', function() {
        const selectedOption = document.querySelector(`#ingredients-list option[value="${this.value}"]`);
        if (selectedOption) {
            row.setAttribute('data-id', selectedOption.dataset.id);
            row.setAttribute('data-calories', selectedOption.dataset.calories);
            updateCaloriesForRow(row);
        } else {
            row.setAttribute('data-id', '');
            row.setAttribute('data-calories', '0');
            caloriesBadge.textContent = '0 ккал';
        }
    });
    
    // При изменении количества
    quantityInput.addEventListener('input', function() {
        updateCaloriesForRow(row);
    });
    
    // Кнопка удаления
    row.querySelector('.remove-ingredient').addEventListener('click', function() {
        row.remove();
    });
    
    // Обновляем калории, если есть данные
    if (quantity > 0 && caloriesPer100g > 0) {
        updateCaloriesForRow(row);
    }
    
    return row;
}

// Функция обновления калорий для одной строки
function updateCaloriesForRow(row) {
    const quantity = parseFloat(row.querySelector('.ingredient-quantity').value) || 0;
    const caloriesPer100g = parseFloat(row.getAttribute('data-calories')) || 0;
    const calories = (caloriesPer100g / 100) * quantity;
    const badge = row.querySelector('.ingredient-calories-badge');
    badge.textContent = Math.round(calories) + ' ккал';
}

// Функция обновления общей калорийности на форме (до отправки)
function updateTotalCaloriesPreview() {
    let total = 0;
    document.querySelectorAll('.ingredient-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.ingredient-quantity').value) || 0;
        const caloriesPer100g = parseFloat(row.getAttribute('data-calories')) || 0;
        total += (caloriesPer100g / 100) * quantity;
    });
    
    // Показываем предварительный итог (опционально)
    let previewElement = document.getElementById('total-preview');
    if (!previewElement) {
        previewElement = document.createElement('div');
        previewElement.id = 'total-preview';
        previewElement.className = 'alert alert-info mt-3 text-center';
        document.querySelector('.calculator-form').appendChild(previewElement);
    }
    previewElement.innerHTML = `<strong>Предварительный итог:</strong> ${Math.round(total)} ккал`;
}

// Инициализация: добавляем пустую строку, если нет сохранённых ингредиентов
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('ingredients-container');
    
    // Если есть сохранённые ингредиенты из предыдущего расчёта
    if (selectedIngredients && Object.keys(selectedIngredients).length > 0) {
        for (const id in selectedIngredients) {
            const ing = selectedIngredients[id];
            const row = createIngredientRow(id, ing.name, ing.quantity, ing.calories_per_100g);
            container.appendChild(row);
        }
    } else {
        // Добавляем одну пустую строку
        const row = createIngredientRow();
        container.appendChild(row);
    }
    
    // Кнопка добавления нового ингредиента
    document.getElementById('add-ingredient').addEventListener('click', function() {
        const row = createIngredientRow();
        container.appendChild(row);
        row.scrollIntoView({ behavior: 'smooth' });
    });
    
    // Кнопка очистки всех ингредиентов
    document.getElementById('clear-all').addEventListener('click', function() {
        if (confirm('Очистить все ингредиенты?')) {
            container.innerHTML = '';
            const row = createIngredientRow();
            container.appendChild(row);
        }
    });
    
    // При отправке формы – удаляем пустые строки
    document.getElementById('calculator-form').addEventListener('submit', function(e) {
        // Удаляем пустые строки
        document.querySelectorAll('.ingredient-row').forEach(row => {
            const nameInput = row.querySelector('.ingredient-name');
            const quantityInput = row.querySelector('.ingredient-quantity');
            if (!nameInput.value.trim() || !quantityInput.value || quantityInput.value <= 0) {
                row.remove();
            }
        });
        
        // Если не осталось ингредиентов – показываем ошибку
        if (document.querySelectorAll('.ingredient-row').length === 0) {
            e.preventDefault();
            alert('Добавьте хотя бы один ингредиент!');
        }
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
    transition: all 0.2s;
}

.ingredient-row:hover {
    background: #f8f9fa;
    border-color: #86b7fe;
}

.ingredient-calories-badge {
    font-size: 0.85rem;
    padding: 8px 12px;
    display: inline-block;
    width: 100%;
    text-align: center;
}

#add-ingredient, #clear-all {
    margin-right: 5px;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';