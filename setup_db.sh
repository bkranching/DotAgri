#!/bin/sh

# Set up regnum DB
# Will exit if there is already databases in postgres.  Add a -f option to the command line to override this
# You probably want to run this script as the postgres superuser - pgsql or whatever user your OS sets up.
# This should be the same user you ran 'initdb' as.

[ -z $1 ] && echo "Usage: $0 password [-f]\n\tThe password is for setting up the regnum user" && exit

count=$(psql -l | wc -l | tr -d ' ')
retval=$?

[ $count -gt 0 -a $2 != '-f' ] && echo "Error: There are already databases in postgres.  If you're sure you want to continue anyways, add a -f flag to this command" && exit
[ $retval != 0 ] && echo "Error from psql" && exit

regnum_dir=${0%/*}
echo "Setting up regnum DB..."
sed -I '' 's/%%%password%%%/$1/' $regnum_dir/db.sql
psql -f $regnum_dir/db.sql
sed -I '' 's/$1/%%%password%%%/' $regnum_dir/db.sql
echo "Done!"
