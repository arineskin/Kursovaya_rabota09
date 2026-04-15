<?php
$title = 'Редактировать пользователя - Админ-панель';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user-edit"></i> Редактирование пользователя</h1>
    <a href="/admin/users" class="btn btn-secondary">← Назад к списку</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Редактирование: <?= htmlspecialchars($user['email']) ?></h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'] ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">👤 Имя пользователя</label>
                        <input type="text" name="username" class="form-control" 
                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                        <small class="text-muted">Отображается на сайте</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">📧 Email адрес</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                        <small class="text-muted">Используется для входа в систему</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">👑 Роль пользователя</label>
                                <select name="role" class="form-select">
                                    <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>
                                        Пользователь
                                    </option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                                        Администратор
                                    </option>
                                </select>
                                <small class="text-muted">Администраторы имеют полный доступ к панели управления</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">🔒 Статус блокировки</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_blocked" 
                                           id="is_blocked" value="1" <?= $user['is_blocked'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_blocked">
                                        <?= $user['is_blocked'] ? 'Пользователь заблокирован' : 'Пользователь активен' ?>
                                    </label>
                                </div>
                                <small class="text-muted">Заблокированные пользователи не могут войти в систему</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">🔑 Новый пароль (оставьте пустым, чтобы не менять)</label>
                        <input type="password" name="new_password" class="form-control" minlength="6">
                        <small class="text-muted">Минимум 6 символов. Заполните только если хотите сменить пароль</small>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="/admin/users" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Информационная карточка -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Информация о пользователе</h6>
            </div>
            <div class="card-body">
                <p><strong>🆔 ID:</strong> <?= $user['id'] ?></p>
                <p><strong>📅 Зарегистрирован:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
                <p><strong>📝 Количество рецептов:</strong> <?= $recipesCount ?></p>
                <p><strong>⭐ Статус:</strong> 
                    <?php if ($user['is_blocked']): ?>
                        <span class="badge bg-danger">Заблокирован</span>
                    <?php else: ?>
                        <span class="badge bg-success">Активен</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Предупреждение -->
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Опасная зона</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Удаление пользователя приведёт к безвозвратному удалению:</p>
                <ul class="small text-muted">
                    <li>Всех рецептов пользователя</li>
                    <li>Всех изображений рецептов</li>
                    <li>Всех записей из избранного</li>
                </ul>
                <a href="/admin/user/delete?id=<?= $user['id'] ?>" 
                   class="btn btn-outline-danger w-100"
                   onclick="return confirm('Вы уверены, что хотите УДАЛИТЬ пользователя &quot;<?= htmlspecialchars($user['email']) ?>&quot;? Это действие необратимо!')">
                    <i class="fas fa-trash"></i> Удалить пользователя
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>