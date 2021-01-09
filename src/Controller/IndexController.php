<?php

namespace App\Controller;

use App\Analytics\ItemStats;
use App\Entity\User;
use App\Renderer\DisplayConfig;
use App\Renderer\DisplayHelper;
use App\Renderer\ListDisplay;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     * @param EntityManagerInterface $em   The entity manager to use.
     * @param LoggerInterface        $log  The logger to use.
     * @param Environment            $twig The twig renderer to use.
     *
     * @return Response
     */
    public function index(
        EntityManagerInterface $em,
        LoggerInterface $log,
        Environment $twig
    ): Response {
        // todo replace with Guard
        $user = $em->find(User::class, 1);

        // todo load from session
        $config = new DisplayConfig();

        $errors = [];

        try {
            $config->processRequest();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        $listDisplay = new ListDisplay($config, $em, $log, $twig, $user);

        $itemStats = new ItemStats($em, $user);

        $listDisplay->setFooter($this->renderView('partials/index/summary.php.twig', [
            'itemStats' => $itemStats,
        ]));

        $listOutput = $listDisplay->getOutput();
        $itemCount = $listDisplay->getOutputCount();

        $sections = $user->getSections()->matching(
            new Criteria(
                new Comparison('status', '=', 'Active')
            )
        );
        $sectionCount = count($sections);

        return $this->render('index.html.twig', [
            'config' => $config,
            'errors' => $errors,
            'filterAgingValues' => DisplayHelper::getFilterAgingValues(),
            'filterClosedValues' => DisplayHelper::getFilterClosedValues(),
            'filterPriorityValues' => DisplayHelper::getFilterPriorityValues(),
            'hasItems' => ($itemCount > 0),
            'hasSections' => ($sectionCount > 0),
            'itemStats' => $itemStats,
            'list' => $listOutput,
            'showDuplicate' => ($config->getFilterClosed() != 'none'),
            'showPriorityValues' => DisplayHelper::getShowPriorityValues(),
            'user' => $user,
        ]);
    }
}
