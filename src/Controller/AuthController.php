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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Handles login and logout.
 */
class AuthController extends AbstractController
{
    /**
     * Renders the login form. The actual credential check is performed by the
     * Symfony security firewall (form_login), not this action.
     *
     * @param AuthenticationUtils $authenticationUtils Symfony auth utilities.
     *
     * @return Response
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_index');
        }

        $authError     = $authenticationUtils->getLastAuthenticationError();
        $lastUsername  = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'errors'        => $authError !== null ? [$authError->getMessageKey()] : [],
            'last_username' => $lastUsername,
        ]);
    }

    /**
     * Logout action. The Symfony firewall intercepts this route before the
     * controller body runs.
     *
     * @return never
     *
     * @throws \LogicException Always.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method should never be reached; the firewall intercepts /logout.');
    }
}
