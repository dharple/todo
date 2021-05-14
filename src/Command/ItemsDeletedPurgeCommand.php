<?php

namespace App\Command;

use App\Entity\Item;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ItemsDeletedPurgeCommand extends Command
{
    /**
     * How long deleted items are retained, in days.
     *
     * @var integer
     */
    const RETAIN_DAYS = 30;

    /**
     * How long deleted items are retained if their completed stamp is NULL, in days.
     *
     * @var integer
     */
    const RETAIN_DAYS_CORRECTION = 120;

    /**
     * @var string
     */
    protected static $defaultName = 'items:deleted:purge';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Purges deleted items older than 30 days';

    /**
     * Holds the EntityManager to use.
     *
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * ItemsDeletedPurgeCommand constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }
    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $qb = $this->em
            ->getRepository(Item::class)
            ->createQueryBuilder('i');

        $items = $qb
            ->where('i.status = :status')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->lte('i.completed', ':stamp'),
                $qb->expr()->andX(
                    $qb->expr()->isNull('i.completed'),
                    $qb->expr()->lte('i.created', ':altStamp')
                )
            ))
            ->setParameter('status', 'Deleted')
            ->setParameter('altStamp', (new Carbon())->subDays(static::RETAIN_DAYS_CORRECTION)->format('Y-m-d'))
            ->setParameter('stamp', (new Carbon())->subDays(static::RETAIN_DAYS)->format('Y-m-d'))
            ->getQuery()
            ->getResult();

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
