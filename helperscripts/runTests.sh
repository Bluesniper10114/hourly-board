#!/bin/bash


echo "Note - this must be run while containers are running"
docker-compose exec php runtest
