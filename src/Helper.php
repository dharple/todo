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
use App\Logger\FileSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Exception;
use Oro\ORM\Query\AST\Functions\SimpleFunction;
use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;

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
     * Generates an entity manager for use in the application.
     *
     * @return EntityManager
     *
     * @throws ORMException
     */
    public static function getEntityManager(): EntityManager
    {
        static $em = null;

        if (!isset($em)) {
            self::loadConfig();

            $isDevMode = true;
            $proxyDir = null;
            $cache = null;
            $useSimpleAnnotationReader = false;
            $config = Setup::createAnnotationMetadataConfiguration(
                [
                    self::getProjectRoot() . '/src'
                ],
                $isDevMode,
                $proxyDir,
                $cache,
                $useSimpleAnnotationReader
            );

            $config->addCustomDatetimeFunction('date', SimpleFunction::class);

            $conn = [
                'driver' => 'pdo_mysql',

                'dbname' => $_ENV['DATABASE_INSTANCE'],
                'host' => $_ENV['DATABASE_HOST'],
                'password' => $_ENV['DATABASE_PASSWORD'],
                'user' => $_ENV['DATABASE_USER'],
            ];

            $config->setSQLLogger(new FileSQLLogger());

            $em = EntityManager::create($conn, $config);
        }

        return $em;
    }

    /**
     * Returns a logger
     *
     * @return LoggerInterface
     */
    public static function getLogger()
    {
        return new FileLogger();
    }

    /**
     * Returns the root of the project.
     *
     * @return string
     */
    public static function getProjectRoot(): string
    {
        return dirname(__DIR__);
    }

    /**
     * Returns the current user
     *
     * @return User
     *
     * @throws Exception
     */
    public static function getUser(): User
    {
        static $user = null;

        if ($user === null) {
            $userRepository = static::getEntityManager()->getRepository(User::class);
            $user = $userRepository->find($GLOBALS['user_id']);
            if ($user === null) {
                throw new Exception('Unable to find user.');
            }
        }

        return $user;
    }

    /**
     * Loads config settings from the .env and puts them in to $_ENV.
     *
     * @return void
     */
    public static function loadConfig(): void
    {
        static $loaded = false;

        if (!$loaded) {
            $dotenv = new Dotenv();
            $dotenv->loadEnv(self::getProjectRoot() . '/.env');
            $loaded = true;
        }
    }
}
