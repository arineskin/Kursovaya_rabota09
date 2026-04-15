<?php
// app/Controllers/AuthController.php
namespace Controllers;

use Models\User;

class AuthController extends BaseController
{
    private User $userModel;  // Это должно работать в PHP 8.3
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->generateCsrfToken();
    }
    
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $error = "Заполните все поля!";
                $this->render('auth/login', ['error' => $error]);
                return;
            }
            
            $user = $this->userModel->findByEmail($email);
            
            if ($user && isset($user['is_blocked']) && $user['is_blocked'] == 1) {
                $error = "Ваш аккаунт заблокирован. Обратитесь к администратору.";
                $this->render('auth/login', ['error' => $error]);
                return;
            }
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username'] ?? $user['email'];
                
                if ($user['role'] === 'admin') {
                    $this->redirect('/admin');
                } else {
                    $this->redirect('/');
                }
            } else {
                $error = "Неверный email или пароль";
                $this->render('auth/login', ['error' => $error]);
            }
        } else {
            $this->render('auth/login');
        }
    }
    
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF-токен
            $this->requireCsrfToken();
            
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $username = trim($_POST['username'] ?? '');
            
            if (empty($email) || empty($password)) {
                $error = "Заполните все поля!";
                $this->render('auth/register', ['error' => $error]);
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Некорректный формат Email!";
                $this->render('auth/register', ['error' => $error]);
                return;
            }
            
            if ($password !== $passwordConfirm) {
                $error = "Пароли не совпадают!";
                $this->render('auth/register', ['error' => $error]);
                return;
            }
            
            if (strlen($password) < 6) {
                $error = "Пароль должен содержать минимум 6 символов!";
                $this->render('auth/register', ['error' => $error]);
                return;
            }
            
            $success = $this->userModel->create($email, $password, $username);
            
            if ($success) {
                $successMsg = "Регистрация успешна! Теперь вы можете войти.";
                $this->render('auth/register', ['success' => $successMsg]);
            } else {
                $error = "Такой email уже зарегистрирован.";
                $this->render('auth/register', ['error' => $error]);
            }
        } else {
            $this->render('auth/register');
        }
    }
    
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/');
    }
}