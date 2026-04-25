<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Analytics\ItemStats;
use App\Entity\Item;
use App\Entity\Section;
use App\Entity\User;
use App\Renderer\DisplayConfig;
use App\Renderer\DisplayHelper;
use App\Renderer\ListDisplay;
use App\Repository\ItemRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * Handles the main to-do list, item editing, bulk adding, and prioritization.
 */
class ItemController extends AbstractController
{
    /**
     * Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $log;

    /**
     * Twig template environment.
     *
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Constructs a new ItemController.
     *
     * @param EntityManagerInterface $em   Doctrine entity manager.
     * @param LoggerInterface        $log  Logger.
     * @param Environment            $twig Twig template environment.
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $log, Environment $twig)
    {
        $this->em   = $em;
        $this->log  = $log;
        $this->twig = $twig;
    }

    /**
     * Displays the main to-do list and handles bulk item actions.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     */
    #[Route('/', name: 'app_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        assert($user instanceof User);

        $errors = [];

        if ($request->isMethod('POST')) {
            $markDoneButton   = $request->request->get('markDoneButton');
            $editButton       = $request->request->get('editButton');
            $prioritizeButton = $request->request->get('prioritizeButton');
            $duplicateButton  = $request->request->get('duplicateButton');
            $itemIds          = (array) $request->request->all('itemIds');

            if (!empty($markDoneButton)) {
                try {
                    foreach ($itemIds as $itemId) {
                        $item = $this->em->find(Item::class, $itemId);

                        if ($item === null) {
                            $errors[] = sprintf('Unable to load item #%s', $itemId);
                            continue;
                        }

                        $item
                            ->setStatus(($markDoneButton !== 'Delete') ? 'Closed' : 'Deleted')
                            ->setCompleted(new \DateTime(($markDoneButton === 'Mark Done Yesterday') ? 'yesterday 23:45' : 'now'));
                        $this->em->persist($item);
                    }
                    $this->em->flush();
                } catch (\Exception $e) {
                    $errors[] = sprintf('Failed to mark items done: %s', $e->getMessage());
                }
            } elseif (!empty($editButton)) {
                if (!empty($itemIds)) {
                    return $this->redirectToRoute('app_item_edit', [
                        'op'  => 'edit',
                        'ids' => $itemIds,
                    ]);
                }
                $errors[] = 'Please select one or more items to edit';
            } elseif (!empty($prioritizeButton)) {
                $params = !empty($itemIds) ? ['ids' => $itemIds] : [];
                return $this->redirectToRoute('app_item_prioritize', $params);
            } elseif (!empty($duplicateButton)) {
                try {
                    foreach ($itemIds as $itemId) {
                        $item = $this->em->find(Item::class, $itemId);

                        if ($item === null) {
                            $errors[] = sprintf('Unable to load item #%s', $itemId);
                            continue;
                        }

                        $newItem = clone $item;
                        $newItem
                            ->setCompleted(null)
                            ->setCreated(new \DateTime())
                            ->setStatus('Open');

                        $this->em->persist($newItem);
                    }
                    $this->em->flush();
                } catch (\Exception $e) {
                    $errors[] = sprintf('Failed to duplicate items: %s', $e->getMessage());
                }
            }
        }

        $config = $this->loadDisplayConfig($request);

        try {
            $config->processRequest();
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        $this->saveDisplayConfig($request, $config);

        $listDisplay = new ListDisplay($config, $this->em, $this->log, $this->twig, $user);

        $itemStats = new ItemStats($this->em, $user);

        $listDisplay->setFooter($this->twig->render('partials/index/summary.php.twig', [
            'itemStats' => $itemStats,
        ]));

        $listOutput     = $listDisplay->getOutput();
        $itemCount      = $listDisplay->getOutputCount()->getTotalCount();
        $shownOpenCount = $listDisplay->getOutputCount()->getOpenCount();
        $itemRepository = $this->em->getRepository(Item::class);
        assert($itemRepository instanceof ItemRepository);
        $totalOpenCount = $itemRepository->getOpenItemCount($user);

        // If nothing has been closed and nothing has been hidden, omit the footer on printed view.
        $hideFooter = (($shownOpenCount === $totalOpenCount) && ($itemStats->doneTotal() === 0));
        if ($hideFooter) {
            $listDisplay->setFooter('');
            $listOutput = $listDisplay->getOutput();
        }

        $sections     = $user->getSections()->matching(
            new Criteria(
                new Comparison('status', '=', 'Active')
            )
        );
        $sectionCount = count($sections);

        return $this->render('index.html.twig', [
            'chartData'             => $itemStats->getWeeklySummary(4),
            'config'                => $config,
            'countOpen'             => $totalOpenCount,
            'countShown'            => $shownOpenCount,
            'errors'                => $errors,
            'filterAgingValues'     => DisplayHelper::getFilterAgingValues(),
            'filterClosedValues'    => DisplayHelper::getFilterClosedValues(),
            'filterDeletedValues'   => DisplayHelper::getFilterDeletedValues(),
            'filterFreshnessValues' => DisplayHelper::getFilterFreshnessValues(),
            'filterPriorityValues'  => DisplayHelper::getFilterPriorityValues(),
            'hasItems'              => ($itemCount > 0),
            'hasSections'           => ($sectionCount > 0),
            'itemStats'             => $itemStats,
            'list'                  => $listOutput,
            'showAdvanced'          => ($config->getFilterClosed() !== 'none' || $config->getFilterDeleted() !== 'none'),
            'showPriorityValues'    => DisplayHelper::getShowPriorityValues(),
            'user'                  => $user,
        ]);
    }

