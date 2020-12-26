<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * File logger.
 */
class FileLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * Valid log levels, sorted in ascending order of severity.
     *
     * This drives the LOG_LEVEL-based filtering.
     *
     * @var string[]
     */
    protected const VALID_LOG_LEVELS = [
        'debug'     => 0,
        'info'      => 1,
        'notice'    => 2,
        'warning'   => 3,
        'error'     => 4,
        'critical'  => 5,
        'alert'     => 6,
        'emergency' => 7,
    ];

    /**
     * Checks the passed log level.
     *
     * @param string $level The log level being used.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    protected function checkLogLevel(string $level): bool
    {
        if (!array_key_exists($level, static::VALID_LOG_LEVELS)) {
            throw new InvalidArgumentException(sprintf('Invalid log level %s', $level));
        }

        $minimumLevel = $_ENV['LOG_LEVEL'] ?: 'debug';

        return (static::VALID_LOG_LEVELS[$level] >= static::VALID_LOG_LEVELS[$minimumLevel]);
    }

    /**
     * Returns the filename to log to.
     *
     * @return string|null
     */
    protected function getFilename(): ?string
    {
        return $_ENV['LOG_FILE'] ?: null;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level   The log level to use.
     * @param string  $message The message to log.
     * @param mixed[] $context Any additional context to include.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = []): void
    {
        $filename = $this->getFilename();
        if ($filename === null) {
            return;
        }

        if (!$this->checkLogLevel($level)) {
            return;
        }

        $this->write($level, $message);
        if (!empty($context)) {
            $this->write($level, sprintf('CONTEXT: %s', implode(PHP_EOL, $context)));
        }
    }

    /**
     * Writes a line to a file.
     *
     * @param string $level The current log level.
     * @param string $line  The message to log.
     *
     * @return void
     */
    protected function write(string $level, string $line): void
    {
        $prefix = sprintf('%s [%s]', date('c'), strtoupper($level));

        file_put_contents(
            $this->getFilename(),
            sprintf("%s %s\n", $prefix, $line),
            FILE_APPEND
        );
    }
}
