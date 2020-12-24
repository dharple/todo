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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
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

        $dotenv = new Dotenv();
        $dotenv->loadEnv(dirname(__DIR__) . '/.env');

        $conn = [
            'driver' => 'pdo_mysql',

            'dbname' => $_ENV['DATABASE_INSTANCE'],
            'host' => $_ENV['DATABASE_HOST'],
            'password' => $_ENV['DATABASE_PASSWORD'],
            'user' => $_ENV['DATABASE_USER'],
        ];

        return EntityManager::create($conn, $config);
    }
}
