#!/bin/bash

sqlUs="SA"
sqlPw="TestPasswordH@er@"
sqlSv="mssql"
sqlDb="MultiCodeBoard"

printf "restoreDemo=$restoreDemo"
printf "doDeployment=$doDeployment"
printf "doInitialisation=$doInitialisation"

printf "Attempting to connect. Retrying until server comes online"

# Wait for DB server to come online - 'select getdate()' is just
# a test query to ensure it's returning query results properly.
# Keep looping until it works.

while true; do
  printf "."
  sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw"  -Q "select getdate()" -l 1 &> /dev/null
  offline="$?"

  # Once the return code of the cmd is zero, we can exit the loop and continue
  if [[ "$offline" = "0" ]] ; then
    break;
  fi

  sleep 0.1;

done

echo "";
echo "";

# Server has been found to be online
echo "Server is now online! Continuing."

echo "Step 1/3: Restoring database"

# Next check if the DB already exists

# sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw" -Q "select * from sys.databases where name = '$sqlDb'" -l 1
# echo "select * from sys.databases where name = '$sqlDb'"
# exit;

doesDbExist=`sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw" -Q "select count(1) from sys.databases where name = '$sqlDb'" -l 1 | head -n 3 | tail -1 | awk '{print \$1}'`

if [[ "$doesDbExist" = "0" ]] ; then

  # Finally, if the DB does not exist, import the dump
  echo "Database '$sqlDb' does not exist. Running import now."
  if [[ "$restoreDemo" = "1" ]] ; then
    sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw" -i /var/opt/mssql/backup/restore_demo.sql;
  else
    sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw" -i /var/opt/mssql/backup/restore.sql;
  fi

  # Exit with the code from the sqlcmd run to properly return errors
  if [[ "$?" -ne "0" ]] ; then
    echo "Initial loading of database FAILED"
    exit $?;
  fi

  export gitBaseFolder="/opt/sql/" 
  export DatabaseName="$sqlDb" 

  if [[ "$doDeployment" = "1" ]] ; then
    # Next step - parse the XML file to run the migrations which are needed
    echo "";
    echo "";
    echo "Step 2/3: Running deploy SQL scripts";
    echo "";

    for sql in `xmlstarlet sel -t -v "//Deploy/File[@Applied!='true']/text()" /opt/sql/Settings.xml` ; do
      sqlfile="/opt/sql${sql//\\/\/}"
      echo " -- Running script $sqlfile";
      sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw" -i "$sqlfile";
    done

    if [[ "$doInitialisation" = "1" ]] ; then

      echo "Step 3/3: Running post-deploy scripts";
      sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw" -i "/opt/sql/steps/post-deployment/run.sql";
    fi
  fi

else
  buildNumber=`sqlcmd -S "$sqlSv" -U "$sqlUs" -P "$sqlPw" -Q "select max(id) from [$sqlDb].[ver].[Version]" -l 1 | head -n 3 | tail -1 | awk '{print \$1}'`

  # Otherwise, if it does - note that in the logs and move on
  echo "Database '$sqlDb' already exists (latest build number is $buildNumber) - skipping provisioning script."
fi

# # uncomment this part to keep the sqlcmd container online
# while :
# do
# 	echo "Press [CTRL+C] to stop.."
# 	sleep 1
# done


# then, in a terminal, run SQL commands like this:
# docker-compose exec sqlcmd sqlcmd -S "mssql" -U "SA" -P "TestPasswordH@er@" -Q "select max(id) from [MultiCodeBoard].[ver].[Version]" -l 1 | head -n 3 | tail -1 | awk '{print \$1}'
