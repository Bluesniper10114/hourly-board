# Docker development environment
After firing up the docker containers:
Visit http://localhost:4200 to see the public billboard (angular / hb-client)
Visit http://localhost/management after up to see the management tool (php / hb-web).
The api is located at http://localhost/api/v1 (hb-web)

# Known issues

Since http://localhost:4200 and localhost are two different base routes, you'll run into issues when the frontend is trying to access the API.
Please enable CORS in your browser
https://en.wikipedia.org/wiki/Cross-origin_resource_sharing

## Common Tasks
Populated version: the database is pre-populated with data and the DATE is locked to Feb 1st 2019.
The time will correspond to the UTC time of the day on Feb 1st 2019. E.g. if it's 10:30am Mar 3rd 2019, it will be 10:30am Feb 1st 2019 on the
dev system.

### Start
To start the development environment populated, run `docker-compose -f docker-compose-populated.yml up -d`.
To start the development environment and wait for the latest update script to apply, run `docker-compose up -d`.

### View logs
To view logs from all containers, run `docker-compose logs -f`.
To view logs from a single container, run `docker-compose logs -f php`. (or mssql, or node, or sqlcmd)

### Stop
To stop the populated development environment, run `docker-compose -f docker-compose-populated.yml down`.
To stop the development environment, run `docker-compose down`.

### To Update
If container image configs are updated, OR if you need to reset your database you'll need to rebuild. After 
the first time, it runs quickly.
Populated version: `docker-compose -f docker-compose-populated.yml build`.
Populated version: `docker-compose build`.

### Run Command in Container
`docker-compose exec [container name] [command]`

For example, to open a shell in the webserver container (like SSH) : 
`docker-compose exec php bash`

Or to run phpstan tests (docker/php/tests.sh)
`docker-compose exec php runtest`

Or to run phpstan 
'docker-compose exec php php vendor/bin/phpstan analyse --configuration=phpstan.neon --level=max src mvc-php settings language management'

Note: the first "php" after exec is the machine name, the second "php" is the 
actual executable name

## First Time setup
1. Create a folder ~/developer/hb
2. `git clone --recurse-submodules https://bitbucket.org/poweb/hb-docker.git`
3. `cd hb-docker`

4.  Assuming you want to work with branch "develop". Otherwise, follow your feature-branch in all submodules
    checkout develop
    cd hb-web | checkout develop | cd..
    cd hb-client | checkout develop | cd..
    cd hb-sql | checkout develop | cd..

    e.g. replace "develop" with "feature/TRW-951-Angular-Frontend" if you need another feature branch

4. copy sample.env to .env and update the base path to the hb-docker folder (e.g. ~/developer/hb/hb-docker)
5. `docker-compose -f docker-compose-populated.yml build`
6. `docker-compose -f docker-compose-populated.yml up`
7. http://localhost:4200
8. http://localhost/management (username: assy, password test)

## Installing Docker

### Linux
Install docker using normal methods.
Typically `curl https://get.docker.com/ | bash` works well for me - other methods should work fine as well.
Ensure you also install docker-compose, which is a seperate tool.

### Windows

https://store.docker.com/editions/community/docker-ce-desktop-windows?ref=login


OR (not fully tested):

```
choco install docker -y
choco install docker-machine -y
choco install docker-compose -y
choco install virtualbox -y
docker-machine create -d virtualbox dev

# ensure the new machine is running
docker-machine ls 

# Test the new machine
docker run hello-world
```

## Windows 7 / Windows 10
1. If you are on Windows 7 / 10, open sample.env and follow instructions there. You will need to specify the local path to the repo

2. Open Docker Settings \ Shared Drives and check the drive where the repo resides.
3. During the docker installation, make sure to configure docker using Linux containers, not Windows containers.
If you missed that you have the option to reconfigure: open the Docker taskbar menu and select "Switch to Linux containers" 


Note that there are multiple ways to install docker on windows and mac, you may find it useful to use some of the other methods available.

## Docker Cleanup
https://docs.docker.com/config/pruning/

to prunes images, containers, and networks
$ docker system prune

if you want to also prune volumes, add the --volumes flag:
$ docker system prune --volumes