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

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Console command to add a user.
 */
class UserAddCommand extends Command
{
    /**
     * Command name
     *
     * @var string
     */
    protected static $defaultName = 'user:add';

    /**
     * Entity manager
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Password encoder
     *
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * Constructor
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder
    ) {
        parent::__construct();
        $this->em = $em;
        $this->encoder = $encoder;
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password');
    }

    /**
     * Runs the command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = new User();
        $user->setEmail($input->getArgument('email'));
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->encoder->encodePassword($user, $input->getArgument('password')));

        $this->em->persist($user);
        $this->em->flush();

        $io->success('created user: ' . $user->getEmail());

        return 0;
    }
}
