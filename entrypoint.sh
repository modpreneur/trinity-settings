#!/bin/bash sh

php Tests/Functional/bin/console.php doctrine:database:create
php Tests/Functionalbin/console.php doctrine:schema:update --force

exec php entrypoint.php


