#!/bin/bash

############
# Run startup commands
############

# If file does not exist, get clean copy package.json

# if [ ! -f /usr/src/app/package.json ] then 
#   rm /usr/src/app/package.json
# fi

# cp /tmp/package.json /usr/src/app/package.json

# install and cache app dependencies


# Only run rebuild if package.json is different at buildtime than now

BUILDTIME_FILE="/usr/src/package.json"
RUNTIME_FILE="/usr/src/app/package.json"


diff $BUILDTIME_FILE $RUNTIME_FILE &> /dev/null
retCode="$?";

if [[ "$retCode" != "0" ]] ; then
  echo "";
  echo "NOTE! This up is running more slowly because your node package.json has changed.";
  echo "Run a rebuild of this container to produce faster up in the future";
  echo "";
  npm install
  npm install -g @angular/cli
fi

# using proxy, for CORS...
# ng serve --host 0.0.0.0 --proxy-config proxy.conf.json
ng serve --host 0.0.0.0
