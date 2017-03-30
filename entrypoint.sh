#!/bin/bash sh

composer update

phpunit

echo "Tests are done"

tail -f /dev/null