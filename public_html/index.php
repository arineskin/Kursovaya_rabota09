<?php
// public_html/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// ПРАВИЛЬНАЯ АВТОЗАГРУЗКА КЛАССОВ
spl_autoload_register(function ($class) {
    // Убираем обратный слеш в начале
    $class = ltrim($class, '\\');
    
    // Путь к папке с классами (абсолютный путь)
    $base_dir = __DIR__ . '/../recipe-core/app/';
    
    // Преобразуем пространство имен в путь к файлу
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    
    // Проверяем, существует ли файл
    if (file_exists($file)) {
        require $file;
        return true;
    }
    
    // Если файл не найден, пробуем искать в подпапках
    // Для отладки - выводим искомый путь
    // echo "Ищем: " . $file . "<br>";
    
    return false;
});

// Подключаем конфигурацию
$configFile = __DIR__ . '/../recipe-core/config/config.php';

if (!file_exists($configFile)) {
    die("Файл конфигурации не найден: " . $configFile);
}

$config = require $configFile;

// Инициализируем базу данных
use Core\Database;
Database::init($config);

// Запускаем роутер
use Core\Router;
$router = new Router();
$router->dispatch($_SERVER['REQUEST_URI']);