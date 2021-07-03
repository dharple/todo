<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use App\Entity\User;
use App\Logger\FileLogger;
use App\Renderer\DisplayConfig;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

/**
 * Helper methods until we've got DI up and running.
 */
class Helper
{
    /**
     * Don't allow instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Gets the container
     *
     * @return ContainerInterface
     *
     * @throws Exception
     */
    public static function getContainer(): ContainerInterface
    {
        return self::getKernel()->getContainer();
    }

    /**
     * Returns the user's display config.
     *
     * @return DisplayConfig
     */
    public static function getDisplayConfig(): DisplayConfig
    {
        if (isset($_SESSION['displayConfig']) && $_SESSION['displayConfig'] instanceof DisplayConfig && !isset($_REQUEST['reset_display_settings'])) {
            return $_SESSION['displayConfig'];
        }

        return $_SESSION['displayConfig'] = new DisplayConfig();
    }

    /**
     * Generates an entity manager for use in the application.
     *
     * @return EntityManager
     *
     * @throws Exception
     */
    public static function getEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Gets the Kernel
     *
     * @return Kernel
     *
     * @throws Exception
     */
    public static function getKernel(): Kernel
    {
        if (isset($GLOBALS['kernel']) || $GLOBALS['kernel'] instanceof Kernel) {
            return $GLOBALS['kernel'];
        }

        throw new Exception('Unable to find kernel.');
    }

    /**
     * Returns a logger
     *
     * @return LoggerInterface
     *
     * @throws Exception
     */
    public static function getLogger(): LoggerInterface
    {
        return new FileLogger();
    }

    /**
     * Gets a twig renderer.
     *
     * @return Environment
     *
     * @throws Exception
     */
    public static function getTwig(): Environment
    {
        return self::getContainer()->get('twig');
    }

    /**
     * Sets the timezone in both PHP and the database.
     *
     * @param User $user The user to use.
     *
     * @return void
     *
     * @throws Exception
     */
    public static function setTimezone(User $user): void
    {
        if (!empty($user->getTimezone())) {
            date_default_timezone_set($user->getTimezone());

            static::getEntityManager()
                ->getConnection()
                ->executeStatement(
                    'SET time_zone = ?',
                    [$user->getTimezone()]
                );
        }
    }
}
