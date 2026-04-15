<?php
// app/Core/Database.php
namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private static $config;

    public static function init($config)
    {
        self::$config = $config;
    }

    public static function getConnection()
    {
        if (self::$instance === null) {
            try {
                // Проверяем, что конфиг загружен
                if (empty(self::$config)) {
                    throw new \Exception("Конфигурация базы данных не загружена!");
                }
                
                // Формируем DSN
                $dsn = "mysql:host=" . self::$config['host'] . 
                       ";dbname=" . self::$config['dbname'] . 
                       ";charset=utf8mb4";
                
                // Добавляем порт, если указан
                if (isset(self::$config['port'])) {
                    $dsn .= ";port=" . self::$config['port'];
                }
                
                // Создаем подключение
                self::$instance = new PDO(
                    $dsn, 
                    self::$config['user'], 
                    self::$config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
            } catch (PDOException $e) {
                die("Ошибка подключения к базе данных: " . $e->getMessage());
            } catch (\Exception $e) {
                die("Ошибка: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}