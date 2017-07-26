#!/bin/sh

# Set up regnum DB
# Will exit if there is already databases in postgres.  Add a -f option to the command line to override this
# You probably want to run this script as the postgres superuser - pgsql or whatever user your OS sets up.
# This should be the same user you ran 'initdb' as.

[ -z "$1" ] && echo "Usage: $0 password [-f]\n\tThe password is for setting up the regnum user" && exit

out="$(psql -l | grep regnum | wc -l | tr -d " \n"; echo " $?")"

[ "${out% ?}" != 0 -a "$2" != '-f' ] && echo "Error: There are already databases in postgres.  If you're sure you want to continue anyways, add a -f flag to this command" && exit
[ "${out#? }" != 0 ] && echo "Error from psql" && exit

regnum_dir=${0%/*}
echo "Setting up regnum DB..."
psql -f $regnum_dir/db.sql postgres
psql -c "ALTER USER regnum PASSWORD '$1';" postgres
echo "Done!"
