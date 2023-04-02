<?php

declare(strict_types=1);

namespace Larmias\Wind;

use Larmias\Wind\Environment\Environment;
use Larmias\Wind\Evaluator\Evaluator;
use Larmias\Wind\Lexer\Lexer;
use Larmias\Wind\Parser\Parser;

class Repl
{
    public static function run(): void
    {
        $evaluator = new Evaluator();
        $env = Environment::new();
        while (true) {
            fwrite(STDOUT, '>> ');
            $input = fgets(STDIN);
            try {
                $lexer = Lexer::new($input);
                $parser = Parser::new($lexer);
                $program = $parser->parseProgram();
                $evaluated = $evaluator->eval($program, $env);
                if ($evaluated !== null) {
                    fwrite(STDOUT, $evaluated->inspect());
                    fwrite(STDOUT, "\n");
                }
            } catch (\Throwable $e) {
                fwrite(STDOUT, $e->getMessage());
                fwrite(STDOUT, "\n");
            }
        }
    }
}