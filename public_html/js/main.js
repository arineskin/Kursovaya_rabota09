// public/js/main.js
// Основные JavaScript функции для проекта

// Функция для копирования текста в буфер обмена
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Ссылка скопирована в буфер обмена!', 'success');
    }).catch(() => {
        showNotification('Не удалось скопировать ссылку', 'error');
    });
}

// Функция для показа уведомлений
function showNotification(message, type = 'info') {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически скрываем через 3 секунды
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Функция для подтверждения удаления
function confirmDelete(itemName, itemType = 'элемент') {
    return confirm(`Вы уверены, что хотите удалить ${itemType} "${itemName}"?`);
}

// Функция для валидации формы добавления рецепта
function validateRecipeForm(form) {
    const title = form.querySelector('[name="title"]').value.trim();
    const instructions = form.querySelector('[name="instructions"]').value.trim();
    
    if (!title) {
        showNotification('Введите название рецепта!', 'error');
        return false;
    }
    
    if (!instructions) {
        showNotification('Введите инструкцию по приготовлению!', 'error');
        return false;
    }
    
    return true;
}

// Функция для валидации ингредиента
function validateIngredient(form) {
    const name = form.querySelector('[name="name"]').value.trim();
    const calories = form.querySelector('[name="calories_per_100g"]').value;
    
    if (!name) {
        showNotification('Введите название ингредиента!', 'error');
        return false;
    }
    
    if (!calories || calories <= 0) {
        showNotification('Введите корректную калорийность (больше 0)!', 'error');
        return false;
    }
    
    return true;
}

// Функция для автоматического расчета калорий в калькуляторе
function updateCalculatorTotal() {
    let total = 0;
    const rows = document.querySelectorAll('.ingredient-row');
    
    rows.forEach(row => {
        const checkbox = row.querySelector('.ingredient-checkbox');
        const quantityInput = row.querySelector('.ingredient-quantity');
        const caloriesPer100g = parseFloat(row.querySelector('.calories-per-100g')?.dataset.calories || 0);
        
        if (checkbox && checkbox.checked && quantityInput && quantityInput.value) {
            const quantity = parseFloat(quantityInput.value);
            if (!isNaN(quantity) && quantity > 0) {
                total += (caloriesPer100g / 100) * quantity;
            }
        }
    });
    
    const totalElement = document.getElementById('total-calories');
    if (totalElement) {
        totalElement.textContent = total.toFixed(2);
    }
}

// Функция для фильтрации рецептов на главной (без перезагрузки)
function filterRecipes(categoryId) {
    const url = new URL(window.location.href);
    url.searchParams.set('category', categoryId);
    window.location.href = url.toString();
}

// Функция для "Выбрать все" в калькуляторе
function toggleSelectAll(checkbox) {
    const ingredientCheckboxes = document.querySelectorAll('.ingredient-checkbox');
    ingredientCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        const row = cb.closest('.ingredient-row');
        const quantityInput = row?.querySelector('.ingredient-quantity');
        if (quantityInput) {
            quantityInput.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                quantityInput.value = '';
            }
        }
    });
    updateCalculatorTotal();
}

// Функция для включения/отключения поля количества ингредиента
function toggleIngredientQuantity(checkbox) {
    const row = checkbox.closest('.ingredient-row');
    const quantityInput = row?.querySelector('.ingredient-quantity');
    
    if (quantityInput) {
        quantityInput.disabled = !checkbox.checked;
        if (!checkbox.checked) {
            quantityInput.value = '';
        }
    }
    updateCalculatorTotal();
}

// Функция для динамического добавления ингредиента в рецепт (админ-панель)
function addIngredientRow(container, ingredientId, ingredientName, calories) {
    const row = document.createElement('div');
    row.className = 'row mb-2 align-items-center';
    row.innerHTML = `
        <div class="col-md-6">
            ${ingredientName}
            <small class="text-muted">(${calories} ккал/100г)</small>
        </div>
        <div class="col-md-4">
            <input type="number" 
                   name="ingredients[${ingredientId}]" 
                   class="form-control form-control-sm"
                   placeholder="Количество (г)"
                   min="0"
                   step="1">
        </div>
        <div class="col-md-2">
            <small class="text-muted">грамм</small>
        </div>
    `;
    container.appendChild(row);
}

// Функция для предпросмотра изображения перед загрузкой
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Автоматическое скрытие уведомлений
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    }, 100);
    
    // Добавляем обработчики для калькулятора
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            toggleSelectAll(this);
        });
    }
    
    const ingredientCheckboxes = document.querySelectorAll('.ingredient-checkbox');
    ingredientCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleIngredientQuantity(this);
        });
    });
    
    const quantityInputs = document.querySelectorAll('.ingredient-quantity');
    quantityInputs.forEach(input => {
        input.addEventListener('input', updateCalculatorTotal);
    });
    
    // Добавляем анимацию для карточек
    const cards = document.querySelectorAll('.recipe-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
    
    // Обработчик для формы добавления рецепта
    const recipeForm = document.getElementById('recipe-form');
    if (recipeForm) {
        recipeForm.addEventListener('submit', function(e) {
            if (!validateRecipeForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // Обработчик для формы добавления ингредиента
    const ingredientForm = document.getElementById('ingredient-form');
    if (ingredientForm) {
        ingredientForm.addEventListener('submit', function(e) {
            if (!validateIngredient(this)) {
                e.preventDefault();
            }
        });
    }
    
    // Обработчик для предпросмотра изображения
    const imageInput = document.querySelector('[name="image_url"]');
    const imagePreview = document.getElementById('image-preview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            previewImage(this, imagePreview);
        });
    }
});

// Экспортируем функции для использования в других скриптах
window.copyToClipboard = copyToClipboard;
window.showNotification = showNotification;
window.confirmDelete = confirmDelete;
window.filterRecipes = filterRecipes;
window.updateCalculatorTotal = updateCalculatorTotal;
window.toggleSelectAll = toggleSelectAll;