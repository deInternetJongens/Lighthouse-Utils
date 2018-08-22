#!/usr/bin/env bash
php vendor/bin/phpunit --log-junit=build/test-reports/phpunit.xml --coverage-text
