<?php

require '../bootstrap.php';

use Larmias\Crontab\Parser;
use Carbon\Carbon;

$parser = new Parser();

$result = $parser->parse('* * * * *',Carbon::parse('2023-02-08 11:36:05'));

dump($result);