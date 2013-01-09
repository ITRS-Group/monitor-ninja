SHELL = /bin/bash
GENERATE_PHP_MODS=

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
	compass compile --boring application/views/themes/default/css/default || (echo "Run make install-sass to install the necessary prerequisites for generating CSS" && exit 1)
	compass compile --boring application/views/themes/default/css/pink_n_fluffy || (echo "Run make install-sass to install the necessary prerequisites for generating CSS" && exit 1)
	compass compile --boring application/views/themes/default/css/classic || (echo "Run make install-sass to install the necessary prerequisites for generating CSS" && exit 1)
	compass compile --boring application/views/themes/default/css/dark || (echo "Run make install-sass to install the necessary prerequisites for generating CSS" && exit 1)

generate-php:
	$(MAKE) -C src/generators

test: test-php-lint test-reports test-unittest

test-reports:
	make test-ci-prepare
	php index.php ninja_unit_test/reports test/unit_test/reports/*.tst; res=$$?; make test-ci-cleanup; exit $$res

test-unittest:
	make test-ci-prepare
	php index.php ninja_unit_test; res=$$?; make test-ci-cleanup; exit $$res

test-ci-cleanup:
	git checkout test/configs/all-host_service-states/var/status.sav || :
	if [ -e test/configs/all-host_service-states/var/merlin.pid ]; then kill $$(cat test/configs/all-host_service-states/var/merlin.pid) || :; fi
	if [ -e /tmp/ninja-test/nagios.cmd ]; then /bin/echo "[$$(date +%s)] SHUTDOWN_PROGRAM" >> /tmp/ninja-test/nagios.cmd; /bin/sleep 5; fi
	rm -rf /tmp/ninja-test
	rm -f application/config/custom/database.php
	rm -rf test/configs/all-host_service-states/var/spool/checkresults # bugs could cause this to become *huge* if we don't do some trimming

test-ci-prepare: test-ci-cleanup prepare-config
	mkdir -m 0777 -p /tmp/ninja-test/
	mkdir -m 0777 -p test/configs/all-host_service-states/var/spool/checkresults
	chmod 777 test/configs/all-host_service-states/var/
	/opt/monitor/op5/merlin/merlind -c test/configs/all-host_service-states/etc/merlin.conf
	/opt/monitor/bin/monitor -d test/configs/all-host_service-states/etc/nagios.cfg
	/bin/sleep 5

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
	cucumber -r test/cucumber/helpers/step_definitions -r test/cucumber/helpers/support -r test/cucumber/local_steps test/cucumber

test-php-lint:
	 for i in `find . -name "*.php"`; do php -l $$i > /dev/null || exit "Syntax error in $$i"; done

docs: Documentation

clean:
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
	@sed -e "s|@@TESTDIR@@|$$(pwd)/test/configs/all-host_service-states|" test/configs/all-host_service-states/etc/nagios.cfg.in > test/configs/all-host_service-states/etc/nagios.cfg
	@sed -e "s|@@TESTDIR@@|$$(pwd)/test/configs/all-host_service-states|" test/configs/all-host_service-states/etc/merlin.conf.in > test/configs/all-host_service-states/etc/merlin.conf
	echo "<?php \$$config['livestatus'] = array('benchmark' => true, 'path' => 'unix:///tmp/ninja-test/live');" > application/config/custom/database.php
	echo "<?php \$$config['nagios_pipe'] = '/tmp/ninja-test/nagios.cmd';" > application/config/custom/config.php

.PHONY: test help test-reports clean
