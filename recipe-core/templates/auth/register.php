<?php
$title = 'Регистрация - Кулинарный портал';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Регистрация</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php else: ?>
                    <form method="POST" action="/register">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Имя пользователя (опционально)</label>
                            <input type="text" name="username" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email адрес *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Пароль *</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <small class="text-muted">Минимум 6 символов</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Подтверждение пароля *</label>
                            <input type="password" name="password_confirm" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <a href="/login">Уже есть аккаунт? Войти</a>
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