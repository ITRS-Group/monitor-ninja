%define daemon_user monitor
%define htmlroot /var/www/html
%define httpconfdir httpd/conf.d
%define phpdir /usr/share/php
%define daemon_group apache

Name: monitor-ninja
Version: %{op5version}
Release: %{op5release}%{?dist}
License: GPLv2 and LGPLv2 and ASL 2.0 and BSD and MIT and (MIT or GPL+) and (MIT or GPLv2+)
Vendor: op5 AB
BuildRoot: %{_tmppath}/%{name}-%{version}
Summary: op5 monitor ninja
Group: op5/monitor
Prefix: /opt/monitor/op5/ninja
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
BuildRequires: python
BuildRequires: doxygen
BuildRequires: graphviz
Requires: php >= 5.3
Requires: php-ldap
Requires: php-pecl-apc
BuildRequires: php >= 5.3
BuildRequires: shadow-utils
%if 0%{?rhel} >= 6 || 0%{?rhel_version} >=600 || 0%{?centos_version} >=600
Requires: php-process
Requires: php-mbstring
BuildRequires: php-process
%endif
%if 0%{?rhel} >= 7
# For stack trace info
Requires: psmisc
Requires: pciutils
%endif

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
Requires: php-phpunit-PHPUnit

Requires: openldap-servers
# For performance graph links on extinfo
Requires: monitor-pnp

Requires: gcc
Requires: phantomjs
%if 0%{?rhel} <= 6
Requires: ruby20
Requires: ruby20-devel
%else
Requires: ruby
Requires: ruby-devel
%endif

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
%if 0%{?rhel} >= 7
%else
make docs
%endif


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

mkdir -p %buildroot/etc/cron.d/
install -m 644 install_scripts/scheduled_reports.crontab %buildroot/etc/cron.d/scheduled-reports
install -m 644 install_scripts/recurring_downtime.crontab %buildroot/etc/cron.d/recurring-downtime
install -d %buildroot%_sysconfdir/logrotate.d
install -pm 0644 op5build/monitor-ninja.logrotate %{buildroot}%_sysconfdir/logrotate.d/monitor-ninja

# executables
for f in cli-helpers/apr_md5_validate \
		install_scripts/ninja_db_init.sh; do
	chmod 755 %buildroot%prefix/$f
done

mkdir -p %buildroot/opt/monitor/op5/nacoma/hooks/save
install -m 755 install_scripts/nacoma_hooks.py %buildroot/opt/monitor/op5/nacoma/hooks/save/ninja_hooks.py

mkdir -p %buildroot%_sysconfdir/%{httpconfdir}
%if 0%{?rhel} >= 7
install -m 640 op5build/ninja.httpd-conf.el7 %buildroot%_sysconfdir/%{httpconfdir}/monitor-ninja.conf
%else
install -m 640 op5build/ninja.httpd-conf %buildroot%_sysconfdir/%{httpconfdir}/monitor-ninja.conf
%endif

sed -i 's/Ninja/op5 Monitor/' %buildroot%prefix/application/media/report_footer.html

mkdir -p %buildroot%prefix/application/config/custom
install -m 755 test/configs/kohana-configs/exception.php %buildroot%prefix/application/config/custom/exception.php
rm %buildroot%prefix/test/configs/kohana-configs/exception.php

%pre
# This needs to be removed for us to be able to upgrade ninja 2.0.7
# for some reason.
if test -d %buildroot%prefix/application/vendor/phptap/.git; then
	rm -rf %buildroot%prefix/application/vendor/phptap/.git
fi


%post
# Verify that mysql-server is installed and running before executing sql scripts
$(mysql -Be "quit" 2>/dev/null) && MYSQL_AVAILABLE=1
if [ -n "$MYSQL_AVAILABLE" ]; then
  pushd %prefix
    sh install_scripts/ninja_db_init.sh
    php install_scripts/migrate_tac_hostperf_to_listview.php
  popd
else
  echo "WARNING: mysql-server is not installed or not running."
  echo "If a database is to be used you need to maually run:"
  echo "  %prefix/install_scripts/ninja_db_init.sh"
  echo "to complete the setup of %name"
fi

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

sed -i 's/expose_php = .*/expose_php = off/g' /etc/php.ini

%files
%prefix
%attr(644,root,root) /etc/cron.d/*
%attr(755,root,root) /opt/monitor/op5/nacoma/hooks/save/ninja_hooks.py
%attr(644,root,root) /opt/monitor/op5/nacoma/hooks/save/ninja_hooks.pyc
%attr(644,root,root) /opt/monitor/op5/nacoma/hooks/save/ninja_hooks.pyo
%attr(-,root,%daemon_group) %_sysconfdir/%{httpconfdir}/monitor-ninja.conf
%attr(755,root,root) /usr/bin/op5-manage-users

%dir %attr(775,%daemon_user,%daemon_group) %_sysconfdir/op5
%config(noreplace) %attr(660,%daemon_user,%daemon_group) %_sysconfdir/op5/*.yml

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
%if 0%{?rhel} >= 7
%else
%exclude %prefix/Documentation
%endif

%files devel
%defattr(-,root,root)
%phpdir/op5/ninja_sdk
%if 0%{?rhel} >= 7
%else
%prefix/Documentation
%endif

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

%clean
rm -rf %buildroot
