#!/bin/bash

# setup the db tables required for Ninja

target_db_version=6
target_sla_version=9
target_avail_version=11
target_sched_version=8

db_user=merlin
db_pass=merlin
db_name=merlin

progname="$0"

show_usage()
{
        cat << END_OF_HELP

usage: $progname [options]

Where options can be any combination of:
  --help|-h                            Print this cruft and exit
  --db-user=<username>                 User merlin should use with db
  --db-pass=<password>                 Password for the db user
  --db-name=<name>                     Database name

END_OF_HELP
        exit 1
}

get_arg ()
{
        expr "z$1" : 'z[^=]*=\(.*\)'
}

while test "$1"; do
	case "$1" in
		--db-user=*)
			db_user=$(get_arg "$1")
			;;
		--db-user)
			shift
			db_user="$1"
			;;
		--db-pass=*)
			db_pass=$(get_arg "$1")
			;;
		--db-pass)
			shift
			db_pass="$1"
			;;
		--db-name=*)
			db_name=$(get_arg "$1")
			;;
		--db-name)
			shift
			db_name="$1"
			;;
		--help|-h)
			show_usage
			;;
		*)
			echo "Illegal argument. I have no idea what to make of '$1'"
			exit 1
			;;
	esac
	shift
done


prefix=$(dirname $0)"/.."

run_sql_file () # (db_login_opts, sql_script_path)
{
	db_login_opts=$1
	sql_script_path=$2

	mysql $db_login_opts < $sql_script_path >/dev/null 2>/dev/null
}

if [ "$db_pass" != "" ]
then
	db_login_opts="-u$db_user -p$db_pass $db_name"
else
	db_login_opts="-u$db_user $db_name"
fi

db_ver=$(mysql $db_login_opts -Be "SELECT version FROM ninja_db_version" 2>/dev/null | sed -n \$p)

if [ "$db_ver" = '' ]
then
	# nothing found, insert ninja.sql
	echo "Installing database tables for Ninja GUI"
	run_sql_file "$db_login_opts" "$prefix/sql/mysql/ninja.sql"
	db_ver=$(mysql $db_login_opts -Be "SELECT version FROM ninja_db_version" 2>/dev/null | sed -n \$p)
fi

if [[ "$db_ver" = '' ]]
then
	echo "Cannot connect to mysql, cannot install/upgrade database."
	exit 1
fi

function all_versions()
{
	db_ver="$1"
	new_ver=`expr $db_ver + 1`
	echo "Upgrading ninja db from v${db_ver} to v${new_ver}"
	run_sql_file "$db_login_opts" "$prefix/sql/mysql/ninja_db_v${db_ver}_to_v${new_ver}.sql"
	mysql $db_login_opts -Be "UPDATE ninja_db_version SET version=$new_ver" 2>/dev/null
	db_ver=$new_ver
}

while [ "$db_ver" -lt "$target_db_version" ]; do
	case "$db_ver" in
	1)
		# add table for recurring_downtime
		echo "Installing database table for Recurring Downtime"
		run_sql_file "$db_login_opts" "$prefix/sql/mysql/recurring_downtime.sql"
		mysql $db_login_opts -Be "UPDATE ninja_db_version SET version=2" 2>/dev/null
		db_ver=2
		;;
	4)
		php index.php cli/upgrade_auth
		all_versions 4
		;;
	*)
		all_versions "$db_ver"
		;;
	esac
done

sla_ver=$(mysql $db_login_opts -Be "SELECT version FROM sla_db_version" 2>/dev/null | sed -n \$p)

if [ "$sla_ver" = "" ]
then
	echo "Installing database tables for SLA report configuration"
	run_sql_file "$db_login_opts" "$prefix/sql/mysql/sla_v1.sql"
	sla_ver=$(mysql $db_login_opts -Be "SELECT version FROM sla_db_version" 2>/dev/null | sed -n \$p)
fi


