<?php

const BASE_PATH = __DIR__;

define('ROOT_PATH', dirname(BASE_PATH));
define('PROJECT_PATH', dirname(ROOT_PATH));
const ENV_FILE = ROOT_PATH . '/.env';


if (is_file(ENV_FILE)) {
    $data = parse_ini_file(ENV_FILE);
    foreach ($data as $key => $val) {
        $_ENV[$key] = $val;
    }
}

require ROOT_PATH . '/vendor/autoload.php';