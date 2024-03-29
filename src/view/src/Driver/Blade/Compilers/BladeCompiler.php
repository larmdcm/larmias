<?php

namespace Larmias\View\Driver\Blade\Compilers;

class BladeCompiler extends Compiler implements CompilerInterface
{
    /**
     * All custom "directive" handlers.
     *
     * This was implemented as a more usable "extend" in 5.1.
     *
     * @var array
     */
    protected $customDirectives = [];

    /**
     * The file currently being compiled.
     *
     * @var string
     */
    protected $path;

    /**
     * All of the available compiler functions.
     *
     * @var array
     */
    protected $compilers = [
        'Statements',
        'Comments',
        'Echos',
    ];

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = ['{{{', '}}}'];

    /**
     * The "regular" / legacy echo string format.
     *
     * @var string
     */
    protected $echoFormat = 'e(%s)';

    /**
     * Array of footer lines to be added to template.
     *
     * @var array
     */
    protected $footer = [];

    /**
     * Counter to keep track of nested forelse statements.
     *
     * @var int
     */
    protected $forelseCounter = 0;

    /**
     * Compile the view at the given path.
     *
     * @param string $path
     * @return void
     */
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        if (!is_null($this->cachePath)) {
            if (!is_dir($this->cachePath)) {
                mkdir($this->cachePath, 0755, true);
            }
            $contents = $this->compileString($this->files->get($this->getPath()));

            $this->files->put($this->getCompiledPath($this->getPath()), $contents);
        }
    }

    /**
     * Get the path currently being compiled.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the path currently being compiled.
     *
     * @param string $path
     * @return void
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * Compile the given Blade template contents.
     *
     * @param string $value
     * @return string
     */
    public function compileString(string $value): string
    {
        $result = '';

        $this->footer = [];

        // Here we will loop through all of the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        // If there are any footer lines that need to get added to a template we will
        // add them here at the end of the template. This gets used mainly for the
        // template inheritance via the extends keyword that should be appended.
        if (count($this->footer) > 0) {
            $result = ltrim($result, PHP_EOL)
                . PHP_EOL . implode(PHP_EOL, array_reverse($this->footer));
        }

        return $result;
    }

    /**
     * Parse the tokens from the template.
     *
     * @param array $token
     * @return string
     */
    protected function parseToken($token)
    {
        list($id, $content) = $token;

        if ($id == T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }

    /**
     * Compile Blade comments into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileComments($value)
    {
        $pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);

        return preg_replace($pattern, '<?php /*$1*/ ?>', $value);
    }

    /**
     * Compile Blade echos into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileEchos($value)
    {
        foreach ($this->getEchoMethods() as $method => $length) {
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     * Get the echo methods in the proper order for compilation.
     *
     * @return array
     */
    protected function getEchoMethods()
    {
        $methods = [
            'compileRawEchos' => strlen(stripcslashes($this->rawTags[0])),
            'compileEscapedEchos' => strlen(stripcslashes($this->escapedTags[0])),
            'compileRegularEchos' => strlen(stripcslashes($this->contentTags[0])),
        ];

        uksort($methods, function ($method1, $method2) use ($methods) {
            // Ensure the longest tags are processed first
            if ($methods[$method1] > $methods[$method2]) {
                return -1;
            }
            if ($methods[$method1] < $methods[$method2]) {
                return 1;
            }

            // Otherwise give preference to raw tags (assuming they've overridden)
            if ($method1 === 'compileRawEchos') {
                return -1;
            }
            if ($method2 === 'compileRawEchos') {
                return 1;
            }

            if ($method1 === 'compileEscapedEchos') {
                return -1;
            }
            if ($method2 === 'compileEscapedEchos') {
                return 1;
            }
        });

        return $methods;
    }

    /**
     * Compile Blade statements that start with "@".
     *
     * @param string $value
     * @return mixed
     */
    protected function compileStatements($value)
    {
        $callback = function ($match) {
            $expression = isset($match[3]) ? $match[3] : $match;

            if (strpos($match[1], '@') !== false) {
                $match[0] = isset($match[3]) ? $match[1] . $match[3] : $match[1];
            } elseif (isset($this->customDirectives[$match[1]])) {
                $match[0] = call_user_func($this->customDirectives[$match[1]], $expression);
            } elseif (method_exists($this, $method = 'compile' . ucfirst($match[1]))) {
                $match[0] = $this->$method($expression);
            }

            return isset($match[3]) ? $match[0] : $match[0] . $match[2];
        };

        return preg_replace_callback('/\B@(@?\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value);
    }

    /**
     * Compile the "raw" echo statements.
     *
     * @param string $value
     * @return string
     */
    protected function compileRawEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            return $matches[1] ? substr($matches[0], 1) : '<?php echo ' . $this->compileEchoDefaults($matches[2]) . '; ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the "regular" echo statements.
     *
     * @param string $value
     * @return string
     */
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            $wrapped = sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]));

            return $matches[1] ? substr($matches[0], 1) : '<?php echo ' . $wrapped . '; ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the escaped echo statements.
     *
     * @param string $value
     * @return string
     */
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            return $matches[1] ? $matches[0] : '<?php echo  \Larmias\View\Driver\Blade\e(' . $this->compileEchoDefaults($matches[2]) . '); ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the default values for the echo statement.
     *
     * @param string $value
     * @return string
     */
    public function compileEchoDefaults($value)
    {
        return preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
    }

    /**
     * Compile the each statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEach($expression)
    {
        return "<?php echo \$__env->renderEach{$expression}; ?>";
    }

    /**
     * Compile the component statements into valid PHP.
     *
     * @param $expression
     *
     * @return string
     */
    protected function compileComponent($expression)
    {
        return "<?php echo \$__env->startComponent{$expression}; ?>";
    }

    /**
     * Compile the endcomponent statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndcomponent()
    {
        return '<?php echo $__env->renderComponent(); ?>';
    }

    /**
     * Compile the slot statements into valid PHP.
     *
     * @param $expression
     *
     * @return string
     */
    protected function compileSlot($expression)
    {
        return "<?php \$__env->slot{$expression}; ?>";
    }

    /**
     * Compile the endslot statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndslot()
    {
        return '<?php $__env->endSlot(); ?>';
    }

    /**
     * Compile the yield statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileYield($expression)
    {
        return "<?php echo \$__env->yieldContent{$expression}; ?>";
    }

    /**
     * Compile the show statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileShow($expression)
    {
        return '<?php echo $__env->yieldSection(); ?>';
    }

    /**
     * Compile the section statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileSection($expression)
    {
        return "<?php \$__env->startSection{$expression}; ?>";
    }

    /**
     * Compile the append statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileAppend($expression)
    {
        return '<?php $__env->appendSection(); ?>';
    }

    /**
     * Compile the end-section statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndsection($expression)
    {
        return '<?php $__env->stopSection(); ?>';
    }

    /**
     * Compile the stop statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileStop($expression)
    {
        return '<?php $__env->stopSection(); ?>';
    }

    /**
     * Compile the overwrite statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileOverwrite($expression)
    {
        return '<?php $__env->stopSection(true); ?>';
    }

    /**
     * Compile the unless statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileUnless($expression)
    {
        return "<?php if ( ! $expression): ?>";
    }

    /**
     * Compile the end unless statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndunless($expression)
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileElse($expression)
    {
        return '<?php else: ?>';
    }

    /**
     * Compile the for statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Compile the foreach statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileForeach($expression)
    {
        preg_match('/\( *(.*) +as *([^\)]*)/i', $expression, $matches);

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$iteratee}; \$__env->addLoop(\$__currentLoopData);";

        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getFirstLoop();';

        return "<?php {$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} ?>";
    }

    /**
     * Compile the break statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileBreak($expression)
    {
        return $expression ? "<?php if{$expression} break; ?>" : '<?php break; ?>';
    }

    /**
     * Compile the continue statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileContinue($expression)
    {
        return $expression ? "<?php if{$expression} continue; ?>" : '<?php continue; ?>';
    }

    /**
     * Compile the forelse statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileForelse($expression)
    {
        $empty = '$__empty_' . ++$this->forelseCounter;

        preg_match('/\( *(.*) +as *([^\)]*)/', $expression, $matches);

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$iteratee}; \$__env->addLoop(\$__currentLoopData);";

        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getFirstLoop();';

        return "<?php {$empty} = true; {$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} {$empty} = false; ?>";
    }

    /**
     * Compile the if statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compile the else-if statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compile the forelse statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEmpty($expression)
    {
        $empty = '$__empty_' . $this->forelseCounter--;

        return "<?php endforeach; \$__env->popLoop(); \$loop = \$__env->getFirstLoop(); if ({$empty}): ?>";
    }

    /**
     * Compile the while statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    /**
     * Compile the end-while statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndwhile($expression)
    {
        return '<?php endwhile; ?>';
    }

    /**
     * Compile the end-for statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndfor($expression)
    {
        return '<?php endfor; ?>';
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndforeach($expression)
    {
        return '<?php endforeach; $__env->popLoop(); $loop = $__env->getFirstLoop(); ?>';
    }

    /**
     * Compile the end-if statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndif($expression)
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the end-for-else statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndforelse($expression)
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the extends statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileExtends($expression)
    {
        if (strpos($expression, '(') === 0) {
            $expression = substr($expression, 1, -1);
        }

        $data = "<?php echo \$__env->make($expression, \Larmias\View\Driver\Blade\array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";

        $this->footer[] = $data;

        return '';
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileInclude($expression)
    {
        if (strpos($expression, '(') === 0) {
            $expression = substr($expression, 1, -1);
        }

        return "<?php echo \$__env->make($expression, \Larmias\View\Driver\Blade\array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";
    }

    /**
     * Compile the stack statements into the content.
     *
     * @param string $expression
     * @return string
     */
    protected function compileStack($expression)
    {
        return "<?php echo \$__env->yieldContent{$expression}; ?>";
    }

    /**
     * Compile the push statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compilePush($expression)
    {
        return "<?php \$__env->startSection{$expression}; ?>";
    }

    /**
     * Compile the endpush statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndpush($expression)
    {
        return '<?php $__env->appendSection(); ?>';
    }

    /**
     * Register a handler for custom directives.
     *
     * @param string $name
     * @param callable $handler
     * @return void
     */
    public function directive($name, callable $handler)
    {
        $this->customDirectives[$name] = $handler;
    }

    /**
     * Get the list of custom directives.
     *
     * @return array
     */
    public function getCustomDirectives()
    {
        return $this->customDirectives;
    }

    /**
     * Gets the raw tags used by the compiler.
     *
     * @return array
     */
    public function getRawTags()
    {
        return $this->rawTags;
    }

    /**
     * Sets the raw tags used for the compiler.
     *
     * @param string $openTag
     * @param string $closeTag
     * @return void
     */
    public function setRawTags($openTag, $closeTag)
    {
        $this->rawTags = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the content tags used for the compiler.
     *
     * @param string $openTag
     * @param string $closeTag
     * @param bool $escaped
     * @return void
     */
    public function setContentTags($openTag, $closeTag, $escaped = false)
    {
        $property = ($escaped === true) ? 'escapedTags' : 'contentTags';

        $this->{$property} = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the escaped content tags used for the compiler.
     *
     * @param string $openTag
     * @param string $closeTag
     * @return void
     */
    public function setEscapedContentTags($openTag, $closeTag)
    {
        $this->setContentTags($openTag, $closeTag, true);
    }

    /**
     * Gets the content tags used for the compiler.
     *
     * @return array
     */
    public function getContentTags()
    {
        return $this->getTags();
    }

    /**
     * Gets the escaped content tags used for the compiler.
     *
     * @return array
     */
    public function getEscapedContentTags()
    {
        return $this->getTags(true);
    }

    /**
     * Gets the tags used for the compiler.
     *
     * @param bool $escaped
     * @return array
     */
    protected function getTags($escaped = false)
    {
        $tags = $escaped ? $this->escapedTags : $this->contentTags;

        return array_map('stripcslashes', $tags);
    }

    /**
     * Set the echo format to be used by the compiler.
     *
     * @param string $format
     * @return void
     */
    public function setEchoFormat($format)
    {
        $this->echoFormat = $format;
    }
}
