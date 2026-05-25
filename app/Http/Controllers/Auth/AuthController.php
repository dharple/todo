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
     * Renders the login form.
     *
     * @param Request $request The current HTTP request.
     *
     * @return RedirectResponse|View
     */
    public function login(Request $request): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('index');
        }

        return view('auth.login', [
            'errors'        => session('controller_errors', []),
            'last_username' => session('_old_input.username', ''),
        ]);
    }

    /**
     * Handles the login form submission.
     *
     * @param Request $request The current HTTP request.
     *
     * @return RedirectResponse
     */
    public function loginPost(Request $request): RedirectResponse
    {
        $credentials = [
            'username' => $request->input('username', ''),
            'password' => $request->input('password', ''),
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('index');
        }

        return redirect()->route('login')
            ->with('controller_errors', ['Invalid credentials.'])
            ->with('_old_input.username', $credentials['username']);
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
        return redirect()->route('login');
    }
}
