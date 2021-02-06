<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ORM;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Adds DATE() support to DQL.
 */
class Date extends FunctionNode
{
    /**
     * The parameter parsed out of the DQL DATE() Command.
     *
     * @var object
     */
    protected $parameter;

    /**
     * Converts a DQL DATE() expression into a SQL one.
     *
     * @param SqlWalker $sqlWalker The SQL walker to use.
     *
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('DATE(%s)', $this->parameter->dispatch($sqlWalker));
    }

    /**
     * Parses a DATE() expression.
     *
     * @param Parser $parser The parser to use.
     *
     * @return void
     *
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->parameter = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