    /**
     * Displays and processes the bulk item add form.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     */
    #[Route('/items/bulk-add', name: 'app_item_bulk_add', methods: ['GET', 'POST'])]
    public function itemBulkAdd(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        assert($user instanceof User);

        $priorityLevels = DisplayHelper::getPriorityLevels();
        $errors         = [];

        if ($request->isMethod('POST')) {
            try {
                $tasks = preg_split('/[\r\n]/', (string) $request->request->get('tasks', ''));
                foreach ($tasks as $task) {
                    $task = trim((string) $task);
                    if ($task === '') {
                        continue;
                    }

                    $item = (new Item())
                        ->setCreated(new \DateTime())
                        ->setPriority((int) $request->request->get('priority', $priorityLevels['normal']))
                        ->setSection($this->em->getReference(Section::class, (int) $request->request->get('section', 0)))
                        ->setStatus('Open')
                        ->setTask($task)
                        ->setUser($user);

                    $this->em->persist($item);
                }

                $this->em->flush();
            } catch (\Exception $e) {
                $errors[] = sprintf('Failed to add items: %s', $e->getMessage());
            }

            if (empty($errors) && $request->request->get('submitButton') === 'Do It') {
                return $this->redirectToRoute('app_index');
            }
        }

        $config   = $this->loadDisplayConfig($request);
        $selected = DisplayHelper::getDefaultSectionId($this->em, $user, $config);

        $sections = $this->em->getRepository(Section::class)
            ->findBy(['user' => $user], ['name' => 'ASC']);

        return $this->render('item_bulk_add.html.twig', [
            'errors'          => $errors,
            'priorityLevels'  => $priorityLevels,
            'sections'        => $sections,
            'selectedPriority' => $priorityLevels['normal'],
            'selectedSection' => $selected,
        ]);
    }

    /**
     * Displays and processes the item editor (create or edit one or more items).
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     *
     * @throws \Exception If an item cannot be found in the POST data.
     */
    #[Route('/items/edit', name: 'app_item_edit', methods: ['GET', 'POST'])]
    public function itemEdit(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        assert($user instanceof User);

        $priorityLevels = DisplayHelper::getPriorityLevels();
        $errors         = [];

        if ($request->isMethod('POST')) {
            $taskData = $request->request->all('task');
            if (!is_array($taskData)) {
                $taskData = [];
            }

            try {
                $itemIds = array_keys($taskData);
                $items   = [];

                if (count($itemIds) === 1 && $itemIds[0] === 'new') {
                    $items[] = (new Item())
                        ->setCreated(new \DateTime())
                        ->setUser($user)
                        ->setStatus('Open');
                } else {
                    $items = $this->em->getRepository(Item::class)
                        ->findBy([
                            'id'   => $itemIds,
                            'user' => $user,
                        ]);
                }

                $statusData    = $request->request->all('status');
                $completedData = $request->request->all('completed');
                $priorityData  = $request->request->all('priority');
                $sectionData   = $request->request->all('section');

                foreach ($items as $item) {
                    $itemId = $item->getId() ?? 'new';

                    if (!array_key_exists($itemId, $taskData)) {
                        throw new \Exception(sprintf('Could not find item #%s', $itemId));
                    }

                    if ($itemId !== 'new') {
                        $status = (string) ($statusData[$itemId] ?? 'Open');
                        $item->setStatus($status);
                        switch ($status) {
                            case 'Open':
                                $item->setCompleted(null);
                                break;

                            case 'Closed':
                            case 'Deleted':
                                $item->setCompleted(new \DateTime((string) ($completedData[$itemId] ?? 'now')));
                                break;
                        }
                    }

                    $item
                        ->setPriority((int) ($priorityData[$itemId] ?? $priorityLevels['normal']))
                        ->setSection($this->em->getReference(Section::class, (int) ($sectionData[$itemId] ?? 0)))
                        ->setTask((string) $taskData[$itemId]);

                    $this->em->persist($item);
                }

                $this->em->flush();
            } catch (\Exception $e) {
                $errors[] = sprintf('Failed to edit items: %s', $e->getMessage());
            }

            if (empty($errors) && $request->request->get('submitButton') === 'Do It') {
                return $this->redirectToRoute('app_index');
            }
        }

        $sections        = $this->em->getRepository(Section::class)
            ->findBy(['user' => $user], ['name' => 'ASC']);
        $items           = [];
        $op              = $request->query->get('op', 'add');
        $sectionOverride = null;

        if ($op === 'edit') {
            $ids   = $request->query->all('ids');
            $items = $this->em->getRepository(Item::class)
                ->findBy([
                    'id'   => $ids,
                    'user' => $user,
                ]);
        } elseif ($op === 'add') {
            $items = [
                (new Item())
                    ->setPriority($priorityLevels['normal'])
                    ->setStatus('Open'),
            ];
            $config          = $this->loadDisplayConfig($request);
            $sectionOverride = DisplayHelper::getDefaultSectionId($this->em, $user, $config);
        }

        return $this->render('item_edit.html.twig', [
            'errors'          => $errors,
            'items'           => $items,
            'op'              => $op,
            'priorityLevels'  => $priorityLevels,
            'sections'        => $sections,
            'sectionOverride' => $sectionOverride,
            'statuses'        => ['Open', 'Closed', 'Deleted'],
        ]);
    }

