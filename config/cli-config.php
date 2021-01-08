<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

use App\Helper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once "vendor/autoload.php";

$entityManager = Helper::getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
