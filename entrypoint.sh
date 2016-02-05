#!/bin/bash sh

mkdir -p Tests/Functional/app/cache
rm -Rf Tests/Functional/app/cache/*

exec php entrypoint.php


