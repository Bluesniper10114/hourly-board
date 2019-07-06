# Hourly Board SQL Project

# Requirements#
1. You must have PowerShell 4.x installed
2. Microsoft SQL Server must be installed.

# Overview #
This repo is needed to restore your SQL Server database to a certain development phase.
The "master" branch will always contain the live deployment along with a tag (e.g. v1.2.3-5).
For the live deployment a backup file exists with everything that is currently deployed on the live machine. The backup file has the following naming format:

multicodeboard_date_v1_2_3-5.bak

As you can see the last few characters reflect the tag of the master branch.

# The <develop> Branch #

The <develop> branch is the most advanced branch tested.
To restore your database to that state, you need:
0. To checkout and pull the latest <develop> branch
1. The backup file described above (you usually get that from a central download location e.g. on OneDrive)
2. You need to copy the backup file in some "bak" folder (typically at the same level as this repo)
3. You need to edit LocalSettings.xml to reflect the location if the backup file (tag <BackUpFile>)
4. Then, from the root folder of the repo run .\restore_deploy.ps1 and asnwer "y" to all questions.
For any other branch, just follow the same steps, but pull the respective branch.
If you want to completely reset the database, run .\restore_deploy.ps1 again

# First time setup#
If this is the first time you setup your repo, there are a few things you should do in the LocalSettings.xml file.
1. First, create a LocalSettings.xml file from LocalSettings.xml.example
2. Enter the full path to this repo (e.g. c:\developer\mc-hb\hb-sql). Do not use spaces, do not use something like ".\"
3. Enter the full path to the backup folder (c:\developer\mc-hb\bak)
4. Edit the DatabaseServer and RestoreLocation to reflect the installation of your SQL Server
5. DatabaseName must be "MultiCodeBoard"
6. Follow the steps 1,2,3 (described in the section "The <develop> Branch") to direct the scripts to your backup file



