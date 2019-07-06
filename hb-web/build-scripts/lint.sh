#!/bin/bash

# Built to be run in the context of bitbucket pipelines
# May not run properly outside that context

# This only checks the currently committed php files

#jobFail=0;

#  for file in $(git diff-tree --no-commit-id --name-only -r $BITBUCKET_COMMIT | grep "php\$") ; do
#    echo -e "\n\n-----------\nLinting $file\n";
#    php ./vendor-offline/phpcheckstyle/phpcheckstyle/run.php --src ./$file --config ./psr2.cfg.xml || jobFail=1;
#  done;
#

jobFail=0;

php ./vendor/phpcheckstyle/phpcheckstyle/run.php --src ./src --config ./psr2.cfg.xml --outdir ./doc/style-report || jobFail=1;

  if [[ "$jobFail" = "1" ]] ; then
    echo -e "\n\nLINTING FAILED!";
    exit 1;
  fi