while [ "$sla_ver" -lt "$target_sla_version" ]
do
	case "$sla_ver" in
	[5-7])
		new_ver='8'
		upgrade_script="$prefix/sql/mysql/sla_v5_to_v8.sql"
		;;
	*)
		new_ver=`expr $sla_ver + 1 `
		upgrade_script="$prefix/sql/mysql/sla_v${sla_ver}_to_v${new_ver}.sql"
		;;
	esac

	echo -n "Upgrading SLA tables from v${sla_ver} to v${new_ver} ... "
	if [ -r "$upgrade_script" ]
	then
		run_sql_file "$db_login_opts" $upgrade_script
		mysql $db_login_opts -Be "UPDATE sla_db_version SET version = '$new_ver'" 2>/dev/null
		echo "done."
	else
		echo "SCRIPT MISSING."
		echo "Tried to use $upgrade_script"
	fi

	sla_ver=$new_ver
done


avail_ver=$(mysql $db_login_opts -Be "SELECT version FROM avail_db_version" 2>/dev/null | sed -n \$p)

if [ "$avail_ver" = "" ]
then
	echo "Installing database tables for AVAIL report configuration"
	run_sql_file "$db_login_opts" "$prefix/sql/mysql/avail_v1.sql"
	avail_ver=$(mysql $db_login_opts -Be "SELECT version FROM avail_db_version" 2>/dev/null | sed -n \$p)
fi


while [ "$avail_ver" -lt $target_avail_version ]
do
	case "$avail_ver" in
	[2-4])
		new_ver=5
		upgrade_script="$prefix/sql/mysql/avail_v2_to_v5.sql"
		;;
	[6-7])
		new_ver=8
		upgrade_script="$prefix/sql/mysql/avail_v6_to_v8.sql"
		;;
	9)
		php $prefix/index.php cli/upgrade_excluded
		new_ver=10
		upgrade_script="$prefix/sql/mysql/avail_v${avail_ver}_to_v${new_ver}.sql"
		;;
	*)
		new_ver=`expr $avail_ver + 1 `
		upgrade_script="$prefix/sql/mysql/avail_v${avail_ver}_to_v${new_ver}.sql"

		;;
	esac

	echo -n "Upgrading AVAIL tables from v${avail_ver} to v${new_ver} ... "
	if [ -r "$upgrade_script" ]
	then
		run_sql_file "$db_login_opts" $upgrade_script
		mysql $db_login_opts -Be "UPDATE avail_db_version SET version = '$new_ver'" 2>/dev/null
		echo "done."
	else
		echo "SCRIPT MISSING."
		echo "Tried to use $upgrade_script"
	fi

	avail_ver=$new_ver
done

# check that we have the scheduled reports tables in merlin
sched_db_ver=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version" 2>/dev/null | sed -n \$p)

if [ "$sched_db_ver" = "" ]
then
	echo "Installing database tables for scheduled reports configuration"
	upgrade_script="$prefix/sql/mysql/scheduled_reports.sql"
	run_sql_file "$db_login_opts" $upgrade_script
	sched_db_ver=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version" 2>/dev/null | sed -n \$p)
fi

sched_db_ver=$(echo $sched_db_ver | cut -d '.' -f1)
while [ "$sched_db_ver" -lt "$target_sched_version" ]; do
	case "$sched_db_ver" in
	[1-5])
		sched_db_ver=5
		new_ver=6
		upgrade_script="$prefix/sql/mysql/scheduled_reports_v${sched_db_ver}_to_v${new_ver}.sql"
		;;
	*)
		new_ver=`expr $sched_db_ver + 1`
		upgrade_script="$prefix/sql/mysql/scheduled_reports_v${sched_db_ver}_to_v${new_ver}.sql"
		;;
	esac

	echo -n "Upgrading scheduled reports tables from v${sched_db_ver} to v${new_ver}.sql ... "
	if [ -r "$upgrade_script" ]
	then
		run_sql_file "$db_login_opts" $upgrade_script
		mysql $db_login_opts -Be "UPDATE scheduled_reports_db_version SET version = '${new_ver}'" 2>/dev/null
		echo "done."
	else
		echo "SCRIPT MISSING."
		echo "Tried to use $upgrade_script"
	fi

	sched_db_ver=$new_ver
done;

# make sure we have enabled scheduled summary reports
summary_schedules=$(mysql $db_login_opts -Be "SELECT identifier FROM scheduled_report_types WHERE identifier='summary'" 2>/dev/null | sed -n \$p)
if [ "$summary_schedules" = "" ]
then
	mysql $db_login_opts -Be "INSERT INTO scheduled_report_types (name, identifier) VALUES('Alert Summary Reports', 'summary');" 2>/dev/null
fi

echo "Database upgrade complete."
