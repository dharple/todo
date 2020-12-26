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

use App\Logger\FileLogger;
use App\Logger\FileSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Helper methods until we've got DI up and running.
 */
class Helper
{

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
                    dirname(__DIR__ . '/src')
                ],
                $isDevMode,
                $proxyDir,
                $cache,
                $useSimpleAnnotationReader
            );

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
     * Loads config settings from the .env and puts them in to $_ENV.
     *
     * @return void
     */
    public static function loadConfig(): void
    {
        static $loaded = false;

        if (!$loaded) {
            $dotenv = new Dotenv();
            $dotenv->loadEnv(dirname(__DIR__) . '/.env');
            $loaded = true;
        }
    }
}
