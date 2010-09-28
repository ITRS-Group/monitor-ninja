#!/bin/sh

# Upgrades the reports tables in merlin database.
#
# TODO: target versions still hardcoded on certain places in this script
# TODO: nicer default prefix handling
# TODO: better error handling
#

db_user=root
db_pass=

# These are the versions (+1) that will be installed by running this script
target_sla_version=6
target_avail_version=7

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

sla_ver=$(mysql $db_login_opts -Be "SELECT version FROM sla_db_version" merlin 2>/dev/null | sed -n \$p)

if [ "$sla_ver" = "" ]
then
	echo "Installing database tables for SLA report configuration"
	run_sql_file $db_login_opts "$prefix/op5/ninja/op5-upgradescripts/sla_v1.sql"
	sla_ver=$(mysql $db_login_opts -Be "SELECT version FROM sla_db_version"   merlin 2>/dev/null | sed -n \$p)
fi


while [ "$sla_ver" -lt "$target_sla_version" ]
do
	case "$sla_ver" in
	[1-3])
		new_ver=`expr $sla_ver + 1 `
		upgrade_script="$prefix/op5/ninja/op5-upgradescripts/sla_v${sla_ver}_to_v${new_ver}.sql"

		echo -n "Upgrading SLA tables from v${sla_ver} to v${new_ver} ... "
		if [ -r "$upgrade_script" ]
		then
			run_sql_file $db_login_opts $upgrade_script
			echo "done."
		else
			echo "SCRIPT MISSING."
			echo "Tried to use $upgrade_script"
		fi
		;;
	4)
		# upgrade to latest
		upgrade_script="$prefix/op5/ninja/op5-upgradescripts/sla_v4_to_v5.sql"
		echo -n "Upgrading SLA tables to v5 ... "
		if [ -r "$upgrade_script" ]
		then
			run_sql_file $db_login_opts $upgrade_script
			mysql $db_login_opts -Be "UPDATE sla_db_version SET version = '5'" merlin 2>/dev/null
			echo "done."
		else
			echo "SCRIPT MISSING."
			echo "Tried to use $upgrade_script"
		fi
		break
		;;
	esac

	(( sla_ver++ ))
done


avail_ver=$(mysql $db_login_opts -Be "SELECT version FROM avail_db_version" merlin 2>/dev/null | sed -n \$p)

if [ "$avail_ver" = "" ]
then
	echo "Installing database tables for avail report configuration"
	run_sql_file $db_login_opts "$prefix/op5/ninja/op5-upgradescripts/avail_v1.sql"
	avail_ver=$(mysql $db_login_opts -Be "SELECT version FROM avail_db_version" merlin 2>/dev/null | sed -n \$p)
fi


while [ "$avail_ver" -lt $target_avail_version ]
do
	case "$avail_ver" in
	1)
		new_ver=`expr $avail_ver + 1 `
		upgrade_script="$prefix/op5/ninja/op5-upgradescripts/avail_v${avail_ver}_to_v${new_ver}.sql"

		echo -n "Upgrading Avail tables from v${avail_ver} to v${new_ver} ... "
		if [ -r "$upgrade_script" ]
		then
			run_sql_file $db_login_opts $upgrade_script
			echo "done."
		else
			echo "SCRIPT MISSING."
			echo "Tried to use $upgrade_script"
		fi
		;;
	4)
		# upgrade to latest
		upgrade_script="$prefix/op5/ninja/op5-upgradescripts/avail_v2_to_v5.sql"
		echo -n "Upgrading AVAIL tables to v5 ... "
		if [ -r "$upgrade_script" ]
		then
			run_sql_file $db_login_opts $upgrade_script
			mysql $db_login_opts -Be "UPDATE avail_db_version SET version = '5'" merlin 2>/dev/null
			echo "done."
		else
			echo "SCRIPT MISSING."
			echo "Tried to use $upgrade_script"
		fi
		;;
	5)
		# upgrade to latest
		upgrade_script="$prefix/op5/ninja/op5-upgradescripts/avail_v5_to_v6.sql"
		echo -n "Upgrading avail tables to v6 ... "
		if [ -r "$upgrade_script" ]
		then
			run_sql_file $db_login_opts $upgrade_script
			mysql $db_login_opts -Be "UPDATE avail_db_version SET version = '6'" merlin 2>/dev/null
			echo "done."
		else
			echo "SCRIPT MISSING."
			echo "Tried to use $upgrade_script"
		fi
		break
		;;
	esac

	(( avail_ver++ ))
done

# check that we have the scheduled reports tables in merlin
sched_db_ver=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version" merlin 2>/dev/null | sed -n \$p)

if [ "$sched_db_ver" = "" ]
then
	# old scheduled reports hasn't yet been moved into merlin
	# from monitor_gui so let's do so and set the db_version properly
	upgrade_script="$prefix/op5/ninja/op5-upgradescripts/scheduled_reports.sql"
	run_sql_file $db_login_opts $upgrade_script

	echo "Installing scheduled summary reports"
	upgrade_script="$prefix/op5/ninja/op5-upgradescripts/scheduled_reports_v2.sql"
	run_sql_file $db_login_opts $upgrade_script
	mysql $db_login_opts -Be "UPDATE scheduled_reports_db_version SET version = '2'" merlin 2>/dev/null

	# check if old tables exists
	old_sched_db_ver=$(mysql $db_login_opts -Be "SELECT version FROM auto_reports_db_version" monitor_gui 2>/dev/null | sed -n \$p)

	if [ "$old_sched_db_ver" != "" ]
	then
		# import old schedules if any
		echo "Importing old scheduled reports"
		/usr/bin/env php "$prefix/op5/ninja/op5-upgradescripts/import_schedules.php"
	fi
fi

echo "Database upgrade complete."
