#!/usr/bin/env bash

osName=`uname`

if [[ "$osName" == 'Darwin' ]]; then
    vendor/bin/phpcbf --standard=CS-DIJ.xml ./build ./config ./src ./tests
fi

vendor/bin/phpcs --standard=CS-DIJ.xml ./build ./config ./src ./tests

find -L ./build ./config ./src ./tests -name '*.php' -print0 | xargs -0 -n 1 -P 4 php -l

php vendor/bin/phpunit --log-junit=build/test-reports/phpunit.xml --coverage-text
