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

use App\Entity\Section;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for /
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index()
    {
        $em = $this->getDoctrine();

        return $this->render(
            'index.html.twig',
            [
                'sections' => $em->getRepository(Section::class)->findAll(),
            ]
        );
    }
}
