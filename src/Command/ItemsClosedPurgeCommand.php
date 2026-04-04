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

use App\Entity\Item;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to purge closed items in a given timeframe.
 */
class ItemsClosedPurgeCommand extends Command
{
    /**
     * Command description.
     *
     * @var string
     */
    protected static $defaultDescription = 'Purges closed items in a given timeframe';

    /**
     * Command name.
     *
     * @var string
     */
    protected static $defaultName = 'items:closed:purge';

    /**
     * ItemsClosedPurgeCommand constructor.
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
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('year', null, InputOption::VALUE_REQUIRED, 'Purge items closed in this year (YYYY); cannot be combined with --start-date or --end-date')
            ->addOption('start-date', null, InputOption::VALUE_REQUIRED, 'Purge items closed on or after this date (YYYY-MM-DD)')
            ->addOption('end-date', null, InputOption::VALUE_REQUIRED, 'Purge items closed on or before this date (YYYY-MM-DD)');
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $year = $input->getOption('year');
        $startDate = $input->getOption('start-date');
        $endDate = $input->getOption('end-date');

        if ($year !== null && ($startDate !== null || $endDate !== null)) {
            $io->error('--year cannot be combined with --start-date or --end-date');
            return Command::FAILURE;
        }

        if ($year !== null) {
            $startDate = $year . '-01-01';
            $endDate = $year . '-12-31';
        }

        $qb = $this->em
            ->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->where('i.status = :status')
            ->setParameter('status', 'Closed');

        if ($startDate !== null) {
            $start = DateTime::createFromFormat('Y-m-d', $startDate);
            if ($start === false) {
                $io->error('Invalid --start-date format; expected YYYY-MM-DD');
                return Command::FAILURE;
            }
            $qb->andWhere('i.completed >= :startDate')
                ->setParameter('startDate', $start->format('Y-m-d 00:00:00'));
        }

        if ($endDate !== null) {
            $end = DateTime::createFromFormat('Y-m-d', $endDate);
            if ($end === false) {
                $io->error('Invalid --end-date format; expected YYYY-MM-DD');
                return Command::FAILURE;
            }
            $qb->andWhere('i.completed <= :endDate')
                ->setParameter('endDate', $end->format('Y-m-d 23:59:59'));
        }

        $items = $qb->getQuery()->getResult();

        foreach ($items as $item) {
            if (!($item instanceof Item)) {
                continue;
            }

            $io->writeln(sprintf('item purged: %d, %s, %s', $item->getId(), $item->getUser()->getUsername(), $item->getTask()));

            $this->em->remove($item);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
