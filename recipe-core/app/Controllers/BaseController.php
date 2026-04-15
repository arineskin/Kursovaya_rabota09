<?php
// recipe-core/app/Controllers/BaseController.php
namespace Controllers;

abstract class BaseController
{
    /**
     * Рендерит шаблон с передачей данных
     */
    protected function render(string $view, array $data = []): void
    {
        // Извлекаем данные в переменные
        extract($data);
        
        // ИСПРАВЛЕННЫЙ ПУТЬ к файлу шаблона
        // __DIR__ - это папка Controllers
        // Нам нужно подняться на 2 уровня вверх (Controllers -> app -> recipe-core)
        // и затем зайти в templates
        $viewFile = dirname(__DIR__, 2) . '/templates/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            die("Шаблон не найден: " . $viewFile);
        }
        
        // Загружаем шаблон
        require $viewFile;
    }
    
    /**
     * Перенаправляет на указанный URL
     */
    protected function redirect(string $url): void
    {
        header("Location: " . $url);
        exit;
    }
    
    /**
     * Проверяет, авторизован ли пользователь
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Проверяет, является ли пользователь администратором
     */
    protected function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Требует авторизации, иначе перенаправляет на логин
     */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Для доступа к этой странице необходимо войти';
            $this->redirect('/login');
        }
    }
    
    /**
     * Требует прав администратора, иначе перенаправляет на главную
     */
    protected function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Доступ запрещен. Требуются права администратора';
            $this->redirect('/');
        }
    }
    
    /**
     * Генерирует CSRF-токен и сохраняет его в сессии
     */
    protected function generateCsrfToken(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
    
    /**
     * Возвращает HTML-поле для вставки CSRF-токена в форму
     */
    protected function csrfField(): string
    {
        $this->generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
    
    /**
     * Проверяет CSRF-токен из POST-запроса
     */
    protected function verifyCsrfToken(): bool
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
    
    /**
     * Проверяет CSRF и завершает запрос при ошибке
     */
    protected function requireCsrfToken(): void
    {
        if (!$this->verifyCsrfToken()) {
            die('Ошибка CSRF: недействительный токен безопасности');
        }
    }
}