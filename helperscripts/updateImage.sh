#!/bin/bash

echo "Updating containers to latest version";
docker-compose pull ; docker-compose build ;
