#!/usr/bin/env bash

if (( "$#" != 1 ))
then
    echo "The target cannot be empty"
    exit 1
fi

/usr/bin/php vendor/bin/phpunit --bootstrap=tests/bootstrap.php $1