<?php
$title = 'Мой профиль - Кулинарный портал';
ob_start();
?>

<div class="row">
    <!-- Левая колонка - информация о пользователе -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-circle"></i> Мои данные</h4>
            </div>
            <div class="card-body">
                <p><strong>👤 Имя:</strong> <?= htmlspecialchars($user['username'] ?? 'Не указано') ?></p>
                <p><strong>📧 Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>⭐ Роль:</strong> 
                    <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                        <?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?>
                    </span>
                </p>
                <p><strong>📅 Зарегистрирован:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="/profile/edit" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Редактировать профиль
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Правая колонка - рецепты пользователя -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0"><i class="fas fa-utensils"></i> Мои рецепты</h4>
    
    <!-- Кнопка добавления рецепта для всех авторизованных -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($user['role'] === 'admin'): ?>
            <a href="/admin/recipe/create" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> Добавить рецепт (админка)
            </a>
        <?php else: ?>
            <a href="/recipe/create" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> Добавить рецепт
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>
            
            <div class="card-body">
                <?php if (empty($myRecipes)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Вы ещё не добавили ни одного рецепта.</p>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="/admin/recipe/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Добавить рецепт
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($myRecipes as $recipe): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="/recipe?id=<?= $recipe['id'] ?>" class="text-decoration-none">
                                        <strong><?= htmlspecialchars($recipe['title']) ?></strong>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($recipe['created_at'])) ?>
                                        <i class="fas fa-heart text-danger ms-2"></i> <?= $recipe['favorites_count'] ?? 0 ?>
                                    </small>
                                </div>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <div>
                                        <a href="/admin/recipe/edit?id=<?= $recipe['id'] ?>" class="btn btn-sm btn-warning" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>