    /**
     * Displays and processes the item prioritization form.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     */
    #[Route('/items/prioritize', name: 'app_item_prioritize', methods: ['GET', 'POST'])]
    public function itemPrioritize(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        assert($user instanceof User);

        $priorityLevels = DisplayHelper::getPriorityLevels();
        $errors         = [];

        if ($request->isMethod('POST')) {
            if ($request->request->get('submitButton') === 'Update') {
                try {
                    foreach ($request->request->all('itemPriority') as $itemId => $priority) {
                        $item = $this->em->find(Item::class, $itemId);

                        if ($item === null) {
                            $errors[] = sprintf('Unable to load item #%s', $itemId);
                            continue;
                        }

                        $priority = (int) $priority;
                        if ($priority < $priorityLevels['high']) {
                            $priority = $priorityLevels['high'];
                        } elseif ($priority > $priorityLevels['low']) {
                            $priority = $priorityLevels['low'];
                        }

                        $item->setPriority($priority);
                        $this->em->persist($item);
                    }
                    $this->em->flush();
                } catch (\Exception $e) {
                    $errors[] = sprintf('Failed to change priorities: %s', $e->getMessage());
                }
            }

            if (empty($errors)) {
                return $this->redirectToRoute('app_index');
            }
        }

        // Clone the config so the user's saved config is not modified.
        $config = clone $this->loadDisplayConfig($request);

        try {
            $config->setFilterClosed('none');
        } catch (\Exception $e) {
            $errors[] = 'Could not disable closed filter for this view.';
        }

        try {
            $config->setFilterDeleted('none');
        } catch (\Exception $e) {
            $errors[] = 'Could not disable deleted filter for this view.';
        }

        $config->setShowPriorityEditor(true);

        $ids = $request->query->all('ids');
        if (is_array($ids) && count($ids) > 0) {
            $config->setFilterIds($ids);
        }

        $listDisplay = new ListDisplay($config, $this->em, $this->log, $this->twig, $user);
        $listOutput  = $listDisplay->getOutput();
        $itemCount   = $listDisplay->getOutputCount()->getOpenCount();

        return $this->render('item_prioritize.html.twig', [
            'errors'   => $errors,
            'hasItems' => ($itemCount > 0),
            'list'     => $listOutput,
        ]);
    }

    /**
     * Loads the display config from the session.
     *
     * @param Request $request The current HTTP request.
     *
     * @return DisplayConfig
     */
    private function loadDisplayConfig(Request $request): DisplayConfig
    {
        if (!$request->query->has('reset_display_settings')) {
            $config = $request->getSession()->get('displayConfig');
            if ($config instanceof DisplayConfig) {
                return $config;
            }
        }
        return new DisplayConfig();
    }

    /**
     * Saves the display config to the session.
     *
     * @param Request       $request The current HTTP request.
     * @param DisplayConfig $config  The config to save.
     *
     * @return void
     */
    private function saveDisplayConfig(Request $request, DisplayConfig $config): void
    {
        $request->getSession()->set('displayConfig', $config);
    }
}
