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
 * Command to add a user.
 */
class UserAddCommand extends Command
{
    /**
     * Default timezone to use for users.
     *
     * @var string
     */
    protected const DEFAULT_TIMEZONE = 'America/New_York';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'user:add {username : User to add} {fullname? : Full name of the user} {timezone? : Time zone for the user}';

    /**
     * Executes the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $username = (string) $this->argument('username');
        $fullname = (string) ($this->argument('fullname') ?? $username);
        $timezone = (string) ($this->argument('timezone') ?? self::DEFAULT_TIMEZONE);

        $password = (string) $this->secret('Set password to');
        $confirm  = (string) $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match');
            return self::FAILURE;
        }

        $user = (new User())
            ->setUsername($username)
            ->setFullname($fullname)
            ->setTimezone($timezone)
            ->setPassword(Hash::make($password))
            ->save();

        $this->info('User created');

        return self::SUCCESS;
    }
}
