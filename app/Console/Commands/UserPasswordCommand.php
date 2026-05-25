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

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Changes a user's password.
 */
class UserPasswordCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Changes a user's password";

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'user:password {username : User whose password is to be changed}';

    /**
     * Executes the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $username = (string) $this->argument('username');

        $password = (string) $this->secret('Set password to');
        $confirm  = (string) $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match');
            return self::FAILURE;
        }

        $user = User::where('username', $username)->first();

        if ($user === null) {
            $this->error(sprintf('User "%s" not found', $username));
            return self::FAILURE;
        }

        $user->setPassword(Hash::make($password))->save();

        $this->info('User updated');

        return self::SUCCESS;
    }
}
