#!/bin/bash

# PHPStan Runner Script

cd /var/www/html;

php vendor/bin/phpstan \
    analyse --configuration=phpstan.neon \
    --level=max \
    src mvc-php settings language management;
