#!/bin/bash

############
# Run startup commands
############

# If file does not exist, get clean copy local.config.php

if [ ! -f /var/www/html/settings/local.config.php ] ; then
  cp /tmp/config/local.config.php /var/www/html/settings/local.config.php
fi

# Install composer packages, including retry if failure
if [[ "$RUN_COMPOSER" == "true" ]] ; then
  echo "Installing composer DEV packages"
  composer install --no-progress ||
  composer install --no-progress ||
  composer install --no-progress ||
  composer install --no-progress || exit 1


else
  echo "Not installing composer packages since \$RUN_COMPOSER != 'true'";
fi

# Run the main apache daemon
exec apache2-foreground
