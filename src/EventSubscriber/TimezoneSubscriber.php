<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Sets the authenticated user's timezone as the PHP default on each request.
 *
 * Runs after the security firewall (priority -10) so the security token is
 * already populated when this subscriber fires.
 */
class TimezoneSubscriber implements EventSubscriberInterface
{
    /**
     * Constructs a new TimezoneSubscriber.
     *
     * @param Security $security Symfony security helper.
     */
    public function __construct(
        protected Security $security
    ) {
    }

    /**
     * Returns the events this subscriber listens to.
     *
     * @return array<string, array<int, int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -10],
        ];
    }

    /**
     * Sets the PHP default timezone to match the authenticated user's preference.
     *
     * @param RequestEvent $event The kernel request event.
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || empty($user->getTimezone())) {
            return;
        }

        date_default_timezone_set($user->getTimezone());
    }
}
