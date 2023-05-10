<?php

require '../bootstrap.php';

use Larmias\Crontab\Parser;

$parser = new Parser();

$result = $parser->parse('* * * * *', strtotime('2023-02-08 11:36:05'));

dump($result);