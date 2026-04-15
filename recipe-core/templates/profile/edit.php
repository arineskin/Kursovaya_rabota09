<?php
$title = 'Редактирование профиля - Кулинарный портал';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning">
                <h4 class="mb-0"><i class="fas fa-edit"></i> Редактирование профиля</h4>
            </div>
            <div class="card-body">
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <form method="POST" action="/profile/edit">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">👤 Имя пользователя</label>
                        <input type="text" name="username" class="form-control" 
                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" 
                               required>
                        <small class="text-muted">Как вас будут видеть другие пользователи</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">📧 Email адрес</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email']) ?>" 
                               required>
                        <small class="text-muted">Используется для входа в систему</small>
                    </div>
                    
                    <hr>
                    
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
        
        <!-- Подсказка -->
        <div class="card mt-3 bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle"></i> Информация</h6>
                <p class="card-text small text-muted mb-0">
                    Здесь вы можете изменить свои персональные данные. 
                    Email должен быть уникальным и действующим.
                    Для смены пароля используйте соответствующую кнопку в профиле.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>