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

namespace Zephir\Stubs;

use Zephir\AliasManager;
use Zephir\ClassMethod;

/**
 * Stubs Generator
 * @todo: Merge class with documentation generator
 */
class MethodDocBlock extends DocBlock
{
    private $parameters = [];

    private $return;

    private $shortcutName = '';

    private $deprecated = false;

    /**
     * @var AliasManager
     */
    private $aliasManager;

    public function __construct(ClassMethod $method, AliasManager $aliasManager, $indent = '    ')
    {
        parent::__construct($method->getDocBlock(), $indent);

        $this->deprecated = $method->isDeprecated();
        $this->aliasManager = $aliasManager;
        $this->shortcutName = $method->isShortcut() ? $method->getShortcutName() : '';

        $this->parseMethodParameters($method);
        $this->parseLines();
        $this->parseMethodReturnType($method);
        $this->appendParametersLines();

        if (!empty($this->return)) {
            $this->appendReturnLine();
        }
    }

    protected function parseMethodReturnType(ClassMethod $method)
    {
        $return = [];
        $returnTypes = $method->getReturnTypes();

        if ($returnTypes) {
            foreach ($returnTypes as $type) {
                if (isset($type['data-type'])) {
                    $return[] = $type['data-type'] == 'variable' ? 'mixed' : $type['data-type'];
                }
            }
        }

        $returnClassTypes = $method->getReturnClassTypes();
        if ($returnClassTypes) {
            foreach ($returnClassTypes as $key => $returnClassType) {
                if ($this->aliasManager->isAlias($returnClassType)) {
                    $returnClassTypes[$key] = "\\" . $this->aliasManager->getAlias($returnClassType);
                }
            }

            $return = array_merge($return, $returnClassTypes);
        }

        if ($method->hasReturnTypesRaw()) {
            $returnClassTypes = $method->getReturnTypesRaw();

            if (!empty($returnClassTypes['list'])) {
                foreach ($returnClassTypes['list'] as $returnType) {
                    if (empty($returnType['cast']) || !$returnType['collection']) {
                        continue;
                    }

                    $key  = $returnType['cast']['value'];
                    $type = $key;

                    if ($this->aliasManager->isAlias($type)) {
                        $type = "\\" . $this->aliasManager->getAlias($type);
                    }

                    $return[$key] = $type . '[]';
                }
            }
        }

        if (!empty($return)) {
            $this->return = [implode('|', $return), ''];
        }
    }

    protected function parseLines()
    {
        $lines = [];

        foreach ($this->lines as $line) {
            if (preg_match('#^@(param|return|var) +(.*)$#', $line, $matches) === 0) {
                $lines[] = $line;
            } else {
                list(, $docType, $tokens) = $matches;

                $tokens = preg_split('/\s+/', $tokens, 3);
                $type = $tokens[0];

                if ($docType == 'var' && $this->shortcutName == 'set') {
                    $docType = 'param';
                    $name = array_keys($this->parameters);
                    $name = $name[0];
                } elseif ($docType == 'var' && $this->shortcutName == 'get') {
                    $docType = 'return';
                } else {
                    $name = isset($tokens[1]) ? '$' . $tokens[1] : '';
                }

                // TODO: there must be a better way
                if (strpos($type, 'Phalcon\\') === 0) {
                    $type = str_replace('Phalcon\\', '\Phalcon\\', $type);
                }

                $description = isset($tokens[2]) ? $tokens[2] : '';

                switch ($docType) {
                    case 'param':
                        $this->parameters[$name] = array($type, $description);
                        break;
                    case 'return':
                        $this->return = array($type, $description);
                        break;
                }
            }
        }

        $this->lines = $lines;
    }

    private function appendReturnLine()
    {
        list($type, $description) = $this->return;

        $return = $type . ' ' . $description;
        $this->lines[] = '@return ' . trim($return, ' ');
    }

    private function parseMethodParameters(ClassMethod $method)
    {
        $parameters = $method->getParameters();
        $aliasManager = $method->getClassDefinition()->getAliasManager();

        if (!$parameters) {
            return;
        }

        foreach ($method->getParameters() as $parameter) {
            if (isset($parameter['cast'])) {
                if ($aliasManager->isAlias($parameter['cast']['value'])) {
                    $type = '\\' . $aliasManager->getAlias($parameter['cast']['value']);
                } else {
                    $type = $parameter['cast']['value'];
                }
            } elseif (isset($parameter['data-type'])) {
                if ($parameter['data-type'] == 'variable') {
                    $type = 'mixed';
                } else {
                    $type = $parameter['data-type'];
                }
            } else {
                $type = 'mixed';
            }
            $this->parameters['$' . $parameter['name']] = array($type, '');
        }
    }

    private function appendParametersLines()
    {
        foreach ($this->parameters as $name => $parameter) {
            list($type, $description) = $parameter;

            $param = $type . ' ' . $name . ' ' . $description;
            $this->lines[] = '@param ' . trim($param, ' ');
        }

        if ($this->deprecated) {
            $this->lines[] = '@deprecated';
        }

        $this->lines = array_unique($this->lines);
    }
}
