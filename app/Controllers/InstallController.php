<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\User;

class InstallController extends Controller
{
    public function form(): void
    {
        if (User::count() > 0) {
            flash('error', t('already_installed'));
            redirect('/login');
        }
        $this->render('install/form', ['standalone' => true]);
    }

    public function save(): void
    {
        Csrf::verify();
        if (User::count() > 0) redirect('/login');

        $nom = trim((string)($_POST['nom'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            flash('error', t('required_fields'));
            redirect('/install');
        }

        User::create($nom, $email, $password, 'admin');
        flash('success', t('install_ok'));
        redirect('/login');
    }
}
