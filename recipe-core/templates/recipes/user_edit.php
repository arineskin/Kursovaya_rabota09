<?php
$title = 'Редактировать рецепт - Кулинарный портал';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning">
                <h4 class="mb-0"><i class="fas fa-edit"></i> Редактировать рецепт</h4>
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
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Название рецепта *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?= htmlspecialchars($old['title'] ?? $recipe['title']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Категория *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"
                                    <?= (($old['category_id'] ?? $recipe['category_id']) == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Краткое описание</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($old['description'] ?? $recipe['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Изображение рецепта</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        
                        <?php if (!empty($recipe['image_url'])): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($recipe['image_url']) ?>" style="max-height: 100px; border-radius: 5px;">
                                <p class="text-muted small mt-1">Текущее изображение. Загрузите новое, чтобы заменить.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ингредиенты</label>
                        <div class="border p-3 rounded bg-light">
                            <?php foreach ($allIngredients as $ingredient): ?>
                                <?php $quantity = $old['ingredients'][$ingredient['id']] ?? ($ingredientQuantities[$ingredient['id']] ?? 0); ?>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-md-6">
                                        <?= htmlspecialchars($ingredient['name']) ?>
                                        <small class="text-muted">(<?= number_format($ingredient['calories_per_100g'], 2) ?> ккал/100г)</small>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" 
                                               name="ingredients[<?= $ingredient['id'] ?>]" 
                                               class="form-control form-control-sm"
                                               placeholder="Количество (г)"
                                               value="<?= $quantity ?>"
                                               min="0"
                                               step="1">
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">грамм</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Укажите количество грамм для каждого ингредиента</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Пошаговый рецепт *</label>
                        <textarea name="instructions" class="form-control" rows="10" required><?= htmlspecialchars($old['instructions'] ?? $recipe['instructions']) ?></textarea>
                        <small class="text-muted">Опишите процесс приготовления подробно</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="/profile" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Опасная зона - удаление рецепта -->
        <div class="card mt-3 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Опасная зона</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Удаление рецепта необратимо. Все данные о рецепте будут стёрты.</p>
                <a href="/recipe/delete?id=<?= $recipe['id'] ?>" 
                   class="btn btn-outline-danger"
                   onclick="return confirm('Вы уверены, что хотите удалить рецепт &quot;<?= htmlspecialchars($recipe['title']) ?>&quot;?')">
                    <i class="fas fa-trash"></i> Удалить рецепт
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>