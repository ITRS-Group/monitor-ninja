GENERATE_PHP_MODS=
OP5LIBCFG=test/configs/all-host_service-states/op5lib

all: generate-css generate-php

help:
	@echo
	@echo Available make targets:
	@echo -----------------------
	@$(MAKE) --print-data-base --question | sed -n -e '/^Makefile/d' -e 's/^\([A-Za-z0-9_-]*\):.*/\1/p'
	@echo

install-sass:
	gem install compass

generate-css:
	which compass || (echo "Run make install-sass to install the necessary prerequisites for generating CSS" && exit 1)
	for skin in application/views/css/*; do \
		compass compile --trace --boring $$skin; \
	done

regenerate-php:
	$(MAKE) -C src/generators regenerate

generate-php:
	$(MAKE) -C src/generators

clean-php:
	$(MAKE) -C src/generators clean

test: test-reports test-unittest

test-reports:
	make test-ci-prepare
	export OP5LIBCFG="$(OP5LIBCFG)"; php index.php ninja_unit_test/reports test/unit_test/reports/*.tst; res=$$?; make test-ci-cleanup; exit $$res

test-unittest: generate-php
	make test-ci-prepare
	export OP5LIBCFG="$(OP5LIBCFG)"; php index.php ninja_unit_test $(UNITTESTARGS); res=$$?; make test-ci-cleanup; exit $$res

test-ci-cleanup:
	rm -f application/config/custom/config.php
	if [ -e /tmp/ninja-test/var/merlin.pid ]; then kill $$(cat /tmp/ninja-test/var/merlin.pid) || :; fi
	if [ -e /tmp/ninja-test/nagios.cmd ]; then /bin/echo "[$$(date +%s)] SHUTDOWN_PROGRAM" >> /tmp/ninja-test/nagios.cmd; /bin/sleep 5; rm /tmp/ninja-test/nagios.cmd; fi

test-ci-prepare: test-ci-cleanup prepare-config
	chmod -R 0777 /tmp/ninja-test/var
	mkdir -m 0777 -p /tmp/ninja-test/var/spool/checkresults
	/usr/bin/merlind -c /tmp/ninja-test/merlin.conf
	/usr/bin/monitor -d /tmp/ninja-test/nagios.cfg
	export OP5LIBCFG="$(OP5LIBCFG)"; install_scripts/ninja_db_init.sh

test-ci: test-ci-prepare
	sh test/ci/testsuite.sh .
	sh test/ci/testsuite.sh . test/ci/limited_tests.txt
	make test-ci-cleanup

test-coverage:
	@make test-ci-prepare &> /dev/null
	@php test/all_coverage.php $$(pwd)

prepare-config-templates:
	mkdir -m 0777 -p test/configs/templates/var/spool/checkresults
	chmod 777 test/configs/templates/var/

test-cucumber: prepare-config-templates
	rm -rf /tmp/ninja-test/
	cucumber
	cucumber -p wip

test-php-lint:
	 for i in `find . -name "*.php"`; do php -l $$i > /dev/null || exit "Syntax error in $$i"; done

docs: Documentation

clean: clean-php
	rm -rf Documentation

Documentation: clean ninja.doxy application/models/*.php application/helpers/*.php
	a=$$(doxygen ninja.doxy 2>&1); \
	if [[ -n $$a ]]; then \
		echo "$$a"; \
		exit 1; \
	fi;

wipe:
	php index.php ninja_unit_test/wipe_tables

prepare-config:
	mkdir -m 0777 -p /tmp/ninja-test
	mkdir -m 0777 -p /tmp/ninja-test/var
	mkdir -m 0777 -p /tmp/ninja-test/var/archives
	cp test/configs/all-host_service-states/etc/*.cfg /tmp/ninja-test/
	sed -e "s|@@TESTDIR@@|/tmp/ninja-test|" -e "s|@@USER@@|$$(id -un)|" -e "s|@@GROUP@@|$$(id -gn)|" -e "s|@@LIBDIR@@|$$(rpm --eval %{_libdir})|" test/configs/all-host_service-states/etc/nagios.cfg.in > /tmp/ninja-test/nagios.cfg
	sed -e "s|@@TESTDIR@@|/tmp/ninja-test|" -e "s|@@USER@@|$$(id -un)|" -e "s|@@GROUP@@|$$(id -gn)|" -e "s|@@LIBDIR@@|$$(rpm --eval %{_libdir})|" test/configs/all-host_service-states/etc/merlin.conf.in > /tmp/ninja-test/merlin.conf
	cp test/configs/all-host_service-states/var/status.sav /tmp/ninja-test/var/status.sav
	echo "<?php \$$config['nagios_pipe'] = '/tmp/ninja-test/nagios.cmd';\$$config['nagios_base_path'] = '/tmp/ninja-test';\$$config['nagios_etc_path'] = '/tmp/ninja-test';" > application/config/custom/config.php
	echo "path: /tmp/ninja-test/live" > $(OP5LIBCFG)/livestatus.yml

i18n:
	xgettext --debug --output=application/languages/en/en.po \
		-j --package-name=ninja \
		--from-code utf-8 -L php $$(find . -name '*.php')

.PHONY: test help test-reports clean
