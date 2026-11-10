<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Controller;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) redirect('/');
        $this->render('auth/login', ['standalone' => true]);
    }

    public function doLogin(): void
    {
        Csrf::verify();
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if ($email === '' || $password === '' || !Auth::attempt($email, $password)) {
            flash('error', t('login_failed'));
            redirect('/login');
        }
        redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }

    public function lang(string $code): void
    {
        if (in_array($code, ['fr', 'ar'], true)) $_SESSION['lang'] = $code;
        $back = $_SERVER['HTTP_REFERER'] ?? url('/');
        header('Location: ' . $back);
        exit;
    }
}
