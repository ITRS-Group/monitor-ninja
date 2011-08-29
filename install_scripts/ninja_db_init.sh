#!/bin/bash -e

# setup the db tables required for Ninja

db_user=root
db_pass=

if [ $# -ge 1 ]
then
	prefix=$1
else
	prefix="/opt/monitor/op5/ninja";
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
	run_sql_file "$db_login_opts" "$prefix/install_scripts/ninja.sql"
fi

db_ver=$(mysql $db_login_opts -Be "SELECT version FROM ninja_db_version" merlin 2>/dev/null | sed -n \$p)

# check if we should add recurring_downtime table
# and if old monitor_reports tables should be moved to merlin
if [ "$db_ver" = '1' ]
then
	# add table for recurring_downtime
	echo "Installing database table for Recurring Downtime"
	run_sql_file "$db_login_opts" "$prefix/install_scripts/recurring_downtime.sql"

	# check if we should import data fr monitor_reports
	is_new_reports=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version" merlin 2>/dev/null)
	if [ $? -ne 0 ]
	then
		# doesn't exist - make sure we upgrade if necessary
		echo "Report tables doesn't seem to exist in merlin - installing"
		sh $prefix/op5-upgradescripts/merlin-reports-db-upgrade.sh /opt/monitor

		# move old data from monitor_reports -> merlin if monitor_reports exists
		is_old_reports=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version" monitor_reports 2>/dev/null)
		if [ $? -ne 0 ]
		then
			echo "monitor_reports db was not found which is OK.";
		else
			/usr/bin/env php $prefix/op5-upgradescripts/move_reports_tables.php $prefix $db_user $db_pass
		fi
		mysql $db_login_opts -Be "UPDATE ninja_db_version SET version=2" merlin 2>/dev/null

		# let's check this once more to be sure
		is_reports=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version" merlin 2>/dev/null)
		if [ $? -ne 0 ]
		then
			echo "Ooops - this is bad. All the info from old monitor_reports should have been transferred"
			echo "but this doesn't seem to be the case."
		else
			mysql $db_login_opts -Be "DROP database monitor_reports" 2>/dev/null
		fi
	fi
else
	sh $prefix/op5-upgradescripts/merlin-reports-db-upgrade.sh $prefix
fi

if [ -n "$db_ver" ]
then
	if [ "$db_ver" -lt '3' ]
	then
		echo "Renaming columns"
		mysql -f $db_login_opts merlin < $prefix/op5-upgradescripts/ninja_db_upgrade.sql 2>/dev/null
		mysql $db_login_opts merlin -Be "UPDATE ninja_db_version SET version=3" 2>/dev/null
	fi

	if [ "$db_ver" -lt '4' ]
	then
		echo "Adding saved searches"
		mysql -f $db_login_opts merlin < $prefix/op5-upgradescripts/saved_searches.sql 2>/dev/null
		mysql $db_login_opts merlin -Be "UPDATE ninja_db_version SET version=4" 2>/dev/null
	fi
fi

