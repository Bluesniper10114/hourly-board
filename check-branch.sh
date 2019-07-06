hb_branch=`git branch | grep \* | cut -d ' ' -f2`
echo "hb-docker: $hb_branch"

cd hb-web
hb_branch=`git branch | grep \* | cut -d ' ' -f2`
echo "hb-web   : $hb_branch"

cd ../hb-client
hb_branch=`git branch | grep \* | cut -d ' ' -f2`
echo "hb-client: $hb_branch"

cd ../hb-sql
hb_branch=`git branch | grep \* | cut -d ' ' -f2`
echo "hb-sql   : $hb_branch"
cd ..

