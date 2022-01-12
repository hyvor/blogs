<?php

namespace App\Domains\Theme\Twig\Tags\TokenParser; 

use Twig\TokenParser\AbstractTokenParser;
use Twig\Token;
use Twig\Node\Node;

use App\Domains\Theme\Twig\Tags\Node\TwigNode;

class TwigTokenParser extends AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(\Twig\Token::NAME_TYPE)->getValue();
        $stream->expect(\Twig\Token::OPERATOR_TYPE, '=');
        $value = $parser->getExpressionParser()->parseExpression();
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new TwigNode($name, $value, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'hyvorTag';
    }
}
