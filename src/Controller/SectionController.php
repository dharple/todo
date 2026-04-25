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

use App\Entity\Item;
use App\Entity\Section;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles section (category) management.
 */
class SectionController extends AbstractController
{
    /**
     * Constructs a new SectionController.
     *
     * @param EntityManagerInterface $em Doctrine entity manager.
     */
    public function __construct(
        protected EntityManagerInterface $em
    ) {
    }

    /**
     * Displays and processes the section management form.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     */
    #[Route('/sections', name: 'app_section_edit', methods: ['GET', 'POST'])]
    public function sectionEdit(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        assert($user instanceof User);

        $sectionRepository = $this->em->getRepository(Section::class);

        $errors = [];

        if ($request->isMethod('POST')) {
            $submitButton = (string) $request->request->get('submitButton', '');

            if ($submitButton !== '') {
                try {
                    if ($submitButton === 'Add') {
                        $name = trim((string) $request->request->get('add_name', ''));

                        if ($name !== '') {
                            $section = (new Section())
                                ->setName($name)
                                ->setStatus('Active')
                                ->setUser($user);

                            $this->em->persist($section);
                        } else {
                            $errors[] = 'Please specify the name of the section to add.';
                        }
                    } elseif ($submitButton === 'Rename') {
                        $name = trim((string) $request->request->get('edit_name', ''));
                        $id   = (int) $request->request->get('edit_section_id', 0);

                        if ($id > 0) {
                            $section = $sectionRepository
                                ->findOneBy([
                                    'id'   => $id,
                                    'user' => $user,
                                ]);

                            if ($section !== null) {
                                $section->setName($name);
                                $this->em->persist($section);
                            }
                        } else {
                            $errors[] = 'Please specify a section to rename.';
                        }
                    } elseif ($submitButton === 'Activate') {
                        $findBy = [
                            'status' => 'Inactive',
                            'user'   => $user,
                        ];

                        $toggleId = $request->request->get('toggle_section_id');
                        if ($toggleId !== 'all') {
                            $findBy['id'] = (int) $toggleId;
                        }

                        $sections = $sectionRepository->findBy($findBy);

                        foreach ($sections as $section) {
                            if ($request->request->get('resetStartTimes') !== null) {
                                $items = $this->em->getRepository(Item::class)
                                    ->findBy([
                                        'section' => $section,
                                        'status'  => 'Open',
                                        'user'    => $user,
                                    ]);
                                foreach ($items as $item) {
                                    $item->setCreated(new \DateTime());
                                    $this->em->persist($item);
                                }
                            }
                            $section->setStatus('Active');
                            $this->em->persist($section);
                        }
                    } elseif ($submitButton === 'Deactivate') {
                        $findBy = [
                            'status' => 'Active',
                            'user'   => $user,
                        ];

                        $toggleId = $request->request->get('toggle_section_id');
                        if ($toggleId !== 'all') {
                            $findBy['id'] = (int) $toggleId;
                        }

                        $sections = $sectionRepository->findBy($findBy);

                        foreach ($sections as $section) {
                            $section->setStatus('Inactive');
                            $this->em->persist($section);
                        }
                    }

                    $this->em->flush();
                } catch (\Exception $e) {
                    $errors[] = sprintf('Failed to edit sections: %s', $e->getMessage());
                }
            }
        }

        $sections = $sectionRepository->findBy(['user' => $user], ['name' => 'ASC']);

        return $this->render('section_edit.html.twig', [
            'errors'   => $errors,
            'sections' => $sections,
        ]);
    }
}
