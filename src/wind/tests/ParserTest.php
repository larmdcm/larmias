<?php

declare(strict_types=1);

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

    /**
     * @return void
     */
    public function testOperatorPrecedence(): void
    {
        $inputs = [
            [
                '-a * b',
                '((-a) * b)',
            ],
            [
                'a + b + c',
                '((a + b) + c)',
            ],
            [
                'true',
                'true'
            ],
        ];

        foreach ($inputs as $inputItem) {
            $lexer = Lexer::new($inputItem[0]);
            $parser = Parser::new($lexer);
            $program = $parser->parseProgram();
            $actual = $program->toString();
            var_dump($actual);

            $this->assertSame($actual, $inputItem[1]);
        }

        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testFunction(): void
    {
        $inputs = [
            [
                'fn() {}',
                ''
            ],
            [
                'fn(x) {}',
            ],
            [
                'fn(x,y,z) {}',
            ],
            [
                'add(x,y)',
            ]
        ];

        foreach ($inputs as $inputItem) {
            $lexer = Lexer::new($inputItem[0]);
            $parser = Parser::new($lexer);
            $program = $parser->parseProgram();
            $actual = $program->toString();
            var_dump($actual);
        }

        $this->assertTrue(true);
    }
}