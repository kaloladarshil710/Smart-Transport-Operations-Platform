<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;
use App\Support\Auth;

final class AuthController
{
    public function showLogin(): Response
    {
        Auth::startSession();
        if (isset($_SESSION['user'])) {
            return Response::redirect('/dashboard');
        }

        return Response::html(View::render('auth.login', ['title' => 'Login', 'csrf' => Auth::csrfToken()]));
    }

    public function login(): Response
    {
        Auth::startSession();
        if (!Auth::validateCsrf()) {
            return Response::html(View::render('auth.login', ['title' => 'Login', 'error' => 'Invalid request token.', 'csrf' => Auth::csrfToken()]));
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = Auth::loginUser($email, $password);

        if ($user === null) {
            return Response::html(View::render('auth.login', ['title' => 'Login', 'error' => 'Invalid credentials.', 'csrf' => Auth::csrfToken()]));
        }

        Auth::login($user);

        return Response::redirect('/dashboard');
    }

    public function logout(): Response
    {
        Auth::logout();

        return Response::redirect('/login');
    }
}
