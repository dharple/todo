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
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Helper methods until we've got DI up and running.
 */
class Helper
{
    /**
     * Directory to use for Doctrine proxy classes.
     *
     * @var string
     */
    protected const PROXY_DIR = '/tmp/todo-doctrine-proxy';

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
            $cache = null;
            $useSimpleAnnotationReader = false;
            $config = Setup::createAnnotationMetadataConfiguration(
                [
                    self::getProjectRoot() . '/src'
                ],
                $isDevMode,
                static::getProxyDir(),
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
     * Configures and returns the proxy directory for Doctrine.
     *
     * @return string
     */
    public static function getProxyDir(): string
    {
        $dir = self::PROXY_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Gets a twig renderer.
     *
     * @return Environment
     */
    public static function getTwig(): Environment
    {
        $loader = new FilesystemLoader(static::getProjectRoot() . '/templates');
        return new Environment($loader);
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
            if (empty($_SESSION['user_id'])) {
                throw new Exception('Unable to find user.');
            }

            $user = static::getEntityManager()->find(User::class, $_SESSION['user_id']);
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

    /**
     * Sets the timezone in both PHP and the database.
     *
     * @return void
     *
     * @throws Exception
     */
    public static function setTimezone(): void
    {
        $user = static::getUser();
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
