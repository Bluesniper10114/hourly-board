#!/bin/bash

# Built to be run in the context of bitbucket pipelines
# May not run properly outside that context

jobFail=0;
  for f in `find $dir -type f -name "*.php"  -and -not -path "*vendor/*"` ; do

    php -l "$f" 2>&1 | grep -v "No syntax errors detected" | grep -v "PHP Warning"

    if [[ $? -eq 0 ]] ; then
      jobFail=1;
      echo "FAIL on file $f";
    fi
  done


  if [[ "$jobFail" = "1" ]] ; then
    echo -e "\n\nSyntax check FAILED!";
    exit 1;
  fi

