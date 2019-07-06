#!/bin/bash

echo "Starting (press ctrl+c to push logs to background)"
docker-compose up -d ; docker-compose logs -f;
