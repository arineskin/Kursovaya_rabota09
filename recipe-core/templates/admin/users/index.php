<?php
$title = 'Управление пользователями - Админ-панель';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users"></i> Управление пользователями</h1>
</div>

<!-- Форма поиска -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" 
                       placeholder="Поиск по email или имени пользователя..." 
                       value="<?= htmlspecialchars($searchQuery ?? '') ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Найти
                </button>
            </div>
            <?php if (!empty($searchQuery)): ?>
                <div class="col-12 text-end">
                    <a href="/admin/users" class="text-muted">Сбросить поиск</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info text-center">
                <p>Пользователей не найдено.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Рецептов</th>
                            <th>В избранном</th>
                            <th>Регистрация</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?= $user['is_blocked'] ? 'table-secondary text-muted' : '' ?>">
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($user['username'] ?? 'Без имени') ?></strong>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                        <?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_blocked']): ?>
                                        <span class="badge bg-danger">Заблокирован</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Активен</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $user['recipes_count'] ?? 0 ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">
                                        <?= $user['favorites_count'] ?? 0 ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                <td class="action-buttons">
                                    <a href="/admin/user/edit?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if (!$user['is_blocked']): ?>
                                        <a href="/admin/user/toggle-block?id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-secondary" 
                                           title="Заблокировать пользователя"
                                           onclick="return confirm('Вы уверены, что хотите заблокировать пользователя &quot;<?= htmlspecialchars($user['email']) ?>&quot;? После блокировки пользователь не сможет войти в систему.')">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="/admin/user/toggle-block?id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Разблокировать пользователя"
                                           onclick="return confirm('Вы уверены, что хотите разблокировать пользователя &quot;<?= htmlspecialchars($user['email']) ?>&quot;?')">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="/admin/user/delete?id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Удалить пользователя (безвозвратно)"
                                           onclick="return confirm('Вы уверены, что хотите УДАЛИТЬ пользователя &quot;<?= htmlspecialchars($user['email']) ?>&quot;? Все его рецепты и избранное будут также удалены! Это действие необратимо.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>">
                                    &laquo; Назад
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>">
                                    Вперед &raquo;
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="text-center text-muted mt-3">
                <small>Показано <?= count($users) ?> из <?= $totalUsers ?> пользователей</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<a href="/admin" class="btn btn-secondary mt-3">← Назад в админ-панель</a>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>