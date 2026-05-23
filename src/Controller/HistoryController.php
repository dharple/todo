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

use App\Analytics\ItemHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the completed items history page.
 */
class HistoryController extends AbstractController
{
    /**
     * Constructs a new HistoryController.
     *
     * @param EntityManagerInterface $em Doctrine entity manager.
     */
    public function __construct(
        protected EntityManagerInterface $em
    ) {
    }

    /**
     * Displays completed items filtered by a time period and sort order.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     */
    #[Route('/history', name: 'app_show_done', methods: ['GET'])]
    public function showDone(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        assert($user instanceof User);

        $errors = [];

        $view = $request->query->get('view', 'all');
        $sort = $request->query->get('sort', 'section');
        $year = $request->query->get('year', date('Y'));
        if (!is_numeric($year)) {
            $year = '1969';
        }

        $itemHistory = new ItemHistory($this->em, $user);
        if ($sort === 'section') {
            $itemHistory->setOrdering('section');
        }

        switch ($view) {
            case 'month':
                $items  = $itemHistory->doneThisMonth();
                $period = 'This Month';
                break;

            case 'last-month':
                $items  = $itemHistory->doneLastMonth();
                $period = 'Last Month';
                break;

            case 'week':
                $items  = $itemHistory->doneThisWeek();
                $period = 'This Week';
                break;

            case 'last-week':
                $items  = $itemHistory->doneLastWeek();
                $period = 'Last Week';
                break;

            case 'yesterday':
                $items  = $itemHistory->doneYesterday();
                $period = 'Yesterday';
                break;

            case 'today':
                $items  = $itemHistory->doneToday();
                $period = 'Today';
                break;

            case 'month3':
                $items  = $itemHistory->donePreviousMonths(3);
                $period = 'Past 3 Months';
                break;

            case 'month6':
                $items  = $itemHistory->donePreviousMonths(6);
                $period = 'Past 6 Months';
                break;

            case 'month9':
                $items  = $itemHistory->donePreviousMonths(9);
                $period = 'Past 9 Months';
                break;

            case 'month12':
                $items  = $itemHistory->donePreviousMonths(12);
                $period = 'Past 12 Months';
                break;

            case 'year':
                $items  = $itemHistory->doneDuringYear($year);
                $period = sprintf('Done During %d', $year);
                break;

            case 'all':
                $items  = $itemHistory->doneTotal();
                $period = 'All';
                break;

            default:
                $items    = [];
                $period   = 'UNKNOWN';
                $errors[] = 'Invalid selector';
                break;
        }

        return $this->render('show_done.html.twig', [
            'errors' => $errors,
            'items'  => $items,
            'period' => $period,
            'sort'   => $sort,
            'view'   => $view,
            'year'   => $year,
        ]);
    }
}
