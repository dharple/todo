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
 * Changes a user's password.
 */
class UserPasswordCommand extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected static $defaultName = 'user:password';

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Changes a user\'s password')
            ->addArgument('username', InputArgument::REQUIRED, 'User whose password is to be changed')
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
        $password = $io->askHidden('Set password to');
        $confirm = $io->askHidden('Confirm password');
        if ($password !== $confirm) {
            $io->error('Passwords do not match');
            return Command::FAILURE;
        }

        $em = Helper::getEntityManager();

        $user = $em->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        Guard::setPassword($user, $password);

        $em->persist($user);
        $em->flush();

        $io->success('User updated');

        return Command::SUCCESS;
    }
}
