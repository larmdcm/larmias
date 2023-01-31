<?php

require '../bootstrap.php';

use Larmias\Http\Utils\Html\Content;
use Larmias\Http\Utils\Html\Parser;

$content = new Content(file_get_contents('./test1.html'));
$parser = new Parser($content);

$parser->parse();