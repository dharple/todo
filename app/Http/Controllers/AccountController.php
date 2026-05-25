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

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles the user account settings page.
 */
class AccountController extends Controller
{
    /**
     * Handles the account settings form submission.
     *
     * @param Request $request The current HTTP request.
     *
     * @return RedirectResponse
     */
    public function accountPost(Request $request): RedirectResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $errors = [];

        try {
            $user->setFullname((string) $request->input('fullname', ''));
            $user->setTimezone((string) $request->input('timezone', ''));
            $user->save();
        } catch (\Exception $e) {
            $errors[] = sprintf('Failed to update user information: %s', $e->getMessage());
        }

        return redirect()->route('index')->with('controller_errors', $errors);
    }

    /**
     * Draws the password editor.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     */
    public function password(Request $request): View
    {
        $user = Auth::user();
        assert($user instanceof User);

        return view('password', [
            'errors' => session('controller_errors', []),
            'user'   => $user,
        ]);
    }

    /**
     * Handles the password edit submission.
     *
     * @param Request $request The current HTTP request.
     *
     * @return RedirectResponse
     */
    public function passwordPost(Request $request): RedirectResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $errors = [];

        try {
            $oldPassword = (string) $request->input('old_password', '');
            $newPassword = (string) $request->input('new_password', '');
            $confirm     = (string) $request->input('confirm', '');

            $ret = Guard::checkPassword($user, $oldPassword);
            if ($ret && $newPassword === $confirm) {
                Guard::setPassword($user, $newPassword);
                $user->save();
            } elseif (!$ret) {
                $errors[] = 'Incorrect password';
            } else {
                $errors[] = 'New passwords do not match';
            }
        } catch (\Exception $e) {
            $errors[] = sprintf('Failed to change password: %s', $e->getMessage());
        }

        if ($errors) {
            return redirect()->route('password')->with('controller_errors', $errors);
        }

        return redirect()->route('index');
    }
}
