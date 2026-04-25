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

use App\Auth\Guard;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the user account settings page.
 */
class AccountController extends AbstractController
{
    /**
     * Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * Constructs a new AccountController.
     *
     * @param EntityManagerInterface $em Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Displays and processes the account settings form.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Response
     */
    #[Route('/account', name: 'app_account', methods: ['GET', 'POST'])]
    public function account(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        assert($user instanceof User);

        $errors = [];

        if ($request->isMethod('POST')) {
            $submitButton = $request->request->get('submitButton');

            if ($submitButton === 'Update') {
                try {
                    $user->setFullname((string) $request->request->get('fullname', ''));
                    $timezone = (string) $request->request->get('timezone', '');
                    if ($timezone === 'Other') {
                        $timezone = (string) $request->request->get('timezone_other', '');
                    }
                    $user->setTimezone($timezone);

                    $this->em->persist($user);
                    $this->em->flush();
                } catch (\Exception $e) {
                    $errors[] = sprintf('Failed to update user information: %s', $e->getMessage());
                }
            } elseif ($submitButton === 'Change Password') {
                try {
                    $oldPassword = (string) $request->request->get('old_password', '');
                    $newPassword = (string) $request->request->get('new_password', '');
                    $confirm     = (string) $request->request->get('confirm', '');

                    $ret = Guard::checkPassword($user, $oldPassword);
                    if ($ret && $newPassword === $confirm) {
                        Guard::setPassword($user, $newPassword);
                        $this->em->persist($user);
                        $this->em->flush();
                    } elseif (!$ret) {
                        $errors[] = 'Incorrect password';
                    } else {
                        $errors[] = 'New passwords do not match';
                    }
                } catch (\Exception $e) {
                    $errors[] = sprintf('Failed to change password: %s', $e->getMessage());
                }
            }
        }

        $timezones = timezone_identifiers_list(\DateTimeZone::PER_COUNTRY, 'US');

        return $this->render('account.html.twig', [
            'errors'    => $errors,
            'timezones' => $timezones,
            'user'      => $user,
        ]);
    }
}
