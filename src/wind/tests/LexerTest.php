<?php

declare (strict_types=1);

namespace Tests;

use Larmias\Wind\Lexer\Lexer;
use Larmias\Wind\Lexer\TokenType;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testScript1()
    {
        $lexer = Lexer::new(file_get_contents(dirname(__DIR__) . '/examples/script1.wd'));

        while (true) {
            $nextToken = $lexer->nextToken();
            if ($nextToken->getTokenType()->getValue() === TokenType::EOF) {
                break;
            }
            var_dump($nextToken->getLiteral());
        }


        $this->assertTrue(true);
    }
}