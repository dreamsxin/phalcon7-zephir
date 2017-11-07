<?php

/*
 +--------------------------------------------------------------------------+
 | Zephir                                                                   |
 | Copyright (c) 2013-present Zephir Team (https://zephir-lang.com/)        |
 |                                                                          |
 | This source file is subject the MIT license, that is bundled with this   |
 | package in the file LICENSE, and is available through the world-wide-web |
 | at the following url: http://zephir-lang.com/license.html                |
 +--------------------------------------------------------------------------+
*/

namespace Zephir\Builder;

/**
 * StatementsBlockBuilder
 *
 * Allows to manually build a statements block AST node
 */
class StatementsBlockBuilder
{
    protected $statements;

    protected $raw;

    public function __construct(array $statements, $raw = false)
    {
        $this->statements = $statements;
        $this->raw = $raw;
    }

    /**
     * Returns a builder definition
     *
     * @return array
     */
    public function get()
    {
        if (!$this->raw) {
            $statements = array();

            foreach ($this->statements as $statement) {
                $statements[] = $statement->get();
            }

            return $statements;
        }

        return $this->statements;
    }
}
