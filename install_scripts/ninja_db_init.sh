#!/bin/sh

# setup the db tables required for Ninja

db_user=root
db_pass=

if [ $# -ge 1 ]
then
	prefix=$1
else
	prefix="/opt/monitor";
fi

run_sql_file () # (db_login_opts, sql_script_path)
{
	db_login_opts=$1
	sql_script_path=$2

	mysql $db_login_opts merlin < $sql_script_path >/dev/null 2>/dev/null
}

if [ "$db_pass" != "" ]
then
	db_login_opts="-u$db_user -p$db_pass"
else
	db_login_opts="-u$db_user"
fi

db_ver=$(mysql $db_login_opts -Be "SELECT version FROM ninja_db_version" merlin 2>/dev/null | sed -n \$p)

if [ "$db_ver" = '' ]
then
	# nothing found, insert ninja.sql
	echo "Installing database tables for Ninja GUI"
	run_sql_file $db_login_opts "$prefix/op5/ninja/install_scripts/ninja.sql"
fi

# import users annd authorization data
/usr/bin/env php "$prefix/op5/ninja/install_scripts/auth_import.php"