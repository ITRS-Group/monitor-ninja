%define daemon_user monitor
%define htmlroot /var/www/html
%define httpconfdir httpd/conf.d
%define phpdir /usr/share/php
%define daemon_group apache
%define base_prefix /opt/monitor
%define nacoma_hooks_path %{base_prefix}/op5/nacoma/hooks/save

Name: monitor-ninja
Version: %{op5version}
Release: %{op5release}%{?dist}
License: GPLv2 and LGPLv2 and ASL 2.0 and BSD and MIT and (MIT or GPL+) and (MIT or GPLv2+)
Vendor: op5 AB
BuildRoot: %{_tmppath}/%{name}-%{version}
Summary: op5 monitor ninja
Group: op5/monitor
Prefix: %{base_prefix}/op5/ninja
Obsoletes: monitor-gui <= 3.5.13
Obsoletes: monitor-reports-gui <= 1.4.9
Obsoletes: op5-nagios-gui-core <= 4.0.4
Obsoletes: php-op5lib < %version
Provides: monitor-gui = %version
Provides: monitor-reports-gui = %version
Provides: op5-nagios-gui-core = %version
Provides: php-op5lib = %version
Requires: wkhtmltopdf
Requires: op5-mysql
Requires: op5-monitor-supported-webserver
Requires: monitor-livestatus
Requires: op5-lmd
Requires: monitor-backup
Requires: op5-bootstrap
# Merlin creates our database
Requires: merlin
Requires: monitor-ninja-monitoring
BuildRequires: python2
BuildRequires: doxygen
BuildRequires: graphviz
Requires: python2
Requires: php
Requires: php-cli
Requires: php-json
Requires: php-ldap
Requires: php-pecl-apcu
BuildRequires: php-cli
BuildRequires: php-json
BuildRequires: shadow-utils
Requires: php-process
Requires: php-mbstring
BuildRequires: php-process
# For stack trace info
Requires: psmisc
Requires: pciutils
%{?systemd_requires}

Source: %name-%version.tar.gz
%description
Webgui for Naemon.

%package test
Summary: Test files for ninja
Group: op5/Monitor
Requires: monitor-ninja = %version
Requires: op5-naemon
Requires: monitor-livestatus
Requires: op5-lmd
Requires: monitor-nagvis
Requires: monitor-nacoma
Requires: monitor-plugin-check_dummyv2
Requires: php-phpunit-PHPUnit
Requires: op5int_webtest


# Note: openldap-servers is not available on EL8, so those RPM files are
# manually copied onto our internal repo server, as this is for tests only.
Requires: openldap-servers

# For performance graph links on extinfo
Requires: monitor-pnp

Requires: gcc
Requires: chromedriver
Requires: python3
Requires: ruby
Requires: ruby-devel

%description test
Additional test files for ninja

%package monitoring
Summary: Naemon and Livestatus module for ninja
Group: op5/monitor
Requires: op5-naemon
Requires: monitor-merlin
Requires: monitor-livestatus
Requires: op5-lmd

%description monitoring
Provides ORM, bindings and interfaces for Livestatus, Naemon and queryhandler.

%package devel
Summary: Development files for ninja
Group: op5/monitor
Requires: monitor-ninja = %version
Requires: doxygen
Requires: graphviz

%description devel
Development files files for ninja

%prep
%setup -q

%build
pushd cli-helpers
make
popd
make


%install
rm -rf %buildroot
mkdir -p -m 755 %buildroot%prefix
mkdir -p -m 775 %buildroot%prefix/upload
mkdir -p -m 775 %buildroot%prefix/application/logs
mkdir -p -m 775 %buildroot/var/log/op5/ninja

make install SYSCONFDIR=%buildroot%_sysconfdir PREFIX=%buildroot%prefix PHPDIR=%buildroot%phpdir ETC_USER=$(id -un) ETC_GROUP=$(id -gn) BINDIR=%buildroot/usr/bin

# copy everything and then remove what we don't want to ship
cp -r * %buildroot%prefix
for d in op5build monitor-ninja.spec ninja.doxy \
	example.htaccess cli-helpers/apr_md5_validate.c \
	README.md
do
	rm -rf %buildroot%prefix/$d
done

sed -i "s/\(IN_PRODUCTION', \)FALSE/\1TRUE/" \
	%buildroot%prefix/index.php
sed -i \
	-e 's,^\(.config..site_domain.. = .\)/ninja/,\1/monitor/,' \
	-e 's/^\(.config..product_name.. = .\)Ninja/\1ITRS OP5 Monitor/' \
	-e 's/^\(.config..version_info.. = .\)\/etc\/ninja-release/\1\/etc\/op5-monitor-release/' \
	%buildroot%prefix/application/config/config.php

cp op5build/favicon.ico \
	%buildroot%prefix/application/views/icons/
cp op5build/icon.png \
	%buildroot%prefix/application/views/icons/

find %buildroot -print0 | xargs -0 chmod a+r
find %buildroot -type d -print0 | xargs -0 chmod a+x

install -d %buildroot%_sysconfdir/logrotate.d
install -pm 0644 op5build/monitor-ninja.logrotate %{buildroot}%_sysconfdir/logrotate.d/monitor-ninja

