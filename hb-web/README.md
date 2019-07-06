# README #

### What is this repository for? ###

* Hourly Board MultiCode project
* Allows Target settings and KPI visualisation on the production plant shopfloor
* Components: Web Admin (this repo), Web Client (this repo), SQL Database (integrated with Oracle)

### Developer Environment ###

* Code style: PSR-2, phpcheckstyle
* Code quality: phpstan (max level), phpunit (testing and coverage check)
* Package manager: composer
* Versioning: semver
* Code source control: git
* Time tracking: git commit -m "HB-1 #time 1h Comments" (tracks one hour for the HB-1 task)
* Language: PHP 7.0, PDO
* Containers: 1. Apache PHP | 2. MS SQL Server on Linux (infrastructure will be provided)
* if you use javascript, use http://babeljs.io to translate your next generation classes into backwards compatible javascript

### Customer configuration: ###

* IIS on Windows 7, or W10 or Windows Server2012 R2. with php 7.0 or above.
* SQL Server 2012
* IE 11 on user PCs for the management tool (adminLTE)
* Raspberry PI with Chromium browser (but can vary, depending on the RPI version)

This README would normally document whatever steps are necessary to get your application up and running.

### How do I get set up? ###

* git pull the web repo
* git pull the sql repo
* download the latest sql server backup file
* start the docker containers  (instructions tbd)
* change the local web configuration file as needed

* On the live machine, please check if .user.ini works as setup in hb-web

## How do I submit code?

* work on your assigned branch or feature branch
* commit often, no need to push more than once a day, unless needed by collaborators
* commit message is standard: XXX-123 #time 1d 2h 30m Add some commit message
    where XXX-123 is the JIRA task assigned to you and the time is the EFFECTIVE development time (no coffee time, sorry)
* test and lint your code before submitting

* hb-web:
    ./phpstan.ps1
    ./lint.ps1  

### Who do I talk to? ###

* Marian Brostean, marianb@profidocs.com, Skype: johannbro
