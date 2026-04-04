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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to list closed items in a given timeframe.
 */
class ItemsClosedListCommand extends Command
{
    /**
     * Command description.
     *
     * @var string
     */
    protected static $defaultDescription = 'List closed items';

    /**
     * Command name.
     *
     * @var string
     */
    protected static $defaultName = 'items:closed:list';

    /**
     * ItemsClosedListCommand constructor.
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
            ->addOption('year', null, InputOption::VALUE_REQUIRED, 'Show items closed in this year (YYYY); cannot be combined with --start-date or --end-date')
            ->addOption('start-date', null, InputOption::VALUE_REQUIRED, 'Show items closed on or after this date (YYYY-MM-DD)')
            ->addOption('end-date', null, InputOption::VALUE_REQUIRED, 'Show items closed on or before this date (YYYY-MM-DD)');
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
        $year = $input->getOption('year');
        $startDate = $input->getOption('start-date');
        $endDate = $input->getOption('end-date');

        if ($year !== null && ($startDate !== null || $endDate !== null)) {
            $output->writeln('<error>--year cannot be combined with --start-date or --end-date</error>');
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
            ->setParameter('status', 'Closed')
            ->orderBy('i.completed', 'DESC');

        if ($startDate !== null) {
            $start = DateTime::createFromFormat('Y-m-d', $startDate);
            if ($start === false) {
                $output->writeln('<error>Invalid --start-date format; expected YYYY-MM-DD</error>');
                return Command::FAILURE;
            }
            $qb->andWhere('i.completed >= :startDate')
                ->setParameter('startDate', $start->format('Y-m-d 00:00:00'));
        }

        if ($endDate !== null) {
            $end = DateTime::createFromFormat('Y-m-d', $endDate);
            if ($end === false) {
                $output->writeln('<error>Invalid --end-date format; expected YYYY-MM-DD</error>');
                return Command::FAILURE;
            }
            $qb->andWhere('i.completed <= :endDate')
                ->setParameter('endDate', $end->format('Y-m-d 23:59:59'));
        }

        $items = $qb->getQuery()->getResult();

        $table = new Table($output);
        $table->setHeaders([
            'id',
            'task',
            'closed at',
            'section',
            'user',
        ]);

        foreach ($items as $item) {
            if (!($item instanceof Item)) {
                continue;
            }

            $table->addRow([
                $item->getId(),
                $item->getTask(),
                $item->getCompleted() ? $item->getCompleted()->format('Y-m-d') : 'unknown',
                $item->getSection()->getName(),
                $item->getUser()->getUsername(),
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
