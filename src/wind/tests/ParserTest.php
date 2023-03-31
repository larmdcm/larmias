<?php

declare (strict_types=1);

namespace Tests;

use Larmias\Wind\Lexer\Lexer;
use Larmias\Wind\Parser\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public function testLetStatement()
    {
        $input = '
            let x = 5;
            let y = 10;
            let foobar = 666666;
        ';

        $lexer = Lexer::new($input);
        $parser = Parser::new($lexer);

        $program = $parser->parseProgram();

        var_dump($program);

        $this->assertTrue(true);
    }
}