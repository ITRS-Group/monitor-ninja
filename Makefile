test: test-php-lint test-reports test-unittest

test-reports:
	php index.php ninja_unit_test/reports modules/unit_test/reports/*.tst

test-unittest: test-ci-prepare
	php index.php ninja_unit_test
	make test-ci-cleanup

test-ci-cleanup:
	git checkout test/configs/all-host_service-states/var/status.sav || :
	if [ -f test/configs/all-host_service-states/var/merlin.pid ]; then kill $$(cat test/configs/all-host_service-states/var/merlin.pid); rm test/configs/all-host_service-states/var/merlin.pid; fi
	if [ -f /tmp/ninja-test/nagios.cmd ]; then /bin/echo "[$$(date +%s)] SHUTDOWN_PROGRAM" >> /tmp/ninja-test/nagios.cmd; fi
	/bin/sleep 5 # give nagios some time to read
	rm -rf /tmp/ninja-test # 'pparently, sockets can't always be created otherwise. Weird.
	rm -f application/config/custom/config.php
	rm -rf test/configs/all-host_service-states/var/spool/checkresults # bugs could cause this to become *huge* if we don't do some trimming

test-ci-prepare: test-ci-cleanup prepare-config
	mkdir -m 0777 -p /tmp/ninja-test/
	mkdir -m 0777 -p test/configs/all-host_service-states/var/spool/checkresults
	chmod 777 test/configs/all-host_service-states/var/
	/opt/monitor/op5/merlin/merlind -c test/configs/all-host_service-states/etc/merlin.conf
	/opt/monitor/bin/monitor -d test/configs/all-host_service-states/etc/nagios.cfg
	php index.php 'cli/insert_user_data'
	sed -e 's#/opt/monitor/var/rw/live#/tmp/ninja-test/live#' application/config/config.php > application/config/custom/config.php
	/bin/sleep 5

test-ci: test-ci-prepare
	sh test/ci/testsuite.sh .
	sh test/ci/testsuite.sh . test/ci/limited_tests.txt
	make test-ci-cleanup

test-coverage:
	@make test-ci-prepare &> /dev/null
	@php test/all_coverage.php $$(pwd)

test-cucumber:
	HEADLESS=1 cucumber -f Cucumber::Formatter::Nagios -r test/cucumber/helpers/step_definitions -r test/cucumber/helpers/support -r test/cucumber/local_steps test/cucumber

test-php-lint:
	 for i in `find . -name "*.php"`; do php -l $$i || exit "Syntax error in $$i"; done

docs: Documentation

clean:
	rm -rf Documentation

Documentation: clean ninja.doxy application/models/*.php application/helpers/*.php
	a=$$(doxygen ninja.doxy 2>&1); \
	if [[ -n $$a ]]; then \
		echo "$$a"; \
		exit 1; \
	fi;

help:
	@echo
	@echo Available make targets:
	@echo -----------------------
	@$(MAKE) --print-data-base --question | sed -n -e '/^Makefile/d' -e 's/^\([A-Za-z0-9_-]*\):.*/\1/p'
	@echo

wipe:
	php index.php ninja_unit_test/wipe_tables

prepare-config:
	@sed -e "s|@@TESTDIR@@|$$(pwd)/test/configs/all-host_service-states|" test/configs/all-host_service-states/etc/nagios.cfg.in > test/configs/all-host_service-states/etc/nagios.cfg
	@sed -e "s|@@TESTDIR@@|$$(pwd)/test/configs/all-host_service-states|" test/configs/all-host_service-states/etc/merlin.conf.in > test/configs/all-host_service-states/etc/merlin.conf

.PHONY: test help test-reports clean
