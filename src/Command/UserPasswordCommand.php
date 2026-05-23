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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Changes a user's password.
 */
#[AsCommand(name: 'user:password', description: "Changes a user's password")]
class UserPasswordCommand extends Command
{
    /**
     * UserPasswordCommand constructor.
     *
     * @param EntityManagerInterface $em The entity manager.
     */
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'User whose password is to be changed');
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return int
     *
     * @throws \Exception
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

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        Guard::setPassword($user, $password);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('User updated');

        return Command::SUCCESS;
    }
}
