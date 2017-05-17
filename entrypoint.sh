#!/bin/bash sh

composer update

phpunit

phpstan analyse DependencyInjection/ Entity/ Exception/ Manager/ Resources/ Twig/ Tests/ --level=4

echo "Tests are done"

tail -f /dev/null