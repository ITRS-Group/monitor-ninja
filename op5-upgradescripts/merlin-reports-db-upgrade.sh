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
target_sla_version=8
target_avail_version=8
target_sched_version=7

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

sla_ver=$(mysql $db_login_opts -Be "SELECT version FROM sla_db_version" merlin 2>/dev/null | sed -n \$p)

if [ "$sla_ver" = "" ]
then
	echo "Installing database tables for SLA report configuration"
	run_sql_file "$db_login_opts" "$prefix/op5-upgradescripts/sla_v1.sql"
	sla_ver=$(mysql $db_login_opts -Be "SELECT version FROM sla_db_version"   merlin 2>/dev/null | sed -n \$p)
fi


while [ "$sla_ver" -lt "$target_sla_version" ]
do
	case "$sla_ver" in
	[5-7])
		new_ver='8'
		upgrade_script="$prefix/op5-upgradescripts/sla_v5_to_v8.sql"
		;;
	*)
		new_ver=`expr $sla_ver + 1 `
		upgrade_script="$prefix/op5-upgradescripts/sla_v${sla_ver}_to_v${new_ver}.sql"
		;;
	esac

	echo -n "Upgrading SLA tables from v${sla_ver} to v${new_ver} ... "
	if [ -r "$upgrade_script" ]
	then
		run_sql_file "$db_login_opts" $upgrade_script
		mysql $db_login_opts -Be "UPDATE sla_db_version SET version = '$new_ver'" merlin 2>/dev/null
		echo "done."
	else
		echo "SCRIPT MISSING."
		echo "Tried to use $upgrade_script"
	fi

	sla_ver=$new_ver
done


avail_ver=$(mysql $db_login_opts -Be "SELECT version FROM avail_db_version" merlin 2>/dev/null | sed -n \$p)

if [ "$avail_ver" = "" ]
then
	echo "Installing database tables for AVAIL report configuration"
	run_sql_file "$db_login_opts" "$prefix/op5-upgradescripts/avail_v1.sql"
	avail_ver=$(mysql $db_login_opts -Be "SELECT version FROM avail_db_version" merlin 2>/dev/null | sed -n \$p)
fi


while [ "$avail_ver" -lt $target_avail_version ]
do
	case "$avail_ver" in
	[2-4])
		new_ver=5
		upgrade_script="$prefix/op5-upgradescripts/avail_v2_to_v5.sql"
		;;
	[6-7])
		new_ver=8
		upgrade_script="$prefix/op5-upgradescripts/avail_v6_to_v8.sql"
		;;
	*)
		new_ver=`expr $avail_ver + 1 `
		upgrade_script="$prefix/op5-upgradescripts/avail_v${avail_ver}_to_v${new_ver}.sql"

		;;
	esac

	echo -n "Upgrading AVAIL tables from v${avail_ver} to v${new_ver} ... "
	if [ -r "$upgrade_script" ]
	then
		run_sql_file "$db_login_opts" $upgrade_script
		mysql $db_login_opts -Be "UPDATE avail_db_version SET version = '$new_ver'" merlin 2>/dev/null
		echo "done."
	else
		echo "SCRIPT MISSING."
		echo "Tried to use $upgrade_script"
	fi

	avail_ver=$new_ver
done

# check that we have the scheduled reports tables in merlin
sched_db_ver=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version" merlin 2>/dev/null | sed -n \$p)

if [ "$sched_db_ver" = "" ]
then
	# old scheduled reports hasn't yet been moved into merlin
	# from monitor_gui so let's do so and set the db_version properly
	echo "Installing database tables for scheduled reports configuration"
	upgrade_script="$prefix/op5-upgradescripts/scheduled_reports.sql"
	run_sql_file "$db_login_opts" $upgrade_script
	sched_db_ver=$(mysql $db_login_opts -Be "SELECT version FROM scheduled_reports_db_version"   merlin 2>/dev/null | sed -n \$p)
fi

sched_db_ver=$(echo $sched_db_ver | cut -d '.' -f1)
while [ "$sched_db_ver" -lt "$target_sched_version" ]; do
	case "$sched_db_ver" in
	[1-5])
		sched_db_ver=5
		new_ver=6
		upgrade_script="$prefix/op5-upgradescripts/scheduled_reports_v${sched_db_ver}_to_v${new_ver}.sql"
		;;
	*)
		new_ver=`expr $sched_db_ver + 1`
		upgrade_script="$prefix/op5-upgradescripts/scheduled_reports_v${sched_db_ver}_to_v${new_ver}.sql"
		;;
	esac

	echo -n "Upgrading scheduled reports tables from v${sched_db_ver} to v${new_ver}.sql ... "
	if [ -r "$upgrade_script" ]
	then
		run_sql_file "$db_login_opts" $upgrade_script
		mysql $db_login_opts -Be "UPDATE scheduled_reports_db_version SET version = '${new_ver}'" merlin 2>/dev/null
		echo "done."
	else
		echo "SCRIPT MISSING."
		echo "Tried to use $upgrade_script"
	fi

	sched_db_ver=$new_ver
done;

# make sure we have enabled scheduled summary reports
summary_schedules=$(mysql $db_login_opts -Be "SELECT identifier FROM scheduled_report_types WHERE identifier='summary'" merlin 2>/dev/null | sed -n \$p)
if [ "$summary_schedules" = "" ]
then
	mysql $db_login_opts -Be "INSERT INTO scheduled_report_types (name, identifier) VALUES('Alert Summary Reports', 'summary');" merlin 2>/dev/null
fi

# check if old tables exists and should be imported
old_sched_db_ver=$(mysql $db_login_opts -Be "SELECT version FROM auto_reports_db_version" monitor_gui 2>/dev/null | sed -n \$p)

if [ "$old_sched_db_ver" != "" ]
then
	# import old schedules if any
	echo "Importing old scheduled reports"
	/usr/bin/env php "$prefix/op5-upgradescripts/import_schedules.php"
fi

echo "Database upgrade complete."
