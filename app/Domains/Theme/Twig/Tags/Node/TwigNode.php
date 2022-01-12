<?php

namespace App\Domains\Theme\Twig\Tags\Node; 
use Twig\Node\Node;
use Twig\Compiler;


class TwigNode extends Node
{
    public function __construct($name, \Twig\Node\Expression\AbstractExpression $value, $line, $tag = null)
    {
        parent::__construct(['value' => $value], ['name' => $name], $line, $tag);
    }

    public function compile(\Twig\Compiler $compiler)
    {
        $json_data = "Testing wheather this is working";

        $compiler
            ->addDebugInfo($this)
            ->write('$context[\''.$this->getAttribute('name').'\'] = '.var_export($json_data, true).';')
            ->subcompile($this->getNode('value'))
            ->raw(";\n") 
        ;
    }
}