OP5LIBCFG=test/configs/all-host_service-states/op5lib
PHPDIR=/usr/local/share/php

SYSCONFDIR := /etc
ETC_USER := apache
ETC_GROUP := apache

PHPUNIT := phpunit --debug --bootstrap test/bootstrap.php

all: generate-php

generate-php:
	php build.php

# All php "unit" tests that affects/needs a currently installed monitor should
# be tagged with @nonlocal, so that we can avoid them when running them
# locally. This also marks them as "badly written" in the sense that they need
# to be mocked out further. In the future, all unit tests should avoid
# affecting the current environment and thus the group @nonlocal should not be
# needed.
test-local: generate-php
	$(PHPUNIT) --exclude-group nonlocal test

test: generate-php
	make test-ci-prepare
	export OP5LIBCFG="$(OP5LIBCFG)"; $(PHPUNIT) test/; res=$$?; make test-ci-cleanup; exit $$res

test-ci-cleanup:
	rm -f application/config/custom/config.php
	rm -f application/config/custom/database.php
	if [ -e /tmp/ninja-test/var/merlin.pid ]; then kill $$(cat /tmp/ninja-test/var/merlin.pid) || :; fi
	if [ -e /tmp/ninja-test/nagios.cmd ]; then /bin/echo "[$$(date +%s)] SHUTDOWN_PROGRAM" >> /tmp/ninja-test/nagios.cmd; /bin/sleep 5; rm /tmp/ninja-test/nagios.cmd; fi

test-ci-prepare: test-ci-cleanup prepare-config
	chmod -R 0777 /tmp/ninja-test/var
	mkdir -m 0777 -p /tmp/ninja-test/var/spool/checkresults
	chown -R monitor:apache /tmp/ninja-test/
	source $$(rpm --eval %{_libdir})/merlin/install-merlin.sh; \
	db_name=merlin_test; \
	mysql -uroot -e "CREATE DATABASE IF NOT EXISTS $$db_name";\
	mysql -uroot -e "GRANT ALL ON $$db_name.* TO $$db_user@localhost IDENTIFIED BY '$$db_pass'"; \
	db_setup
	export OP5LIBCFG="$(OP5LIBCFG)"; install_scripts/ninja_db_init.sh --db-name=merlin_test
	/usr/bin/merlind -c /tmp/ninja-test/merlin.conf
	/usr/bin/asmonitor /usr/bin/naemon -d /tmp/ninja-test/nagios.cfg

test-php-lint:
	 for i in `find . -name "*.php"`; do php -l $$i > /dev/null || exit "Syntax error in $$i"; done

docs: Documentation

clean:
	rm -rf Documentation

Documentation: clean ninja.doxy application/models/*.php application/helpers/*.php
	a=$$(doxygen ninja.doxy 2>&1); \
	if [ -n "$$a" ]; then \
		echo "$$a"; \
		exit 1; \
	fi;

prepare-config:
	mkdir -m 0777 -p /tmp/ninja-test
	mkdir -m 0777 -p /tmp/ninja-test/var
	mkdir -m 0777 -p /tmp/ninja-test/var/archives
	cp test/configs/all-host_service-states/etc/*.cfg /tmp/ninja-test/
	sed -e "s|@@TESTDIR@@|/tmp/ninja-test|" -e "s|@@USER@@|$$(id -un)|" -e "s|@@GROUP@@|$$(id -gn)|" -e "s|@@LIBDIR@@|$$(rpm --eval %{_libdir})|" test/configs/all-host_service-states/etc/nagios.cfg.in > /tmp/ninja-test/nagios.cfg
	sed -e "s|@@TESTDIR@@|/tmp/ninja-test|" -e "s|@@USER@@|$$(id -un)|" -e "s|@@GROUP@@|$$(id -gn)|" -e "s|@@LIBDIR@@|$$(rpm --eval %{_libdir})|" test/configs/all-host_service-states/etc/merlin.conf.in > /tmp/ninja-test/merlin.conf
	cp test/configs/all-host_service-states/var/status.sav /tmp/ninja-test/var/status.sav
	echo "<?php \$$config['nagios_pipe'] = '/tmp/ninja-test/nagios.cmd';\$$config['nagios_base_path'] = '/tmp/ninja-test';\$$config['nagios_etc_path'] = '/tmp/ninja-test';" > application/config/custom/config.php
	echo "<?php \$$config['default']['connection']['database'] = 'merlin_test';" > application/config/custom/database.php
	echo "path: /tmp/ninja-test/live" > $(OP5LIBCFG)/livestatus.yml
	echo "socket_path: /tmp/ninja-test/nagios.qh" > $(OP5LIBCFG)/queryhandler.yml

i18n:
	xgettext --debug --output=application/languages/en/en.po \
		-j --package-name=ninja \
		--from-code utf-8 -L php $$(find . -name '*.php')

install: install-lib install-config install-bin

install-bin:
	mkdir -m 0755 -p $(BINDIR)
	cp install_scripts/op5-manage-users $(BINDIR)

install-lib:
	mkdir -m 0755 -p $(PHPDIR)
	cp -a src/op5 $(PHPDIR)/op5

install-config:
	mkdir -m 770 -p $(SYSCONFDIR)/op5
	cp -R etc/* $(SYSCONFDIR)/op5
	chown -R $(ETC_USER):$(ETC_GROUP) $(SYSCONFDIR)/op5

.PHONY: test clean install install-lib install-config
