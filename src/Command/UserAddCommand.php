<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Auth\Guard;
use App\Entity\User;
use App\Helper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to add a user.
 */
class UserAddCommand extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected static $defaultName = 'user:add';

    /**
     * Default timezone to use for users.
     *
     * @var string
     */
    protected const DEFAULT_TIMEZONE = 'America/New_York';

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Add a new user')
            ->addArgument('username', InputArgument::REQUIRED, 'User to add')
            ->addArgument('fullname', InputArgument::OPTIONAL, 'Full name of the user to add')
            ->addArgument('timezone', InputArgument::OPTIONAL, 'Time zone for the user')
        ;
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return int
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $fullname = $input->getArgument('fullname') ?? $username;
        $timezone = $input->getArgument('timezone') ?? self::DEFAULT_TIMEZONE;
        $password = $io->askHidden('Set password to');
        $confirm = $io->askHidden('Confirm password');
        if ($password !== $confirm) {
            $io->error('Passwords do not match');
            return Command::FAILURE;
        }

        $user = (new User())
            ->setUsername($username)
            ->setFullname($fullname)
            ->setTimezone($timezone);
        Guard::setPassword($user, $password);

        $em = Helper::getEntityManager();
        $em->persist($user);
        $em->flush();

        $io->success('User created');

        return Command::SUCCESS;
    }
}
