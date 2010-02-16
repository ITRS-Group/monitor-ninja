#!/bin/sh

ftp_server=reports.op5.com

ftp_send()
{
	echo "Sending $1 to $ftp_server"
	lftp $ftp_server -e "cd upload; put $1; exit"
}

if [ "$1" -a -f "$1" ]; then
	ftp_send "$1"
	exit $?
fi

[ "$1" ] && tarfile="$1.tar.bz2" || tarfile=logs-config-$(date +%s).tar.bz2

cfg_files=$(mktemp /tmp/config-files.XXXXXX)
sed -n 's/^cfg_file=//p' /opt/monitor/etc/nagios.cfg > $cfg_files
for d in $(grep ^cfg_dir /opt/monitor/etc/nagios.cfg); do
	find $d -type f -name "*.cfg" >> $cfg_files
done
tar -p -P -c -v -j -f $tarfile \
	$(cat $cfg_files) \
	/opt/monitor/var/nagios.log \
	/opt/monitor/var/archives/nagios-*.log

ftp_send $tarfile
