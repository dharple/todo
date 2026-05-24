<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles login and logout.
 */
class AuthController extends Controller
{
    /**
     * Renders the login form, or handles the login POST.
     *
     * @param Request $request The current HTTP request.
     *
     * @return View|RedirectResponse
     */
    public function login(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('app_index');
        }

        $errors       = [];
        $lastUsername = session('_old_input.username', '');

        if ($request->isMethod('POST')) {
            $credentials = [
                'username' => $request->input('username', ''),
                'password' => $request->input('password', ''),
            ];

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return redirect()->route('app_index');
            }

            $errors[]     = 'Invalid credentials.';
            $lastUsername = $credentials['username'];
        }

        return view('auth.login', [
            'errors'        => $errors,
            'last_username' => $lastUsername,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @param Request $request The current HTTP request.
     *
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('app_login');
    }
}
