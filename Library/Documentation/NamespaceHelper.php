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

namespace Zephir\Documentation;

use Zephir\CompilerFile;

class NamespaceHelper
{
    protected $fullNamespace;

    protected $endNamespace;
    protected $parentName;

    protected $classes = array();
    protected $namespaces = array();

    public function __construct($fullNamespace)
    {
        $this->fullNamespace = $fullNamespace;

        $ex = explode("\\", $fullNamespace);
        $this->endNamespace = $ex[count($ex) - 1];

        array_pop($ex);

        $this->parentName = implode("\\", $ex);
    }

    public function addClass(CompilerFile $c)
    {
        $this->classes[] = $c;
    }

    public function addNamespace(NamespaceHelper $n)
    {
        if (!isset($this->namespaces[$n->getFullNamespace()])) {
            $this->namespaces[$n->getFullNamespace()] = $n;
        }
    }

    public function getFullNamespace()
    {
        return $this->fullNamespace;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->endNamespace;
    }

    /**
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * @return CompilerFile[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @return NamespaceHelper[]
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }
}
