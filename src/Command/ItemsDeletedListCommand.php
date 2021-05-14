<?php

namespace App\Command;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ItemsDeletedListCommand extends Command
{
    /**
     * Command description.
     *
     * @var string
     */
    protected static string $defaultDescription = 'List deleted items';

    /**
     * Command name.
     *
     * @var string
     */
    protected static $defaultName = 'items:deleted:list';

    /**
     * Holds the EntityManager to use.
     *
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * ItemsDeletedListCommand constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders([
            'id',
            'task',
            'deleted at',
            'section',
            'user',
        ]);

        $items = $this->em
            ->getRepository(Item::class)
            ->findBy(['status' => 'Deleted'], ['completed' => 'DESC']);

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