# executables
for f in cli-helpers/apr_md5_validate \
		install_scripts/ninja_db_init.sh; do
	chmod 755 %buildroot%prefix/$f
done

install -D -m 755 install_scripts/nacoma_hooks.py %{buildroot}%{nacoma_hooks_path}/ninja_hooks.py
install -D -m 644 install_scripts/nacoma_hooks.pyc %{buildroot}%{nacoma_hooks_path}/ninja_hooks.pyc
install -D -m 644 install_scripts/nacoma_hooks.pyo %{buildroot}%{nacoma_hooks_path}/ninja_hooks.pyo

install -d %buildroot%_unitdir
install -D -m 644 -t %buildroot%_unitdir op5build/systemd/*.{service,timer}
install -D op5build/libexec/op5_scheduled_reports.py %buildroot%base_prefix/libexec/op5_scheduled_reports.py

install -D -m 640 op5build/ninja-httpd.conf %buildroot%_sysconfdir/%{httpconfdir}/monitor-ninja.conf
install -D -m 644 op5build/php-ninja.ini %buildroot%_sysconfdir/php.d/50-op5-ninja.ini
install -D -m 644 op5build/php-ninja-tests.ini %buildroot%_sysconfdir/php.d/52-ninja-tests.ini

install -D test/configs/kohana-configs/exception.php %buildroot%prefix/application/config/custom/exception.php
rm %buildroot%prefix/test/configs/kohana-configs/exception.php

%post
# Verify that mysql-server is installed and running before executing sql scripts
if mysql -Be "quit" 2>/dev/null; then
	%prefix/install_scripts/ninja_db_init.sh
	php %prefix/install_scripts/migrate_tac_hostperf_to_listview.php
else
	echo "WARNING: mysql-server is not installed or not running."
	echo "If a database is to be used you need to maually run:"
	echo "  %prefix/install_scripts/ninja_db_init.sh"
	echo "to complete the setup of %name"
fi

systemctl daemon-reload &>/dev/null || :
systemctl enable --now op5-scheduled-reports.service &>/dev/null || :
systemctl enable --now op5-recurring-downtime.timer &>/dev/null || :

# Cleanup symlinks we don't use anymore
for link in %{htmlroot}/monitor %{htmlroot}/ninja /op5/monitor/op5/ninja/op5 /opt/monitor/op5/ninja/css /opt/monitor/op5/ninja/js /opt/monitor/op5/ninja/images /opt/monitor/op5/ninja/stylesheets
do
	if [ -f $link ]; then
		rm -f $link
	fi
done

# Migrate auth and upgrade user-groups permissions
php %prefix/install_scripts/migrate_auth.php
# The line above can leave artifacts created by root, making ninja-backup fail
chown %daemon_user:%daemon_group %_sysconfdir/op5/*.yml
if [ -f %_sysconfdir/op5/ninja_menu.yml ]; then
	chown %daemon_group:%daemon_group %_sysconfdir/op5/ninja_menu.yml
fi

%postun
%systemd_postun_with_restart op5-scheduled-reports.service
%systemd_postun_with_restart op5-recurring-downtime.timer
if [ $1 -eq 0 ]; then
	systemctl disable op5-recurring-downtime.timer &>/dev/null || :
fi

%files
%base_prefix/*
%_unitdir/*
%{nacoma_hooks_path}/ninja_hooks.*
%attr(-,root,%daemon_group) %_sysconfdir/%{httpconfdir}/monitor-ninja.conf
%_sysconfdir/php.d/50-op5-ninja.ini
%attr(755,root,root) /usr/bin/op5-manage-users

%dir %attr(775,%daemon_user,%daemon_group) %_sysconfdir/op5
%config(noreplace) %attr(660,%daemon_user,%daemon_group) %_sysconfdir/op5/*.yml
%ghost %_sysconfdir/op5/ninja_menu.yml

%dir %attr(775,%daemon_user,%daemon_group) /var/log/op5
%dir %attr(775,%daemon_user,%daemon_group) /var/log/op5/ninja
%dir %attr(-,-,%daemon_group) %prefix/upload
%dir %attr(-,-,%daemon_group) %prefix/application/logs

%attr(440,%daemon_user,%daemon_group) %prefix/application/config/database.php
%config %_sysconfdir/logrotate.d/monitor-ninja

%phpdir/op5
%exclude %phpdir/op5/ninja_sdk
%exclude %prefix/src
%exclude %prefix/test
%exclude %prefix/modules/test
%exclude %prefix/modules/monitoring
%exclude %prefix/Makefile
%exclude %prefix/features
%exclude %prefix/application/config/custom/exception.php

%files devel
%defattr(-,root,root)
%phpdir/op5/ninja_sdk

%files monitoring
%prefix/modules/monitoring

%files test
%defattr(-,%daemon_user,%daemon_group)
%prefix/src
%prefix/features
%prefix/test
%prefix/modules/test
%prefix/Makefile
%prefix/application/config/custom/exception.php
%_sysconfdir/php.d/52-ninja-tests.ini

%clean
rm -rf %buildroot
